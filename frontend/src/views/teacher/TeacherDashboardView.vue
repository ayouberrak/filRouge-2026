<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== HEADER ===== -->
        <header class="dash-header animate-in">
          <div class="dash-header-left">
            <div class="header-greeting">
              <div class="greeting-avatar">{{ user.first_name?.charAt(0)?.toUpperCase() }}</div>
              <div>
                <div class="greeting-sub">{{ formattedDate }}</div>
                <h1 class="greeting-title">Bonjour, {{ user.first_name }} <span class="greeting-role">· Formateur</span></h1>
              </div>
            </div>
          </div>
          <div class="dash-header-right">
            <div class="live-badge">
              <span class="live-dot"></span>
              Session Active
            </div>
            <button class="btn-new-brief" @click="router.push('/teacher/briefs/create')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
              Nouveau Brief
            </button>
          </div>
        </header>

        <!-- ===== LOADING ===== -->
        <div v-if="isLoading" style="text-align: center; padding: 20px;">Chargement en cours...</div>

        <template v-else>

          <!-- ===== KPI STATS ===== -->
          <div class="kpi-grid animate-in" style="animation-delay: 0.05s">

            <div class="kpi-card" @click="router.push('/teacher/students')">
              <div class="kpi-icon kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-label">Effectif Total</div>
                <div class="kpi-value">{{ stats.total_students }}</div>
                <div class="kpi-sub">étudiants actifs</div>
              </div>
              <div class="kpi-trend">→</div>
            </div>

            <div class="kpi-card kpi-card--urgent" @click="router.push('/teacher/submissions')">
              <div class="kpi-icon kpi-icon--amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-label">À Corriger</div>
                <div class="kpi-value kpi-value--amber">{{ stats.pending_deliverables }}</div>
                <div class="kpi-sub">livrables en attente</div>
              </div>
              <div v-if="stats.pending_deliverables > 0" class="kpi-alert-dot"></div>
              <div class="kpi-trend">→</div>
            </div>

            <div class="kpi-card">
              <div class="kpi-icon kpi-icon--red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="14" x2="8" y2="14" stroke-linecap="round" stroke-width="2.5"/><line x1="12" y1="14" x2="12" y2="14" stroke-linecap="round" stroke-width="2.5"/><line x1="16" y1="14" x2="16" y2="14" stroke-linecap="round" stroke-width="2.5"/></svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-label">Absences</div>
                <div class="kpi-value kpi-value--red">{{ String(stats.absences_today).padStart(2, '0') }}</div>
                <div class="kpi-sub">ce jour</div>
              </div>
              <div class="kpi-trend">→</div>
            </div>

            <div class="kpi-card">
              <div class="kpi-icon kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-label">Progression</div>
                <div class="kpi-value kpi-value--green">{{ stats.global_progression }}<span class="kpi-pct">%</span></div>
                <div class="kpi-sub">taux global</div>
              </div>
              <!-- Progress arc -->
              <svg class="kpi-arc" viewBox="0 0 36 36">
                <circle class="arc-bg" cx="18" cy="18" r="15"/>
                <circle class="arc-fill"
                  cx="18" cy="18" r="15"
                  :stroke-dasharray="`${(stats.global_progression / 100) * 94.2} 94.2`"
                />
              </svg>
            </div>
          </div>

          <!-- ===== MAIN GRID ===== -->
          <div class="main-grid">

            <!-- ===== SQUADS SECTION (LEFT) ===== -->
            <section class="squads-panel animate-in" style="animation-delay: 0.1s">
              <div class="panel-header">
                <div>
                  <h2 class="panel-title">Squads Actifs</h2>
                  <p class="panel-desc">{{ squads.length }} groupe{{ squads.length > 1 ? 's' : '' }} · {{ squads.reduce((a, s) => a + (s.members?.length || 0), 0) }} étudiants</p>
                </div>
                <router-link to="/teacher/squads" class="panel-link">
                  Gérer les squads
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </router-link>
              </div>

              <div v-if="squads.length === 0" class="panel-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <p>Aucun squad configuré</p>
                <span>Créez des groupes dans la section Squads</span>
              </div>

              <div v-else class="squads-grid">
                <div v-for="(squad, si) in squads" :key="squad.id" class="squad-card" :style="{ animationDelay: (si * 0.07) + 's' }">
                  <div class="squad-card-top">
                    <div class="squad-id-block">
                      <span class="squad-label">SQUAD</span>
                      <span class="squad-name">{{ squad.name }}</span>
                    </div>
                    <span class="squad-member-badge">{{ squad.members?.length || 0 }} membres</span>
                  </div>
                  <div class="squad-avatars">
                    <div v-for="(m, mi) in (squad.members || []).slice(0, 6)" :key="m.id" class="squad-avatar-wrap" :style="{ zIndex: 10 - mi, marginLeft: mi > 0 ? '-10px' : '0' }">
                      <img :src="m.avatar_url || getAvatar(m.first_name)" class="squad-avatar" :title="`${m.first_name} ${m.last_name}`" />
                    </div>
                    <div v-if="(squad.members || []).length > 6" class="squad-avatar-more">
                      +{{ squad.members.length - 6 }}
                    </div>
                  </div>
                  <div class="squad-progress-bar">
                    <div class="squad-progress-fill" :style="{ width: squad.progress ? squad.progress + '%' : '60%' }"></div>
                  </div>
                </div>
              </div>
            </section>

            <!-- ===== LEADERBOARD (RIGHT) ===== -->
            <aside class="leaderboard-panel animate-in" style="animation-delay: 0.15s">
              <div class="panel-header">
                <div>
                  <h2 class="panel-title">Progrès des Briefs</h2>
                  <p class="panel-desc">Top performers · Briefs Validés</p>
                </div>
              </div>

              <div v-if="leaderboard.length === 0" class="panel-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <p>Aucun point attribué</p>
                <span>Les points s'affichent après validation des briefs</span>
              </div>

              <div v-else class="leaderboard-list">
                <div v-for="(student, idx) in leaderboard.slice(0, 8)" :key="student.id" class="rank-row" :class="{ 'rank-row--top3': idx < 3 }">
                  <!-- Rank medal or number -->
                  <div class="rank-pos" :class="[`rank-pos--${idx + 1}`, idx === 0 ? 'medal-gold' : idx === 1 ? 'medal-silver' : idx === 2 ? 'medal-bronze' : '']">
                    <svg v-if="idx < 3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.09 6.26L21 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <!-- Avatar -->
                  <img :src="student.avatar_url || getAvatar(student.first_name)" class="rank-avatar" />
                  <!-- Info -->
                  <div class="rank-info">
                    <span class="rank-name">{{ student.first_name }} {{ student.last_name }}</span>
                    <div class="rank-bar-wrap">
                      <div class="rank-bar" :style="{ width: ((student.validated_briefs_count / (leaderboard[0]?.validated_briefs_count || 1)) * 100) + '%', background: idx === 0 ? '#f2bc1b' : idx === 1 ? '#8b949e' : idx === 2 ? '#d45a1e' : '#388bfd' }"></div>
                    </div>
                  </div>
                  <!-- Stats -->
                  <div class="rank-pts">
                    <span class="rank-pts-val">{{ student.validated_briefs_count || 0 }}</span>
                    <span class="rank-pts-label">v-briefs</span>
                  </div>
                </div>
              </div>

              <button class="leaderboard-footer" @click="router.push('/teacher/students')">
                Voir la progression de tous les étudiants
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </aside>

          </div>

          <!-- ===== QUICK ACTIONS ===== -->
          <div class="quick-actions animate-in" style="animation-delay: 0.2s">
            <div class="quick-label">Accès Rapide</div>
            <div class="quick-grid">
              <button class="quick-btn" @click="router.push('/teacher/briefs')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v13a2 2 0 002 2h14a2 2 0 002-2V7M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>
                <span>Briefs</span>
              </button>
              <button class="quick-btn" @click="router.push('/teacher/submissions')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Corrections</span>
              </button>
              <button class="quick-btn" @click="router.push('/teacher/squads')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <span>Squads</span>
              </button>
              <button class="quick-btn" @click="router.push('/teacher/students')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <span>Classement</span>
              </button>
              <button class="quick-btn" @click="router.push('/teacher/absences')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Absences</span>
              </button>
              <button class="quick-btn" @click="router.push('/teacher/activities')">
                <svg class="quick-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Activités</span>
              </button>
            </div>
          </div>

        </template>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import api from '../../services/api';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Formateur', last_name: '' });
const stats = ref({ total_students: 0, pending_deliverables: 0, absences_today: 0, global_progression: 0 });
const squads = ref([]);
const leaderboard = ref([]);
const isLoading = ref(true);
const classroomId = ref(null);

const formattedDate = computed(() =>
  new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date())
    .replace(/^\w/, c => c.toUpperCase())
);

const getAvatar = (name) =>
  `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=161b22&color=388bfd&bold=true`;

onMounted(async () => {
  const cached = localStorage.getItem('teacher_dashboard_cache');
  if (cached) {
    const cacheData = JSON.parse(cached);
    stats.value = cacheData.stats;
    squads.value = cacheData.squads;
    leaderboard.value = cacheData.leaderboard;
    classroomId.value = cacheData.classroomId;
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }
  
  try {
    const [classroomsRes, statsRes, leaderboardRes] = await Promise.all([
      api.get('/classrooms/my'),
      api.get('/dashboard/stats'),
      api.get('/leaderboard')
    ]);

    const classrooms = classroomsRes.data?.data || classroomsRes.data || [];
    classroomId.value = classrooms[0]?.id ?? null;

    stats.value = { ...stats.value, ...(statsRes.data?.data || {}) };
    leaderboard.value = leaderboardRes.data?.data || [];

    if (classroomId.value) {
      const squadsRes = await api.get('/squads', { params: { classroom_id: classroomId.value } });
      squads.value = squadsRes.data?.squads?.data || squadsRes.data?.squads || [];
    }

    localStorage.setItem('teacher_dashboard_cache', JSON.stringify({
      stats: stats.value,
      squads: squads.value,
      leaderboard: leaderboard.value,
      classroomId: classroomId.value
    }));

  } catch (err) {
    console.error("Erreur Dashboard Teacher:", err);
  }

  isLoading.value = false;
});

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.4) transparent; }
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }
.content { padding: 44px 52px; max-width: 1500px; margin: 0 auto; display: flex; flex-direction: column; gap: 36px; }

/* ===== HEADER ===== */
.dash-header { display: flex; justify-content: space-between; align-items: center; }
.header-greeting { display: flex; align-items: center; gap: 18px; }
.greeting-avatar { width: 48px; height: 48px; border-radius: 13px; background: linear-gradient(135deg, rgba(56,139,253,0.2), rgba(56,139,253,0.08)); border: 1px solid rgba(56,139,253,0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; color: #388bfd; flex-shrink: 0; }
.greeting-sub { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 5px; }
.greeting-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; line-height: 1; }
.greeting-role { color: #484f58; font-weight: 500; font-size: 20px; }
.dash-header-right { display: flex; align-items: center; gap: 14px; }
.live-badge { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 9px; background: rgba(63,185,80,0.06); border: 1px solid rgba(63,185,80,0.2); font-size: 12px; font-weight: 700; color: #3fb950; }
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: #3fb950; box-shadow: 0 0 8px rgba(63,185,80,0.6); animation: pulse 2s ease-in-out infinite; }
.btn-new-brief { display: flex; align-items: center; gap: 7px; background: #238636; color: #fff; border: 1px solid #2ea043; padding: 10px 18px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-new-brief svg { width: 14px; height: 14px; }
.btn-new-brief:hover { background: #2ea043; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(35,134,54,0.3); }

/* ===== SKELETON LOADING ===== */
.loading-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }



/* ===== KPI GRID ===== */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }

.kpi-card {
  position: relative; display: flex; align-items: center; gap: 18px;
  padding: 22px 24px; border-radius: 16px; cursor: pointer;
  background: linear-gradient(145deg, rgba(22,27,34,0.9), rgba(13,17,23,0.95));
  border: 1px solid rgba(48,54,61,0.5); transition: all 0.22s; overflow: hidden;
}
.kpi-card::before { content: ''; position: absolute; inset: 0; opacity: 0; transition: opacity 0.22s; background: radial-gradient(circle at 30% 50%, rgba(56,139,253,0.05), transparent 70%); }
.kpi-card:hover { border-color: rgba(56,139,253,0.3); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,0.25); }
.kpi-card:hover::before { opacity: 1; }
.kpi-card--urgent { border-color: rgba(210,153,34,0.2); }

.kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icon svg { width: 20px; height: 20px; }
.kpi-icon--blue { background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); color: #388bfd; }
.kpi-icon--amber { background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.2); color: #d29922; }
.kpi-icon--red { background: rgba(248,81,73,0.1); border: 1px solid rgba(248,81,73,0.2); color: #f85149; }
.kpi-icon--green { background: rgba(63,185,80,0.1); border: 1px solid rgba(63,185,80,0.2); color: #3fb950; }

.kpi-body { flex: 1; min-width: 0; }
.kpi-label { font-size: 10px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.14em; margin-bottom: 5px; }
.kpi-value { font-size: 30px; font-weight: 900; color: #fff; line-height: 1; letter-spacing: -0.03em; }
.kpi-value--amber { color: #d29922; }
.kpi-value--red { color: #f85149; }
.kpi-value--green { color: #3fb950; }
.kpi-pct { font-size: 18px; font-weight: 700; }
.kpi-sub { font-size: 11px; color: #484f58; margin-top: 3px; }
.kpi-trend { font-size: 16px; color: #30363d; transition: all 0.2s; flex-shrink: 0; }
.kpi-card:hover .kpi-trend { color: #388bfd; transform: translateX(3px); }
.kpi-alert-dot { position: absolute; top: 14px; right: 14px; width: 9px; height: 9px; background: #d29922; border-radius: 50%; box-shadow: 0 0 10px rgba(210,153,34,0.5); animation: pulse 2s ease-in-out infinite; }

.kpi-arc { position: absolute; right: 18px; bottom: 12px; width: 52px; height: 52px; transform: rotate(-90deg); opacity: 0.35; }
.arc-bg { fill: none; stroke: rgba(63,185,80,0.15); stroke-width: 3; }
.arc-fill { fill: none; stroke: #3fb950; stroke-width: 3; stroke-linecap: round; transition: stroke-dasharray 1s ease; }

/* ===== MAIN GRID ===== */
.main-grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start; }

/* Panel shared */
.squads-panel, .leaderboard-panel {
  background: linear-gradient(145deg, rgba(22,27,34,0.85), rgba(13,17,23,0.9));
  border: 1px solid rgba(48,54,61,0.5); border-radius: 18px; overflow: hidden;
  backdrop-filter: blur(8px);
}
.panel-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 28px 28px 24px; border-bottom: 1px solid rgba(48,54,61,0.3); }
.panel-title { font-size: 16px; font-weight: 800; color: #f0f6fc; margin-bottom: 3px; }
.panel-desc { font-size: 12px; color: #8b949e; }
.panel-link { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #388bfd; text-decoration: none; transition: gap 0.15s; }
.panel-link svg { width: 13px; height: 13px; transition: transform 0.15s; }
.panel-link:hover { gap: 9px; }
.panel-link:hover svg { transform: translateX(2px); }
.panel-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 56px 40px; color: #484f58; text-align: center; }
.panel-empty span { font-size: 11px; color: #30363d; max-width: 200px; line-height: 1.5; }
.panel-empty svg { width: 40px; height: 40px; opacity: 0.2; }
.panel-empty p { font-size: 13px; }

/* ===== SQUADS ===== */
.squads-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; padding: 24px; }
.squad-card { background: rgba(13,17,23,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 16px; transition: all 0.2s; animation: fadeUp 0.4s both; }
.squad-card:hover { border-color: rgba(56,139,253,0.25); transform: translateY(-2px); }
.squad-card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.squad-id-block { display: flex; flex-direction: column; }
.squad-label { font-size: 9px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 2px; }
.squad-name { font-size: 20px; font-weight: 900; color: #fff; letter-spacing: -0.02em; }
.squad-member-badge { font-size: 10px; font-weight: 800; color: #8b949e; background: rgba(48,54,61,0.4); padding: 3px 9px; border-radius: 6px; }
.squad-avatars { display: flex; align-items: center; }
.squad-avatar-wrap { border-radius: 9px; overflow: hidden; width: 36px; height: 36px; border: 2px solid rgba(13,17,23,0.8); flex-shrink: 0; }
.squad-avatar { width: 100%; height: 100%; object-fit: cover; }
.squad-avatar-more { width: 36px; height: 36px; border-radius: 9px; background: rgba(48,54,61,0.5); border: 2px solid rgba(13,17,23,0.8); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #8b949e; margin-left: -10px; flex-shrink: 0; }
.squad-progress-bar { height: 3px; background: rgba(48,54,61,0.4); border-radius: 3px; overflow: hidden; }
.squad-progress-fill { height: 100%; background: linear-gradient(90deg, #388bfd, #3fb950); border-radius: 3px; transition: width 1s ease; }

/* ===== LEADERBOARD ===== */
.leaderboard-list { display: flex; flex-direction: column; }
.rank-row { display: flex; align-items: center; gap: 12px; padding: 14px 24px; border-bottom: 1px solid rgba(48,54,61,0.15); transition: background 0.15s; }
.rank-row:hover { background: rgba(56,139,253,0.03); }
.rank-row--top3 { background: rgba(242,188,27,0.02); }
.rank-pos { font-size: 13px; font-weight: 900; line-height: 1; width: 28px; text-align: center; flex-shrink: 0; color: #484f58; display: flex; align-items: center; justify-content: center; }
.rank-pos svg { width: 18px; height: 18px; }
.medal-gold { color: #f2bc1b; filter: drop-shadow(0 0 6px rgba(242,188,27,0.4)); }
.medal-silver { color: #8b949e; filter: drop-shadow(0 0 4px rgba(139,148,158,0.3)); }
.medal-bronze { color: #c76b3a; filter: drop-shadow(0 0 4px rgba(199,107,58,0.3)); }
.rank-avatar { width: 34px; height: 34px; border-radius: 9px; object-fit: cover; border: 1px solid rgba(48,54,61,0.5); flex-shrink: 0; }
.rank-info { flex: 1; min-width: 0; }
.rank-name { font-size: 13px; font-weight: 700; color: #f0f6fc; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 5px; }
.rank-bar-wrap { height: 3px; background: rgba(48,54,61,0.4); border-radius: 3px; overflow: hidden; }
.rank-bar { height: 100%; border-radius: 3px; transition: width 1s ease; }
.rank-pts { display: flex; flex-direction: column; align-items: flex-end; flex-shrink: 0; }
.rank-pts-val { font-size: 14px; font-weight: 900; color: #fff; font-family: 'JetBrains Mono', monospace; line-height: 1; }
.rank-pts-label { font-size: 9px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
.leaderboard-footer { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 16px; background: transparent; border: none; border-top: 1px solid rgba(48,54,61,0.3); color: #8b949e; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.leaderboard-footer svg { width: 13px; height: 13px; transition: transform 0.15s; }
.leaderboard-footer:hover { color: #388bfd; background: rgba(56,139,253,0.04); }
.leaderboard-footer:hover svg { transform: translateX(3px); }

/* ===== QUICK ACTIONS ===== */
.quick-actions { display: flex; align-items: center; gap: 20px; padding: 20px 24px; background: linear-gradient(145deg, rgba(22,27,34,0.6), rgba(13,17,23,0.7)); border: 1px solid rgba(48,54,61,0.4); border-radius: 14px; }
.quick-label { font-size: 10px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.18em; white-space: nowrap; }
.quick-grid { display: flex; gap: 10px; flex: 1; flex-wrap: wrap; }
.quick-btn { display: flex; align-items: center; gap: 9px; padding: 10px 16px; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.4); border-radius: 10px; font-size: 13px; font-weight: 700; color: #c9d1d9; cursor: pointer; transition: all 0.18s; font-family: inherit; white-space: nowrap; }
.quick-icon { width: 16px; height: 16px; flex-shrink: 0; color: #8b949e; transition: color 0.18s; }
.quick-btn:hover .quick-icon { color: #388bfd; }
.quick-btn:hover { border-color: rgba(56,139,253,0.3); color: #fff; background: rgba(56,139,253,0.06); transform: translateY(-1px); }

/* Animations */
.animate-in { animation: fadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.85); } }
</style>


