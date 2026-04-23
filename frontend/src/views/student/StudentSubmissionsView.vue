<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="submissions-container">

        <!-- Left Sidebar: Briefs List -->
        <aside class="briefs-sidebar">
          <div class="sidebar-header">
            <h2>Mes Missions</h2>
            <span class="sidebar-count">{{ briefs.length }} projets assignés</span>
          </div>

          <div class="sidebar-search">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input
              v-model="searchBriefs"
              type="text"
              placeholder="Rechercher une mission..."
              class="search-input"
            />
          </div>

          <div class="briefs-list">
            <div
              v-for="brief in filteredBriefs"
              :key="brief.id"
              class="brief-item"
              :class="{ 'brief-item--active': selectedBrief?.id === brief.id }"
              @click="selectBrief(brief)"
            >
              <div class="brief-icon" :class="{ 'brief-icon--active': selectedBrief?.id === brief.id }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path v-if="brief.modality === 'Individuel'"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  <path v-else
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>

              <div class="brief-info">
                <p class="brief-name">{{ brief.title }}</p>
                <div class="brief-tags">
                  <span class="modality-pill" :class="brief.modality === 'Individuel' ? 'modality-pill--blue' : 'modality-pill--cyan'">
                    {{ brief.modality }}
                  </span>
                  <span v-if="getSubmissionStatus(brief.id)" class="submitted-pill">✓ Soumis</span>
                  <span v-if="brief.has_quiz" class="quiz-pill" title="Possède un quiz">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0012 18.75c-1.03 0-1.9-.4-2.59-1.177l-.547-.547z"/>
                    </svg>
                  </span>
                </div>
              </div>
            </div>

            <div v-if="filteredBriefs.length === 0" class="sidebar-empty">
              Aucun brief trouvé
            </div>
          </div>
        </aside>

        <!-- Right Content -->
        <section class="submission-content">

          <!-- Loading -->
          <div v-if="isLoading" class="state-center animate-in">
            
            <p>Chargement en cours...</p>
          </div>

          <!-- No Selection -->
          <div v-else-if="!selectedBrief" class="state-center animate-in">
            <div class="state-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h2>Sélectionnez un projet</h2>
            <p>Cliquez sur l'un de vos livrables à gauche pour le visualiser ou le soumettre.</p>
          </div>

          <!-- Brief Content -->
          <div v-else class="brief-details animate-in">

            <!-- Header -->
            <header class="details-header">
              <div class="header-text">
                <h1>{{ selectedBrief.title }}</h1>
                <p>{{ selectedBrief.description }}</p>
              </div>
              <div class="header-badges">
                <span class="badge" :class="`badge--${selectedBrief.difficulty_level?.toLowerCase()}`">
                  {{ selectedBrief.difficulty_level }}
                </span>
                <span class="badge" :class="selectedBrief.modality === 'Individuel' ? 'badge--blue' : 'badge--cyan'">
                  {{ selectedBrief.modality }}
                </span>
              </div>
            </header>

            <!-- Quiz area -->
            <div v-if="selectedBrief.has_quiz && currentSubmission" class="quiz-card animate-in">
              <div class="quiz-visual" :class="{ 'quiz-visual--success': quizResult?.status === 'VALIDATED' || quizResult?.status === 'REJECTED_LIVRABLE', 'quiz-visual--fail': quizResult?.status === 'REJECTED_QUIZ' || quizResult?.status === 'REJECTED' }">
                <div class="quiz-icon-glow"></div>
                <svg v-if="!quizResult" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0012 18.75c-1.03 0-1.9-.4-2.59-1.177l-.547-.547z"/>
                </svg>
                <svg v-else-if="quizResult.status === 'VALIDATED' || quizResult.status === 'REJECTED_LIVRABLE'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                  <path d="M20 6L9 17l-5-5"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </div>
              <div class="quiz-body">
                <h3>Certification par l'Intelligence Artificielle</h3>
                
                <div v-if="isQuizLoading" class="quiz-loading-placeholder">
                  
                  
                </div>

                <div v-else-if="quizResult && ['VALIDATED', 'REJECTED_QUIZ', 'REJECTED', 'REJECTED_LIVRABLE'].includes(quizResult.status)" class="quiz-result-content">
                  <div class="result-main">
                    <div class="result-status">
                      <span class="status-label" :class="{ 
                        'status-label--pass': quizResult.status === 'VALIDATED' || quizResult.status === 'REJECTED_LIVRABLE', 
                        'status-label--fail': ['REJECTED_QUIZ', 'REJECTED'].includes(quizResult.status)
                      }">
                        {{ (quizResult.status === 'VALIDATED' || quizResult.status === 'REJECTED_LIVRABLE') ? 'Quiz Validé' : 'Tentative insuffisante' }}
                      </span>
                      <p class="status-desc">
                        {{ (quizResult.status === 'VALIDATED' || quizResult.status === 'REJECTED_LIVRABLE') ? 'Vos compétences théoriques ont été certifiées par l\'IA.' : 'Le seuil de réussite n\'a pas été atteint, révisez vos concepts.' }}
                      </p>
                    </div>
                    <div class="result-score">
                      <span class="score-num">{{ quizResult.score !== null ? quizResult.score : '--' }}</span>
                      <span class="score-total">/ 20</span>
                    </div>
                  </div>
                </div>

                <div v-else class="quiz-action-area">
                  <p class="quiz-promo">Votre Github a été rattaché. Vous pouvez maintenant passer l'évaluation théorique AI associée à ce projet.</p>
                  <button @click="startQuiz(selectedBrief.id)" class="btn-nadi-gold">
                    Démarrer l'Évaluation
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <div v-else-if="selectedBrief.has_quiz && !currentSubmission" class="quiz-hint animate-in">
              <div class="hint-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <span>Commencez par soumettre l'URL de votre projet pour déverrouiller le module d'intelligence artificielle.</span>
            </div>

            <!-- Form Section -->
            <div class="form-section animate-in mt-4">
              <h2 class="section-heading">{{ currentSubmission ? 'Dossier de Rendu' : 'Formulaire de Soumission' }}</h2>

              <!-- Submit Form -->
              <form v-if="!currentSubmission" @submit.prevent="submitLivrable" class="submission-form">
                <div class="form-group">
                  <label for="github-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/>
                    </svg>
                    URL du Projet Universel (GitHub, GitLab, Vercel...)
                  </label>
                  <input
                    id="github-link"
                    v-model="form.link"
                    type="url"
                    placeholder="https://github.com/username/projet"
                    class="nadi-input"
                    required
                  />
                </div>

                <div class="form-group">
                  <label for="message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    Note au formateur (Optionnel)
                  </label>
                  <textarea
                    id="message"
                    v-model="form.message"
                    placeholder="Détaillez les choix d'architecture ou les difficultés rencontrées..."
                    class="nadi-textarea"
                    rows="4"
                  ></textarea>
                </div>

                <button
                  type="submit"
                  class="btn-nadi-primary submit-btn-long"
                  :disabled="isSubmitting || !form.link"
                >
                  <svg v-if="!isSubmitting" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
                  <div v-else class="spinner-sm"></div>
                  {{ isSubmitting ? 'Traitement en cours...' : 'Valider mon livrable' }}
                </button>
              </form>

              <!-- Already Submitted View -->
              <div v-else class="submitted-view">
                <div class="submitted-banner">
                  <div class="submitted-status">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Dossier scellé
                  </div>
                  <span class="submitted-date">Envoyé le {{ formatDate(currentSubmission.created_at) }}</span>
                </div>

                <div class="submission-card-detail">
                  <div class="submission-field">
                    <span class="field-lbl">Point d'ancrage du projet</span>
                    <a :href="currentSubmission.link" target="_blank" class="submission-link-box">
                      <span class="truncate">{{ currentSubmission.link }}</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                      </svg>
                    </a>
                  </div>

                  <div class="submission-field mt-3">
                    <span class="field-lbl">État du formateur / IA</span>
                    <span class="status-pill-large" :class="`status-pill-large--${currentSubmission.status.toLowerCase()}`">
                      <span class="status-dot"></span>
                      {{ currentSubmission.status === 'SUBMITTED' ? 'EN ATTENTE D\'INSPECTION' : currentSubmission.status }}
                    </span>
                  </div>

                  <div v-if="currentSubmission.formateur_message" class="submission-field mt-3">
                    <span class="field-lbl">Feedback du formateur</span>
                    <div class="feedback-bubble">
                      {{ currentSubmission.formateur_message }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Success Toast -->
              <Transition name="toast">
                <div v-if="submitSuccess" class="success-toast">
                  <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                  </svg>
                  Livrable archivé dans la base avec succès !
                </div>
              </Transition>

            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

// --- VARIABLES D'ÉTAT (REFS) ---
const router              = useRouter();
const user                = ref(null); // Utilisateur connecté
const briefs              = ref([]); // Liste des projets assignés
const selectedBrief       = ref(null); // Le projet actuellement sélectionné dans la barre latérale
const previousSubmissions = ref([]); // Historique des rendus de l'étudiant
const isLoading           = ref(true); // Chargement des données au démarrage
const isSubmitting        = ref(false); // État lors de l'envoi d'un nouveau livrable
const submitSuccess       = ref(false); // Affichage du message de succès
const searchBriefs        = ref(''); // Texte de recherche pour les projets
const quizResult          = ref(null); // Résultat de l'IA pour le quiz
const isQuizLoading       = ref(false); // Chargement du statut du quiz

// Formulaire de rendu
const form = ref({ link: '', message: '' });

// --- LOGIQUE CALCULÉE (COMPUTED) ---

// Filtre la liste des projets à gauche selon la recherche
const filteredBriefs = computed(() => {
  const query = searchBriefs.value.toLowerCase().trim();
  if (!query) return briefs.value;
  return briefs.value.filter(b =>
    b.title.toLowerCase().includes(query) ||
    b.description?.toLowerCase().includes(query)
  );
});

// Trouve si le projet sélectionné a déjà été rendu (soumis)
const currentSubmission = computed(() =>
  selectedBrief.value
    ? previousSubmissions.value.find(s => s.brief_id === selectedBrief.value.id) ?? null
    : null
);

// --- ACTIONS (MÉTHODES) ---

// 1. Sélectionner un projet et charger ses infos (soumission + quiz)
const selectBrief = (brief) => {
  selectedBrief.value = brief;
  form.value          = { link: '', message: '' }; // Réinitialise le formulaire
  submitSuccess.value = false;
  quizResult.value    = null;
  
  // On vérifie si ce projet a déjà été rendu et quel est le statut du quiz
  loadPreviousSubmissions();
  checkQuizStatus(brief.id);
};

// 2. Vérifier si l'IA a validé le quiz pour ce projet
const checkQuizStatus = async (briefId) => {
  isQuizLoading.value = true;
  
  const res = await api.get(`/quizzes/briefs/${briefId}/validate`);
  const status = res.data.status;
  
  // Si le quiz est terminé (peu importe le score), on récupère le résultat
  if (status && (status.is_completed || status.status !== 'PENDING_QUIZ')) {
    quizResult.value = status;
  } else {
    quizResult.value = null;
  }
  
  isQuizLoading.value = false;
};

// 3. Charger la liste des projets
const loadBriefs = async () => {
  // Cache
  const cached = localStorage.getItem('student_submissions_briefs_cache');
  if (cached) {
    briefs.value = JSON.parse(cached);
    if (briefs.value.length > 0 && !selectedBrief.value) selectBrief(briefs.value[0]);
  }

  try {
    const res = await api.get('/briefs');
    briefs.value = res.data?.data || [];
    localStorage.setItem('student_submissions_briefs_cache', JSON.stringify(briefs.value));
    
    if (briefs.value.length > 0 && !selectedBrief.value) selectBrief(briefs.value[0]);
  } catch (err) {
    console.error("Erreur Briefs:", err);
  }
};

// 4. Charger l'historique des rendus de l'étudiant
const loadPreviousSubmissions = async () => {
  // Cache
  const cached = localStorage.getItem('student_submissions_list_cache');
  if (cached) {
    previousSubmissions.value = JSON.parse(cached);
  }

  try {
    const res = await api.get('/livrables');
    previousSubmissions.value = res.data?.data || [];
    localStorage.setItem('student_submissions_list_cache', JSON.stringify(previousSubmissions.value));
  } catch (err) {
    console.error("Erreur Submissions:", err);
  }
};

// 5. Envoyer (Soumettre) un nouveau livrable
const submitLivrable = async () => {
  if (!form.value.link || !selectedBrief.value) return;

  const studentId = parseInt(user.value?.id);
  if (isNaN(studentId)) return alert('Utilisateur non identifié.');

  isSubmitting.value = true;
  
  await api.post('/livrables', {
    brief_id:   parseInt(selectedBrief.value.id),
    student_id: studentId,
    link:       form.value.link,
    message:    form.value.message,
  });

  form.value = { link: '', message: '' };
  submitSuccess.value = true;
  setTimeout(() => { submitSuccess.value = false; }, 3500);
  
  // Rafraîchir les données pour montrer que c'est soumis
  loadPreviousSubmissions();
  isSubmitting.value = false;
};

// 6. Lancer le quiz AI
const startQuiz = async (briefId) => {
  const res = await api.get(`/quizzes/briefs/${briefId}/session`);
  const session = res.data?.data;
  
  if (session?.id) {
    router.push(`/student/quiz/${session.id}?briefId=${briefId}`);
  } else {
    alert('Impossible de lancer le quiz pour le moment.');
  }
};

// --- HELPERS ---
const getSubmissionStatus = (briefId) => previousSubmissions.value.some(s => s.brief_id === briefId);
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

// --- CYCLE DE VIE ---
onMounted(async () => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  
  isLoading.value = true;
  // On charge les données (plus simple que Promise.all pour un débutant)
  await loadBriefs();
  await loadPreviousSubmissions();
  isLoading.value = false;
});
</script>

<style scoped>
/* ─── Reset ─────────────────────────────────────────────────────────────────── */
* { box-sizing: border-box; }

/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #e6edf3; font-family: 'Inter', system-ui, sans-serif;
}
.main { flex: 1; overflow: hidden; display: flex; }
.submissions-container { display: flex; width: 100%; height: 100%; overflow: hidden; }

/* ─── Left sidebar ───────────────────────────────────────────────────────────── */
.briefs-sidebar {
  width: 320px; flex-shrink: 0; display: flex; flex-direction: column;
  background: #010409; border-right: 1px solid rgba(255,255,255,0.06); overflow: hidden;
  position: relative; z-index: 10;
}
.sidebar-header {
  padding: 30px 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); flex-shrink: 0;
}
.sidebar-header h2 { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 4px; letter-spacing: -0.01em; }
.sidebar-count { font-size: 12px; color: #8b949e; font-weight: 600; }

.sidebar-search {
  display: flex; align-items: center; gap: 10px; margin: 16px 20px 10px; padding: 10px 16px;
  background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;
  flex-shrink: 0; transition: border-color 0.2s, background 0.2s;
}
.sidebar-search:focus-within { border-color: rgba(56,139,253,0.5); background: rgba(56,139,253,0.03); }
.search-icon { width: 16px; height: 16px; stroke: #8b949e; flex-shrink: 0; }
.search-input { flex: 1; background: transparent; border: none; outline: none; font-size: 13px; color: #fff; }
.search-input::placeholder { color: #484f58; font-weight: 500; }

.briefs-list { flex: 1; overflow-y: auto; scrollbar-width: none; padding: 8px 12px; }
.briefs-list::-webkit-scrollbar { display: none; }

.brief-item {
  display: flex; align-items: center; gap: 14px; padding: 16px; margin-bottom: 6px;
  border-radius: 12px; cursor: pointer; border: 1px solid transparent;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.brief-item:hover { background: rgba(255,255,255,0.03); }
.brief-item--active { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

.brief-icon {
  width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: all 0.2s; color: #8b949e;
}
.brief-icon--active { background: rgba(56,139,253,0.1); border-color: rgba(56,139,253,0.3); color: #79c0ff; box-shadow: 0 0 15px rgba(56,139,253,0.15); }
.brief-icon svg { width: 18px; height: 18px; }

.brief-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px;}
.brief-name { font-size: 14px; font-weight: 700; color: #c9d1d9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.2s;}
.brief-item--active .brief-name { color: #fff; }
.brief-tags { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.modality-pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
.modality-pill--blue { background: rgba(56,139,253,0.1); color: #79c0ff; border: 1px solid rgba(56,139,253,0.2); }
.modality-pill--cyan { background: rgba(34,211,238,0.1); color: #22d3ee; border: 1px solid rgba(34,211,238,0.2); }

.submitted-pill { font-size: 10px; font-weight: 800; color: #56d364; background: rgba(35,134,54,0.1); border: 1px solid rgba(35,134,54,0.2); padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.1em;}
.quiz-pill { display: flex; align-items: center; color: #e3b341; background: rgba(210,153,34,0.1); padding: 4px; border-radius: 6px; border: 1px solid rgba(210,153,34,0.2);}
.quiz-pill svg { width: 12px; height: 12px; stroke-width: 2.5;}

.sidebar-empty { padding: 40px 20px; font-size: 13px; color: #484f58; text-align: center; }

/* ─── Right content ──────────────────────────────────────────────────────────── */
.submission-content {
  flex: 1; overflow-y: auto; background: #010409;
  scrollbar-width: thin; scrollbar-color: #21262d transparent; padding-top: 20px;
}
.submission-content::-webkit-scrollbar { width: 5px; }
.submission-content::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }

/* ─── State screens ──────────────────────────────────────────────────────────── */
.state-center {
  height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 16px; text-align: center; color: #484f58;
}
.spinner {
  width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.05);
  border-top-color: #79c0ff; border-radius: 50%; animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
.spinner-sm {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.2);
  border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite;
}
.state-icon {
  width: 64px; height: 64px; border-radius: 16px; background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; margin-bottom: 8px;
}
.state-icon svg { width: 28px; height: 28px; stroke: #8b949e; }
.state-center h2 { font-size: 20px; font-weight: 700; color: #e6edf3; letter-spacing: -0.01em;}
.state-center p  { font-size: 14px; color: #8b949e; max-width: 400px; line-height: 1.6;}

/* ─── Brief details wrapper ─────────────────────────────────────────────────── */
.brief-details {
  padding: 40px; max-width: 900px; margin: 0 auto; padding-bottom: 120px;
}

.details-header {
  display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;
  padding-bottom: 32px; border-bottom: 1px solid rgba(255,255,255,0.06); margin-bottom: 40px;
}
.header-text h1 { font-size: 32px; font-weight: 800; color: #fff; margin-bottom: 12px; letter-spacing: -0.02em; }
.header-text p { font-size: 15px; color: #8b949e; line-height: 1.7; max-width: 600px; }
.header-badges { display: flex; gap: 8px; flex-shrink: 0; }
.badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; background: rgba(255,255,255,0.05); color: #c9d1d9; border: 1px solid rgba(255,255,255,0.1); }
.badge--senior  { background: rgba(248,81,73,0.1); color: #ff7b72; border-color: rgba(248,81,73,0.2); }
.badge--junior  { background: rgba(63,185,80,0.1); color: #56d364; border-color: rgba(63,185,80,0.2); }
.badge--blue    { background: rgba(56,139,253,0.1); color: #79c0ff; border-color: rgba(56,139,253,0.2); }
.badge--cyan    { background: rgba(34,211,238,0.1); color: #22d3ee; border-color: rgba(34,211,238,0.2); }

/* ─── AI Quiz Card (Nadi Premium) ───────────────────────────────────────────── */
.quiz-card {
  background: rgba(210,153,34,0.03); border: 1px solid rgba(210,153,34,0.2);
  border-radius: 16px; padding: 32px; display: flex; gap: 24px; margin-bottom: 40px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; overflow: hidden;
}
.quiz-card::before {
  content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #d29922;
}

.quiz-visual {
  width: 64px; height: 64px; border-radius: 16px; background: rgba(210,153,34,0.1);
  border: 1px solid rgba(210,153,34,0.3); display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #e3b341; position: relative;
}
.quiz-icon-glow { position: absolute; inset: -10px; background: radial-gradient(circle, rgba(210,153,34,0.2), transparent 70%); z-index: 0;}
.quiz-visual svg { width: 30px; height: 30px; z-index: 1; position: relative;}
.quiz-visual--success { background: rgba(63, 185, 80, 0.1); border-color: rgba(63, 185, 80, 0.3); color: #56d364; }
.quiz-visual--success .quiz-icon-glow { background: radial-gradient(circle, rgba(63, 185, 80, 0.2), transparent 70%); }
.quiz-visual--fail    { background: rgba(248, 81, 73, 0.1); border-color: rgba(248, 81, 73, 0.3); color: #ff7b72; }
.quiz-visual--fail .quiz-icon-glow { background: radial-gradient(circle, rgba(248, 81, 73, 0.2), transparent 70%); }

.quiz-body { flex: 1; z-index: 1;}
.quiz-body h3 { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 8px; letter-spacing: -0.01em;}
.quiz-promo { font-size: 14px; color: #8b949e; margin-bottom: 20px; line-height: 1.6;}

.btn-nadi-gold {
  display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px;
  background: #d29922; color: #010409; font-size: 14px; font-weight: 800; border: none;
  border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(210,153,34,0.3);
}
.btn-nadi-gold:hover { background: #e3b341; box-shadow: 0 8px 25px rgba(210,153,34,0.4); transform: translateY(-2px); }
.btn-nadi-gold svg { width: 18px; height: 18px; }

.quiz-hint {
  display: flex; align-items: center; gap: 16px; padding: 20px 24px;
  background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);
  border-radius: 12px; margin-bottom: 40px; color: #8b949e; font-size: 14px; line-height: 1.5; font-weight: 500;
}
.hint-icon { width: 40px; height: 40px; background: rgba(255,255,255,0.05); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #8b949e; }
.hint-icon svg { width: 20px; height: 20px; }

.quiz-result-content { margin-top: 16px; }
.result-main {
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(1, 4, 9, 0.4); padding: 20px 24px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);
}
.status-label {
  display: inline-block; font-size: 11px; font-weight: 800; text-transform: uppercase;
  padding: 4px 10px; border-radius: 6px; margin-bottom: 8px; letter-spacing: 0.1em; border: 1px solid transparent;
}
.status-label--pass { background: rgba(86,211,100,0.1); color: #56d364; border-color: rgba(86,211,100,0.2); }
.status-label--fail { background: rgba(248,81,73,0.1); color: #ff7b72; border-color: rgba(248,81,73,0.2); }
.status-desc { font-size: 13px; color: #8b949e; margin: 0; }

.result-score { text-align: right; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 24px; }
.score-num   { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; }
.score-total { font-size: 14px; color: #484f58; font-weight: 700; margin-left: 4px; }

/* ─── Form Section (Nadi Premium) ────────────────────────────────────────── */
.form-section { display: flex; flex-direction: column; gap: 24px; }
.section-heading { font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }

.submission-form {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 16px; padding: 32px; display: flex; flex-direction: column; gap: 24px;
}
.form-group { display: flex; flex-direction: column; gap: 10px; }
.form-group label {
  display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700;
  color: #8b949e; text-transform: uppercase; letter-spacing: 0.08em;
}
.form-group label svg { width: 14px; height: 14px; }

.nadi-input, .nadi-textarea {
  background: #010409; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  padding: 14px 18px; font-size: 14px; color: #fff; outline: none; font-family: 'Inter', sans-serif;
  transition: all 0.2s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
.nadi-textarea { resize: vertical; line-height: 1.6; }
.nadi-input::placeholder, .nadi-textarea::placeholder { color: #484f58; font-weight: 500;}
.nadi-input:focus, .nadi-textarea:focus {
  border-color: #388bfd; box-shadow: 0 0 0 4px rgba(56, 139, 253, 0.15), inset 0 2px 4px rgba(0,0,0,0.2);
}

.submit-btn-long {
  margin-top: 10px; padding: 16px; font-size: 15px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; gap: 12px;
}
.btn-nadi-primary {
  background: #238636; color: white; border: 1px solid #2ea043; font-weight: 700;
  cursor: pointer; box-shadow: 0 4px 15px rgba(35, 134, 54, 0.2); transition: all 0.2s;
}
.btn-nadi-primary:hover:not(:disabled) { background: #2ea043; border-color: #3fb950; box-shadow: 0 8px 20px rgba(35, 134, 54, 0.3); transform: translateY(-1px); }
.btn-nadi-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-nadi-primary svg { width: 20px; height: 20px; }

/* Already Submitted View */
.submitted-view { display: flex; flex-direction: column; gap: 20px; }
.submitted-banner {
  display: flex; align-items: center; justify-content: space-between; padding: 20px 24px;
  background: rgba(35, 134, 54, 0.05); border: 1px solid rgba(35, 134, 54, 0.2); border-radius: 12px;
}
.submitted-status { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 800; color: #56d364; }
.submitted-status svg { width: 22px; height: 22px; }
.submitted-date { font-size: 12px; color: #8b949e; font-weight: 600; }

.submission-card-detail {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 16px; padding: 28px;
}
.field-lbl { display: block; font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; }
.submission-link-box {
  display: flex; align-items: center; justify-content: space-between; padding: 14px 18px;
  background: #010409; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  text-decoration: none; transition: border-color 0.2s;
}
.submission-link-box:hover { border-color: #388bfd; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; font-weight: 600; color: #79c0ff; font-family: 'JetBrains Mono', monospace;}
.submission-link-box svg { width: 18px; height: 18px; stroke: #8b949e; flex-shrink: 0; }

.status-pill-large {
  display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; border-radius: 10px;
  font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; border: 1px solid transparent;
}
.status-dot { width: 8px; height: 8px; border-radius: 50%; }
.status-pill-large--submitted { background: rgba(56,139,253,0.1); color: #79c0ff; border-color: rgba(56,139,253,0.2); }
.status-pill-large--submitted .status-dot { background: #79c0ff; animation: pulse-blue 2s infinite; }
.status-pill-large--validated { background: rgba(63,185,80,0.1); color: #56d364; border-color: rgba(63,185,80,0.2); }
.status-pill-large--validated .status-dot { background: #56d364; }
.status-pill-large--rejected { background: rgba(248,81,73,0.1); color: #ff7b72; border-color: rgba(248,81,73,0.2); }
.status-pill-large--rejected .status-dot { background: #ff7b72; }
.feedback-bubble {
  background: rgba(56, 139, 253, 0.05);
  border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 12px;
  padding: 16px;
  font-size: 14px;
  line-height: 1.6;
  color: #c9d1d9;
}

/* Toasts */
.success-toast {
  display: flex; align-items: center; gap: 12px; padding: 16px 24px;
  background: #238636; color: white; border-radius: 12px; border: 1px solid #2ea043;
  font-size: 14px; font-weight: 700; box-shadow: 0 10px 30px rgba(35, 134, 54, 0.3); margin-top: 16px;
}
.success-toast svg { width: 22px; height: 22px; }

/* ─── Animations ────────────────────────────────────────────────────────────── */

@keyframes pulse-blue { 0%, 100% { opacity: 1; box-shadow: 0 0 8px #79c0ff; } 50% { opacity: 0.5; box-shadow: 0 0 2px #79c0ff; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.mt-4 { margin-top: 24px; }
.mt-3 { margin-top: 16px; }

.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-10px); }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .submissions-container { flex-direction: column; }
  .briefs-sidebar { width: 100%; height: 350px; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
  .brief-details { padding: 24px; }
}
</style>


