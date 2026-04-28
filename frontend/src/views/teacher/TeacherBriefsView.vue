<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in">

        <!-- Header -->
        <header class="page-header">
          <div>
            <div class="page-breadcrumb">Tableau de bord <span>/</span> Projets</div>
            <h1 class="page-title">Mes Briefs</h1>
            <p class="page-sub">{{ briefs.length }} projet{{ briefs.length > 1 ? 's' : '' }} · Gestion pédagogique</p>
          </div>
          <button class="btn-create" @click="router.push('/teacher/briefs/create')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Nouveau Brief
          </button>
        </header>

        <!-- Filters -->
        <div class="filters-row">
          <button
            v-for="f in filters"
            :key="f.key"
            class="filter-btn"
            :class="{ 'filter-btn--active': activeFilter === f.key }"
            @click="activeFilter = f.key"
          >
            {{ f.label }}
            <span class="filter-count">{{ filterCount(f.key) }}</span>
          </button>
        </div>

        <!-- Loader -->
        <div v-if="isLoading" class="state-box">
          <div class="loader-ring"></div>
          <p>Chargement en cours...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredBriefs.length === 0" class="state-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21a9 9 0 100-18 9 9 0 000 18zM8 12l3 3 5-5"/></svg>
          <h3>Aucun projet ici</h3>
          <p>Créez votre premier brief ou changez de filtre.</p>
        </div>

        <!-- Grid -->
        <section v-else class="briefs-grid">
          <div
            v-for="(brief, i) in filteredBriefs"
            :key="brief.id"
            class="brief-card animate-in"
            :style="{ animationDelay: (i * 0.06) + 's' }"
          >
            <!-- Card Image -->
            <div class="card-image">
              <img
                :src="brief.image_url || defaultImg"
                :alt="brief.title"
                class="card-img"
                @error="$event.target.src = defaultImg"
              />
              <div class="card-image-overlay"></div>

              <!-- Image Badges -->
              <div class="card-badges">
                <span class="card-badge" :class="diffClass(brief.difficulty)">
                  {{ diffLabel(brief.difficulty) }}
                </span>
                <span class="card-badge card-badge--modality">
                  {{ brief.modality === 'GROUP' ? '👥' : '👤' }}
                </span>
              </div>

              <!-- Assigned ribbon -->
              <div v-if="brief.classrooms?.length > 0" class="assigned-ribbon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                Assigné
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body">
              <div class="card-meta-row">
                <span class="card-id">#{{ brief.id }}</span>
                <span class="card-status" :class="brief.status === 'PUBLISHED' ? 'status-pub' : 'status-draft'">
                  {{ brief.status === 'PUBLISHED' ? '● Publié' : '○ Brouillon' }}
                </span>
              </div>

              <h2 class="card-title">{{ brief.title }}</h2>
              <p class="card-desc">{{ brief.description || 'Aucune description.' }}</p>

              <!-- Stats Row -->
              <div class="card-stats">

                <div v-if="brief.classrooms?.length > 0" class="stat stat--green">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                  <span>{{ brief.classrooms.length }} classe{{ brief.classrooms.length > 1 ? 's' : '' }}</span>
                </div>
                <div v-if="brief.date_end" class="stat stat--amber">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <span>{{ new Date(brief.date_end).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) }}</span>
                </div>
              </div>

                <!-- Actions Row -->
                <div class="card-actions">
                  <button class="btn-action btn-action--secondary" @click="router.push(`/teacher/briefs/${brief.id}/edit`)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Éditer
                  </button>

                  <button
                    v-if="(!brief.classrooms || brief.classrooms.length === 0) && (!brief.squads || brief.squads.length === 0)"
                    class="btn-action btn-action--primary"
                    @click="openAssignModal(brief)"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
                    Assigner
                  </button>

                  <template v-else>
                    <!-- Brief Assigned Status -->
                    <div class="btn-assigned-status">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                      <span v-if="brief.classrooms?.length > 0">Assigné ({{ brief.classrooms.length }} Cls)</span>
                      <span v-else>Assigné ({{ brief.squads?.length }} Sq)</span>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </section>

        <!-- Assign Modal -->
        <Transition name="modal">
          <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
            <div class="modal-card">
              <div class="modal-header">
                <div class="modal-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
                </div>
                <div>
                  <h2 class="modal-title">Assigner le Brief</h2>
                  <p class="modal-subtitle">Sélectionnez les classes cibles</p>
                </div>
                <button class="modal-close" @click="showModal = false">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
              </div>

              <div class="modal-body">
                <!-- Brief Preview -->
                <div class="modal-brief-preview">
                  <img :src="selectedBrief?.image_url || defaultImg" class="modal-brief-img" />
                  <div class="modal-brief-info">
                    <span class="modal-brief-id">#{{ selectedBrief?.id }}</span>
                    <h3 class="modal-brief-title">{{ selectedBrief?.title }}</h3>
                    <span class="modal-brief-pts">{{ selectedBrief?.difficulty }}</span>
                  </div>
                </div>

                <!-- Assignment Type Tabs -->
                <div class="assign-tabs">
                  <button 
                    class="assign-tab" 
                    :class="{ 'assign-tab--active': assignType === 'classroom' }"
                    @click="assignType = 'classroom'; selectedSquadIds = []"
                  >
                    Par Classes
                  </button>
                  <button 
                    class="assign-tab" 
                    :class="{ 'assign-tab--active': assignType === 'squad' }"
                    @click="assignType = 'squad'; selectedClassroomIds = []"
                  >
                    Par Squads
                  </button>
                </div>

                <!-- Classrooms Selector -->
                <div v-if="assignType === 'classroom'" class="classrooms-section">
                  <div class="classrooms-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    Vos classes
                    <span class="classrooms-count">{{ selectedClassroomIds.length }} sélectionnée{{ selectedClassroomIds.length > 1 ? 's' : '' }}</span>
                  </div>

                  <!-- Loading classrooms -->
                  <div v-if="isClassroomsLoading" class="classrooms-loading">
                    <div class="btn-spinner" style="border-color: rgba(56,139,253,0.3); border-top-color: #388bfd;"></div>
                    <span>Chargement en cours...</span>
                  </div>

                  <!-- No classrooms -->
                  <div v-else-if="myClassrooms.length === 0" class="classrooms-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Aucune classe ne vous est assignée. Contactez un administrateur.
                  </div>

                  <!-- Classroom list -->
                  <div v-else class="classrooms-list">
                    <label
                      v-for="cls in myClassrooms"
                      :key="cls.id"
                      class="classroom-row"
                      :class="{ 'classroom-row--selected': selectedClassroomIds.includes(cls.id) }"
                    >
                      <input
                        type="checkbox"
                        :value="cls.id"
                        v-model="selectedClassroomIds"
                        class="classroom-checkbox"
                      />
                      <div class="classroom-check-icon" :class="{ 'check-active': selectedClassroomIds.includes(cls.id) }">
                        <svg v-if="selectedClassroomIds.includes(cls.id)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                      </div>
                      <div class="classroom-info">
                        <span class="classroom-name">{{ cls.name }}</span>
                        <span class="classroom-students">{{ cls.students_count }} étudiant{{ cls.students_count > 1 ? 's' : '' }}</span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Squads Selector -->
                <div v-else class="classrooms-section">
                  <div class="classrooms-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    Vos squads
                    <span class="classrooms-count">{{ selectedSquadIds.length }} sélectionnée{{ selectedSquadIds.length > 1 ? 's' : '' }}</span>
                  </div>

                  <div v-if="isClassroomsLoading" class="classrooms-loading">
                    <div class="btn-spinner" style="border-color: rgba(56,139,253,0.3); border-top-color: #388bfd;"></div>
                    <span>Chargement en cours...</span>
                  </div>

                  <div v-else-if="allMySquads.length === 0" class="classrooms-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Aucune squad disponible.
                  </div>

                  <div v-else class="classrooms-list">
                    <label
                      v-for="sq in allMySquads"
                      :key="sq.id"
                      class="classroom-row"
                      :class="{ 'classroom-row--selected': selectedSquadIds.includes(sq.id) }"
                    >
                      <input
                        type="checkbox"
                        :value="sq.id"
                        v-model="selectedSquadIds"
                        class="classroom-checkbox"
                      />
                      <div class="classroom-check-icon" :class="{ 'check-active': selectedSquadIds.includes(sq.id) }">
                        <svg v-if="selectedSquadIds.includes(sq.id)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                      </div>
                      <div class="classroom-info">
                        <span class="classroom-name">{{ sq.name }}</span>
                        <span class="classroom-students">{{ myClassrooms.find(c => c.id === sq.classroom_id)?.name || 'Classe inconnue' }}</span>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button class="btn-modal-cancel" @click="showModal = false">Annuler</button>
                <button
                  class="btn-modal-confirm"
                  @click="confirmAssign"
                  :disabled="isAssigning || (assignType === 'classroom' ? selectedClassroomIds.length === 0 : selectedSquadIds.length === 0)"
                >
                  <svg v-if="!isAssigning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                  <div v-else class="btn-spinner"></div>
                  {{ isAssigning ? 'Déploiement...' : 'Confirmer la publication' }}
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
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import { BriefService } from '../../services/ApiService';
import api from '../../services/api';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { id: 1, first_name: 'Coach', last_name: '' });
const briefs = ref([]);
const isLoading = ref(true);
const isAssigning = ref(false);
const isClassroomsLoading = ref(false);
const showModal = ref(false);
const selectedBrief = ref(null);
const myClassrooms = ref([]);
const selectedClassroomIds = ref([]);
const assignType = ref('classroom'); // 'classroom' or 'squad'
const allMySquads = ref([]);
const selectedSquadIds = ref([]);
const activeFilter = ref('all');
const defaultImg = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=800';

const filters = [
  { key: 'all', label: 'Tous' },
  { key: 'draft', label: 'Brouillons' },
  { key: 'published', label: 'Publiés' },
  { key: 'assigned', label: 'Assignés' },
];

const filterCount = (key) => {
  if (key === 'all') return briefs.value.length;
  if (key === 'assigned') return briefs.value.filter(b => (b.classrooms?.length > 0 || b.squads?.length > 0)).length;
  if (key === 'published') return briefs.value.filter(b => b.status === 'PUBLISHED').length;
  if (key === 'draft') return briefs.value.filter(b => b.status === 'DRAFT').length;
  return 0;
};

const filteredBriefs = computed(() => {
  if (activeFilter.value === 'assigned') return briefs.value.filter(b => (b.classrooms?.length > 0 || b.squads?.length > 0));
  if (activeFilter.value === 'published') return briefs.value.filter(b => b.status === 'PUBLISHED');
  if (activeFilter.value === 'draft') return briefs.value.filter(b => b.status === 'DRAFT');
  return briefs.value;
});

const diffLabel = (d) => d === 'EASY' ? '🌱 Débutant' : d === 'MEDIUM' ? '⚡ Intermédiaire' : '🔥 Avancé';
const diffClass = (d) => d === 'EASY' ? 'diff-easy' : d === 'MEDIUM' ? 'diff-medium' : 'diff-hard';

onMounted(async () => {
  isLoading.value = true;
  
  try {
    const response = await BriefService.getAllList();
    const data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
    briefs.value = data;
    localStorage.setItem('teacher_briefs_cache', JSON.stringify(data));
  } catch (err) {
    console.error("Erreur Briefs:", err);
  } finally {
    isLoading.value = false;
  }
});

const openAssignModal = async (brief) => {
  selectedBrief.value = brief;
  selectedClassroomIds.value = [];
  selectedSquadIds.value = [];
  assignType.value = 'classroom';
  showModal.value = true;
  
  // Cache for classrooms
  const cachedClassrooms = localStorage.getItem('teacher_briefs_my_classrooms_cache');
  if (cachedClassrooms) {
    myClassrooms.value = JSON.parse(cachedClassrooms);
  }

  // Charger les classes et squads
  isClassroomsLoading.value = true;
  try {
    const [resCls, resSq] = await Promise.all([
      api.get('/classrooms/my'),
      api.get('/squads')
    ]);
    myClassrooms.value = resCls.data?.data || [];
    const squadsData = resSq.data?.squads;
    allMySquads.value = Array.isArray(squadsData) ? squadsData : (squadsData?.data || []);
    localStorage.setItem('teacher_briefs_my_classrooms_cache', JSON.stringify(myClassrooms.value));
  } catch (err) {
    console.error("Erreur Chargement:", err);
  }
  isClassroomsLoading.value = false;
};

const confirmAssign = async () => {
  if (assignType.value === 'classroom' && selectedClassroomIds.value.length === 0) return;
  if (assignType.value === 'squad' && selectedSquadIds.value.length === 0) return;

  isAssigning.value = true;
  try {
    if (assignType.value === 'classroom') {
      await BriefService.assignClassrooms(selectedBrief.value.id, selectedClassroomIds.value);
    } else {
      await BriefService.assignSquads(selectedBrief.value.id, selectedSquadIds.value);
    }

    // Mise à jour locale (optimiste)
    const idx = briefs.value.findIndex(b => b.id === selectedBrief.value.id);
    if (idx !== -1) {
      briefs.value[idx] = {
        ...briefs.value[idx],
        status: 'PUBLISHED',
        classrooms: assignType.value === 'classroom' 
          ? myClassrooms.value.filter(c => selectedClassroomIds.value.includes(c.id))
          : (briefs.value[idx].classrooms || []),
        squads: assignType.value === 'squad'
          ? allMySquads.value.filter(s => selectedSquadIds.value.includes(s.id))
          : (briefs.value[idx].squads || [])
      };
    }
    showModal.value = false;
  } catch (err) {
    console.error("Erreur assignation:", err);
  }
  
  selectedClassroomIds.value = [];
  selectedSquadIds.value = [];
  isAssigning.value = false;
};



const handleLogout = () => router.push('/login');
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.4) transparent; }
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }
.content { padding: 48px 52px; max-width: 1500px; margin: 0 auto; display: flex; flex-direction: column; gap: 36px; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: flex-end; }
.page-breadcrumb { font-size: 11px; color: #484f58; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px; }
.page-breadcrumb span { color: #388bfd; }
.page-title { font-size: 28px; font-weight: 900; color: #fff; letter-spacing: -0.03em; margin-bottom: 4px; }
.page-sub { font-size: 13px; color: #8b949e; }

.btn-create { display: flex; align-items: center; gap: 8px; background: #238636; color: #fff; border: 1px solid #2ea043; padding: 11px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-create svg { width: 14px; height: 14px; }
.btn-create:hover { background: #2ea043; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(35,134,54,0.3); }

/* Filters */
.filters-row { display: flex; gap: 8px; }
.filter-btn { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 9px; background: transparent; border: 1px solid rgba(48,54,61,0.5); color: #8b949e; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.filter-btn:hover { border-color: rgba(56,139,253,0.3); color: #c9d1d9; }
.filter-btn--active { background: rgba(56,139,253,0.08); border-color: rgba(56,139,253,0.4); color: #388bfd; }
.filter-count { background: rgba(48,54,61,0.5); color: #8b949e; font-size: 10px; font-weight: 800; padding: 1px 7px; border-radius: 20px; }
.filter-btn--active .filter-count { background: rgba(56,139,253,0.15); color: #388bfd; }

/* State Boxes */
.state-box { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; padding: 100px 40px; color: #484f58; text-align: center; }
.state-box svg { width: 48px; height: 48px; opacity: 0.2; }
.state-box h3 { font-size: 16px; font-weight: 700; color: #8b949e; }
.state-box p { font-size: 13px; max-width: 260px; line-height: 1.6; }
.loader-ring { width: 36px; height: 36px; border: 3px solid rgba(56,139,253,0.15); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.9s linear infinite; }


/* Grid */
.briefs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px; }

/* Card */
.brief-card {
  background: linear-gradient(145deg, rgba(22,27,34,0.9), rgba(13,17,23,0.95));
  border: 1px solid rgba(48,54,61,0.5); border-radius: 16px; overflow: hidden;
  display: flex; flex-direction: column; transition: all 0.25s;
}
.brief-card:hover { border-color: rgba(56,139,253,0.25); transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,0.3); }

/* Card Image */
.card-image { position: relative; height: 180px; overflow: hidden; flex-shrink: 0; }
.card-img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.4s; }
.brief-card:hover .card-img { transform: scale(1.04); }
.card-image-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(13,17,23,0.75) 0%, transparent 50%); }

.card-badges { position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: flex-start; }
.card-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 7px; border: 1px solid; }
.card-badge--modality { background: rgba(0,0,0,0.6); border-color: rgba(255,255,255,0.1); color: #fff; backdrop-filter: blur(8px); }
.diff-easy { background: rgba(63,185,80,0.15); border-color: rgba(63,185,80,0.3); color: #3fb950; }
.diff-medium { background: rgba(210,153,34,0.15); border-color: rgba(210,153,34,0.3); color: #d29922; }
.diff-hard { background: rgba(248,81,73,0.15); border-color: rgba(248,81,73,0.3); color: #f85149; }

/* Assigned Ribbon */
.assigned-ribbon { position: absolute; bottom: 12px; right: 12px; display: flex; align-items: center; gap: 6px; background: rgba(63,185,80,0.15); border: 1px solid rgba(63,185,80,0.35); color: #3fb950; padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 800; backdrop-filter: blur(6px); }
.assigned-ribbon svg { width: 11px; height: 11px; }

/* Card Body */
.card-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 14px; flex: 1; }
.card-meta-row { display: flex; justify-content: space-between; align-items: center; }
.card-id { font-size: 10px; font-weight: 900; color: #484f58; font-family: 'JetBrains Mono', monospace; }
.card-status { font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 6px; }
.status-pub { background: rgba(63,185,80,0.08); color: #3fb950; border: 1px solid rgba(63,185,80,0.2); }
.status-draft { background: rgba(72,79,88,0.2); color: #8b949e; border: 1px solid rgba(72,79,88,0.3); }

.card-title { font-size: 17px; font-weight: 800; color: #f0f6fc; letter-spacing: -0.01em; line-height: 1.3; margin: 0; }
.card-desc { font-size: 13px; color: #8b949e; line-height: 1.6; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

/* Stats Row */
.card-stats { display: flex; gap: 16px; padding: 12px 0; border-top: 1px solid rgba(48,54,61,0.3); border-bottom: 1px solid rgba(48,54,61,0.3); }
.stat { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #8b949e; }
.stat svg { width: 13px; height: 13px; flex-shrink: 0; }
.stat--green { color: #3fb950; }
.stat--amber { color: #d29922; }

/* Actions Row */
.card-actions { display: flex; gap: 10px; margin-top: 2px; }
.btn-action { display: flex; align-items: center; gap: 7px; padding: 9px 14px; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.18s; font-family: inherit; flex: 1; justify-content: center; }
.btn-action svg { width: 13px; height: 13px; flex-shrink: 0; }

.btn-action--secondary { background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.5); color: #8b949e; }
.btn-action--secondary:hover { border-color: rgba(56,139,253,0.3); color: #c9d1d9; background: rgba(56,139,253,0.04); }

.btn-action--primary { background: rgba(56,139,253,0.08); border: 1px solid rgba(56,139,253,0.3); color: #388bfd; }
.btn-action--primary:hover { background: rgba(56,139,253,0.15); border-color: rgba(56,139,253,0.5); transform: translateY(-1px); }

.btn-action--assigned { background: rgba(63,185,80,0.05); border: 1px solid rgba(63,185,80,0.2); color: #3fb950; cursor: default; flex: 1.5; font-size: 11px; }

.btn-assigned-status { display: flex; align-items: center; gap: 8px; padding: 9px 12px; background: rgba(63,185,80,0.05); border: 1px solid rgba(63,185,80,0.2); border-radius: 9px; color: #3fb950; font-size: 10px; font-weight: 800; flex: 1.5; }
.btn-assigned-status svg { width: 12px; height: 12px; flex-shrink: 0; }

.btn-action--quiz { background: #388bfd; border: 1px solid #388bfd; color: #fff; flex: 1.2; box-shadow: 0 4px 12px rgba(56,139,253,0.2); }
.btn-action--quiz:hover:not(:disabled) { background: #1f6feb; border-color: #1f6feb; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(56,139,253,0.3); }
.btn-action--quiz:disabled { opacity: 0.6; cursor: not-allowed; }

.no-quiz-label { font-size: 10px; font-weight: 700; color: #484f58; padding: 9px 12px; background: rgba(48,54,61,0.2); border: 1px solid rgba(48,54,61,0.3); border-radius: 9px; flex: 1.2; text-align: center; }

/* Assign Tabs */
.assign-tabs { display: flex; gap: 4px; padding: 4px; background: rgba(13,17,23,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 12px; margin-bottom: 8px; }
.assign-tab { flex: 1; padding: 8px; border-radius: 9px; border: none; background: transparent; color: #8b949e; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.assign-tab--active { background: rgba(56,139,253,0.1); color: #388bfd; }
.assign-tab:hover:not(.assign-tab--active) { color: #f0f6fc; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(1,4,9,0.85); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-card { width: 480px; background: linear-gradient(145deg, rgba(22,27,34,0.98), rgba(13,17,23,1)); border: 1px solid rgba(48,54,61,0.6); border-radius: 20px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.6); }

.modal-header { display: flex; align-items: center; gap: 16px; padding: 28px 32px; border-bottom: 1px solid rgba(48,54,61,0.4); }
.modal-icon { width: 42px; height: 42px; border-radius: 11px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-icon svg { width: 18px; height: 18px; color: #388bfd; }
.modal-title { font-size: 17px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 3px; }
.modal-subtitle { font-size: 12px; color: #8b949e; }
.modal-close { margin-left: auto; width: 30px; height: 30px; border-radius: 7px; background: transparent; border: 1px solid rgba(48,54,61,0.5); color: #8b949e; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s; }
.modal-close svg { width: 14px; height: 14px; }
.modal-close:hover { border-color: rgba(248,81,73,0.3); color: #f85149; }

.modal-body { padding: 28px 32px; display: flex; flex-direction: column; gap: 20px; }
.modal-brief-preview { display: flex; gap: 16px; align-items: center; padding: 16px; background: rgba(13,17,23,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 12px; }
.modal-brief-img { width: 64px; height: 48px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.modal-brief-info { flex: 1; }
.modal-brief-id { font-size: 10px; font-weight: 900; color: #484f58; font-family: 'JetBrains Mono', monospace; display: block; margin-bottom: 3px; }
.modal-brief-title { font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 3px; }
.modal-brief-pts { font-size: 11px; color: #8b949e; }
.modal-confirm-text { display: flex; align-items: flex-start; gap: 10px; padding: 14px; background: rgba(56,139,253,0.04); border: 1px solid rgba(56,139,253,0.15); border-radius: 10px; font-size: 13px; color: #8b949e; line-height: 1.5; }
.modal-confirm-text svg { width: 16px; height: 16px; color: #388bfd; flex-shrink: 0; margin-top: 1px; }

.modal-footer { display: flex; gap: 12px; padding: 24px 32px; border-top: 1px solid rgba(48,54,61,0.3); background: rgba(13,17,23,0.4); }
.btn-modal-cancel { flex: 1; padding: 12px; background: transparent; border: 1px solid rgba(48,54,61,0.5); color: #8b949e; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.15s; }
.btn-modal-cancel:hover { border-color: rgba(48,54,61,0.8); color: #c9d1d9; }
.btn-modal-confirm { flex: 1.5; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: #238636; border: 1px solid #2ea043; color: #fff; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.2s; }
.btn-modal-confirm svg { width: 15px; height: 15px; }
.btn-modal-confirm:hover:not(:disabled) { background: #2ea043; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(35,134,54,0.3); }
.btn-modal-confirm:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }

/* Modal Transition */
.modal-enter-active, .modal-leave-active { transition: all 0.25s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .modal-card, .modal-leave-to .modal-card { transform: scale(0.92); }

/* Classrooms Selector */
.classrooms-section { display: flex; flex-direction: column; gap: 12px; }
.classrooms-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; }
.classrooms-label svg { width: 14px; height: 14px; }
.classrooms-count { margin-left: auto; font-size: 11px; font-weight: 800; background: rgba(56,139,253,0.1); color: #388bfd; padding: 2px 9px; border-radius: 20px; text-transform: none; letter-spacing: 0; }
.classrooms-loading { display: flex; align-items: center; gap: 10px; padding: 16px; color: #8b949e; font-size: 13px; }
.classrooms-empty { display: flex; align-items: flex-start; gap: 10px; padding: 16px; background: rgba(210,153,34,0.04); border: 1px solid rgba(210,153,34,0.15); border-radius: 10px; font-size: 13px; color: #d29922; line-height: 1.5; }
.classrooms-empty svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
.classrooms-list { display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow-y: auto; }
.classrooms-list::-webkit-scrollbar { width: 3px; }
.classrooms-list::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.5); border-radius: 10px; }

.classroom-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: rgba(22,27,34,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 10px; cursor: pointer; transition: all 0.15s; user-select: none; }
.classroom-row:hover { border-color: rgba(56,139,253,0.3); background: rgba(56,139,253,0.04); }
.classroom-row--selected { border-color: rgba(63,185,80,0.35) !important; background: rgba(63,185,80,0.06) !important; }
.classroom-checkbox { display: none; }
.classroom-check-icon { width: 20px; height: 20px; border-radius: 6px; border: 2px solid rgba(48,54,61,0.6); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.classroom-check-icon.check-active { border-color: #3fb950; background: rgba(63,185,80,0.15); }
.classroom-check-icon svg { width: 11px; height: 11px; color: #3fb950; }
.classroom-info { flex: 1; }
.classroom-name { font-size: 13px; font-weight: 700; color: #f0f6fc; display: block; margin-bottom: 2px; }
.classroom-students { font-size: 11px; color: #8b949e; }

/* Animation */
.animate-in { animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>



