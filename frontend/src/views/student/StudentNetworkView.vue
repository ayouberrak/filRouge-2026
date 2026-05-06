<template>
  <div class="layout">
    <!-- Sidebar -->
    <SidebarStudent :user="user" @logout="handleLogout" />

    <!-- Main Content -->
    <main class="main">
      <div class="content">
        <!-- Page Header -->
        <div class="page-header">
          <div class="page-heading">
            <h1>Ma Promotion</h1>
            <p>Connectez-vous avec vos camarades et suivez l'actualité de votre classe.</p>
          </div>

          <div class="search-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
            </svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Rechercher un étudiant..." 
              class="search-input"
            />
          </div>
        </div>

        <!-- Classmates Grid -->
        <div v-if="isLoading" class="loading-state">
          <div class="loader-ring"></div>
          <p>Chargement de la promotion...</p>
        </div>

        <div v-else-if="filteredStudents.length > 0" class="network-grid">
          <div 
            v-for="(student, index) in filteredStudents" 
            :key="student.id" 
            class="student-card"
            :style="{ animationDelay: `${index * 0.05}s` }"
          >
            <div class="card-glow"></div>
            
            <div class="avatar-wrap">
              <img :src="student.avatar_url || defaultAvatar" :alt="student.first_name" class="avatar-img" />
              <div class="status-indicator" :class="{ 'online': student.is_online }"></div>
            </div>

            <div class="student-info">
              <h3 class="student-name">{{ student.first_name }} {{ student.last_name }}</h3>
              <p class="student-email">{{ student.email }}</p>
              
              <div class="student-badges">
                <span class="badge badge-squad" v-if="student.squad_name">{{ student.squad_name }}</span>
                <span class="badge badge-role">Étudiant</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn-contact" @click="startChat(student)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 11-7.6-11.7 8.38 8.38 0 013.8.9L21 3z"/>
                </svg>
                Message
              </button>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
          </svg>
          <h3>Aucun étudiant trouvé</h3>
          <p>Essayez de modifier votre recherche.</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const students = ref([]);
const searchQuery = ref('');
const isLoading = ref(true);

const defaultAvatar = 'https://ui-avatars.com/api/?background=388bfd&color=fff';

const filteredStudents = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return students.value;
  return students.value.filter(s => 
    s.first_name.toLowerCase().includes(query) || 
    s.last_name.toLowerCase().includes(query) ||
    s.email.toLowerCase().includes(query)
  );
});

const fetchClassmates = async () => {
  try {
    const response = await api.get('/analytics/students');
    // Filter out the current user if needed
    const allStudents = response.data.data || [];
    students.value = allStudents.filter(s => s.id !== user.value?.id);
  } catch (err) {
    console.error("Erreur chargement promotion:", err);
  } finally {
    isLoading.value = false;
  }
};

const startChat = (student) => {
  router.push(`/student/chat?userId=${student.id}`);
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  const cachedUser = localStorage.getItem('user');
  if (cachedUser) user.value = JSON.parse(cachedUser);
  fetchClassmates();
});
</script>

<style scoped>
.layout {
  display: flex; height: 100vh; background: #010409; color: #e6edf3; font-family: 'Inter', sans-serif;
}

.main { flex: 1; overflow-y: auto; }

.content {
  padding: 40px; max-width: 1200px; margin: 0 auto;
  display: flex; flex-direction: column; gap: 32px;
}

.page-header {
  display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;
}

.page-heading h1 { font-size: 32px; font-weight: 800; color: #fff; margin-bottom: 8px; letter-spacing: -0.03em; }
.page-heading p { color: #8b949e; font-size: 14px; }

.search-wrap { position: relative; width: 300px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; color: #8b949e; }
.search-input {
  width: 100%; background: #0d1117; border: 1px solid #30363d; border-radius: 8px;
  padding: 10px 12px 10px 40px; color: #fff; outline: none; transition: all 0.2s;
}
.search-input:focus { border-color: #388bfd; box-shadow: 0 0 0 3px rgba(56,139,253,0.15); }

.network-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;
}

.student-card {
  background: #0d1117; border: 1px solid #30363d; border-radius: 16px; padding: 24px;
  display: flex; flex-direction: column; align-items: center; text-align: center;
  position: relative; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fadeUp 0.5s ease forwards; opacity: 0;
}

.student-card:hover { transform: translateY(-5px); border-color: #388bfd; background: #161b22; }

.card-glow {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background: radial-gradient(circle at 50% 0%, rgba(56, 139, 253, 0.1), transparent 70%);
  pointer-events: none; opacity: 0; transition: opacity 0.3s;
}
.student-card:hover .card-glow { opacity: 1; }

.avatar-wrap { position: relative; margin-bottom: 16px; }
.avatar-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #30363d; }
.status-indicator {
  position: absolute; bottom: 5px; right: 5px; width: 14px; height: 14px;
  border-radius: 50%; background: #484f58; border: 2px solid #0d1117;
}
.status-indicator.online { background: #3fb950; }

.student-name { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.student-email { font-size: 13px; color: #8b949e; margin-bottom: 16px; }

.student-badges { display: flex; gap: 8px; justify-content: center; }
.badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.badge-squad { background: rgba(56, 139, 253, 0.1); color: #388bfd; border: 1px solid rgba(56, 139, 253, 0.2); }
.badge-role { background: rgba(48, 54, 61, 0.5); color: #8b949e; }

.card-actions { margin-top: 24px; width: 100%; }
.btn-contact {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px; background: #21262d; border: 1px solid #30363d; border-radius: 8px;
  color: #c9d1d9; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-contact:hover { background: #30363d; color: #fff; }
.btn-contact svg { width: 16px; height: 16px; }

.loading-state, .empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 100px 0; color: #8b949e; gap: 16px;
}

.loading-state svg, .empty-state svg {
  width: 64px; height: 64px; opacity: 0.3;
}

.loader-ring {
  width: 40px; height: 40px; border: 3px solid rgba(56,139,253,0.1);
  border-top-color: #388bfd; border-radius: 50%; animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  .page-header { flex-direction: column; align-items: flex-start; }
  .search-wrap { width: 100%; }
}
</style>
