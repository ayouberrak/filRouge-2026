<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Activités</h1>
            <p class="topbar-sub">Planning & immersion</p>
          </div>
        </div>

        <div class="topbar-right animate-in">
          <div v-if="!isLoading && nextActivity" class="next-pulse">
            <span class="pulse-dot"></span>
            <span class="next-label">PROCHAIN : {{ nextActivity.title }}</span>
          </div>
        </div>
      </header>

      <div class="content">
        <!-- Page Header -->
        <div class="page-header animate-in">
          <div class="header-glance">
            <div class="nadi-badge">PARCOURS IMMERSIF</div>
            <h2 class="glance-title">Votre Progression.</h2>
            <p class="glance-desc">Suivez vos sessions de formation, vos live codings et vos veilles technologiques en temps réel.</p>
          </div>
          <div class="points-totem">
            <div class="totem-value">{{ totalPoints }}</div>
            <div class="totem-label">XP TOTAL</div>
          </div>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-row animate-in">
          <div class="stat-card">
            <div class="stat-icon icon-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
            <div class="stat-info">
              <span class="stat-v">{{ liveCodingActivities.length }}</span>
              <span class="stat-l">Live Coding</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg></div>
            <div class="stat-info">
              <span class="stat-v">{{ veilleActivities.length }}</span>
              <span class="stat-l">Veilles Tech</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon icon-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div>
            <div class="stat-info">
              <span class="stat-v">{{ workshopActivities.length }}</span>
              <span class="stat-l">Workshops</span>
            </div>
          </div>
        </div>

        <!-- Kan-style columns -->
        <div class="kan-container">
          
          <!-- Column: Live Coding -->
          <div class="kan-col">
            <div class="col-head">
              <span class="head-dot dot-blue"></span>
              <h3 class="head-title">Live Coding</h3>
              <span class="head-count">{{ liveCodingActivities.length }}</span>
            </div>
            
            <div v-if="isLoading" class="col-list">
              <div v-for="i in 2" :key="i" class="card-skeleton shimmer"></div>
            </div>
            <div v-else class="col-list">
              <div v-for="(act, idx) in liveCodingActivities" :key="act.id" 
                   class="act-card card-blue animate-in" 
                   :style="{ animationDelay: `${idx * 0.1}s` }"
                   @click="openDetails(act)">
                <div class="act-glow"></div>
                <div class="act-top">
                  <span class="act-time">{{ formatTime(act.scheduled_at) }}</span>
                  <span class="act-pts">+{{ act.points }} XP</span>
                </div>
                <h4 class="act-title">{{ act.title }}</h4>
                <p class="act-desc">{{ act.description }}</p>
                <div v-if="act.students?.length" class="act-meta">
                  <div class="meta-avatars">
                    <img v-for="s in act.students.slice(0, 3)" :key="s.id" :src="s.avatar_url || 'https://i.pravatar.cc/100'" class="meta-avatar" />
                    <span v-if="act.students.length > 3" class="meta-more">+{{ act.students.length - 3 }}</span>
                  </div>
                </div>
              </div>
              <div v-if="!liveCodingActivities.length" class="empty-col">Aucune session active</div>
            </div>
          </div>

          <!-- Column: Veille Tech -->
          <div class="kan-col">
            <div class="col-head">
              <span class="head-dot dot-green"></span>
              <h3 class="head-title">Veille Tech</h3>
              <span class="head-count">{{ veilleActivities.length }}</span>
            </div>
            
            <div v-if="isLoading" class="col-list">
              <div v-for="i in 2" :key="i" class="card-skeleton shimmer"></div>
            </div>
            <div v-else class="col-list">
              <div v-for="(act, idx) in veilleActivities" :key="act.id" 
                   class="act-card card-green animate-in" 
                   :style="{ animationDelay: `${idx * 0.1}s` }"
                   @click="openDetails(act)">
                <div class="act-glow"></div>
                <div class="act-top">
                  <span class="act-time">{{ formatTime(act.scheduled_at) }}</span>
                  <span class="act-pts">+{{ act.points }} XP</span>
                </div>
                <h4 class="act-title">{{ act.title }}</h4>
                <p class="act-desc">{{ act.description }}</p>
                <div class="act-footer">
                   <div class="duration-badge">
                     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                     {{ act.duration }}
                   </div>
                </div>
              </div>
              <div v-if="!veilleActivities.length" class="empty-col">Aucune veille à venir</div>
            </div>
          </div>

          <!-- Column: Workshop -->
          <div class="kan-col">
            <div class="col-head">
              <span class="head-dot dot-red"></span>
              <h3 class="head-title">Workshops</h3>
              <span class="head-count">{{ workshopActivities.length }}</span>
            </div>
            
            <div v-if="isLoading" class="col-list">
              <div v-for="i in 2" :key="i" class="card-skeleton shimmer"></div>
            </div>
            <div v-else class="col-list">
              <div v-for="(act, idx) in workshopActivities" :key="act.id" 
                   class="act-card card-red animate-in" 
                   :style="{ animationDelay: `${idx * 0.1}s` }"
                   @click="openDetails(act)">
                <div class="act-glow"></div>
                <div class="act-top">
                  <span class="act-time">{{ formatTime(act.scheduled_at) }}</span>
                  <span class="act-pts">+{{ act.points }} XP</span>
                </div>
                <h4 class="act-title">{{ act.title }}</h4>
                <div class="act-progress">
                   <div class="prog-label">Progression</div>
                   <div class="prog-track">
                     <div class="prog-fill" :style="{ width: (act.progress || 0) + '%' }"></div>
                   </div>
                   <div class="prog-val">{{ act.progress || 0 }}%</div>
                </div>
              </div>
              <div v-if="!workshopActivities.length" class="empty-col">Aucun workshop programmé</div>
            </div>
          </div>

        </div>
      </div>

      <!-- Detailed Activity Modal -->
      <Transition name="slide-up">
        <div v-if="selectedActivity" class="modal-overlay" @click.self="selectedActivity = null">
          <div class="modal-container">
            <header class="modal-header">
              <div class="modal-h-left">
                <div class="type-pill" :class="`pill-${selectedActivity.activity_type}`">
                  {{ selectedActivity.activity_type.replace('_', ' ') }}
                </div>
                <h2 class="modal-title">{{ selectedActivity.title }}</h2>
              </div>
              <button class="modal-close" @click="selectedActivity = null">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
              </button>
            </header>

            <div class="modal-body">
              <div class="modal-main">
                <section class="m-section">
                  <h4 class="m-label">DESCRIPTION</h4>
                  <p class="m-text">{{ selectedActivity.description }}</p>
                </section>

                <section class="m-section" v-if="selectedActivity.objectives">
                  <h4 class="m-label">OBJECTIFS</h4>
                  <ul class="m-list">
                    <li v-for="(obj, idx) in formatList(selectedActivity.objectives)" :key="idx">{{ obj }}</li>
                  </ul>
                </section>

                <section class="m-section" v-if="selectedActivity.exploration_points">
                  <h4 class="m-label">POINTS D'EXPLORATION</h4>
                  <div class="m-text" v-html="renderMD(selectedActivity.exploration_points)"></div>
                </section>
              </div>

              <aside class="modal-side">
                <div class="side-item">
                  <span class="s-label">PLANIFICATION</span>
                  <span class="s-value">{{ formatTime(selectedActivity.scheduled_at) }}</span>
                </div>
                <div class="side-item">
                  <span class="s-label">DURÉE</span>
                  <span class="s-value">{{ selectedActivity.duration }}</span>
                </div>
                <div class="side-item">
                  <span class="s-label">RÉCOMPENSE</span>
                  <span class="s-value text-gold">{{ selectedActivity.points }} XP</span>
                </div>
                
                <div class="side-item" v-if="selectedActivity.students?.length">
                  <span class="s-label">VOS COLLÈGUES ({{ selectedActivity.students.length }})</span>
                  <div class="coworkers-list">
                    <div v-for="s in selectedActivity.students" :key="s.id" class="coworker">
                      <img :src="s.avatar_url || 'https://i.pravatar.cc/100'" class="c-avatar" />
                      <span class="c-name">{{ s.first_name }}</span>
                    </div>
                  </div>
                </div>
              </aside>
            </div>
            
            <footer class="modal-footer">
              <button class="btn-action" @click="selectedActivity = null">J'ai compris</button>
            </footer>
          </div>
        </div>
      </Transition>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const activities = ref([]);
const isLoading = ref(true);
const selectedActivity = ref(null);

const liveCodingActivities = computed(() => activities.value.filter(a => a.activity_type === 'live_coding'));
const veilleActivities = computed(() => activities.value.filter(a => a.activity_type === 'veille'));
const workshopActivities = computed(() => activities.value.filter(a => a.activity_type === 'workshop'));

const totalPoints = computed(() => activities.value.reduce((sum, a) => sum + (a.points || 0), 0));

const nextActivity = computed(() => {
  const future = activities.value.filter(a => new Date(a.scheduled_at) > new Date());
  return future[0] || null;
});

const formatTime = (dateStr) => {
  if (!dateStr) return 'TBD';
  const d = new Date(dateStr);
  return d.toLocaleString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const formatList = (text) => text ? text.split('\n').filter(l => l.trim()) : [];

const renderMD = (text) => {
  if (!text) return '';
  return text.replace(/- (.*)/g, '<li>$1</li>').replace(/### (.*)/g, '<h5>$1</h5>');
};

const openDetails = (act) => {
  selectedActivity.value = act;
};

const fetchActivities = async () => {
  try {
    isLoading.value = true;
    const res = await api.get('/activities/me');
    activities.value = res.data.data || res.data;
  } catch (err) {
    console.error('Error fetching activities:', err);
  } finally {
    isLoading.value = false;
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  fetchActivities();
});
</script>

<style scoped>
.layout {
  display: flex;
  height: 100vh;
  background: #010409;
  color: #e6edf3;
  font-family: 'Inter', sans-serif;
  overflow: hidden;
}

.main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar {
  height: 72px;
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #21262d;
  background: rgba(1, 4, 9, 0.8);
  backdrop-filter: blur(12px);
  position: sticky; top: 0; z-index: 100;
}

.topbar-icon {
  width: 40px; height: 40px;
  background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #58a6ff;
}

.topbar-title { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; }
.topbar-sub { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; }

.next-pulse {
  display: flex; align-items: center; gap: 10px;
  background: #161b22; padding: 8px 16px; border-radius: 20px; border: 1px solid #30363d;
}

.pulse-dot {
  width: 8px; height: 8px; background: #238636; border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(35, 134, 54, 0.7);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(35, 134, 54, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(35, 134, 54, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(35, 134, 54, 0); }
}

.next-label { font-size: 11px; font-weight: 700; color: #8b949e; }

/* ─── Content ───────────────────────────────────────────────────────────────── */
.content {
  padding: 40px; max-width: 1400px; width: 100%; margin: 0 auto;
}

.page-header {
  display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;
}

.nadi-badge {
  display: inline-block; padding: 4px 10px; background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2); border-radius: 20px;
  font-size: 10px; font-weight: 700; color: #58a6ff; letter-spacing: 0.05em; margin-bottom: 12px;
}

.glance-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; }
.glance-desc { color: #8b949e; font-size: 15px; max-width: 500px; margin-top: 8px; }

.points-totem {
  text-align: right;
}
.totem-value { font-size: 40px; font-weight: 900; color: #d29922; line-height: 1; }
.totem-label { font-size: 12px; font-weight: 700; color: #8b949e; margin-top: 4px; }

/* ─── Stats Row ─────────────────────────────────────────────────────────────── */
.stats-row {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px;
}

.stat-card {
  background: #0d1117; border: 1px solid #30363d; border-radius: 20px;
  padding: 20px 24px; display: flex; align-items: center; gap: 16px;
  transition: all 0.3s;
}

.stat-card:hover { border-color: #58a6ff; transform: translateY(-2px); }

.stat-icon {
  width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
}
.icon-blue { background: rgba(56, 139, 253, 0.1); color: #58a6ff; }
.icon-green { background: rgba(35, 134, 54, 0.1); color: #3fb950; }
.icon-red { background: rgba(248, 81, 73, 0.1); color: #f85149; }

.stat-icon svg { width: 24px; height: 24px; }
.stat-v { font-size: 24px; font-weight: 800; display: block; line-height: 1.1; }
.stat-l { font-size: 11px; font-weight: 600; color: #8b949e; }

/* ─── Kan Container ─────────────────────────────────────────────────────────── */
.kan-container {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; align-items: start;
}

.kan-col {
  display: flex; flex-direction: column; gap: 20px;
}

.col-head {
  display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 2px solid #21262d;
}

.head-dot { width: 8px; height: 8px; border-radius: 50%; }
.dot-blue { background: #58a6ff; box-shadow: 0 0 10px #58a6ff; }
.dot-green { background: #3fb950; box-shadow: 0 0 10px #3fb950; }
.dot-red { background: #f85149; box-shadow: 0 0 10px #f85149; }

.head-title { font-size: 15px; font-weight: 700; flex: 1; }
.head-count { font-size: 12px; color: #484f58; font-weight: 600; }

.col-list { display: flex; flex-direction: column; gap: 16px; }

/* ─── Cards ────────────────────────────────────────────────────────────────── */
.act-card {
  background: #0d1117; border: 1px solid #30363d; border-radius: 18px;
  padding: 20px; position: relative; overflow: hidden; cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.act-card:hover {
  transform: translateY(-4px); border-color: #58a6ff;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.act-glow {
  position: absolute; inset: 0;
  background: radial-gradient(circle at 50% -20%, rgba(56, 139, 253, 0.1) 0%, transparent 70%);
  opacity: 0; transition: opacity 0.3s;
}
.act-card:hover .act-glow { opacity: 1; }

.act-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.act-time { font-size: 10px; font-weight: 700; color: #8b949e; text-transform: uppercase; }
.act-pts { font-size: 11px; font-weight: 800; color: #d29922; }

.act-title { font-size: 15px; font-weight: 700; color: #f0f6fc; line-height: 1.4; margin-bottom: 8px; }
.act-desc { font-size: 12px; color: #8b949e; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.act-meta { margin-top: 16px; padding-top: 16px; border-top: 1px solid #21262d; }
.meta-avatars { display: flex; align-items: center; }
.meta-avatar { width: 24px; height: 24px; border-radius: 6px; border: 2px solid #0d1117; margin-right: -8px; }
.meta-more { font-size: 10px; color: #8b949e; margin-left: 12px; font-weight: 600; }

.act-progress { margin-top: 16px; }
.prog-label { font-size: 10px; color: #8b949e; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
.prog-track { height: 6px; background: #21262d; border-radius: 3px; overflow: hidden; }
.prog-fill { height: 100%; background: #f85149; border-radius: 3px; }
.prog-val { font-size: 10px; color: #f85149; font-weight: 700; margin-top: 4px; text-align: right; }

/* ─── Modal ────────────────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px);
  z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 40px;
}

.modal-container {
  width: 100%; max-width: 900px; background: #0d1117; border: 1px solid #30363d;
  border-radius: 24px; overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;
}

.modal-header {
  padding: 24px 32px; background: #161b22; border-bottom: 1px solid #21262d;
  display: flex; justify-content: space-between; align-items: flex-start;
}

.type-pill {
  display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px;
}
.pill-live_coding { background: rgba(56, 139, 253, 0.1); color: #58a6ff; }
.pill-veille { background: rgba(63, 185, 80, 0.1); color: #3fb950; }
.pill-workshop { background: rgba(248, 81, 73, 0.1); color: #f85149; }

.modal-title { font-size: 24px; font-weight: 800; color: #f0f6fc; }
.modal-close { background: none; border: none; color: #484f58; cursor: pointer; transition: color 0.2s; display: flex; align-items: center; justify-content: center; }
.modal-close svg { width: 20px; height: 20px; }
.modal-close:hover { color: #f0f6fc; }

.modal-body { display: flex; overflow-y: auto; flex: 1; }
.modal-main { flex: 1; padding: 32px; display: flex; flex-direction: column; gap: 32px; }
.modal-side { width: 300px; background: #161b22; border-left: 1px solid #21262d; padding: 32px; display: flex; flex-direction: column; gap: 24px; }

.m-label { font-size: 11px; font-weight: 700; color: #58a6ff; letter-spacing: 0.1em; margin-bottom: 12px; }
.m-text { font-size: 14px; line-height: 1.7; color: #8b949e; }
.m-list { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.m-list li { position: relative; padding-left: 20px; font-size: 14px; color: #8b949e; }
.m-list li::before { content: "→"; position: absolute; left: 0; color: #58a6ff; font-weight: 700; }

.side-item { display: flex; flex-direction: column; gap: 4px; }
.s-label { font-size: 10px; font-weight: 700; color: #484f58; text-transform: uppercase; }
.s-value { font-size: 14px; font-weight: 600; color: #f0f6fc; }
.text-gold { color: #d29922; }

.coworkers-list { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.coworker { display: flex; align-items: center; gap: 10px; }
.c-avatar { width: 28px; height: 28px; border-radius: 6px; }
.c-name { font-size: 13px; font-weight: 600; color: #8b949e; }

.modal-footer { padding: 24px 32px; border-top: 1px solid #21262d; text-align: right; }
.duration-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 10px; background: #161b22; border: 1px solid #30363d;
  border-radius: 6px; font-size: 11px; font-weight: 600; color: #8b949e;
}
.duration-badge svg { width: 14px; height: 14px; }

.btn-action { padding: 10px 24px; background: #238636; border: none; border-radius: 10px; color: white; font-weight: 700; cursor: pointer; }

/* ─── States ────────────────────────────────────────────────────────────────── */
.card-skeleton { height: 140px; background: #161b22; border-radius: 18px; }
.shimmer { position: relative; overflow: hidden; }
.shimmer::after {
  content: ""; position: absolute; inset: 0; transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer { 100% { transform: translateX(100%); } }

.empty-col { padding: 40px 20px; text-align: center; font-size: 13px; color: #484f58; border: 1px dashed #21262d; border-radius: 18px; }

.animate-in { animation: fadeInUp 0.5s ease forwards; opacity: 0; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 1024px) {
  .kan-container, .stats-row { grid-template-columns: 1fr; }
  .modal-side { display: none; }
}
</style>