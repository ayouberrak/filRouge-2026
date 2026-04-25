<template>
  <div class="layout">
    <SidebarAdmin :user="currentUser" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== TOPBAR ===== -->
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Classes & Espaces</h1>
            <p class="topbar-sub">Structure organisationnelle de YouCode</p>
          </div>
          <div class="topbar-right">
             <button class="btn-create" @click="openCreateModal">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
               Nouvelle Classe
             </button>
          </div>
        </header>

        <!-- ===== CLASSROOMS GRID ===== -->
        <div v-if="isLoading" style="text-align: center; padding: 20px;">Chargement en cours...</div>
        <div v-else class="class-grid animate-in" style="animation-delay: 0.1s">
          <div v-for="(cls, idx) in classrooms" :key="cls.id" class="class-card">
            <div class="card-header">
              <div class="class-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              </div>
              <div class="card-meta">
                 <span class="id-badge">ID: {{ cls.id }}</span>
                 <div class="class-actions">
                    <button class="btn-action primary" @click="openAssignModal(cls)">Assigner Coach</button>
                    <button class="btn-action secondary" @click="openAddStudentsModal(cls)">Ajouter Étudiants</button>
                    <button class="btn-action danger" @click="deleteClassroom(cls.id)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                 </div>
              </div>
            </div>

            <div class="card-body">
              <h2 class="class-name">{{ cls.name }}</h2>
              <div class="formateur-info">
                 <span class="label">Coach Responsable</span>
                 <div v-if="cls.formateur" class="formateur-chip" @click="openAssignModal(cls)">
                    <img :src="cls.formateur.avatar_url || getAvatar(cls.formateur.first_name)" class="f-avatar" />
                    <span>{{ cls.formateur.first_name }} {{ cls.formateur.last_name }}</span>
                 </div>
                 <div v-else class="formateur-empty" @click="openAssignModal(cls)">
                   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                   <span>Assigner un coach</span>
                 </div>
              </div>
            </div>  

            <div class="card-footer">
               <div class="stat-item">
                 <span class="stat-v">{{ cls.students_count || 0 }}</span>
                 <span class="stat-l">Étudiants</span>
               </div>
               <div class="stat-divider"></div>
               <div class="stat-item">
                 <span class="stat-v">{{ cls.active_briefs_count || 0 }}</span>
                 <span class="stat-l">Briefs Actifs</span>
               </div>
            </div>
          </div>

          <div v-if="classrooms.length === 0" class="empty-col">
            <p>Aucune classe configurée</p>
          </div>
        </div>

      </div>
    </main>
    
    <!-- Modals -->
    <Transition name="fade">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal-content">
          <h3 class="modal-t">Nouvelle Classe</h3>
          <input v-model="newClassName" type="text" placeholder="Nom de la classe (ex: Java-P3)" class="nadi-input" />
          <div class="modal-btns">
            <button class="btn-cancel" @click="showModal = false">Annuler</button>
            <button class="btn-confirm" @click="createClassroom">Créer</button>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="showAssignModal" class="modal-overlay" @click.self="showAssignModal = false">
        <div class="modal-content">
          <h3 class="modal-t">Assigner un Formateur</h3>
          <p class="modal-sub-p">Classe: {{ selectedClassroom?.name }}</p>
          <select v-model="selectedFormateurId" class="nadi-select">
            <option value="" disabled>Choisir un formateur...</option>
            <option v-for="f in formateurs" :key="f.id" :value="f.id">
              {{ f.first_name }} {{ f.last_name }}
            </option>
          </select>
          <div class="modal-btns">
            <button class="btn-cancel" @click="showAssignModal = false">Annuler</button>
            <button class="btn-confirm" @click="doAssign">Confirmer l'assignation</button>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="showAssignStudentsModal" class="modal-overlay" @click.self="showAssignStudentsModal = false">
        <div class="modal-content large">
          <div class="modal-header">
            <h3 class="modal-t">Ajouter des Étudiants</h3>
            <span class="modal-sub-p">Assignation à {{ selectedClassroom?.name }}</span>
          </div>

          <div class="search-box">
             <input v-model="studentSearch" type="text" placeholder="Rechercher un étudiant (Nom, Email...)" class="nadi-input search" />
          </div>

          <div class="students-list-container">
            <div v-for="student in filteredStudents" :key="student.id" class="student-select-item" :class="{ selected: selectedStudentIds.includes(student.id) }" @click="toggleStudentSelection(student.id)">
              <div class="s-info">
                <img :src="student.avatar_url || getAvatar(student.first_name)" class="s-mini-avatar" />
                <div class="s-text">
                  <span class="s-name">{{ student.first_name }} {{ student.last_name }}</span>
                  <span class="s-meta">{{ student.classroom?.name || 'Sans classe' }} • {{ student.total_points }} XP</span>
                </div>
              </div>
              <div class="s-checkbox">
                <div class="check" :class="{ checked: selectedStudentIds.includes(student.id) }">
                   <svg v-if="selectedStudentIds.includes(student.id)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <span class="selection-count">{{ selectedStudentIds.length }} étududiant(s) sélectionné(s)</span>
            <div class="modal-btns">
              <button class="btn-cancel" @click="showAssignStudentsModal = false">Annuler</button>
              <button class="btn-confirm" @click="doAssignStudents" :disabled="!selectedStudentIds.length">Ajouter à la classe</button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
// Nous importons nos services centralisés pour plus de clarté
import { BriefService } from '../../services/ApiService'; 
import api from '../../services/api'; 

// --- VARIABLES D'ÉTAT (REFS) ---
// Ces variables permettent de stocker les données et de mettre à jour l'interface automatiquement
const router = useRouter();
const currentUser = ref(JSON.parse(localStorage.getItem('user')) || {});
const classrooms = ref([]); // Liste des classes
const isLoading = ref(true); // État de chargement

// Modals (fenêtres surgissantes)
const showModal = ref(false); 
const showAssignModal = ref(false);
const showAssignStudentsModal = ref(false);

// Formulaires
const newClassName = ref('');
const selectedClassroom = ref(null);
const selectedFormateurId = ref('');
const formateurs = ref([]);
const allStudents = ref([]);
const selectedStudentIds = ref([]);
const studentSearch = ref('');

// --- LOGIQUE CALCULÉE (COMPUTED) ---
// Ces fonctions se recalculent toutes seules quand les données changent
const filteredStudents = computed(() => {
  // Filtrer d'abord pour ne garder que ceux qui n'ont pas de classe
  let list = allStudents.value.filter(st => !st.classroom_id);
  
  if (!studentSearch.value) return list;
  
  const s = studentSearch.value.toLowerCase();
  return list.filter(st => 
    st.first_name.toLowerCase().includes(s) || 
    st.last_name.toLowerCase().includes(s) || 
    st.email.toLowerCase().includes(s)
  );
});

// --- ACTIONS (MÉTHODES) ---

// 1. Récupérer les données depuis le serveur
const fetchData = async () => {
  // Cache
  const cachedCls = localStorage.getItem('admin_classrooms_cache');
  const cachedForm = localStorage.getItem('admin_formateurs_cache');
  const cachedSt = localStorage.getItem('admin_all_students_cache');
  
  if (cachedCls && cachedForm && cachedSt) {
    classrooms.value = JSON.parse(cachedCls);
    formateurs.value = JSON.parse(cachedForm);
    allStudents.value = JSON.parse(cachedSt);
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }

  try {
    const [classRes, formRes, studentsRes] = await Promise.all([
      api.get('/classrooms'),
      api.get('/analytics/students?role=formateur'),
      api.get('/analytics/students?role=student')
    ]);

    classrooms.value = classRes.data.data;
    formateurs.value = formRes.data.data;
    allStudents.value = studentsRes.data.data;

    // Update Cache
    localStorage.setItem('admin_classrooms_cache', JSON.stringify(classrooms.value));
    localStorage.setItem('admin_formateurs_cache', JSON.stringify(formateurs.value));
    localStorage.setItem('admin_all_students_cache', JSON.stringify(allStudents.value));
  } catch (err) {
    console.error("Erreur Admin Data:", err);
  }
  
  isLoading.value = false;
};

// 2. Créer une nouvelle classe
const createClassroom = async () => {
  if (!newClassName.value) return;
  await api.post('/classrooms/create', { name: newClassName.value });
  newClassName.value = '';
  showModal.value = false;
  fetchData();
};

// 3. Supprimer une classe
const deleteClassroom = async (id) => {
  if (!confirm('Voulez-vous supprimer cette classe ?')) return;
  await api.delete(`/classrooms/${id}`);
  fetchData();
};

// 4. Gestion des assignations
const openAssignModal = (cls) => {
  selectedClassroom.value = cls;
  selectedFormateurId.value = cls.formateur_id || '';
  showAssignModal.value = true;
};

const doAssign = async () => {
  if (!selectedFormateurId.value) return;
  await api.post(`/classrooms/${selectedClassroom.value.id}/assign-formateur`, {
    formateur_id: selectedFormateurId.value
  });
  showAssignModal.value = false;
  fetchData();
};

// 5. Gestion des étudiants
const openAddStudentsModal = (cls) => {
  selectedClassroom.value = cls;
  selectedStudentIds.value = [];
  studentSearch.value = '';
  showAssignStudentsModal.value = true;
};

const toggleStudentSelection = (id) => {
  const idx = selectedStudentIds.value.indexOf(id);
  if (idx > -1) selectedStudentIds.value.splice(idx, 1);
  else selectedStudentIds.value.push(id);
};

const doAssignStudents = async () => {
  if (!selectedStudentIds.value.length) return;
  await api.post(`/classrooms/${selectedClassroom.value.id}/assign-students`, {
    student_ids: selectedStudentIds.value
  });
  showAssignStudentsModal.value = false;
  fetchData();
};

const openCreateModal = () => showModal.value = true;

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

const getAvatar = (name) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=21262d&color=a371f7&bold=true`;

// --- CYCLE DE VIE ---
// Cette fonction s'exécute quand le composant est affiché à l'écran
onMounted(fetchData);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 40px; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: flex-end; }
.topbar-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }

.btn-create { display: flex; align-items: center; gap: 10px; background: #a371f7; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; }
.btn-create:hover { background: #b085f9; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(163, 113, 247, 0.3); }

/* Class Grid */
.class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
.class-card { background: #0d1117; border: 1px solid #21262d; border-radius: 20px; display: flex; flex-direction: column; transition: all 0.3s; }
.class-card:hover { border-color: #a371f7; transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }

.card-header { padding: 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #21262d; }
.class-icon { width: 40px; height: 40px; border-radius: 12px; background: rgba(163, 113, 247, 0.1); color: #a371f7; display: flex; align-items: center; justify-content: center; }
.class-icon svg { width: 20px; height: 20px; }

.card-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
.id-badge { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: #484f58; background: #161b22; padding: 2px 6px; border-radius: 4px; }
.class-actions { display: flex; gap: 8px; }

.card-body { padding: 24px; flex: 1; }
.class-name { font-size: 22px; font-weight: 800; color: #f0f6fc; margin-bottom: 20px; letter-spacing: -0.02em; }

.formateur-info { display: flex; flex-direction: column; gap: 8px; }
.label { font-size: 10px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; }
.formateur-chip { display: flex; align-items: center; gap: 10px; background: #161b22; padding: 8px 12px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #c9d1d9; border: 1px solid #21262d; }
.f-avatar { width: 24px; height: 24px; border-radius: 6px; }

.formateur-empty { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px dashed #30363d; border-radius: 10px; font-size: 12px; color: #8b949e; cursor: pointer; transition: all 0.2s; }
.formateur-empty:hover { border-color: #a371f7; color: #a371f7; background: rgba(163, 113, 247, 0.05); }
.formateur-empty svg { width: 14px; height: 14px; }

.card-footer { padding: 20px 24px; background: rgba(22, 27, 34, 0.5); border-top: 1px solid #21262d; display: flex; justify-content: space-around; align-items: center; border-radius: 0 0 20px 20px; }
.stat-item { display: flex; flex-direction: column; align-items: center; flex: 1; }
.stat-v { font-family: 'JetBrains Mono', monospace; font-size: 18px; font-weight: 800; color: #fff; }
.stat-l { font-size: 10px; color: #8b949e; font-weight: 600; text-transform: uppercase; margin-top: 2px; }
.stat-divider { width: 1px; height: 24px; background: #21262d; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: #0d1117; border: 1px solid #30363d; border-radius: 20px; padding: 32px; width: 400px; display: flex; flex-direction: column; gap: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
.modal-t { font-size: 20px; font-weight: 800; }
.nadi-input, .nadi-select { background: #161b22; border: 1px solid #30363d; padding: 14px; border-radius: 10px; color: white; font-size: 14px; outline: none; width: 100%; }
.nadi-input:focus, .nadi-select:focus { border-color: #a371f7; }
.modal-sub-p { color: #8b949e; font-size: 13px; margin-bottom: 8px; }
.modal-content.large { width: 600px; max-height: 80vh; padding: 0; gap: 0; }
.modal-header { padding: 32px; border-bottom: 1px solid #21262d; }
.search-box { padding: 0 32px; margin-top: 20px; }
.nadi-input.search { background: #0d1117; }
.students-list-container { flex: 1; overflow-y: auto; padding: 20px 32px; display: flex; flex-direction: column; gap: 8px; }
.student-select-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 12px; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; }
.student-select-item:hover { background: #161b22; }
.student-select-item.selected { background: rgba(163, 113, 247, 0.05); border-color: rgba(163, 113, 247, 0.2); }
.s-info { display: flex; align-items: center; gap: 12px; }
.s-mini-avatar { width: 36px; height: 36px; border-radius: 10px; }
.s-name { display: block; font-weight: 700; color: #f0f6fc; font-size: 14px; }
.s-meta { font-size: 11px; color: #8b949e; }
.s-checkbox .check { width: 20px; height: 20px; border-radius: 6px; border: 2px solid #30363d; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.check.checked { background: #a371f7; border-color: #a371f7; }
.check svg { width: 12px; height: 12px; color: white; }
.modal-footer { padding: 24px 32px; border-top: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center; }
.selection-count { font-size: 12px; color: #8b949e; font-weight: 600; }

.modal-btns { display: flex; gap: 12px; }
.btn-cancel { flex: 1; padding: 12px; background: none; border: 1px solid #30363d; color: #8b949e; border-radius: 10px; cursor: pointer; }
.btn-confirm { flex: 1; padding: 12px; background: #a371f7; border: none; color: white; border-radius: 10px; font-weight: 700; cursor: pointer; }

.btn-action { padding: 8px 16px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; flex: 1; white-space: nowrap; }
.btn-action.primary { background: rgba(163, 113, 247, 0.1); color: #a371f7; border-color: rgba(163, 113, 247, 0.2); }
.btn-action.primary:hover { background: #a371f7; color: white; }
.btn-action.secondary { background: rgba(48, 54, 61, 0.5); color: #8b949e; border-color: #30363d; }
.btn-action.secondary:hover { background: #30363d; color: white; }
.btn-action.danger { width: 36px; flex: 0 0 36px; display: flex; align-items: center; justify-content: center; background: rgba(248, 81, 73, 0.1); color: #f85149; border-color: rgba(248, 81, 73, 0.2); }
.btn-action.danger:hover { background: #f85149; color: white; }
.btn-action.danger svg { width: 14px; height: 14px; }

/* Skeleton */


.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>


