<template>
  <div class="layout">
    <SidebarAdmin :user="currentUser" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Gestion des Utilisateurs</h1>
            <p class="topbar-sub">{{ users.length }} collaborateurs & étudiants enregistrés</p>
          </div>
          <div class="topbar-right">
             <div class="search-box">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
               <input v-model="searchQuery" type="text" placeholder="Rechercher un nom ou email..." />
             </div>
             <button class="btn-create" @click="openCreateModal">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
               Créer un utilisateur
             </button>
          </div>
        </header>

        
        <div class="filters-row animate-in" style="animation-delay: 0.1s">
          <div class="filter-chips">
            <button 
              v-for="role in roles" :key="role.val"
              class="chip" :class="{ active: filterRole === role.val }"
              @click="filterRole = role.val"
            >
              {{ role.label }}
            </button>
          </div>
          <div class="stats-mini">
            <div class="mini-item"><strong>{{ studentsCount }}</strong> Étudiants</div>
            <div class="mini-item"><strong>{{ staffCount }}</strong> Staff</div>
          </div>
        </div>

        
        <div class="table-container animate-in" style="animation-delay: 0.2s">
          <table class="nadi-table">
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Rôle</th>
                <th>Status</th>
                <th>Points / XP</th>
                <th class="actions-col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading"><td colspan="10" style="text-align: center; padding: 20px;">Chargement en cours...</td></tr>
              <tr v-else v-for="user in filteredUsers" :key="user.id">
                <td>
                  <div class="user-cell">
                    <img :src="user.avatar_url || getAvatar(user.first_name)" class="u-avatar" />
                    <div class="u-info">
                      <span class="u-name">{{ user.first_name }} {{ user.last_name }}</span>
                      <span class="u-email">{{ user.email }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge-role" :class="user.role">{{ user.role }}</span>
                </td>
                <td>
                  <span class="status-indicator" :class="user.status || 'active'">
                    {{ user.status || 'Active' }}
                  </span>
                </td>
                <td>
                   <div class="xp-cell">
                     <span class="xp-val">{{ user.total_points || 0 }}</span>
                     <span class="xp-lbl">XP</span>
                   </div>
                </td>
                <td class="actions-col">
                  <div class="action-btns">
                    <button class="btn-action" @click="editUser(user)" title="Modifier">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-action ban" @click="toggleBan(user)" :title="user.status === 'banned' ? 'Débloquer' : 'Bannir'">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!isLoading && !filteredUsers.length">
                <td colspan="5" class="empty-table">Aucun utilisateur trouvé</td>
              </tr>
            </tbody>
          </table>
        </div>

        
        <Transition name="fade">
          <div v-if="showUserModal" class="modal-overlay" @click.self="closeUserModal">
            <div class="modal-content">
              <div class="modal-header">
                <h3 class="modal-t">{{ isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h3>
                <button class="close-btn" @click="closeUserModal">×</button>
              </div>

              <div class="modal-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Prénom</label>
                    <input v-model="userForm.first_name" type="text" placeholder="ex: Ayoub" class="nadi-input" />
                  </div>
                  <div class="form-group">
                    <label>Nom</label>
                    <input v-model="userForm.last_name" type="text" placeholder="ex: E." class="nadi-input" />
                  </div>
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="userForm.email" type="email" placeholder="email@youcode.ma" class="nadi-input" />
                </div>

                <div v-if="!isEdit" class="form-group">
                  <label>Mot de passe temporaire</label>
                  <input v-model="userForm.password" type="password" placeholder="••••••••" class="nadi-input" />
                </div>

                <div class="form-group">
                  <label>Rôle</label>
                  <select v-model="userForm.role" class="nadi-select">
                    <option value="student">Étudiant</option>
                    <option value="formateur">Staff / Formateur</option>
                    <option value="admin">Administrateur</option>
                  </select>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn-cancel" @click="closeUserModal">Annuler</button>
                <button class="btn-confirm" @click="saveUser" :disabled="isSaving">
                  {{ isSaving ? 'Enregistrement...' : 'Confirmer' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
import api from '../../services/api';

const router = useRouter();
const currentUser = ref(JSON.parse(localStorage.getItem('user')) || {});
const users = ref([]);
const classrooms = ref([]);
const isLoading = ref(true);
const isSaving = ref(false); 

const searchQuery = ref(''); 
const filterRole = ref('ALL'); 

const showUserModal = ref(false); 
const isEdit = ref(false); 
const editingUserId = ref(null); 


const userForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  role: 'student',
  classroom_id: null
});

const roles = [
  { val: 'ALL', label: 'Tout le monde' },
  { val: 'student', label: 'Étudiants' },
  { val: 'formateur', label: 'Staff / Formateurs' },
  { val: 'admin', label: 'Admins' }
];

const studentsCount = computed(() => users.value.filter(u => u.role === 'student').length);
const staffCount = computed(() => users.value.filter(u => u.role === 'formateur').length);


const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const fullName = (u.first_name + ' ' + u.last_name + ' ' + u.email).toLowerCase();
    const matchesSearch = fullName.includes(searchQuery.value.toLowerCase());
    const matchesRole = filterRole.value === 'ALL' || u.role === filterRole.value;
    return matchesSearch && matchesRole;
  });
});
const getAvatar = (name) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=21262d&color=a371f7&bold=true`;

const fetchData = async () => {
  const cachedUsers = localStorage.getItem('admin_users_cache');
  const cachedClasses = localStorage.getItem('admin_classrooms_cache');
  if (cachedUsers && cachedClasses) {
    users.value = JSON.parse(cachedUsers);
    classrooms.value = JSON.parse(cachedClasses);
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }

  try {
    const [userRes, classRes] = await Promise.all([
      api.get('/users'),
      api.get('/classrooms')
    ]);

    const rawUsers = userRes.data.users || userRes.data.data || userRes.data;
    users.value = Array.isArray(rawUsers) ? rawUsers : (rawUsers.data || []);
    classrooms.value = classRes.data.data || classRes.data;

    localStorage.setItem('admin_users_cache', JSON.stringify(users.value));
    localStorage.setItem('admin_classrooms_cache', JSON.stringify(classrooms.value));
  } catch (err) {
    console.error("Erreur Admin Data:", err);
  }
  
  isLoading.value = false;
};

const openCreateModal = () => {
  isEdit.value = false;
  userForm.value = { first_name: '', last_name: '', email: '', password: '', role: 'student', classroom_id: null };
  showUserModal.value = true;
};

const editUser = (user) => {
  isEdit.value = true;
  editingUserId.value = user.id;
  userForm.value = { ...user };
  showUserModal.value = true;
};

const closeUserModal = () => showUserModal.value = false;

const saveUser = async () => {
  isSaving.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/users/update/${editingUserId.value}`, userForm.value);
    } else {
      await api.post('/users/create', userForm.value);
    }
    showUserModal.value = false;
    fetchData();
  } catch (err) {
    console.error("Erreur Save User:", err);
    const msg = err.response?.data?.message || "Une erreur est survenue lors de l'enregistrement.";
    const errors = err.response?.data?.errors;
    
    if (errors) {
      const firstError = Object.values(errors)[0][0];
      alert(`Erreur de validation : ${firstError}`);
    } else {
      alert(msg);
    }
  } finally {
    isSaving.value = false;
  }
};

const toggleBan = async (user) => {
  const isCurrentlyBanned = user.status === 'banned';
  const action = isCurrentlyBanned ? 'débloquer' : 'bannir';
  
  if (!confirm(`Voulez-vous vraiment ${action} ${user.first_name}?`)) return;
  
  await api.patch(`/users/ban/${user.id}`);
  user.status = isCurrentlyBanned ? 'active' : 'banned';
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(fetchData);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: center; }
.topbar-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }
.topbar-right { display: flex; gap: 16px; align-items: center; }

.search-box { position: relative; background: #0d1117; border: 1px solid #30363d; border-radius: 12px; display: flex; align-items: center; padding: 0 16px; width: 300px; transition: all 0.2s; }
.search-box:focus-within { border-color: #a371f7; box-shadow: 0 0 0 3px rgba(163, 113, 247, 0.1); }
.search-box svg { width: 16px; height: 16px; color: #484f58; margin-right: 12px; }
.search-box input { background: none; border: none; height: 44px; color: #fff; font-size: 14px; width: 100%; outline: none; }

.btn-create { display: flex; align-items: center; gap: 10px; background: #a371f7; color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; }
.btn-create:hover { background: #b085f9; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(163, 113, 247, 0.3); }

/* Filters */
.filters-row { display: flex; justify-content: space-between; align-items: center; }
.filter-chips { display: flex; gap: 8px; }
.chip { background: #0d1117; border: 1px solid #30363d; color: #8b949e; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.chip:hover { border-color: #484f58; color: #c9d1d9; }
.chip.active { background: rgba(163, 113, 247, 0.1); border-color: #a371f7; color: #a371f7; }

.stats-mini { display: flex; gap: 24px; font-size: 13px; color: #8b949e; }
.mini-item strong { color: #e6edf3; font-weight: 800; }

/* Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: #0d1117; border: 1px solid #30363d; border-radius: 20px; width: 500px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
.modal-header { padding: 24px 32px; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center; }
.modal-t { font-size: 20px; font-weight: 800; }
.close-btn { background: none; border: none; font-size: 24px; color: #484f58; cursor: pointer; }

.modal-body { padding: 32px; display: flex; flex-direction: column; gap: 20px; }
.form-row { display: flex; gap: 16px; }
.form-group { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 13px; font-weight: 700; color: #8b949e; }

.nadi-input, .nadi-select { background: #161b22; border: 1px solid #30363d; padding: 12px 16px; border-radius: 10px; color: #fff; font-size: 14px; outline: none; transition: all 0.2s; }
.nadi-input:focus, .nadi-select:focus { border-color: #a371f7; box-shadow: 0 0 0 3px rgba(163, 113, 247, 0.1); }

.modal-footer { padding: 24px 32px; background: #161b22; display: flex; gap: 12px; justify-content: flex-end; }
.btn-cancel { padding: 12px 24px; background: none; border: 1px solid #30363d; color: #8b949e; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-confirm { padding: 12px 32px; background: #a371f7; color: #fff; border: none; border-radius: 10px; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-confirm:hover { background: #b085f9; }
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* Table Nadi Style */
.table-container { background: #0d1117; border: 1px solid #30363d; border-radius: 16px; overflow: hidden; }
.nadi-table { width: 100%; border-collapse: collapse; text-align: left; }
.nadi-table th { background: rgba(22, 27, 34, 0.5); padding: 16px 24px; font-size: 11px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #30363d; }
.nadi-table td { padding: 16px 24px; font-size: 14px; border-bottom: 1px solid rgba(48, 54, 61, 0.4); }
.nadi-table tr:hover td { background: rgba(163, 113, 247, 0.02); }
.nadi-table tr:last-child td { border-bottom: none; }

.user-cell { display: flex; align-items: center; gap: 12px; }
.u-avatar { width: 36px; height: 36px; border-radius: 10px; border: 1px solid #30363d; }
.u-name { display: block; font-weight: 700; color: #f0f6fc; }
.u-email { display: block; font-size: 11px; color: #8b949e; margin-top: 2px; }

.badge-role { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
.badge-role.student { background: rgba(63, 185, 80, 0.1); color: #3fb950; }
.badge-role.formateur { background: rgba(56, 139, 253, 0.1); color: #58a6ff; }
.badge-role.admin { background: rgba(163, 113, 247, 0.1); color: #a371f7; }

.status-indicator { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 11px; text-transform: uppercase; }
.status-indicator::before { content: ""; width: 8px; height: 8px; border-radius: 50%; }
.status-indicator.active { color: #3fb950; }
.status-indicator.active::before { background: #3fb950; box-shadow: 0 0 10px rgba(63, 185, 80, 0.5); }
.status-indicator.banned { color: #f85149; }
.status-indicator.banned::before { background: #f85149; }

.xp-cell { line-height: 1.2; }
.xp-val { display: block; font-size: 18px; font-weight: 900; color: #d29922; font-family: 'JetBrains Mono', monospace; }
.xp-lbl { font-size: 10px; color: #484f58; font-weight: 800; text-transform: uppercase; }

.actions-col { text-align: right; width: 120px; }
.action-btns { display: flex; gap: 8px; justify-content: flex-end; }
.btn-action { width: 36px; height: 36px; border-radius: 10px; border: 1px solid #30363d; background: #0d1117; color: #8b949e; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-action:hover { border-color: #a371f7; color: #a371f7; background: rgba(163, 113, 247, 0.1); transform: translateY(-2px); }
.btn-action.ban:hover { border-color: #f85149; color: #f85149; background: rgba(248, 81, 73, 0.1); }
.btn-action svg { width: 16px; height: 16px; }

.empty-table { text-align: center; padding: 80px 20px; color: #484f58; font-style: italic; font-size: 15px; }




.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

