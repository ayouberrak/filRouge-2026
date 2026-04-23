<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Communauté</h1>
            <p class="topbar-sub">Annuaire des talents Nadi</p>
          </div>
        </div>

        <div class="topbar-right animate-in">
          <div class="search-box">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-6-6"/></svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par nom ou tech..."
              class="search-input"
              @input="debouncedFetch"
            />
          </div>
        </div>
      </header>

      <div class="content">
        <!-- Explorer Header -->
        <div class="explorer-header animate-in">
          <div class="network-glance">
            <div class="glance-badge">YOUCONNECT NETWORK</div>
            <h2 class="glance-title">Réseau des Talents</h2>
            <p class="glance-desc">Découvrez, collaborez et apprenez avec les {{ totalStudents }} membres de la promotion.</p>
          </div>
          <div class="stats-pills">
            <div class="stat-pill">
              <span class="stat-v">{{ totalStudents }}</span>
              <span class="stat-l">Étudiants</span>
            </div>
          </div>
        </div>

        <!-- Filters -->

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          
          <p>Chargement en cours...</p>
        </div>

        <!-- Grid -->
        <div v-else-if="students.length > 0" class="talents-grid">
          <div
            v-for="(student, idx) in students"
            :key="student.id"
            class="talent-card animate-in"
            :style="{ animationDelay: `${idx * 0.05}s` }"
            @click="viewProfile(student.id)"
          >
            <!-- Card Decoration -->
            <div class="card-glow"></div>
            
            <div class="talent-header">
              <div class="avatar-wrap">
                <img :src="student.avatar_url || `https://ui-avatars.com/api/?name=${student.first_name}+${student.last_name}&background=0d1117&color=388bfd`" class="talent-avatar" />
                <div class="status-indicator"></div>
              </div>

            </div>

            <div class="talent-body">
              <h3 class="talent-name">{{ student.first_name }} {{ student.last_name }}</h3>
            </div>

            <div class="talent-footer">
              <button class="btn-view-profile">Voir le profil</button>
              <button class="btn-send-msg" @click.stop="startChat(student.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <h3>Aucun membre trouvé</h3>
          <p>Essayez d'ajuster vos critères de recherche.</p>
          <button @click="resetFilters" class="reset-btn">Réinitialiser tout</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const students = ref([]);
const totalStudents = ref(0);
const loading = ref(true);
const searchQuery = ref('');

let searchTimeout = null;

const fetchStudents = async () => {
  loading.value = true;
  const res = await api.get('/analytics/students', {
    params: {
      search: searchQuery.value
    }
  });
  students.value = res.data.data;
  totalStudents.value = students.value.length;
  loading.value = false;
};

const debouncedFetch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fetchStudents, 300);
};

const viewProfile = (id) => {
  router.push(`/network/${id}`);
};

const startChat = (id) => {
  router.push({ name: 'student.chat', params: { id } });
};

const resetFilters = () => {
  searchQuery.value = '';
  fetchStudents();
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  fetchStudents();
});

onUnmounted(() => {
  clearTimeout(searchTimeout);
});
</script>

<style scoped>
/* ─── Base Layout ───────────────────────────────────────────────────────────── */
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
  overflow: hidden;
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
  z-index: 100;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.topbar-icon {
  width: 40px;
  height: 40px;
  background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #58a6ff;
}

.topbar-icon svg { width: 20px; height: 20px; }
.topbar-title { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; }
.topbar-sub { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }

.search-box {
  position: relative;
  width: 320px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  color: #8b949e;
  pointer-events: none;
}

.search-input {
  width: 100%;
  background: #0d1117;
  border: 1px solid #30363d;
  border-radius: 12px;
  padding: 10px 16px 10px 42px;
  color: white;
  font-size: 13px;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #58a6ff;
  box-shadow: 0 0 0 3px rgba(56, 139, 253, 0.1);
  outline: none;
}

/* ─── Content Area ──────────────────────────────────────────────────────────── */
.content {
  flex: 1;
  overflow-y: auto;
  padding: 40px;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}

.explorer-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 32px;
}

.glance-badge {
  display: inline-block;
  padding: 4px 10px;
  background: rgba(35, 134, 54, 0.1);
  border: 1px solid rgba(35, 134, 54, 0.2);
  border-radius: 20px;
  font-size: 10px;
  font-weight: 700;
  color: #3fb950;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
}

.glance-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; margin-bottom: 8px; }
.glance-desc { color: #8b949e; font-size: 15px; max-width: 500px; line-height: 1.6; }

.stat-pill {
  background: #161b22;
  border: 1px solid #30363d;
  padding: 12px 24px;
  border-radius: 16px;
  text-align: center;
}

.stat-v { font-size: 24px; font-weight: 800; color: #58a6ff; display: block; }
.stat-l { font-size: 11px; font-weight: 600; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; }

/* ─── Filters ───────────────────────────────────────────────────────────────── */
.filters-row {
  display: flex;
  margin-bottom: 32px;
}

.speciality-tabs {
  display: flex;
  background: #0d1117;
  padding: 4px;
  border: 1px solid #30363d;
  border-radius: 12px;
  gap: 4px;
}

.spec-tab {
  padding: 8px 18px;
  border: none;
  background: transparent;
  color: #8b949e;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
}

.spec-tab:hover { color: #e6edf3; background: rgba(255, 255, 255, 0.03); }
.spec-tab.active { background: #58a6ff; color: white; }

/* ─── Talents Grid ──────────────────────────────────────────────────────────── */
.talents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.talent-card {
  background: #0d1117;
  border: 1px solid #30363d;
  border-radius: 20px;
  padding: 24px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.talent-card:hover {
  transform: translateY(-6px);
  border-color: #58a6ff;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
}

.card-glow {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: radial-gradient(circle at 50% -20%, rgba(56, 139, 253, 0.15) 0%, transparent 70%);
  opacity: 0;
  transition: opacity 0.3s;
}

.talent-card:hover .card-glow { opacity: 1; }

.talent-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  position: relative;
}

.avatar-wrap {
  position: relative;
  width: 64px;
  height: 64px;
}

.talent-avatar {
  width: 100%;
  height: 100%;
  border-radius: 18px;
  object-fit: cover;
  border: 2px solid #30363d;
  transition: all 0.3s;
}

.talent-card:hover .talent-avatar { border-color: #58a6ff; }

.status-indicator {
  position: absolute;
  bottom: -4px;
  right: -4px;
  width: 14px;
  height: 14px;
  background: #3fb950;
  border: 3px solid #0d1117;
  border-radius: 50%;
}

.points-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: rgba(210, 153, 34, 0.1);
  border: 1px solid rgba(210, 153, 34, 0.2);
  border-radius: 12px;
  font-size: 13px;
  font-weight: 800;
  color: #d29922;
}

.points-badge svg { width: 14px; height: 14px; }

.talent-body { position: relative; }
.talent-name { font-size: 18px; font-weight: 700; color: #f0f6fc; margin-bottom: 4px; }
.talent-role { font-size: 13px; color: #58a6ff; font-weight: 600; margin-bottom: 16px; }

.talent-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 20px;
}

.tag {
  padding: 4px 10px;
  background: #21262d;
  border: 1px solid #30363d;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #8b949e;
}

.talent-meta {
  display: flex;
  gap: 12px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #8b949e;
}

.meta-item svg { width: 14px; height: 14px; }

.talent-footer {
  margin-top: 24px;
  display: flex;
  gap: 12px;
  position: relative;
}

.btn-view-profile {
  flex: 1;
  padding: 10px;
  background: #21262d;
  border: 1px solid #30363d;
  border-radius: 10px;
  color: #c9d1d9;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-view-profile:hover {
  background: #30363d;
  border-color: #8b949e;
}

.btn-send-msg {
  width: 40px;
  height: 40px;
  background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 10px;
  color: #58a6ff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-send-msg:hover {
  background: #58a6ff;
  color: white;
}

/* ─── Empty & Loading ────────────────────────────────────────────────────────── */
.loading-state {
  text-align: center;
  padding: 80px 0;
}



.empty-state {
  text-align: center;
  padding: 100px 40px;
  background: #0d1117;
  border: 1px dashed #30363d;
  border-radius: 20px;
}

.empty-icon {
  width: 64px;
  height: 64px;
  background: #161b22;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  color: #484f58;
}

.empty-icon svg { width: 32px; height: 32px; }

.reset-btn {
  margin-top: 20px;
  padding: 10px 24px;
  background: #238636;
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 600;
  cursor: pointer;
}

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-in {
  animation: fadeInUp 0.5s ease forwards;
}

@media (max-width: 768px) {
  .explorer-header { flex-direction: column; align-items: flex-start; gap: 20px; }
  .topbar-right { display: none; }
}
</style>

