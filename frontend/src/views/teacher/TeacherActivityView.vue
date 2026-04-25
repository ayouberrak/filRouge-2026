<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in" v-if="!showEditor">

        <!-- ===== HEADER ===== -->
        <header class="page-header">
          <div class="header-left">
            <div class="header-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
              <h1 class="page-title">Activités <span class="dim">/ Workspace</span></h1>
              <p class="page-sub">Ingénierie pédagogique et suivi des ateliers pratiques</p>
            </div>
          </div>
          
          <div class="header-right">
              <div class="stats-mini">
                <div class="stat-item">
                  <span class="val">{{ activities.length }}</span>
                  <span class="lbl">Activités</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                  <span class="val">{{ classroomName }}</span>
                  <span class="lbl">Classe</span>
                </div>
              </div>
              <button class="btn-elite-primary" @click="openEditor(null)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
              Nouvelle Activité
            </button>
          </div>
        </header>

        <!-- ===== SEARCH & FILTERS ===== -->
        <div class="filter-bar">
          <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" v-model="searchQuery" placeholder="Rechercher une activité..." />
          </div>
          <div class="type-filters">
            <button 
              v-for="t in ['all', 'live_coding', 'veille', 'workshop', 'quiz']" 
              :key="t" 
              class="filter-chip"
              :class="{ active: activeFilter === t }"
              @click="activeFilter = t"
            >
              {{ t === 'all' ? 'Tous' : formatType(t) }}
            </button>
          </div>
        </div>

        <!-- ===== ACTIVITIES GRID ===== -->
        <div class="activities-grid">
          <div v-for="act in filteredActivities" :key="act.id" class="activity-card-elite">
            <div class="card-glow"></div>
            
            <div class="card-header">
              <span class="type-tag" :class="act.type">{{ formatType(act.type) }}</span>
              <div class="card-meta">
                <span class="status-badge" :class="act.status">
                  <span class="dot"></span>
                  {{ formatStatus(act.status) }}
                </span>

              </div>
            </div>

            <div class="card-body">
              <div class="card-schedule" v-if="act.scheduled_at">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ formatDate(act.scheduled_at) }} ({{ act.duration }})
              </div>
              <h3 class="act-title">{{ act.title }}</h3>
              <p class="act-desc">{{ act.description }}</p>
              
              <div class="assignment-status" v-if="act.students && act.students.length > 0">
                <div class="assigned-label">Assigné à</div>
                <div class="avatar-stack">
                  <img 
                    v-for="s in act.students.slice(0, 3)" 
                    :key="s.id" 
                    :src="getAvatar(s)" 
                    :title="s.first_name"
                    class="stack-avatar" 
                  />
                  <div class="stack-more" v-if="act.students.length > 3">+{{ act.students.length - 3 }}</div>
                </div>
              </div>
              <div class="unassigned-status" v-else>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                Non assignée
              </div>
            </div>

            <div class="card-footer">
              <button class="btn-action btn-edit" @click="openEditor(act)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modifier
              </button>
              <button class="btn-action btn-assign" v-if="!act.students || act.students.length === 0" @click="openAssignModal(act)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M12 3a4 4 0 110 8 4 4 0 010-8zM19 8v6M22 11h-6"/></svg>
                Assigner
              </button>
              <button class="btn-action btn-assigned" v-else disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                {{ act.students.length }} assigné{{ act.students.length > 1 ? 's' : '' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredActivities.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <h3 v-if="fetchError" class="text-error">Défaut de synchronisation</h3>
          <h3 v-else>Aucune activité trouvée</h3>
          
          <p v-if="fetchError">{{ fetchError }}</p>
          <p v-else>Commencez par créer une nouvelle activité pédagogique pour votre classe.</p>

          <button v-if="fetchError" class="btn-elite-ghost" style="margin-top: 16px" @click="fetchData">Réessayer</button>
        </div>

      </div>

      <!-- ===== EDITOR MODE ===== -->
      <div class="content animate-in" v-else>
        <header class="page-header">
          <div class="header-left">
            <button class="btn-back" @click="showEditor = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
            </button>
            <div>
              <h1 class="page-title">{{ editingId ? 'Configuration' : 'Nouvelle' }} <span class="dim">/ Activité</span></h1>
              <p class="page-sub">Paramétrage technique et pédagogique</p>
            </div>
          </div>
          <div class="header-right">
            <button class="btn-elite-ghost" @click="showEditor = false">Annuler</button>
            <button class="btn-elite-primary" @click="saveActivity" :disabled="isSaving">
              {{ isSaving ? 'Enregistrement...' : 'Confirmer' }}
            </button>
          </div>
        </header>

        <div class="editor-grid">
          <!-- Main Form -->
          <div class="editor-main elite-panel">
            <div class="form-section">
              <h2 class="section-title">Informations Générales</h2>
              <div class="input-grid">
                <div class="input-group full">
                  <label>Titre de l'activité</label>
                  <input type="text" v-model="form.title" placeholder="Ex: Live Coding Refactoring..." />
                </div>
                <div class="input-group">
                  <label>Type d'activité</label>
                  <select v-model="form.type">
                    <option value="workshop">Workshop</option>
                    <option value="live_coding">Live Coding</option>
                    <option value="veille">Veille</option>
                    <option value="quiz">Quiz</option>
                  </select>
                </div>
                <div class="input-group">
                  <label>Date et heure de début</label>
                  <input type="datetime-local" v-model="form.scheduled_at" />
                </div>
                <div class="input-group">
                  <label>Durée (ex: 2h, 45min)</label>
                  <input type="text" v-model="form.duration" placeholder="Affichage (ex: 2h)" />
                </div>
                <div class="input-group">
                  <label>Durée technique (minutes)</label>
                  <input type="number" v-model="form.duration_minutes" placeholder="Pour l'auto-completion" />
                </div>

              </div>
            </div>

            <div class="form-section">
              <h2 class="section-title">Contenu</h2>
              <div class="input-group">
                <label>Description (Aperçu carte)</label>
                <textarea v-model="form.description" rows="5" placeholder="Brève description attractive..."></textarea>
              </div>
            </div>
          </div>

          <!-- Sidebar Form -->
          <div class="editor-side">
            <!-- Sidebar Form Removed Resources section -->

            <div class="assignment-preview elite-panel" v-if="form.type">
              <h2 class="section-title">Modalité d'Assignation</h2>
              <div class="logic-box" :class="form.type">
                <div class="logic-icon">
                  <svg v-if="form.type === 'quiz'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div class="logic-desc">
                  <strong v-if="form.type === 'quiz'">Toute la classe</strong>
                  <strong v-else-if="form.type === 'workshop'">Groupe multiple</strong>
                  <strong v-else>1 ou 2 étudiants</strong>
                  <p v-if="form.type === 'quiz'">L'activité sera automatiquement assignée à tous les étudiants de {{ classroomName }}.</p>
                  <p v-else>Vous pourrez choisir les participants après avoir enregistré l'activité.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ===== ASSIGNMENT MODAL ===== -->
    <Transition name="fade">
      <div class="modal-overlay" v-if="showAssignModal" @click.self="closeAssignModal">
        <div class="modal-content elite-panel animate-scale">
          <div class="modal-header">
            <div>
              <h2 class="modal-title">Assigner l'activité</h2>
              <p class="modal-sub">{{ selectedActivity?.title }}</p>
            </div>
            <button class="btn-close" @click="closeAssignModal">&times;</button>
          </div>

          <div class="modal-body">
            <!-- Summary -->
            <div class="selection-summary" :class="{ error: isSelectionInvalid }">
              <span class="count">{{ selectedStudentIds.length }}</span>
              <span class="lbl">étudiant(s) sélectionné(s)</span>
              <span class="limit" v-if="maxParticipants > 0">/ Max {{ maxParticipants }}</span>
            </div>

            <div class="student-selector">
              <div 
                v-for="s in classroomStudents" 
                :key="s.id" 
                class="student-item"
                :class="{ selected: selectedStudentIds.includes(s.id), disabled: isSelectionFull && !selectedStudentIds.includes(s.id) }"
                @click="toggleStudent(s.id)"
              >
                <img :src="getAvatar(s)" class="student-avatar" />
                <div class="student-info">
                  <span class="name">{{ s.first_name }} {{ s.last_name }}</span>
                </div>
                <div class="check-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-elite-ghost" @click="closeAssignModal">Annuler</button>
            <button 
              class="btn-elite-primary" 
              :disabled="selectedStudentIds.length === 0 || isSelectionInvalid || isAssigning"
              @click="confirmAssignment"
            >
              {{ isAssigning ? 'Assignation...' : 'Confirmer l\'assignation' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import { ActivityService } from '../../services/ApiService';
import api from '../../services/api';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Formateur', last_name: '', id: 1 });
const stats = ref({ total_activities: 0, pending: 0 });
const activities = ref([]);
const classroomStudents = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const fetchError = ref(null);

// Dynamic classroomId selection
const classroomId = computed(() => user.value.classroom_id || 1);
const classroomName = ref('Promotion 2026');

const totalActivities = computed(() => activities.value.length);
const activeFilter = ref('all');
const searchQuery = ref('');
const showEditor = ref(false);
const editingId = ref(null);
const isAssigning = ref(false);

const form = ref({
  title: '',
  description: '',
  type: 'workshop',
  duration: '2h',
  duration_minutes: 120,
  scheduled_at: ''
});

// Assignment Modal
const showAssignModal = ref(false);
const selectedActivity = ref(null);
const selectedStudentIds = ref([]);

// ─── COMPUTED ────────────────────────────────────────────────────────────────

const filteredActivities = computed(() => {
  return activities.value.filter(act => {
    const actType = act.type || act.activity_type;
    const matchesSearch = act.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesFilter = activeFilter.value === 'all' || actType === activeFilter.value;
    return matchesSearch && matchesFilter;
  });
});

const maxParticipants = computed(() => {
  if (!selectedActivity.value) return 0;
  const type = selectedActivity.value.type || selectedActivity.value.activity_type;
  if (type === 'live_coding' || type === 'veille') return 2;
  return 0; // 0 means no limit
});

const isSelectionFull = computed(() => {
  return maxParticipants.value > 0 && selectedStudentIds.value.length >= maxParticipants.value;
});

const isSelectionInvalid = computed(() => {
  if (maxParticipants.value > 0 && selectedStudentIds.value.length > maxParticipants.value) return true;
  return false;
});

// ─── METHODS ─────────────────────────────────────────────────────────────────

const formatType = (type) => {
  const map = {
    'live_coding': 'Live Coding',
    'veille': 'Veille',
    'workshop': 'Workshop',
    'quiz': 'Quiz'
  };
  return map[type] || type;
};

const formatStatus = (status) => {
  const map = {
    'scheduled': 'Programmé',
    'active': 'En cours',
    'completed': 'Terminé'
  };
  return map[status] || status;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('fr-FR', { 
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' 
  });
};

const getAvatar = (s) => {
  return s.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(s.first_name + ' ' + s.last_name)}&background=161b22&color=388bfd&bold=true`;
};

const fetchData = async () => {
  fetchError.value = null;
  
  // Cache
  const cachedAct = localStorage.getItem(`teacher_activities_cache_${classroomId.value}`);
  const cachedStu = localStorage.getItem(`teacher_classroom_students_cache_${classroomId.value}`);
  
  if (cachedAct && cachedStu) {
    activities.value = JSON.parse(cachedAct);
    classroomStudents.value = JSON.parse(cachedStu);
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }

  try {
    const [actRes, stuRes] = await Promise.all([
      ActivityService.getByClassroom(classroomId.value),
      api.get('/analytics/students', { params: { classroom_id: classroomId.value } })
    ]);

    const fetchedData = actRes.data?.data || actRes.data;
    activities.value = Array.isArray(fetchedData) ? fetchedData : [];
    classroomStudents.value = stuRes.data?.data || [];

    // Update Cache
    localStorage.setItem(`teacher_activities_cache_${classroomId.value}`, JSON.stringify(activities.value));
    localStorage.setItem(`teacher_classroom_students_cache_${classroomId.value}`, JSON.stringify(classroomStudents.value));
  } catch (err) {
    console.error("Erreur Activity Data:", err);
  }
  
  isLoading.value = false;
};

const openEditor = (act) => {
  if (act) {
    editingId.value = act.id;
    form.value = { 
      ...act,
      type: act.type || act.activity_type,
      scheduled_at: act.scheduled_at ? act.scheduled_at.substring(0, 16) : '', // format for datetime-local
      duration_minutes: act.duration_minutes || 60
    };
  } else {
    editingId.value = null;
    form.value = {
      title: '', description: '', type: 'workshop',
      duration: '2h', duration_minutes: 120, scheduled_at: new Date().toISOString().substring(0, 16)
    };
  }
  showEditor.value = true;
};

const saveActivity = async () => {
  isSaving.value = true;
  const payload = {
    ...form.value,
    classroom_id: classroomId.value,
    formateur_id: user.value.id || 1,
    student_ids: [] // DTO compatibility
  };

  const res = await ActivityService.create(payload);
  const newAct = res.data?.data || res.data;

  // Spécial pour Quiz : auto-assignation à tout le monde
  if (newAct.type === 'quiz' || newAct.activity_type === 'quiz') {
    await ActivityService.assignToClassroom(newAct.id, classroomId.value);
  }

  await fetchData();
  showEditor.value = false;
  isSaving.value = false;
};

const openAssignModal = (act) => {
  selectedActivity.value = act;
  selectedStudentIds.value = [];
  showAssignModal.value = true;
};

const closeAssignModal = () => {
  showAssignModal.value = false;
  selectedActivity.value = null;
};

const toggleStudent = (id) => {
  const idx = selectedStudentIds.value.indexOf(id);
  if (idx > -1) {
    selectedStudentIds.value.splice(idx, 1);
  } else {
    if (isSelectionFull.value) return;
    selectedStudentIds.value.push(id);
  }
};

const confirmAssignment = async () => {
  if (!selectedActivity.value) return;
  isAssigning.value = true;
  await ActivityService.assignToStudents(selectedActivity.value.id, selectedStudentIds.value);
  await fetchData();
  closeAssignModal();
  isAssigning.value = false;
};

const handleLogout = () => router.push('/login');

onMounted(fetchData);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', sans-serif; overflow: hidden; }
.main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 44px 52px; display: flex; flex-direction: column; gap: 40px; }

/* ===== ELITE COMPONENTS ===== */
.elite-panel { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 20px; padding: 32px; backdrop-filter: blur(10px); }

/* ===== HEADER ===== */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.header-left { display: flex; align-items: center; gap: 20px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #388bfd; }
.header-icon svg { width: 22px; height: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; }
.dim { color: #484f58; font-weight: 500; }
.page-sub { font-size: 13px; color: #8b949e; margin-top: 6px; }

.header-right { display: flex; align-items: center; gap: 32px; }
.stats-mini { display: flex; align-items: center; gap: 24px; background: rgba(22,27,34,0.6); padding: 8px 20px; border-radius: 12px; border: 1px solid rgba(48,54,61,0.5); }
.stat-item { display: flex; flex-direction: column; }
.stat-item .val { font-size: 16px; font-weight: 800; color: #fff; }
.stat-item .lbl { font-size: 10px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-divider { width: 1px; height: 24px; background: rgba(48,54,61,0.5); }

.stat-item.debug-badge { background: rgba(56,139,253,0.15); padding: 4px 12px; border-radius: 8px; border: 1px dashed rgba(56,139,253,0.4); }
.text-error { color: #f85149; }

.btn-elite-primary { background: #388bfd; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s; box-shadow: 0 4px 15px rgba(56,139,253,0.25); }
.btn-elite-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(56,139,253,0.35); filter: brightness(1.1); }
.btn-elite-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-elite-primary svg { width: 14px; height: 14px; }

.btn-elite-ghost { background: transparent; border: 1px solid rgba(48,54,61,0.8); color: #8b949e; padding: 12px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.btn-elite-ghost:hover { border-color: #388bfd; color: #fff; background: rgba(56,139,253,0.05); }

/* ===== FILTERS ===== */
.filter-bar { display: flex; justify-content: space-between; align-items: center; gap: 32px; }
.search-wrap { flex: 1; max-width: 400px; position: relative; }
.search-wrap svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #484f58; }
.search-wrap input { width: 100%; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 14px; padding: 12px 16px 12px 48px; color: #fff; font-size: 14px; transition: all 0.2s; }
.search-wrap input:focus { outline: none; border-color: #388bfd; background: rgba(13,17,23,0.8); }

.type-filters { display: flex; gap: 10px; }
.filter-chip { background: transparent; border: 1px solid rgba(48,54,61,0.5); color: #8b949e; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.filter-chip:hover { color: #fff; border-color: rgba(48,54,61,1); }
.filter-chip.active { background: #388bfd; color: #fff; border-color: #388bfd; box-shadow: 0 4px 12px rgba(56,139,253,0.2); }

/* ===== GRID ===== */
.activities-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 28px; }

.activity-card-elite { position: relative; background: rgba(13,17,23,0.4); border: 1px solid rgba(48,54,61,0.6); border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 24px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden; }
.activity-card-elite:hover { transform: translateY(-6px); border-color: #388bfd; box-shadow: 0 12px 30px rgba(0,0,0,0.4); }
.card-glow { position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(56,139,253,0.05) 0%, transparent 60%); opacity: 0; transition: opacity 0.3s; pointer-events: none; }
.activity-card-elite:hover .card-glow { opacity: 1; }

.card-header { display: flex; justify-content: space-between; align-items: center; }
.type-tag { font-size: 9px; font-weight: 900; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.1em; }
.type-tag.workshop { background: rgba(56,139,253,0.1); color: #388bfd; }
.type-tag.live_coding { background: rgba(63,185,80,0.1); color: #3fb950; }
.type-tag.veille { background: rgba(210,153,34,0.1); color: #d29922; }
.type-tag.quiz { background: rgba(248,81,73,0.1); color: #f85149; }

.status-badge { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 8px; }
.status-badge .dot { width: 6px; height: 6px; border-radius: 50%; }
.status-badge.active { background: rgba(63,185,80,0.1); color: #3fb950; }
.status-badge.active .dot { background: #3fb950; animation: pulse-green 2s infinite; }
.status-badge.completed { background: rgba(48,54,61,0.3); color: #8b949e; }
.status-badge.completed .dot { background: #8b949e; }
.status-badge.scheduled { background: rgba(56,139,253,0.1); color: #388bfd; }
.status-badge.scheduled .dot { background: #388bfd; }

@keyframes pulse-green {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(63,185,80, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(63,185,80, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(63,185,80, 0); }
}

.card-schedule { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #8b949e; margin-bottom: 12px; }
.card-schedule svg { width: 14px; height: 14px; }
.points { color: #3fb950; font-family: 'JetBrains Mono', monospace; }

.act-title { font-size: 20px; font-weight: 800; color: #fff; line-height: 1.2; }
.act-desc { font-size: 13px; color: #8b949e; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.assignment-status { display: flex; align-items: center; gap: 16px; margin-top: 8px; }
.assigned-label { font-size: 9px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.05em; }
.avatar-stack { display: flex; align-items: center; }
.stack-avatar { width: 28px; height: 28px; border-radius: 8px; border: 2px solid #0d1117; margin-right: -8px; object-fit: cover; }
.stack-more { width: 28px; height: 28px; border-radius: 8px; background: #161b22; border: 2px solid #0d1117; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #8b949e; }

.unassigned-status { display: flex; align-items: center; gap: 8px; color: #484f58; font-size: 11px; font-weight: 700; margin-top: 8px; }
.unassigned-status svg { width: 14px; height: 14px; }

.card-footer { display: flex; gap: 12px; margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(48,54,61,0.3); }
.btn-action { flex: 1; padding: 10px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-edit { background: rgba(48,54,61,0.3); border: 1px solid rgba(48,54,61,0.6); color: #8b949e; }
.btn-edit:hover { background: rgba(48,54,61,0.5); color: #fff; }
.btn-edit svg { width: 14px; height: 14px; }
.btn-assign { background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.3); color: #388bfd; }
.btn-assign:hover { background: #388bfd; color: #fff; }
.btn-assigned { background: rgba(63,185,80,0.08); border: 1px solid rgba(63,185,80,0.25); color: #3fb950; cursor: default; opacity: 0.85; }

/* ===== EDITOR ===== */
.btn-back { width: 48px; height: 48px; border-radius: 14px; background: transparent; border: 1px solid rgba(48,54,61,0.6); color: #8b949e; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-back:hover { border-color: #388bfd; color: #fff; }
.btn-back svg { width: 20px; height: 20px; }

.editor-grid { display: grid; grid-template-columns: 1fr 380px; gap: 32px; align-items: start; }
.form-section { display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px; }
.form-section:last-child { margin-bottom: 0; }
.section-title { font-size: 14px; font-weight: 800; color: #388bfd; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }

.input-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
.input-group.full { grid-column: span 3; }
.input-group { display: flex; flex-direction: column; gap: 10px; }
.input-group label { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.05em; }
.input-group input, .input-group select, .input-group textarea { background: rgba(1,4,9,0.5); border: 1px solid rgba(48,54,61,0.8); border-radius: 12px; padding: 14px 16px; color: #fff; font-size: 14px; transition: all 0.2s; font-family: inherit; }
.input-group input:focus, .input-group select:focus, .input-group textarea:focus { outline: none; border-color: #388bfd; background: rgba(1,4,9,0.8); box-shadow: 0 0 0 4px rgba(56,139,253,0.1); }

.resource-panel { margin-bottom: 32px; display: flex; flex-direction: column; gap: 24px; }
.logic-box { display: flex; gap: 16px; padding: 20px; border-radius: 16px; border: 1px solid rgba(48,54,61,0.6); background: rgba(1,4,9,0.3); }
.logic-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.logic-icon svg { width: 20px; height: 20px; }

.logic-box.workshop .logic-icon { background: rgba(56,139,253,0.1); color: #388bfd; }
.logic-box.quiz .logic-icon { background: rgba(248,81,73,0.1); color: #f85149; }
.logic-box.live_coding .logic-icon, .logic-box.veille .logic-icon { background: rgba(63,185,80,0.1); color: #3fb950; }

.logic-desc strong { display: block; font-size: 14px; color: #fff; margin-bottom: 4px; }
.logic-desc p { font-size: 12px; color: #8b949e; line-height: 1.5; }

/* ===== MODAL ===== */
.modal-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-content { width: 100%; max-width: 500px; max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
.modal-title { font-size: 20px; font-weight: 900; color: #fff; }
.modal-sub { font-size: 13px; color: #8b949e; margin-top: 4px; }
.btn-close { background: transparent; border: none; font-size: 28px; color: #484f58; cursor: pointer; transition: color 0.2s; }
.btn-close:hover { color: #fff; }

.modal-body { flex: 1; overflow-y: auto; padding-right: 8px; }
.selection-summary { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 12px 20px; background: rgba(56,139,253,0.1); border-radius: 12px; border: 1px solid rgba(56,139,253,0.2); }
.selection-summary.error { background: rgba(248,81,73,0.1); border-color: rgba(248,81,73,0.2); }
.selection-summary.error .count { color: #f85149; }
.selection-summary .count { font-size: 18px; font-weight: 900; color: #388bfd; }
.selection-summary .lbl { font-size: 12px; font-weight: 700; color: #fff; }
.selection-summary .limit { font-size: 11px; color: #484f58; margin-left: auto; font-weight: 700; }

.student-selector { display: flex; flex-direction: column; gap: 8px; }
.student-item { display: flex; align-items: center; gap: 16px; padding: 12px 16px; border-radius: 14px; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; }
.student-item:hover { background: rgba(48,54,61,0.3); }
.student-item.selected { background: rgba(56,139,253,0.1); border-color: rgba(56,139,253,0.3); }
.student-item.disabled { opacity: 0.4; cursor: not-allowed; }

.student-avatar { width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(48,54,61,0.6); }
.student-info { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.student-info .name { font-size: 13px; font-weight: 700; color: #fff; }
.student-info .pts { font-size: 11px; color: #3fb950; font-family: 'JetBrains Mono', monospace; }
.check-icon { width: 22px; height: 22px; border-radius: 6px; border: 2px solid rgba(48,54,61,0.6); display: flex; align-items: center; justify-content: center; color: transparent; transition: all 0.2s; }
.student-item.selected .check-icon { background: #388bfd; border-color: #388bfd; color: #fff; }

.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(48,54,61,0.3); }

/* ===== ANIMATIONS ===== */
.animate-in { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-scale { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
@keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Empty state */
.empty-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px; text-align: center; }
.empty-icon { width: 80px; height: 80px; background: rgba(48,54,61,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #484f58; margin-bottom: 24px; }
.empty-icon svg { width: 40px; height: 40px; }
.empty-state h3 { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px; }
.empty-state p { font-size: 14px; color: #8b949e; max-width: 320px; }
</style>
