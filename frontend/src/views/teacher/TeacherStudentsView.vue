<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== HEADER ===== -->
        <header class="page-header animate-in">
          <div class="header-left">
            <div class="header-title-row">
              <div class="header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
              </div>
              <div>
                <h1 class="page-title">Workspace <span class="dim">/ Étudiants</span></h1>
                <p class="page-sub">Promotion 2026 • Gestion de la performance</p>
              </div>
            </div>
          </div>
          
          <div class="header-right">
            <div class="search-wrapper">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Rechercher un étudiant..." 
                class="search-input"
              />
            </div>
            <button class="btn-action">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              Exporter
            </button>
          </div>
        </header>

        <!-- ===== MINI STATS ===== -->
        <div class="mini-stats animate-in" style="animation-delay: 0.1s">
          <div class="mini-stat-card">
            <div class="ms-label">Total Promotion</div>
            <div class="ms-value">{{ students.length }}</div>
          </div>
          <div class="mini-stat-card">
            <div class="ms-label">Étudiants Actifs</div>
            <div class="ms-value text-green">{{ students.filter(s => s.status === 'active').length }}</div>
          </div>
          <div class="mini-stat-card">
            <div class="ms-label">Moyenne Points</div>
            <div class="ms-value text-blue">{{ Math.round(students.reduce((a, s) => a + (s.total_points || 0), 0) / (students.length || 1)).toLocaleString() }}</div>
          </div>
        </div>

        <!-- ===== TABLE SECTION ===== -->
        <section class="table-outer animate-in" style="animation-delay: 0.2s">
          <table class="elite-table">
            <thead>
              <tr>
                <th class="col-student">Étudiant</th>
                <th class="col-email">Email</th>
                <th class="col-squad">Squad</th>
                <th class="col-points text-center">Points</th>
                <th class="col-status text-center">Statut</th>
                <th class="col-actions text-right"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Data Rows -->
              <tr 
                v-for="(student, index) in filteredStudents" 
                :key="student.id" 
                class="elite-row"
                :style="{ animationDelay: (0.3 + (index * 0.03)) + 's' }"
              >
                <td>
                  <div class="student-cell">
                    <img :src="getAvatar(student)" class="student-avatar" />
                    <div class="student-info">
                      <span class="student-name">{{ student.first_name }} {{ student.last_name }}</span>
                      <span class="student-id">ID: #{{ String(student.id).padStart(4, '0') }}</span>
                    </div>
                  </div>
                </td>
                <td class="email-cell">{{ student.email }}</td>
                <td>
                   <div v-if="student.squad_id" class="squad-chip">
                     <span class="chip-label">SQUAD</span>
                     <span class="chip-val">{{ student.squad_name || '#' + student.squad_id }}</span>
                   </div>
                   <span v-else class="squad-none">Non assigné</span>
                </td>
                <td class="text-center">
                  <span class="points-badge">{{ (student.total_points || 0).toLocaleString() }}</span>
                </td>
                <td class="text-center">
                   <div 
                    class="status-pill" 
                    :class="student.status === 'active' ? 'status-pill--active' : 'status-pill--banned'"
                  >
                    <span class="status-dot"></span>
                    {{ student.status === 'active' ? 'ACTIF' : 'BANNI' }}
                  </div>
                </td>
                <td class="text-right">
                  <div class="actions-group">
                    <button class="btn-chat" title="Envoyer un message" @click="router.push({ name: 'teacher.chat', query: { user: student.id } })">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </button>
                    <button class="btn-profile" title="Voir profil">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Loading State -->
              <tr v-if="isLoading" class="table-empty">
                <td colspan="6">
                  <div class="loader-wrap">
                    <div class="loader"></div>
                    Initialisation des données promotionnelles...
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!isLoading && filteredStudents.length === 0" class="table-empty">
                <td colspan="6">
                   <div class="empty-wrap">
                     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                     <p>Aucun étudiant trouvé pour "{{ searchQuery }}"</p>
                   </div>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

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
const searchQuery = ref('');
const students = ref([]);
const isLoading = ref(true);
const classroomId = ref(1); // Promotion 2026

const fetchStudents = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/students', {
      params: { classroom_id: classroomId.value }
    });
    students.value = response.data.data;
  } catch (error) {
    console.error("Load students error:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchStudents);

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(s => 
    s.first_name.toLowerCase().includes(q) || 
    s.last_name.toLowerCase().includes(q) || 
    s.email.toLowerCase().includes(q)
  );
});

const getAvatar = (s) => {
  return s.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(s.first_name + ' ' + s.last_name)}&background=161b22&color=388bfd&bold=true`;
};

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
.content { padding: 44px 52px; max-width: 1440px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }

/* ===== HEADER ===== */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.header-title-row { display: flex; align-items: center; gap: 18px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #388bfd; }
.header-icon svg { width: 22px; height: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; line-height: 1; }
.dim { color: #484f58; font-weight: 500; font-size: 20px; }
.page-sub { font-size: 13px; color: #8b949e; margin-top: 6px; }

.header-right { display: flex; align-items: center; gap: 16px; }
.search-wrapper { position: relative; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #484f58; pointer-events: none; }
.search-input { background: rgba(13,17,23,0.8); border: 1px solid rgba(48,54,61,0.6); color: #fff; padding: 10px 16px 10px 38px; border-radius: 10px; font-size: 13px; width: 300px; transition: all 0.2s; font-family: inherit; }
.search-input:focus { outline: none; border-color: #388bfd; background: #0d1117; box-shadow: 0 0 0 3px rgba(56, 139, 253, 0.1); }

.btn-action { display: flex; align-items: center; gap: 8px; background: #21262d; border: 1px solid #30363d; color: #c9d1d9; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-action svg { width: 14px; height: 14px; }
.btn-action:hover { background: #30363d; border-color: #8b949e; color: #fff; }

/* ===== MINI STATS ===== */
.mini-stats { display: flex; gap: 20px; }
.mini-stat-card { flex: 1; background: rgba(22,27,34,0.4); border: 1px solid rgba(48,54,61,0.4); border-radius: 14px; padding: 16px 20px; }
.ms-label { font-size: 10px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
.ms-value { font-size: 22px; font-weight: 900; color: #fff; }
.text-green { color: #3fb950; }
.text-blue { color: #388bfd; }

/* ===== ELITE TABLE ===== */
.table-outer { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.5); border-radius: 18px; overflow: hidden; backdrop-filter: blur(8px); }
.elite-table { width: 100%; border-collapse: collapse; text-align: left; }

.elite-table th { background: rgba(22,27,34,0.8); padding: 18px 24px; font-size: 11px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; border-bottom: 1px solid rgba(48,54,61,0.4); }
.elite-row td { padding: 16px 24px; border-bottom: 1px solid rgba(48,54,61,0.2); transition: all 0.15s; vertical-align: middle; }
.elite-row:hover td { background: rgba(56,139,253,0.02); }
.elite-row:last-child td { border-bottom: none; }

.student-cell { display: flex; align-items: center; gap: 14px; }
.student-avatar { width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(48,54,61,0.6); }
.student-info { display: flex; flex-direction: column; gap: 2px; }
.student-name { font-size: 14px; font-weight: 700; color: #f0f6fc; transition: color 0.15s; }
.student-id { font-size: 10px; color: #484f58; font-weight: 600; font-family: 'JetBrains Mono', monospace; }
.elite-row:hover .student-name { color: #388bfd; }

.email-cell { font-size: 13px; color: #8b949e; font-family: 'JetBrains Mono', monospace; }

.squad-chip { display: inline-flex; flex-direction: column; background: rgba(56,139,253,0.06); border: 1px solid rgba(56,139,253,0.15); border-radius: 8px; padding: 4px 10px; }
.chip-label { font-size: 8px; font-weight: 900; color: #388bfd; opacity: 0.8; letter-spacing: 0.1em; }
.chip-val { font-size: 11px; font-weight: 800; color: #e6edf3; }
.squad-none { font-size: 12px; color: #484f58; font-style: italic; }

.points-badge { font-size: 14px; font-weight: 800; color: #fff; background: rgba(255,255,255,0.03); padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); font-family: 'JetBrains Mono', monospace; }

.status-pill { display: inline-flex; align-items: center; gap: 8px; font-size: 10px; font-weight: 900; padding: 5px 12px; border-radius: 99px; letter-spacing: 0.05em; border: 1px solid transparent; }
.status-pill--active { color: #3fb950; background: rgba(63,185,80,0.05); border-color: rgba(63,185,80,0.15); }
.status-pill--banned { color: #f85149; background: rgba(248,81,73,0.05); border-color: rgba(248,81,73,0.15); }
.status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

.actions-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn-profile, .btn-chat { 
  width: 32px; 
  height: 32px; 
  border-radius: 8px; 
  border: 1px solid rgba(48,54,61,0.6); 
  background: transparent; 
  color: #484f58; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  cursor: pointer; 
  transition: all 0.2s; 
}
.btn-profile svg, .btn-chat svg { width: 14px; height: 14px; }

.btn-chat:hover { 
  border-color: #3fb950; 
  color: #3fb950; 
  background: rgba(63,185,80,0.06); 
  transform: scale(1.05); 
}

.btn-profile:hover { border-color: #388bfd; color: #388bfd; background: rgba(56,139,253,0.06); transform: scale(1.05); }

/* Empty/Loading */
.table-empty { height: 200px; text-align: center; }
.loader-wrap, .empty-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; color: #484f58; font-size: 14px; }
.empty-wrap svg { width: 40px; height: 40px; opacity: 0.3; }
.loader { width: 24px; height: 24px; border: 2px solid rgba(56,139,253,0.2); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Cols */
.col-student { width: 300px; }
.col-email { width: 250px; }
.col-squad { width: 180px; }
.col-points { width: 140px; }
.col-status { width: 150px; }

/* Anims */
.animate-in { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
.elite-row { opacity: 0; animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.text-center { text-align: center; }
.text-right { text-align: right; }
</style>

