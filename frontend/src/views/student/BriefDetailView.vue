<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Full Loading -->
      <div v-if="isLoading" class="loader-full animate-in">
        <div class="loader-ring"></div>
        <p>Récupération de la mission...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="hasError" class="state-container animate-in">
        <div class="error-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <h2>Échec du chargement</h2>
        <p>Impossible de récupérer les détails de ce brief. Votre session a peut-être expiré.</p>
        <div class="error-actions">
          <button @click="fetchBrief" class="btn-nadi-secondary">Réessayer</button>
          <router-link to="/student/briefs" class="btn-nadi-primary">Retour aux missions</router-link>
        </div>
      </div>

      <template v-else>
        <!-- Hero Section with Image -->
        <div class="hero">
          <img
            :src="brief.image_url || 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=1400&auto=format&fit=crop'"
            class="hero-img"
            :alt="brief.title"
          />
          <div class="hero-overlay"></div>

          <div class="hero-wrapper">
            <div class="hero-nav">
              <router-link to="/student/briefs" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
                <span>Missions</span>
              </router-link>
              <div class="status-wrap">
                <span class="status-dot" :class="brief.status.toLowerCase()"></span>
                <span class="status-label">{{ brief.status }}</span>
              </div>
            </div>

            <div class="hero-content animate-in">
              <div class="modality-chip">{{ brief.modality === 'INDIVIDUAL' ? 'Individuel' : 'Squad' }}</div>
              <h1 class="brief-title">{{ brief.title }}</h1>
              <div class="brief-tags" v-if="brief.tags?.length">
                <span v-for="tag in brief.tags" :key="tag" class="tag">#{{ tag }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
          
          <!-- Left: Main Info -->
          <div class="col-main">
            <!-- Description Card -->
            <section class="card-glass animate-in" style="animation-delay: 0.1s">
              <div class="card-header">
                <span class="section-num">01</span>
                <h3>Description</h3>
              </div>
              <div class="card-body">
                <p class="text-secondary">{{ brief.description || 'Chargement en cours...' }}</p>
              </div>
            </section>

            <!-- Context Card -->
            <section class="card-glass animate-in" style="animation-delay: 0.2s">
              <div class="card-header">
                <span class="section-num">02</span>
                <h3>Contexte & Enjeux</h3>
              </div>
              <div class="card-body">
                <div class="context-highlight">
                  <p class="text-primary">{{ brief.context || 'Aucun contexte spécifié.' }}</p>
                </div>
              </div>
            </section>

            <!-- Resource Card (if file exists) -->
            <section class="card-glass animate-in" style="animation-delay: 0.3s" v-if="brief.file">
              <div class="card-header">
                <span class="section-num">03</span>
                <h3>Ressources</h3>
              </div>
              <div class="card-body">
                <a :href="brief.file" target="_blank" class="resource-link">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><path d="M13 2v7h7"/></svg>
                  <span>Télécharger la fiche technique (PDF)</span>
                </a>
              </div>
            </section>
          </div>

          <!-- Right: Sidebar -->
          <aside class="col-sidebar">
            <!-- Technical Card -->
            <div class="sidebar-card animate-in" style="animation-delay: 0.4s">
              <div class="sidebar-row">
                <span class="label">Date limite</span>
                <span class="value spotlight">{{ brief.date_end_f || 'À définir' }}</span>
              </div>

              <div class="sidebar-divider"></div>
              <div class="action-stack">
                <button 
                  class="btn-nadi-primary" 
                  @click="router.push('/student/submissions')"
                  v-if="brief.status === 'IN_PROGRESS'"
                >
                  Soumettre mon travail
                </button>
                <button class="btn-nadi-primary" v-else>
                  Démarrer le brief
                </button>
              </div>
            </div>

            <!-- Coach Card -->
            <div class="sidebar-card coach-card animate-in" style="animation-delay: 0.5s">
              <span class="sidebar-label">Encadrant</span>
              <div class="coach-flex">
                <div class="avatar initials">{{ (brief.formateur_name || 'Coach YouCode').slice(0, 2).toUpperCase() }}</div>
                <div class="coach-info">
                  <span class="coach-name">{{ brief.formateur_name || 'Coach YouCode' }}</span>
                  <span class="coach-role">Formateur Référent</span>
                </div>
              </div>
            </div>
          </aside>

        </div>
      </template>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

// --- VARIABLES D'ÉTAT (REFS) ---
const route = useRoute();
const router = useRouter();
const user = ref(null); // Utilisateur actuel
const isLoading = ref(true); // État de chargement initial
const hasError = ref(false); // État en cas d'erreur de récupération

// Les détails du brief
const brief = ref({
  title: '',
  description: '',
  tags: [],
  date_end_f: '',
  modality: '',
  status: '',
  context: '',
  file: null,
});

// --- ACTIONS (MÉTHODES) ---

// Récupérer les détails d'un brief spécifique
const fetchBrief = async () => {
  isLoading.value = true;
  hasError.value = false;
  
  // Appel à l'API pour récupérer un brief par son ID
  const res = await api.get(`/briefs/${route.params.id}`);
  const data = res.data.data || res.data;
  
  // Mise à jour de l'état avec les données formatées
  brief.value = {
    ...data,
    date_end_f: formatDate(data.date_end),
    tags: data.tags || [],
  };
  
  isLoading.value = false;
};

// Formater une date ISO en texte lisible (ex: 12 avril 2026)
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  });
};

// Déconnexion
const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

// --- CYCLE DE VIE ---
onMounted(() => {
  // Récupération de l'utilisateur depuis le localStorage
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  
  // Lancement de la récupération du brief
  fetchBrief();
});
</script>

<style scoped>
/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #e6edf3; font-family: 'Inter', system-ui, sans-serif;
}

.main {
  flex: 1; overflow-y: auto; display: flex; flex-direction: column;
  scrollbar-width: thin; scrollbar-color: #21262d transparent;
}
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: #30363d; border-radius: 4px; }

/* ─── Hero Section ──────────────────────────────────────────────────────────── */
.hero {
  position: relative; height: 380px; width: 100%; flex-shrink: 0; overflow: hidden;
}
.hero-img {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
  filter: grayscale(0.5) brightness(0.6);
}
.hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, rgba(1,4,9,0.2) 0%, rgba(1,4,9,0.8) 70%, #010409 100%);
}

.hero-wrapper {
  position: relative; z-index: 10; max-width: 1200px; margin: 0 auto;
  width: 100%; height: 100%; padding: 32px 40px; display: flex; flex-direction: column; justify-content: space-between;
}

.hero-nav { display: flex; align-items: center; justify-content: space-between; }
.back-link {
  display: flex; align-items: center; gap: 8px; color: #8b949e; text-decoration: none;
  font-size: 13px; font-weight: 600; transition: color 0.2s;
}
.back-link:hover { color: #fff; }
.back-link svg { width: 16px; height: 16px; }

.status-wrap { display: flex; align-items: center; gap: 10px; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: #30363d; }
.status-dot.in_progress { background: #3fb950; box-shadow: 0 0 10px #238636; }
.status-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #8b949e; }

.hero-content { max-width: 800px; }
.modality-chip {
  display: inline-block; padding: 4px 12px; background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.3); border-radius: 6px;
  font-size: 11px; font-weight: 700; color: #79c0ff; text-transform: uppercase; margin-bottom: 16px;
}
.brief-title { font-size: 44px; font-weight: 800; color: #fff; letter-spacing: -0.03em; margin-bottom: 16px; }
.brief-tags { display: flex; flex-wrap: wrap; gap: 12px; }
.tag { font-size: 13px; color: #e3b341; font-weight: 600; opacity: 0.9; }

/* ─── Content Grid ──────────────────────────────────────────────────────────── */
.content-grid {
  display: grid; grid-template-columns: 1fr 340px; gap: 40px;
  max-width: 1200px; margin: -40px auto 0; width: 100%; padding: 0 40px 80px; position: relative; z-index: 20;
}

/* ─── Cards ─────────────────────────────────────────────────────────────────── */
.card-glass {
  background: rgba(22, 27, 34, 0.6); border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px); border-radius: 16px; padding: 32px; margin-bottom: 24px;
}
.card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 12px; }
.section-num { font-size: 11px; font-weight: 700; color: #484f58; font-family: 'JetBrains Mono', monospace; }
.card-header h3 { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #8b949e; }

.text-secondary { font-size: 15px; line-height: 1.8; color: #8b949e; }
.text-primary { font-size: 16px; line-height: 1.8; color: #c9d1d9; }

.context-highlight {
  background: rgba(56, 139, 253, 0.03); border-left: 4px solid #388bfd;
  padding: 24px; border-radius: 0 12px 12px 0;
}

.resource-link {
  display: flex; align-items: center; gap: 12px; padding: 16px;
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px; color: #79c0ff; text-decoration: none; font-weight: 600; transition: all 0.2s;
}
.resource-link:hover { background: rgba(56, 139, 253, 0.1); border-color: rgba(56, 139, 253, 0.4); }
.resource-link svg { width: 18px; height: 18px; }

/* ─── Sidebar ───────────────────────────────────────────────────────────────── */
.col-sidebar { display: flex; flex-direction: column; gap: 24px; }
.sidebar-card { background: rgba(22, 27, 34, 0.8); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 24px; }

.sidebar-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 20px; }
.sidebar-row .label { font-size: 11px; text-transform: uppercase; color: #484f58; font-weight: 700; letter-spacing: 0.1em; }
.sidebar-row .value { font-size: 16px; font-weight: 700; color: #fff; }
.spotlight { color: #e3b341 !important; text-shadow: 0 0 10px rgba(227, 179, 65, 0.2); }
.points { color: #79c0ff !important; }

.sidebar-divider { height: 1px; background: rgba(255,255,255,0.05); margin-bottom: 24px; }

.btn-nadi-primary {
  width: 100%; padding: 14px; background: #238636; color: #fff; border: 1px solid #2ea043;
  border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.btn-nadi-primary:hover { background: #2ea043; box-shadow: 0 0 20px rgba(35, 134, 54, 0.2); transform: translateY(-1px); }

.sidebar-label { font-size: 11px; text-transform: uppercase; color: #484f58; font-weight: 700; display: block; margin-bottom: 16px; }
.coach-flex { display: flex; align-items: center; gap: 14px; }
.avatar { width: 44px; height: 44px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); }
.avatar.initials { display: flex; align-items: center; justify-content: center; background: rgba(56, 139, 253, 0.15); color: #79c0ff; font-weight: 700; font-size: 15px; letter-spacing: 0.05em; }
.coach-name { font-size: 14px; font-weight: 700; color: #fff; display: block; }
.coach-role { font-size: 12px; color: #8b949e; }

/* ─── States ────────────────────────────────────────────────────────────────── */
.loader-full { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; color: #8b949e; }
.loader-ring { width: 40px; height: 40px; border: 3px solid rgba(56, 139, 253, 0.1); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }


.state-container { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 16px; padding: 40px; }
.error-icon { width: 56px; height: 56px; color: #f85149; }
.error-actions { display: flex; gap: 12px; margin-top: 20px; }
.btn-nadi-secondary { padding: 12px 24px; background: #21262d; border: 1px solid #30363d; border-radius: 8px; color: #c9d1d9; cursor: pointer; font-weight: 600; }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@media (max-width: 900px) {
  .content-grid { grid-template-columns: 1fr; padding: 20px; }
  .hero { height: 300px; }
  .brief-title { font-size: 32px; }
}
</style>

