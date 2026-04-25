<template>
  <div class="layout">
    <SidebarAdmin :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== TOPBAR ===== -->
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Tableau de Bord Admin</h1>
            <p class="topbar-sub">Supervision & Pilotage Global</p>
          </div>
          <div class="topbar-right">
             <div class="status-chip">
               <span class="status-dot"></span>
               Système Opérationnel
             </div>
             <div class="date-chip">{{ formattedDate }}</div>
          </div>
        </header>

        <!-- ===== KPI STATS ===== -->
        <div class="kpi-grid animate-in" style="animation-delay: 0.1s">
          <div class="kpi-card" v-for="(stat, key) in kpiStats" :key="key">
            <div class="kpi-header">
              <div class="kpi-icon" :class="stat.class" v-html="stat.icon"></div>
              <div class="kpi-trend" :class="stat.trendClass">{{ stat.trend }}</div>
            </div>
            <div class="kpi-body">
              <div class="kpi-value">{{ stat.value }}</div>
              <div class="kpi-label">{{ stat.label }}</div>
            </div>
            <div class="kpi-footer">
              <span class="kpi-update">Mis à jour à l'instant</span>
            </div>
            <div class="kpi-glow" :class="stat.class"></div>
          </div>
        </div>

        <!-- ===== MAIN CONTENT AREA ===== -->
        <div class="main-grid">
          
          <!-- Activity Feed -->
          <section class="panel animate-in" style="animation-delay: 0.2s">
            <div class="panel-header">
              <h2 class="panel-title">Flux d'Activité</h2>
              <button class="btn-text">Voir tout</button>
            </div>
            <div class="activity-list">
              <div v-for="(act, idx) in recentActivity" :key="idx" class="activity-item">
                <div class="activity-marker"></div>
                <div class="activity-content">
                  <p class="activity-text"><strong>{{ act.user }}</strong>: {{ act.label }}</p>
                  <span class="activity-time">{{ act.time }}</span>
                </div>
              </div>
              <div v-if="!recentActivity.length" class="empty-state">
                <p>Aucune activité récente</p>
              </div>
            </div>
          </section>

          <!-- Distribution / Alerts -->
          <section class="panel animate-in" style="animation-delay: 0.3s">
            <div class="panel-header">
              <h2 class="panel-title">Alertes & Maintenance</h2>
            </div>
            <div class="alert-list">
              <div v-if="stats.pending_justifications > 0" class="alert-item warning">
                <div class="alert-icon">!</div>
                <div class="alert-body">
                   <p class="alert-title">{{ stats.pending_justifications }} Justifications en attente</p>
                   <p class="alert-desc">Des absences nécessitent une validation administrative.</p>
                </div>
                <router-link to="/admin/absences" class="alert-action">Réviser</router-link>
              </div>
              <div class="alert-item info">
                <div class="alert-icon">i</div>
                <div class="alert-body">
                   <p class="alert-title">{{ stats.active_classrooms }} Classes Actives</p>
                   <p class="alert-desc">Toutes les classes sont assignées à des formateurs.</p>
                </div>
              </div>
            </div>
          </section>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
import api from '../../services/api';

// --- VARIABLES D'ÉTAT (REFS) ---
// On utilise 'ref' pour créer des variables que Vue peut surveiller et mettre à jour dans l'interface
const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || {});
const stats = ref({}); // Contiendra les chiffres du dashboard
const recentActivity = ref([]);
const isLoading = ref(true);

// --- LOGIQUE CALCULÉE (COMPUTED) ---

// Formate la date du jour (ex: lundi 23 avril)
const formattedDate = computed(() => {
  return new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date());
});

// Génère la liste des cartes KPI dynamiquement
const kpiStats = computed(() => [
  {
    label: 'Étudiants Totaux',
    value: stats.value.total_students || 0,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    class: 'purple',
    trend: '+2',
    trendClass: 'up'
  },
  {
    label: 'Staff Formateurs',
    value: stats.value.total_staff || 0,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
    class: 'blue',
    trend: 'stable',
    trendClass: 'neutral'
  },
  {
    label: 'Absences (Jour)',
    value: stats.value.absences_today || 0,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    class: 'red',
    trend: '-5%',
    trendClass: 'down'
  },
  {
    label: 'Classes Actives',
    value: stats.value.active_classrooms || 0,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7M4 21v-4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4M20 7V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/></svg>',
    class: 'gold',
    trend: 'stable',
    trendClass: 'neutral'
  }
]);

// --- ACTIONS (MÉTHODES) ---

// Récupère les statistiques depuis l'API Laravel
const fetchStats = async () => {
  // 1. Charger le cache
  const cached = localStorage.getItem('admin_dashboard_cache');
  if (cached) {
    const cacheData = JSON.parse(cached);
    stats.value = cacheData.stats;
    recentActivity.value = cacheData.activity;
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }

  try {
    const res = await api.get('/admin/stats');
    stats.value = res.data.data;
    recentActivity.value = stats.value.recent_activity || [];

    // 2. Sauvegarder dans le cache
    localStorage.setItem('admin_dashboard_cache', JSON.stringify({
      stats: stats.value,
      activity: recentActivity.value
    }));

  } catch (err) {
    console.error("Erreur Dashboard Admin:", err);
  }
  isLoading.value = false;
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

// --- CYCLE DE VIE ---
onMounted(fetchStats);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 40px; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: flex-end; }
.topbar-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; background: linear-gradient(90deg, #fff, #a371f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }
.topbar-right { display: flex; gap: 12px; align-items: center; }

.status-chip { display: flex; align-items: center; gap: 8px; padding: 6px 12px; background: rgba(35, 134, 54, 0.1); border: 1px solid rgba(35, 134, 54, 0.2); border-radius: 20px; font-size: 11px; font-weight: 700; color: #3fb950; }
.status-dot { width: 6px; height: 6px; background: #3fb950; border-radius: 50%; box-shadow: 0 0 8px #3fb950; animation: pulse 2s infinite; }
.date-chip { font-size: 12px; font-weight: 600; color: #8b949e; text-transform: capitalize; }

/* KPI Grid */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.kpi-card { background: #0d1117; border: 1px solid #21262d; border-radius: 20px; padding: 24px; position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 20px; transition: all 0.3s; }
.kpi-card:hover { border-color: #a371f7; transform: translateY(-4px); }

.kpi-header { display: flex; justify-content: space-between; align-items: flex-start; }
.kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.kpi-icon.purple { background: rgba(163, 113, 247, 0.1); color: #a371f7; }
.kpi-icon.blue { background: rgba(56, 139, 253, 0.1); color: #58a6ff; }
.kpi-icon.red { background: rgba(248, 81, 73, 0.1); color: #f85149; }
.kpi-icon.gold { background: rgba(210, 153, 34, 0.1); color: #d29922; }
.kpi-icon svg { width: 22px; height: 22px; }

.kpi-trend { font-size: 11px; font-weight: 800; padding: 4px 8px; border-radius: 6px; }
.kpi-trend.up { background: rgba(35, 134, 54, 0.1); color: #3fb950; }
.kpi-trend.down { background: rgba(248, 81, 73, 0.1); color: #f85149; }
.kpi-trend.neutral { background: rgba(139, 148, 158, 0.1); color: #8b949e; }

.kpi-value { font-size: 32px; font-weight: 800; }
.kpi-label { font-size: 12px; color: #8b949e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.kpi-footer { border-top: 1px solid #21262d; padding-top: 12px; }
.kpi-update { font-size: 10px; color: #484f58; font-weight: 500; }

.kpi-glow { position: absolute; bottom: -20px; right: -20px; width: 80px; height: 80px; border-radius: 50%; filter: blur(40px); opacity: 0.1; }
.kpi-glow.purple { background: #a371f7; }
.kpi-glow.blue { background: #58a6ff; }
.kpi-glow.red { background: #f85149; }
.kpi-glow.gold { background: #d29922; }

/* Main Grid */
.main-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; }
.panel { background: #0d1117; border: 1px solid #21262d; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; }
.panel-header { padding: 24px; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center; }
.panel-title { font-size: 16px; font-weight: 700; color: #f0f6fc; }

.activity-list { padding: 24px; display: flex; flex-direction: column; gap: 20px; }
.activity-item { display: flex; gap: 16px; position: relative; }
.activity-marker { width: 4px; border-radius: 2px; background: #a371f7; flex-shrink: 0; }
.activity-content { flex: 1; }
.activity-text { font-size: 14px; color: #e6edf3; margin-bottom: 4px; }
.activity-time { font-size: 11px; color: #8b949e; }

/* Alerts */
.alert-list { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.alert-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; border: 1px solid transparent; }
.alert-item.warning { background: rgba(210, 153, 34, 0.05); border-color: rgba(210, 153, 34, 0.2); }
.alert-item.info { background: rgba(56, 139, 253, 0.05); border-color: rgba(56, 139, 253, 0.2); }

.alert-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; }
.warning .alert-icon { background: #d29922; color: #0d1117; }
.info .alert-icon { background: #58a6ff; color: #0d1117; }

.alert-body { flex: 1; }
.alert-title { font-size: 14px; font-weight: 700; color: #f0f6fc; }
.alert-desc { font-size: 12px; color: #8b949e; margin-top: 2px; }
.alert-action { font-size: 12px; font-weight: 700; color: #a371f7; text-decoration: none; padding: 6px 12px; border: 1px solid rgba(163, 113, 247, 0.2); border-radius: 8px; transition: all 0.2s; }
.alert-action:hover { background: rgba(163, 113, 247, 0.1); border-color: #a371f7; }

.empty-state { text-align: center; padding: 40px; color: #484f58; }
.btn-text { background: none; border: none; color: #a371f7; font-size: 13px; font-weight: 600; cursor: pointer; }

/* Animations */
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pulse { 0% { transform: scale(0.95); opacity: 0.7; } 50% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.7; } }

@media (max-width: 1100px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .main-grid { grid-template-columns: 1fr; }
}
</style>
