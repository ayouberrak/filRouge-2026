<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Loading Overlay -->
      <div v-if="isLoading" style="text-align: center; padding: 40px; color: #8b949e;">
        Chargement en cours...
      </div>

      <div v-else class="content">

        <!-- ===== HEADER ===== -->
        <header class="dash-header animate-in">
          <div class="dash-header-left">
            <div class="header-greeting">
              <div class="greeting-avatar">{{ user?.first_name?.charAt(0)?.toUpperCase() || 'S' }}</div>
              <div>
                <div class="greeting-sub">{{ formattedDate }}</div>
                <h1 class="greeting-title">Bienvenue, {{ user?.first_name || 'Apprenant' }} <span class="greeting-role">· Promotion 2026</span></h1>
              </div>
            </div>
          </div>
          <div class="dash-header-right">
            <div class="live-badge">
              <span class="live-dot"></span>
              Session Active
            </div>
          </div>
        </header>

        <!-- ===== KPI STATS GRID ===== -->
        <div class="kpi-grid animate-in" style="animation-delay: 0.1s">

          <div class="kpi-card" @click="router.push('/student/network')">
            <div class="kpi-icon kpi-icon--blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div class="kpi-body">
              <div class="kpi-label">Progression</div>
              <div class="kpi-value kpi-value--blue">#{{ studentStats?.rank || '--' }}</div>
              <div class="kpi-sub">rang promotion</div>
            </div>
          </div>

          <div class="kpi-card" @click="router.push('/student/activity')">
            <div class="kpi-icon kpi-icon--green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="kpi-body">
              <div class="kpi-label">Activités</div>
              <div class="kpi-value kpi-value--green">PRÉSENT</div>
              <div class="kpi-sub">engagement actif</div>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--amber">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="kpi-body">
              <div class="kpi-label">Briefs Validés</div>
              <div class="kpi-value kpi-value--amber">{{ studentStats?.validated_briefs || 0 }}</div>
              <div class="kpi-sub">compétences validées</div>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--red">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-body">
              <div class="kpi-label">Absences</div>
              <div class="kpi-value" :class="studentStats?.absences_count > 0 ? 'kpi-value--red' : ''">{{ studentStats?.absences_count || 0 }}</div>
              <div class="kpi-sub">total absences</div>
            </div>
          </div>

        </div>

        <!-- ===== MAIN GRID ===== -->
        <div class="main-grid">

          <!-- Left: Primary Content -->
          <div class="main-left">
            
            <!-- Active Brief Section -->
            <section class="panel-card animate-in" style="animation-delay: 0.2s">
              <div class="panel-header">
                <div>
                  <h2 class="panel-title">Projet en cours</h2>
                  <p class="panel-desc">Gérez vos rendus et vos échéances</p>
                </div>
                <router-link to="/student/briefs" class="panel-link">
                  Voir tous les projets
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </router-link>
              </div>

              <div v-if="activeBrief" class="brief-hero-card">
                <div class="brief-hero-content">
                  <div class="brief-badge">ACTIF</div>
                  <h3 class="brief-title">{{ activeBrief.title }}</h3>
                  <div class="brief-metadata">
                    <div class="meta-inline">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                      J-{{ Math.ceil((new Date(activeBrief.date_end) - new Date()) / (1000 * 60 * 60 * 24)) }} avant rendu
                    </div>
                  </div>
                  <div class="brief-footer">
                    <button class="btn-nadi-primary" @click="router.push('/student/briefs')">Continuer le travail</button>
                    <button class="btn-nadi-secondary" @click="router.push('/student/submissions')">Déposer un livrable</button>
                  </div>
                </div>
              </div>
              <div v-else class="panel-empty">
                <p>Aucun brief actif pour le moment.</p>
              </div>
            </section>

            <!-- My Squad Section -->
            <section class="panel-card animate-in" style="animation-delay: 0.3s">
              <div class="panel-header">
                <div>
                  <h2 class="panel-title">Ma Squad</h2>
                  <p class="panel-desc">Vos coéquipiers pour ce sprint</p>
                </div>
              </div>
              <div class="squad-container">
                <div v-if="squad.length > 0" class="squad-stack">
                  <div v-for="member in squad" :key="member.id" class="squad-member-wrap" :title="member.first_name">
                    <img :src="member.avatar_url || `https://ui-avatars.com/api/?name=${member.first_name}&background=161b22&color=388bfd`" class="squad-avatar" />
                  </div>
                </div>
                <div v-else class="squad-empty-hint">Non assigné à une squad</div>
                <button class="btn-chat-link" @click="router.push('/student/chat')">Ouvrir le salon de discussion</button>
              </div>
            </section>

          </div>

          <!-- Right: Secondary Content -->
          <aside class="main-right">
            
            <!-- Hall of Fame (Leaderboard) -->
            <section class="panel-card leaderboard-panel animate-in" style="animation-delay: 0.4s">
              <div class="panel-header">
                <div>
                  <h2 class="panel-title">Hall of Fame</h2>
                  <p class="panel-desc">Leaders de la promotion</p>
                </div>
              </div>

              <div class="leader-list">
                <div v-for="(student, idx) in leaderboard.slice(0, 5)" :key="student.id" class="leader-row" :class="{ 'leader-row--top': idx < 3 }">
                  <div class="leader-rank" :class="[`rank-${idx + 1}`]">
                    <svg v-if="idx < 3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.09 6.26L21 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <img :src="student.avatar_url || `https://ui-avatars.com/api/?name=${student.first_name}&background=161b22&color=8b949e`" class="leader-avatar" />
                  <div class="leader-details">
                    <span class="leader-name">{{ student.first_name }}</span>
                    <div class="leader-progress-bg">
                      <div class="leader-progress-fill" :style="{ width: ((student.validated_briefs_count / (leaderboard[0]?.validated_briefs_count || 1)) * 100) + '%' }"></div>
                    </div>
                  </div>
                  <div class="leader-xp">{{ student.validated_briefs_count || 0 }}</div>
                </div>
              </div>

              <button class="leaderboard-full-btn" @click="router.push('/student/network')">Classement complet</button>
            </section>

          </aside>

        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import SidebarStudent from '../../components/SidebarStudent.vue';
import api from '../../services/api';

// --- VARIABLES D'ÉTAT (REFS) ---
// On utilise 'ref' pour créer des variables réactives que Vue surveille
const router    = useRouter();
const user      = ref(null); // Informations de l'étudiant connecté
const activeBrief  = ref(null); // Le projet actuel sur lequel travailler
const leaderboard  = ref([]); // Classement des meilleurs étudiants
const squad        = ref([]); // Membres de la squad (groupe)
const studentStats = ref(null); // Statistiques (rang, XP, absences...)
const isLoading    = ref(true); // État de chargement initial

// --- LOGIQUE CALCULÉE (COMPUTED) ---

// Formate la date du jour (ex: lundi 23 avril)
const formattedDate = computed(() => {
  return new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long'
  }).format(new Date());
});

// --- ACTIONS (MÉTHODES) ---

// --- ACTIONS (MÉTHODES) ---

// Déconnexion de l'utilisateur
const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  router.push('/login');
};

// Récupérer les données du Dashboard (Version très simple sans try/catch)
const fetchDashboardData = async () => {
  // 1. Tenter de charger le cache pour un affichage instantané
  const cached = localStorage.getItem('student_dashboard_cache');
  if (cached) {
    const cacheData = JSON.parse(cached);
    user.value = cacheData.user;
    leaderboard.value = cacheData.leaderboard;
    studentStats.value = cacheData.stats;
    squad.value = cacheData.squad;
    activeBrief.value = cacheData.activeBrief;
    isLoading.value = false; // On cache le loader car on a déjà des données
  } else {
    isLoading.value = true;
  }
  
  try {
    const [userRes, leaderboardRes, statsRes, squadRes, briefsRes] = await Promise.all([
      api.get('/user'),
      api.get('/leaderboard'),
      api.get('/analytics/student/stats'),
      api.get('/squads/my'),
      api.get('/briefs')
    ]);

    user.value = userRes.data;
    leaderboard.value = leaderboardRes.data.data || [];
    studentStats.value = statsRes.data.stats || null;

    const squadData = squadRes.data.data || squadRes.data;
    squad.value = squadData?.members || (Array.isArray(squadData) ? squadData : []);

    const allBriefs = briefsRes.data.data || [];
    const now = new Date();
    activeBrief.value = allBriefs
      .filter(b => new Date(b.date_end) > now)
      .sort((a, b) => new Date(a.date_end) - new Date(b.date_end))[0] || null;

    // 2. Sauvegarder dans le cache pour la prochaine fois
    localStorage.setItem('student_dashboard_cache', JSON.stringify({
      user: user.value,
      leaderboard: leaderboard.value,
      stats: studentStats.value,
      squad: squad.value,
      activeBrief: activeBrief.value
    }));

  } catch (err) {
    console.error("Erreur Dashboard:", err);
  }

  isLoading.value = false;
};

// --- CYCLE DE VIE ---
// Cette fonction s'exécute quand le composant est affiché à l'écran
onMounted(fetchDashboardData);
</script>

<style scoped>
/* ─── Base Layout ───────────────────────────────────────────────────────────── */
.layout {
  display: flex;
  height: 100vh;
  background: #010409;
  color: #e6edf3;
  font-family: 'Inter', -apple-system, system-ui, sans-serif;
  overflow: hidden;
}

.main {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}

.content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 40px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}



/* ─── Heading ───────────────────────────────────────────────────────────────── */
.dash-header { display: flex; justify-content: space-between; align-items: center; }
.header-greeting { display: flex; align-items: center; gap: 16px; }
.greeting-avatar {
  width: 52px; height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, #1f6feb 0%, #388bfd 100%);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 700; color: white;
  box-shadow: 0 4px 15px rgba(31, 111, 235, 0.2);
}
.greeting-sub { font-size: 13px; color: #8b949e; margin-bottom: 2px; text-transform: capitalize; }
.greeting-title { font-size: 28px; font-weight: 800; letter-spacing: -0.03em; }
.greeting-role { font-size: 16px; color: #388bfd; font-weight: 500; }

.live-badge {
  display: flex; align-items: center; gap: 8px;
  padding: 6px 14px; background: rgba(35, 134, 54, 0.1);
  border: 1px solid rgba(35, 134, 54, 0.2); border-radius: 20px;
  font-size: 12px; color: #3fb950; font-weight: 600;
}
.live-dot {
  width: 6px; height: 6px; background: #3fb950;
  border-radius: 50%; animation: pulse 2s infinite;
}
@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.5; } 100% { transform: scale(1); opacity: 1; } }

/* ─── KPI Grid ──────────────────────────────────────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
}
.kpi-card {
  background: #161b22; border: 1px solid #21262d;
  border-radius: 16px; padding: 20px;
  display: flex; align-items: center; gap: 20px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.kpi-card:hover { border-color: #388bfd; transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
.kpi-icon {
  width: 56px; height: 56px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icon svg { width: 24px; height: 24px; }

.kpi-icon--blue  { background: rgba(56, 139, 253, 0.1); color: #388bfd; }
.kpi-icon--green { background: rgba(63, 185, 80, 0.1);  color: #3fb950; }
.kpi-icon--amber { background: rgba(210, 153, 34, 0.1); color: #d29922; }
.kpi-icon--red   { background: rgba(248, 81, 73, 0.1);  color: #f85149; }

.kpi-label { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; }
.kpi-value { font-size: 26px; font-weight: 800; font-family: 'JetBrains Mono', monospace; margin: 2px 0; }
.kpi-value--blue  { color: #388bfd; }
.kpi-value--green { color: #3fb950; }
.kpi-value--amber { color: #d29922; }
.kpi-value--red   { color: #f85149; }
.kpi-sub { font-size: 11px; color: #484f58; font-weight: 500; }

/* ─── Panels ────────────────────────────────────────────────────────────────── */
.main-grid { display: grid; grid-template-columns: 1fr 340px; gap: 32px; }
.panel-card { background: #161b22; border: 1px solid #21262d; border-radius: 16px; padding: 24px; margin-bottom: 24px; }
.panel-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.panel-title { font-size: 18px; font-weight: 700; color: #e6edf3; }
.panel-desc { font-size: 13px; color: #8b949e; margin-top: 2px; }

.panel-link {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; color: #388bfd; font-weight: 600; text-decoration: none;
}
.panel-link svg { width: 14px; height: 14px; transition: transform 0.2s; }
.panel-link:hover svg { transform: translateX(3px); }

/* Brief Hero */
.brief-hero-card {
  background: linear-gradient(135deg, rgba(22, 27, 34, 1) 0%, rgba(33, 38, 45, 1) 100%);
  border: 1px solid #30363d; border-radius: 14px; padding: 24px;
}
.brief-badge {
  display: inline-block; padding: 4px 10px; background: rgba(56, 139, 253, 0.15);
  color: #388bfd; border-radius: 6px; font-size: 10px; font-weight: 800;
  letter-spacing: 0.05em; margin-bottom: 12px;
}
.brief-title { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
.brief-metadata { margin-bottom: 24px; }
.meta-inline { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #8b949e; }
.meta-inline svg { width: 14px; height: 14px; }

.brief-footer { display: flex; gap: 12px; }
.btn-nadi-primary {
  background: #238636; border: 1px solid #2ea043; color: white;
  padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.btn-nadi-secondary {
  background: #21262d; border: 1px solid #30363d; color: #c9d1d9;
  padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
}

/* Squad */
.squad-container { display: flex; justify-content: space-between; align-items: center; }
.squad-stack { display: flex; }
.squad-member-wrap {
  width: 44px; height: 44px; border-radius: 50%; border: 3px solid #161b22;
  margin-right: -12px; position: relative; transition: transform 0.2s; cursor: pointer;
}
.squad-member-wrap:hover { transform: translateY(-4px); z-index: 10 !important; }
.squad-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.btn-chat-link {
  background: transparent; border: 1px solid #30363d; color: #8b949e;
  padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;
}

/* Leaderboard */
.leader-list { display: flex; flex-direction: column; gap: 14px; }
.leader-row { display: flex; align-items: center; gap: 14px; padding: 4px 6px; }
.leader-rank { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
.rank-1 { color: #f2bc1b; } .rank-2 { color: #8b949e; } .rank-3 { color: #d45a1e; }
.leader-avatar { width: 34px; height: 34px; border-radius: 10px; border: 1px solid #21262d; }
.leader-details { flex: 1; min-width: 0; }
.leader-name { font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px; }
.leader-progress-bg { height: 4px; background: #21262d; border-radius: 2px; }
.leader-progress-fill { height: 100%; background: #388bfd; border-radius: 2px; }
.leader-xp { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700; color: #388bfd; }

.leaderboard-full-btn {
  width: 100%; margin-top: 24px; padding: 12px; background: rgba(56, 139, 253, 0.05);
  border: 1px dashed #30363d; border-radius: 12px; color: #8b949e; font-size: 12px; font-weight: 600; cursor: pointer;
}

/* Animations */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: fadeInUp 0.5s ease forwards; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
