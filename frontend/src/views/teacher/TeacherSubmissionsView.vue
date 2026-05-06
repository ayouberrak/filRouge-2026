<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">

      <!-- Topbar Elite -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Rendus &amp; Évaluations</h1>
            <p class="topbar-sub">Console de Correction Elite</p>
          </div>
        </div>
        <div class="topbar-stats">
          <div class="stat-chip stat-chip--amber">
            <span class="stat-dot stat-dot--amber"></span>
            {{ pendingCount }} en attente
          </div>
          <div class="stat-chip stat-chip--green">
            <span class="stat-dot stat-dot--green"></span>
            {{ validatedCount }} validés
          </div>
        </div>
      </header>

      <!-- Explorer layout -->
      <div class="explorer">

        <!-- Pane 1: Briefs List -->
        <aside class="pane pane--briefs">
          <div class="pane-header">
            <span class="pane-label">Projets Actifs</span>
            <span class="pane-count">{{ briefs.length }}</span>
          </div>
          <div v-if="isBriefsLoading" class="pane-loading">
            <div class="pulse-sm"></div>
          </div>
          <div v-else class="pane-list">
            <div
              v-for="brief in briefs"
              :key="brief.id"
              class="brief-item"
              :class="{ 'brief-item--active': selectedBriefId === brief.id }"
              @click="handleSelectBrief(brief.id)"
            >
              <div class="brief-item-body">
                <span class="brief-title">{{ brief.title }}</span>
                <div class="brief-meta">
                  <span class="brief-type">{{ brief.difficulty }}</span>
                  <span class="brief-count">#{{ brief.id }}</span>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Pane 2: Students List -->
        <aside class="pane pane--students">
          <div class="pane-header">
            <span class="pane-label">{{ selectedBriefTitle || 'Étudiants' }}</span>
          </div>
          <div v-if="isSubmissionsLoading" class="pane-loading">
            <div class="pulse-sm"></div>
          </div>
          <div v-else class="pane-list">
            <template v-if="selectedBriefId">
              <div
                v-for="student in students"
                :key="student.id"
                class="student-item"
                :class="{
                  'student-item--active': selectedStudentId === student.id,
                  'student-item--no-sub': !student.submission,
                }"
                @click="selectStudent(student)"
              >
                <img 
                  class="student-avatar" 
                  :src="student.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(student.name)}&background=random&color=fff`" 
                  @error="e => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(student.name)}&background=random&color=fff`"
                />
                <div class="student-body">
                  <div class="student-row">
                    <span class="student-name">{{ student.name }}</span>
                    <span class="status-badge" :class="statusClass(student)">{{ statusLabel(student) }}</span>
                  </div>

                </div>
              </div>
            </template>
            <div v-else class="pane-empty">Sélectionnez un projet pour voir les rendus</div>
          </div>
        </aside>

        <!-- Pane 3: Review Desk -->
        <section class="review-pane">

          <div v-if="selectedStudent?.submission" class="review-scroller animate-nadi-in">
            <div class="review-content">

              <!-- Review Header (Dashboard Style) -->
              <div class="review-id-card status-dashboard">
                <div class="rev-student">
                  <img 
                    :src="selectedStudent.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedStudent.name)}&background=random&color=fff`" 
                    @error="e => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedStudent.name)}&background=random&color=fff`"
                    class="rev-avatar" 
                  />
                  <div class="rev-info">
                    <h2 class="rev-name">{{ selectedStudent.name }}</h2>
                    <span class="rev-date">Soumis {{ selectedStudent.submission.date || "Récemment" }}</span>
                  </div>
                </div>

                <div class="dashboard-stats">
                  <!-- Project Column -->
                  <div class="d-col">
                    <span class="d-label">Projet (Git)</span>
                    <div class="d-status" :class="getProjectStatusClass(selectedStudent)">
                      {{ getProjectStatusLabel(selectedStudent) }}
                    </div>
                  </div>

                  <!-- Final Verdict Column -->
                  <div class="d-col">
                    <span class="d-label">Verdict Global</span>
                    <div class="d-verdict" :class="getOverallStatusClass(selectedStudent)">
                      {{ getOverallStatusLabel(selectedStudent) }}
                    </div>
                  </div>
                </div>
              </div>


              <!-- ======= TAB CONTENT ======= -->
              <div class="tab-content">

                <!-- GitHub Repository -->
                <div class="review-section">
                  <div class="s-label-row">
                    <svg class="s-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    <span class="s-label">Dépôt GitHub</span>
                  </div>
                  <a :href="selectedStudent.submission.url" target="_blank" class="repo-card">
                    <div class="repo-card-left">
                      <span class="repo-url">{{ selectedStudent.submission.url }}</span>
                      <span class="repo-link-label">Ouvrir dans GitHub ↗</span>
                    </div>
                    <div class="repo-card-right">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                    </div>
                  </a>
                </div>

                <!-- Student Message -->
                <div class="review-section">
                  <div class="s-label-row">
                    <svg class="s-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span class="s-label">Message de l'étudiant</span>
                  </div>
                  <div v-if="selectedStudent.submission.message" class="student-message-card">
                    <img :src="selectedStudent.avatar" class="msg-avatar" />
                    <div class="student-message-body">
                      <span class="msg-author">{{ selectedStudent.name }}</span>
                      <p class="msg-text">{{ selectedStudent.submission.message }}</p>
                    </div>
                  </div>
                  <div v-else class="message-bubble empty-msg">Aucun message de la part de l'étudiant.</div>
                </div>

                <!-- Feedback History -->
                <div class="review-section">
                  <div class="s-label-row">
                    <svg class="s-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="s-label">Message du Formateur</span>
                  </div>
                  <div v-if="selectedStudent.submission.formateur_message" class="history-list">
                    <div class="message-bubble formateur-msg-bubble">
                      <p class="bubble-text">{{ selectedStudent.submission.formateur_message }}</p>
                    </div>
                  </div>
                  <div v-else class="message-bubble empty-msg">Aucun message de votre part.</div>
                </div>

                <!-- Coach Evaluation or Validated State -->
                <div class="review-section">
                  <div v-if="selectedStudent.submission.status !== 'VALIDE' && selectedStudent.submission.status !== 'VALIDATED'">
                    <div class="s-label-row">
                      <svg class="s-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      <span class="s-label">Évaluer ce Rendu</span>
                    </div>
                    <div class="feedback-area">
                      <textarea v-model="feedback" placeholder="Saisissez vos observations pour l'étudiant..." class="feedback-input"></textarea>
                      <div class="evaluation-actions">
                        <button class="btn-reject" @click="saveReview('INVALID')" :disabled="isSaving">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                          Demander Retravail
                        </button>
                        <button class="btn-validate" @click="saveReview('VALIDE')" :disabled="isSaving">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                          {{ isSaving ? 'Sauvegarde...' : 'Valider ce Rendu' }}
                        </button>
                      </div>
                    </div>
                  </div>
                  <div v-else class="validated-banner animate-nadi-in">
                    <span class="validated-icon">🏆</span>
                    <div>
                      <div class="validated-title">Projet Officiellement Validé</div>
                      <div class="validated-sub">Le projet est maintenant officiellement clôturé.</div>
                    </div>
                  </div>
                </div>

              </div>



            </div>
          </div>

          <!-- Empty Desk -->
          <div v-else class="review-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3"/>
            </svg>
            <p>{{ selectedStudent ? 'En attente de soumission' : 'Sélectionnez un Brief puis un étudiant pour commencer.' }}</p>
          </div>

        </section>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import { BriefService, SubmissionService } from '../../services/ApiService';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { id: 1, first_name: 'Coach', last_name: 'Elite' });
const briefs = ref([]);
const students = ref([]);
const selectedBriefId = ref(null);
const selectedStudentId = ref(null);
const feedback = ref('');
const isSaving = ref(false);
const isBriefsLoading = ref(true);
const isSubmissionsLoading = ref(false);


const pendingCount = computed(() => students.value.filter(s => s.submission && (s.submission.status === 'SUBMITTED' || s.submission.status === 'PENDING')).length);
const validatedCount = computed(() => students.value.filter(s => s.submission && (s.submission.status === 'VALIDE' || s.submission.status === 'VALIDATED')).length);
const selectedBriefTitle = computed(() => briefs.value.find(b => b.id === selectedBriefId.value)?.title);
const selectedStudent = computed(() => students.value.find(s => s.id === selectedStudentId.value));


onMounted(async () => {
  // Cache
  const cached = localStorage.getItem('teacher_submissions_briefs_cache');
  if (cached) {
    briefs.value = JSON.parse(cached);
    isBriefsLoading.value = false;
  } else {
    isBriefsLoading.value = true;
  }

  try {
    const response = await BriefService.getAllList();
    briefs.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    localStorage.setItem('teacher_submissions_briefs_cache', JSON.stringify(briefs.value));
  } catch (err) {
    console.error("Erreur Briefs:", err);
  }
  
  isBriefsLoading.value = false;
});

const handleSelectBrief = async (id) => {
  selectedBriefId.value = id;
  selectedStudentId.value = null;

  const cacheKey = `teacher_submissions_students_${id}`;
  const cached = localStorage.getItem(cacheKey);
  if (cached) {
    students.value = JSON.parse(cached);
    isSubmissionsLoading.value = false;
  } else {
    isSubmissionsLoading.value = true;
  }

  try {
    const response = await SubmissionService.getAllByBrief(id);
    students.value = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    localStorage.setItem(cacheKey, JSON.stringify(students.value));
  } catch (err) {
    console.error("Erreur Submissions:", err);
  }
  
  isSubmissionsLoading.value = false;
};

const selectStudent = async (student) => {
  selectedStudentId.value = student.id;
  feedback.value = '';
};





const statusLabel = (s) => {
  if (!s.submission) return 'En attente';
  const st = s.submission.status;
  if (st === 'VALIDE' || st === 'VALIDATED') return 'Validé';
  if (st === 'INVALID' || st === 'REJECTED') return 'Retravail';
  return 'En attente';
};

const statusClass = (s) => {
  if (!s.submission) return 'st-gray';
  const st = s.submission.status;
  if (st === 'VALIDE' || st === 'VALIDATED') return 'st-green';
  if (st === 'INVALID' || st === 'REJECTED') return 'st-red';
  return 'st-amber';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};


const getProjectStatusLabel = (student) => {
  const s = student.submission?.status;
  if (s === 'VALIDE' || s === 'VALIDATED') return 'Validé';
  if (s === 'INVALID' || s === 'REJECTED') return 'À refaire';
  return 'En attente';
};

const getProjectStatusClass = (student) => {
  const label = getProjectStatusLabel(student);
  if (label === 'Validé') return 'd-status--green';
  if (label === 'À refaire') return 'd-status--red';
  return 'd-status--gray';
};

const getOverallStatusLabel = (student) => {
  return statusLabel(student);
};

const getOverallStatusClass = (student) => {
  return (student.submission?.status === 'VALIDATED' || student.submission?.status === 'VALIDE') ? 'badge-green' : 'badge-amber';
};

const saveReview = async (verdict) => {
  if (!selectedStudent.value?.submission) return;
  const backendVerdict = verdict === 'VALIDE' ? 'VALIDATED' : 'REJECTED';

  isSaving.value = true;
  try {
    await SubmissionService.review(selectedStudent.value.submission.id, {
      status: backendVerdict,
      message: feedback.value,
      formateur_id: user.value.id
    });
    
    await handleSelectBrief(selectedBriefId.value);
    feedback.value = '';
  } catch (err) {
    console.error("Erreur Review:", err);
    alert("Une erreur est survenue lors de la sauvegarde.");
  } finally {
    isSaving.value = false;
  }
};

const handleLogout = () => router.push('/login');
</script>

<style scoped>
* { box-sizing: border-box; }
.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* Topbar */
.topbar { height: 68px; display: flex; align-items: center; justify-content: space-between; padding: 0 36px; border-bottom: 1px solid rgba(48,54,61,0.5); background: rgba(13,17,23,0.85); backdrop-filter: blur(12px); flex-shrink: 0; z-index: 50; }
.topbar-left { display: flex; align-items: center; gap: 16px; }
.topbar-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(35,134,54,0.1); border: 1px solid rgba(63,185,80,0.3); display: flex; align-items: center; justify-content: center; color: #3fb950; }
.topbar-icon svg { width: 18px; height: 18px; }
.topbar-title { font-size: 15px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
.topbar-sub { font-size: 10px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.18em; font-weight: 600; margin-top: 2px; }
.topbar-stats { display: flex; gap: 10px; }
.stat-chip { display: flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid rgba(255,255,255,0.05); background: rgba(255,255,255,0.02); }
.stat-chip--amber { color: #d29922; border-color: rgba(210,153,34,0.2); }
.stat-chip--green { color: #3fb950; border-color: rgba(63,185,80,0.2); }
.stat-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 6px currentColor; }

/* Explorer */
.explorer { display: flex; flex: 1; overflow: hidden; }
.pane { border-right: 1px solid rgba(48,54,61,0.5); display: flex; flex-direction: column; flex-shrink: 0; background: rgba(13,17,23,0.45); }
.pane--briefs { width: 260px; }
.pane--students { width: 290px; }
.pane-header { padding: 18px 20px; border-bottom: 1px solid rgba(48,54,61,0.3); display: flex; justify-content: space-between; align-items: center; }
.pane-label { font-size: 10px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; }
.pane-count { font-size: 10px; font-weight: 800; background: rgba(48,54,61,0.5); color: #fff; padding: 2px 8px; border-radius: 20px; }
.pane-list { flex: 1; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.pane-loading { display: flex; align-items: center; justify-content: center; flex: 1; }

.brief-item { padding: 13px 16px; border-radius: 9px; cursor: pointer; transition: all 0.18s; border: 1px solid transparent; }
.brief-item:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06); transform: translateX(3px); }
.brief-item--active { background: rgba(56,139,253,0.06) !important; border-color: rgba(56,139,253,0.25) !important; }
.brief-title { font-size: 13px; font-weight: 700; color: #f0f6fc; display: block; margin-bottom: 5px; }
.brief-meta { display: flex; gap: 8px; font-size: 9px; font-weight: 800; color: #484f58; text-transform: uppercase; }

.student-item { display: flex; gap: 13px; align-items: center; padding: 12px 14px; border-radius: 9px; cursor: pointer; transition: all 0.18s; border: 1px solid transparent; }
.student-item:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06); }
.student-item--active { background: rgba(56,139,253,0.06) !important; border-color: rgba(56,139,253,0.25) !important; }
.student-avatar { width: 36px; height: 36px; border-radius: 9px; object-fit: cover; border: 2px solid rgba(48,54,61,0.5); flex-shrink: 0; }
.student-body { flex: 1; min-width: 0; }
.student-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
.student-name { font-size: 13px; font-weight: 600; color: #f0f6fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
.status-badge { font-size: 8px; font-weight: 800; padding: 2px 7px; border-radius: 5px; text-transform: uppercase; flex-shrink: 0; }
.student-points { font-size: 11px; color: #8b949e; font-family: 'JetBrains Mono', monospace; }
.st-green { color: #3fb950; background: rgba(63,185,80,0.1); border: 1px solid rgba(63,185,80,0.2); }
.st-red { color: #f85149; background: rgba(248,81,73,0.1); border: 1px solid rgba(248,81,73,0.2); }
.st-amber { color: #d29922; background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.2); }
.st-gray { color: #8b949e; background: rgba(139,148,158,0.05); }

/* Review Pane */
.review-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: radial-gradient(ellipse at 20% 60%, rgba(56,139,253,0.04) 0%, transparent 55%); }
.review-scroller { flex: 1; overflow-y: auto; padding: 36px 44px; }
.review-content { max-width: 820px; margin: 0 auto; display: flex; flex-direction: column; gap: 28px; }

/* Review ID Card */
.review-id-card { background: linear-gradient(145deg, rgba(22,27,34,0.85), rgba(13,17,23,0.9)); border: 1px solid rgba(48,54,61,0.6); border-radius: 16px; padding: 24px 28px; display: flex; justify-content: space-between; align-items: center; backdrop-filter: blur(16px); box-shadow: 0 16px 40px rgba(0,0,0,0.25); }
.status-dashboard { flex-wrap: wrap; gap: 20px; }
.rev-student { display: flex; align-items: center; gap: 18px; min-width: 200px; }
.dashboard-stats { display: flex; gap: 24px; flex: 1; justify-content: flex-end; align-items: center; }
.d-col { display: flex; flex-direction: column; gap: 6px; }
.d-label { font-size: 8px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.15em; }
.d-status { font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); color: #8b949e; }
.d-status--green { color: #3fb950; background: rgba(63,185,80,0.05); border-color: rgba(63,185,80,0.2); }
.d-status--red { color: #f85149; background: rgba(248,81,73,0.05); border-color: rgba(248,81,73,0.2); }
.d-status--amber { color: #d29922; background: rgba(210,153,34,0.05); border-color: rgba(210,153,34,0.2); }
.d-status--gray { color: #484f58; }
.d-verdict { font-size: 12px; font-weight: 900; padding: 8px 16px; border-radius: 8px; text-transform: uppercase; }
.badge-green { background: rgba(63,185,80,0.1); border: 1px solid rgba(63,185,80,0.3); color: #3fb950; box-shadow: 0 0 15px rgba(63,185,80,0.1); }
.badge-amber { background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.3); color: #d29922; box-shadow: 0 0 15px rgba(210,153,34,0.1); }
.badge-icon { font-size: 15px; }

/* Tab Nav */
.tab-nav { display: flex; gap: 3px; padding: 5px; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.5); border-radius: 12px; width: fit-content; }
.tab-btn { display: flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.18s; border: 1px solid transparent; background: transparent; color: #8b949e; }
.tab-btn svg { width: 15px; height: 15px; }
.tab-btn:hover { color: #c9d1d9; background: rgba(255,255,255,0.04); }
.tab-btn--active { background: rgba(56,139,253,0.12); color: #388bfd; border-color: rgba(56,139,253,0.22); }
.tab-spinner { width: 10px; height: 10px; border: 2px solid rgba(56,139,253,0.3); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }

.tab-content { display: flex; flex-direction: column; gap: 24px; }

/* Section Labels */
.review-section { }
.s-label-row { display: flex; align-items: center; gap: 9px; margin-bottom: 14px; }
.s-icon { width: 15px; height: 15px; color: #388bfd; flex-shrink: 0; }
.s-label { font-size: 9px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.2em; }

/* GitHub Repo Card */
.repo-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 22px; background: rgba(13,17,23,0.6); border: 1px solid rgba(56,139,253,0.2); border-radius: 12px; text-decoration: none; transition: all 0.2s; }
.repo-card:hover { border-color: rgba(56,139,253,0.5); background: rgba(56,139,253,0.04); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(56,139,253,0.08); }
.repo-card-left { flex: 1; overflow: hidden; }
.repo-url { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #388bfd; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 3px; }
.repo-link-label { font-size: 11px; color: #8b949e; }
.repo-card-right svg { width: 18px; height: 18px; color: #388bfd; flex-shrink: 0; }

/* Student Message */
.student-message-card { display: flex; gap: 14px; padding: 18px 20px; background: rgba(22,27,34,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 12px; }
.msg-avatar { width: 38px; height: 38px; border-radius: 9px; object-fit: cover; flex-shrink: 0; }
.student-message-body { flex: 1; }
  .msg-author { font-size: 12px; font-weight: 800; color: #c9d1d9; display: block; margin-bottom: 6px; }
.msg-text { font-size: 14px; color: #8b949e; line-height: 1.7; margin: 0; }

/* History */
.history-list { display: flex; flex-direction: column; gap: 10px; }
.message-bubble { background: rgba(22,27,34,0.5); border: 1px solid rgba(48,54,61,0.5); border-radius: 12px; padding: 18px 20px; font-size: 14px; color: #c9d1d9; line-height: 1.6; }
.message-bubble.empty-msg { color: #484f58; font-style: italic; font-size: 13px; }
.bubble-head { font-size: 9px; font-weight: 900; color: #8b949e; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 6px; }
.bubble-date { color: #484f58; font-weight: 600; text-transform: none; letter-spacing: 0; }
.bubble-text { font-size: 14px; color: #c9d1d9; line-height: 1.6; margin: 0; }
.c-green { color: #3fb950; }
.c-red { color: #f85149; }

/* Feedback Editor */
.feedback-area { background: rgba(13,17,23,0.5); border: 1px solid rgba(56,139,253,0.15); border-radius: 16px; overflow: hidden; transition: border-color 0.25s; }
.feedback-area:focus-within { border-color: rgba(56,139,253,0.45); box-shadow: 0 0 24px rgba(56,139,253,0.07); }
.feedback-input { width: 100%; background: transparent; border: none; padding: 22px 26px; color: #fff; font-size: 14px; line-height: 1.8; min-height: 130px; outline: none; font-family: inherit; resize: vertical; }
.evaluation-actions { padding: 18px 26px; background: rgba(1,4,9,0.4); border-top: 1px solid rgba(48,54,61,0.4); display: flex; gap: 14px; }
.btn-validate { background: #238636; color: #fff; border: 1px solid #2ea043; flex: 1.5; padding: 12px; border-radius: 9px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 7px; font-size: 13px; }
.btn-validate svg { width: 15px; height: 15px; }
.btn-validate:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(35,134,54,0.35); }
.btn-validate:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-reject { background: transparent; border: 1px solid rgba(248,81,73,0.35); color: #f85149; flex: 1; padding: 12px; border-radius: 9px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 7px; font-size: 13px; }
.btn-reject svg { width: 15px; height: 15px; }
.btn-reject:hover:not(:disabled) { background: rgba(248,81,73,0.05); border-color: #f85149; }
.btn-reject:disabled { opacity: 0.45; cursor: not-allowed; }

/* Validated Banner */
.validated-banner { display: flex; align-items: center; gap: 18px; padding: 22px 26px; background: rgba(35,134,54,0.07); border: 1px solid rgba(63,185,80,0.2); border-radius: 14px; }
.validated-icon { font-size: 34px; line-height: 1; }
.validated-title { font-size: 14px; font-weight: 800; color: #3fb950; margin-bottom: 3px; }
.validated-sub { font-size: 12px; color: #8b949e; }

/* Quiz Score Card */
.score-card { display: flex; align-items: center; gap: 36px; padding: 28px 32px; background: linear-gradient(145deg, rgba(22,27,34,0.85), rgba(13,17,23,0.9)); border: 1px solid rgba(48,54,61,0.6); border-radius: 18px; backdrop-filter: blur(12px); }
.score-ring-container { position: relative; width: 110px; height: 110px; flex-shrink: 0; }
.score-ring { width: 110px; height: 110px; transform: rotate(-90deg); }
.ring-bg { fill: none; stroke: rgba(48,54,61,0.5); stroke-width: 8; }
.ring-fill { fill: none; stroke-width: 8; stroke-linecap: round; transition: stroke-dasharray 0.8s ease; }
.ring-green { stroke: #3fb950; filter: drop-shadow(0 0 5px rgba(63,185,80,0.4)); }
.ring-amber { stroke: #d29922; filter: drop-shadow(0 0 5px rgba(210,153,34,0.4)); }
.ring-red { stroke: #f85149; filter: drop-shadow(0 0 5px rgba(248,81,73,0.4)); }
.score-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.score-pct { font-size: 22px; font-weight: 900; color: #fff; font-family: 'JetBrains Mono', monospace; line-height: 1; }
.score-label { font-size: 9px; color: #8b949e; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
.score-details { flex: 1; }
.score-title { font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 14px; }
.score-stat-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid rgba(48,54,61,0.3); }
.score-stat-label { font-size: 12px; color: #8b949e; }
.score-stat-val { font-size: 12px; font-weight: 800; color: #fff; font-family: 'JetBrains Mono', monospace; }
.score-verdict { display: inline-flex; margin-top: 14px; padding: 6px 14px; border-radius: 7px; font-size: 11px; font-weight: 800; }
.verdict-pass { background: rgba(63,185,80,0.1); color: #3fb950; border: 1px solid rgba(63,185,80,0.25); }
.verdict-fail { background: rgba(248,81,73,0.1); color: #f85149; border: 1px solid rgba(248,81,73,0.2); }

/* Student Avatars Premium */
.student-avatar { 
  width: 44px; height: 44px; border-radius: 12px; border: 2px solid rgba(48, 54, 61, 0.6); 
  padding: 1.5px; background: #0d1117; object-fit: cover; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.student-item--active .student-avatar { border-color: #58a6ff; box-shadow: 0 0 15px rgba(56, 139, 253, 0.3); transform: scale(1.05); }
.rev-avatar { 
  width: 60px; height: 60px; border-radius: 14px; border: 2.5px solid #30363d; 
  padding: 2px; background: #0d1117; object-fit: cover; box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

/* Quiz States */
.quiz-loading { display: flex; align-items: center; gap: 12px; padding: 40px; color: #8b949e; font-size: 13px; }
.quiz-empty { display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 60px 40px; color: #484f58; text-align: center; }
.quiz-empty svg { width: 44px; height: 44px; opacity: 0.2; }
.quiz-empty p { font-size: 13px; max-width: 220px; line-height: 1.6; }

/* Question Cards */
.questions-list { display: flex; flex-direction: column; gap: 14px; }
.question-card { border-radius: 14px; overflow: hidden; border: 1px solid transparent; }
.qcard-correct { border-color: rgba(63,185,80,0.2); background: linear-gradient(145deg, rgba(22,27,34,0.75), rgba(35,134,54,0.04)); }
.qcard-wrong { border-color: rgba(248,81,73,0.2); background: linear-gradient(145deg, rgba(22,27,34,0.75), rgba(248,81,73,0.03)); }
.qcard-open { border-color: rgba(56,139,253,0.25); background: linear-gradient(145deg, rgba(22,27,34,0.75), rgba(56,139,253,0.04)); }

/* Question Type Badges */
.q-number { 
  font-size: 11px; font-weight: 950; color: #fff; 
  background: linear-gradient(135deg, #388bfd, #1f6feb); 
  width: 38px; height: 28px; display: flex; align-items: center; justify-content: center; 
  border-radius: 8px; flex-shrink: 0; font-family: 'JetBrains Mono', monospace;
  box-shadow: 0 4px 12px rgba(56, 139, 253, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.q-content { flex: 1; font-size: 15px; font-weight: 750; color: #f0f6fc; line-height: 1.7; letter-spacing: -0.01em; }
.q-type-badge { font-size: 9px; font-weight: 900; padding: 2px 8px; border-radius: 5px; text-transform: uppercase; letter-spacing: 0.1em; }
.q-type-badge--mcq { background: rgba(63,185,80,0.1); color: #3fb950; border: 1px solid rgba(63,185,80,0.2); }
.q-type-badge--open { background: rgba(56,139,253,0.1); color: #388bfd; border: 1px solid rgba(56,139,253,0.2); }
.verdict-open { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
.verdict-open span { font-size: 11px; font-weight: 800; color: #388bfd; }

/* AI Feedback Full */
.ai-full-label { font-size: 9px; font-weight: 900; color: #388bfd; text-transform: uppercase; letter-spacing: 0.12em; }
.ai-feedback-text--full { font-size: 13px; color: #c9d1d9; line-height: 1.8; white-space: pre-wrap; }
.ai-feedback-pending { opacity: 0.6; border-style: dashed; }
.verdict-wrong span:first-child { color: #f85149; }
.q-score { font-size: 10px; font-family: 'JetBrains Mono', monospace; color: #8b949e; }
.qcard-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }
.answer-label { font-size: 9px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.15em; display: block; margin-bottom: 7px; }
.answer-text { font-size: 13px; color: #c9d1d9; line-height: 1.6; padding: 11px 14px; background: rgba(13,17,23,0.5); border-radius: 8px; border: 1px solid rgba(48,54,61,0.3); margin: 0; }
.ai-feedback-block { background: rgba(56,139,253,0.04); border: 1px solid rgba(56,139,253,0.14); border-radius: 10px; padding: 14px 18px; }
.ai-feedback-header { display: flex; align-items: center; gap: 7px; margin-bottom: 9px; }
.ai-feedback-header svg { width: 13px; height: 13px; color: #388bfd; }
.ai-feedback-header span { font-size: 9px; font-weight: 900; color: #388bfd; text-transform: uppercase; letter-spacing: 0.18em; }
.ai-feedback-text { font-size: 13px; color: #8b949e; line-height: 1.7; margin: 0; }

/* Shared */
.pane-empty, .review-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #484f58; padding: 40px; gap: 18px; flex: 1; }
.review-empty svg { width: 56px; height: 56px; opacity: 0.1; }
.review-empty p { font-size: 13px; font-weight: 500; max-width: 240px; line-height: 1.6; }
.pulse-sm { width: 10px; height: 10px; background: #388bfd; border-radius: 50%; opacity: 0.5; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%,100% { transform: scale(0.8); opacity: 0.3; } 50% { transform: scale(1.2); opacity: 0.7; } }

/* Manual Grading UI - Premium Rethink */
.animate-in { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUpFade { from { opacity: 0; transform: translateY(12px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

.animate-nadi-in { animation: nadiIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes nadiIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }
</style>

