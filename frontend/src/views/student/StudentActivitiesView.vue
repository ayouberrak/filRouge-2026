<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Activités & Cours</h1>
            <p class="topbar-sub">Planification de votre parcours de formation</p>
          </div>
        </div>
      </header>

      <!-- Content -->
      <div class="content">
        <div v-if="loading" class="loading-state">
          <div class="loader-ripple"><div></div><div></div></div>
          <p>Synchronisation du calendrier...</p>
        </div>

        <div v-else-if="activities.length === 0" class="empty-state animate-in">
          <div class="empty-illustration">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              <circle cx="12" cy="12" r="10" stroke-dasharray="4 4" />
            </svg>
          </div>
          <h3>Aucune activité prévue</h3>
          <p>Profitez de ce temps libre pour avancer sur vos projets en cours.</p>
        </div>

        <div v-else class="activities-grid">
          <div 
            v-for="(activity, idx) in activities" 
            :key="activity.id" 
            class="activity-card animate-in"
            :style="{ animationDelay: (idx * 0.1) + 's' }"
          >
            <!-- Card Header -->
            <div class="card-header">
              <div class="type-badge" :class="activity.type.toLowerCase()">
                <span class="dot"></span>
                {{ activity.type }}
              </div>
              <div class="time-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ formatTime(activity.scheduled_at) }}
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body">
              <h2 class="activity-title">{{ activity.title }}</h2>
              <p class="activity-desc">{{ activity.description }}</p>
            </div>

            <!-- Card Footer -->
            <div class="card-footer">
              <div class="meta-info">
                <div class="meta-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                  <span>{{ activity.duration_minutes }} min</span>
                </div>
                <div class="meta-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M17 11l2 2 4-4"/></svg>
                  <span>Assigné</span>
                </div>
              </div>
              <div class="date-stamp">
                {{ formatDate(activity.scheduled_at) }}
              </div>
            </div>
            
            <!-- Glass Decoration -->
            <div class="card-glow"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Étudiant' });
const activities = ref([]);
const loading = ref(true);

const fetchActivities = async () => {
  try {
    const res = await api.get('/activities/me');
    activities.value = res.data.data || [];
  } catch (err) {
    console.error("Fetch error:", err);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' });
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(fetchActivities);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; font-family: 'Inter', sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }

.topbar { padding: 30px 40px; background: rgba(1, 4, 9, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.05); }
.topbar-left { display: flex; align-items: center; gap: 20px; }
.topbar-icon { width: 48px; height: 48px; background: rgba(110, 64, 201, 0.1); border-radius: 14px; border: 1px solid rgba(110, 64, 201, 0.2); display: flex; align-items: center; justify-content: center; color: #a371f7; }
.topbar-title { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; }
.topbar-sub { font-size: 12px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 4px; }

.content { padding: 40px; max-width: 1200px; margin: 0 auto; width: 100%; }

.activities-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; }

.activity-card { 
  background: rgba(22, 27, 34, 0.7); border: 1px solid rgba(48, 54, 61, 0.8); 
  border-radius: 24px; padding: 24px; display: flex; flex-direction: column; 
  gap: 20px; position: relative; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.activity-card:hover { transform: translateY(-8px); border-color: rgba(110, 64, 201, 0.4); box-shadow: 0 12px 40px rgba(0,0,0,0.5); }

.card-header { display: flex; justify-content: space-between; align-items: center; }

.type-badge { 
  font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
  padding: 6px 14px; border-radius: 10px; display: flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05);
}
.type-badge.cours { color: #58a6ff; background: rgba(56, 139, 253, 0.1); border-color: rgba(56, 139, 253, 0.1); }
.type-badge.atelier { color: #3fb950; background: rgba(63, 185, 80, 0.1); border-color: rgba(63, 185, 80, 0.1); }
.type-badge.projet { color: #f0883e; background: rgba(240, 136, 62, 0.1); border-color: rgba(240, 136, 62, 0.1); }
.type-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 8px currentColor; }

.time-chip { font-size: 13px; font-weight: 700; color: #8b949e; display: flex; align-items: center; gap: 6px; }
.time-chip svg { width: 14px; height: 14px; color: #a371f7; }

.activity-title { font-size: 20px; font-weight: 800; color: #fff; line-height: 1.3; }
.activity-desc { font-size: 14px; color: #8b949e; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

.card-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; }
.meta-info { display: flex; gap: 16px; }
.meta-item { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #484f58; }
.meta-item svg { width: 14px; height: 14px; }
.date-stamp { font-size: 12px; font-weight: 700; color: #a371f7; }

.card-glow { position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(110, 64, 201, 0.1) 0%, transparent 70%); pointer-events: none; }

.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; gap: 20px; color: #8b949e; }
.loader-ripple { display: inline-block; position: relative; width: 80px; height: 80px; }
.loader-ripple div { position: absolute; border: 4px solid #a371f7; opacity: 1; border-radius: 50%; animation: loader-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite; }
.loader-ripple div:nth-child(2) { animation-delay: -0.5s; }
@keyframes loader-ripple {
  0% { top: 36px; left: 36px; width: 0; height: 0; opacity: 1; }
  100% { top: 0px; left: 0px; width: 72px; height: 72px; opacity: 0; }
}

.empty-state { text-align: center; padding: 100px 20px; color: #8b949e; }
.empty-illustration { font-size: 64px; margin-bottom: 24px; opacity: 0.2; }
.empty-illustration svg { width: 80px; height: 80px; margin: 0 auto; }
.empty-state h3 { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px; }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

@media (max-width: 768px) {
  .content { padding: 20px; }
  .topbar { padding: 20px; }
}
</style>
