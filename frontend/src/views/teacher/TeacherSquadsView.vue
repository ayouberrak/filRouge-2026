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
                <h1 class="page-title">Gestion <span class="dim">/ Squads</span></h1>
                <p class="page-sub">Organisation stratégique des unités de force promotionnelles</p>
              </div>
            </div>
          </div>
          
          <div class="header-right">
            <button @click="createSquad" class="btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
              Nouveau Groupe
            </button>
          </div>
        </header>

        <!-- ===== WORKSPACE GRID ===== -->
        <div class="workspace-grid">
          
          <!-- LEFT: ACTIVE SQUADS -->
          <section class="squads-panel animate-in" style="animation-delay: 0.1s">
            <div class="panel-header">
              <h2 class="panel-subtitle">Groupes Actifs ({{ squads.length }})</h2>
              <p class="panel-hint">Déposez un étudiant sur une carte pour l'assigner</p>
            </div>

            <div v-if="isLoading" class="loading-state">
               <div class="loader"></div>
               Synchronisation des unités...
            </div>

            <div v-else-if="squads.length === 0" class="empty-state">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
              <p>Aucun squad opérationnel</p>
            </div>

            <div v-else class="squad-grid">
              <div 
                v-for="(squad, idx) in squads" 
                :key="squad.id" 
                class="elite-squad-card"
                :class="{ 'drop-active': activeDropTarget === squad.id }"
                :style="{ animationDelay: (0.2 + (idx * 0.08)) + 's' }"
                @dragover="onDragOver($event, squad.id)"
                @dragleave="onDragLeave"
                @drop="onDrop($event, squad.id)"
              >
                <!-- Card Header -->
                <div class="squad-card-header">
                  <div class="squad-id">
                    <span class="label">SQUAD</span>
                    <h3 class="name">{{ squad.name }}</h3>
                  </div>
                  <div class="squad-actions">
                    <button class="btn-icon danger" @click="dissolveSquad(squad.id)" title="Dissoudre le groupe">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                  </div>
                </div>

                <!-- Members List -->
                <div class="squad-members-wrap">
                  <div class="members-header">
                    <span class="count">{{ squad.members?.length || 0 }} Membres</span>
                    <div class="header-line"></div>
                  </div>

                  <div class="members-list">
                    <div v-for="(student, sidx) in squad.members" :key="student.id" class="member-item">
                      <div class="member-avatar-wrap">
                        <img :src="student.avatar_url || getAvatar(student.first_name)" class="member-avatar" />
                        <div v-if="sidx === 0" class="lead-badge" title="Chef de groupe">
                          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4h7.8l-6.3 4.6 2.4 7.4-6.3-4.6-6.3 4.6 2.4-7.4-6.3-4.6h7.8z"/></svg>
                        </div>
                      </div>
                      <div class="member-meta">
                        <span class="name">{{ student.first_name }} {{ student.last_name[0] }}.</span>
                        <button class="btn-remove" @click="removeFromSquad(squad.id, student.id)">Retirer</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- RIGHT: UNASSIGNED STUDENTS -->
          <aside class="pool-panel animate-in" style="animation-delay: 0.2s">
            <div class="pool-card">
              <div class="pool-header">
                <div class="title-row">
                  <h3 class="pool-title">Vivier Étudiants</h3>
                  <span class="pool-count">{{ unassignedStudents.length }}</span>
                </div>
                <p class="pool-desc">Glissez-déposez pour assigner</p>
              </div>

              <div v-if="unassignedStudents.length === 0" class="pool-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Tous les étudiants sont affectés</p>
              </div>

              <div v-else class="pool-scroll">
                <div 
                  v-for="st in unassignedStudents" 
                  :key="st.id" 
                  class="pool-item"
                  draggable="true"
                  @dragstart="onDragStart($event, st)"
                  @dragend="onDragEnd"
                >
                  <div class="pi-avatar-wrap">
                    <img :src="st.avatar_url || getAvatar(st.first_name)" class="pi-avatar" />
                    <div class="pi-drag-handle">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </div>
                  </div>
                  <div class="pi-info">
                    <span class="pi-name">{{ st.first_name }} {{ st.last_name }}</span>
                    <span class="pi-points">{{ st.total_points || 0 }} pts</span>
                  </div>
                  <div class="pi-quick-actions">
                    <button v-for="s in squads" :key="s.id" class="qa-btn" @click="assignToSquad(st.id, s.id)">
                      + {{ s.name }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </aside>

        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import api from '../../services/api';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Formateur', last_name: '' });
const squads = ref([]);
const unassignedStudents = ref([]);
const isLoading = ref(true);
const classroomId = ref(1); // Promotion 2026
const activeDropTarget = ref(null);

const fetchData = async (silent = false) => {
  if (!silent) isLoading.value = true;
  try {
    const [squadsRes, studentsRes] = await Promise.all([
      api.get('/squads', { params: { classroom_id: classroomId.value } }),
      api.get('/students', { params: { classroom_id: classroomId.value } })
    ]);
    squads.value = squadsRes.data.squads || [];
    unassignedStudents.value = studentsRes.data.data.filter(s => !s.squad_id);
  } catch (error) {
    console.error("Load squads error:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchData);

// ─── DRAG AND DROP ────────────────────────────────────────────────────────────

const onDragStart = (event, student) => {
  event.dataTransfer.setData('studentId', student.id);
  event.dataTransfer.effectAllowed = 'move';
  setTimeout(() => { event.target.classList.add('is-dragging'); }, 0);
};

const onDragEnd = (event) => {
  event.target.classList.remove('is-dragging');
  activeDropTarget.value = null;
};

const onDragOver = (event, squadId) => {
  event.preventDefault();
  activeDropTarget.value = squadId;
};

const onDragLeave = () => { activeDropTarget.value = null; };

const onDrop = async (event, squadId) => {
  event.preventDefault();
  activeDropTarget.value = null;
  const studentId = parseInt(event.dataTransfer.getData('studentId'));
  if (!studentId) return;

  const studentIndex = unassignedStudents.value.findIndex(s => s.id === studentId);
  if (studentIndex === -1) return;

  const student = unassignedStudents.value[studentIndex];
  unassignedStudents.value.splice(studentIndex, 1);
  const squad = squads.value.find(s => s.id === squadId);
  if (squad) squad.members.push(student);

  try {
    await api.post(`/squads/${squadId}/members`, { user_id: studentId });
    await fetchData(true);
  } catch (error) {
    alert("Erreur lors de l'assignation opérationnelle.");
    await fetchData();
  }
};

// ─── ACTIONS ──────────────────────────────────────────────────────────────────

const assignToSquad = async (studentId, squadId) => {
  // Manual trigger for drop logic
  const studentIndex = unassignedStudents.value.findIndex(s => s.id === studentId);
  if (studentIndex === -1) return;
  const student = unassignedStudents.value[studentIndex];
  unassignedStudents.value.splice(studentIndex, 1);
  const squad = squads.value.find(s => s.id === squadId);
  if (squad) squad.members.push(student);
  
  try {
    await api.post(`/squads/${squadId}/members`, { user_id: studentId });
    await fetchData(true);
  } catch (error) {
    alert("Échec de l'assignation rapide.");
    await fetchData();
  }
};

const removeFromSquad = async (squadId, studentId) => {
  if (!confirm("🚨 Confirmer l'extraction de l'étudiant du squadron ?")) return;
  try {
    await api.delete(`/squads/${squadId}/members/${studentId}`);
    await fetchData();
  } catch (error) {
    alert("Erreur lors de l'extraction.");
  }
};

const dissolveSquad = async (squadId) => {
  if (!confirm("🚨 ALERTE : La dissolution du groupe est irréversible. Confirmer ?")) return;
  try {
    await api.delete(`/squads/${squadId}`);
    await fetchData();
  } catch (error) {
    alert("Erreur critique lors de la dissolution.");
  }
};

const createSquad = async () => {
  const name = prompt("Indicatif d'appel du groupe :");
  if (!name || name.length < 3) return;
  try {
    await api.post('/squads/create', { name, classroom_id: classroomId.value });
    await fetchData();
  } catch (error) {
    alert("Échec de l'initialisation du groupe.");
  }
};

const getAvatar = (name) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=161b22&color=388bfd&bold=true`;
const handleLogout = () => router.push('/login');
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.4) transparent; }
.content { padding: 44px 52px; max-width: 1600px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }

/* ===== HEADER ===== */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.header-title-row { display: flex; align-items: center; gap: 18px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #388bfd; }
.header-icon svg { width: 22px; height: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; line-height: 1; }
.dim { color: #484f58; font-weight: 500; font-size: 20px; }
.page-sub { font-size: 13px; color: #8b949e; margin-top: 6px; }

.btn-primary { display: flex; align-items: center; gap: 8px; background: #238636; color: #fff; border: 1px solid #2ea043; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-primary svg { width: 14px; height: 14px; }
.btn-primary:hover { background: #2ea043; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(35,134,54,0.3); }

/* ===== WORKSPACE GRID ===== */
.workspace-grid { display: grid; grid-template-columns: 1fr 340px; gap: 36px; align-items: start; }

/* LEFT PANEL */
.panel-header { margin-bottom: 24px; }
.panel-subtitle { font-size: 16px; font-weight: 800; color: #f0f6fc; margin-bottom: 4px; }
.panel-hint { font-size: 12px; color: #484f58; }

.squad-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 20px; }

/* ELITE SQUAD CARD */
.elite-squad-card { 
  background: linear-gradient(145deg, rgba(22,27,34,0.9), rgba(13,17,23,0.95));
  border: 1px solid rgba(48,54,61,0.5); border-radius: 20px; padding: 24px;
  display: flex; flex-direction: column; gap: 24px; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative; overflow: hidden;
}
.elite-squad-card:hover { border-color: rgba(56,139,253,0.3); transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
.elite-squad-card.drop-active { border-color: #388bfd; background: rgba(56,139,253,0.05); transform: scale(1.02); box-shadow: 0 0 20px rgba(56,139,253,0.15); }

.squad-card-header { display: flex; justify-content: space-between; align-items: flex-start; }
.squad-id .label { font-size: 9px; font-weight: 900; color: #484f58; letter-spacing: 0.15em; }
.squad-id .name { font-size: 22px; font-weight: 900; color: #fff; letter-spacing: -0.02em; line-height: 1.1; margin-top: 4px; }

.btn-icon { width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(48,54,61,0.5); background: transparent; color: #484f58; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-icon svg { width: 14px; height: 14px; }
.btn-icon.danger:hover { background: rgba(248,81,73,0.1); border-color: #f85149; color: #f85149; }

/* MEMBERS */
.squad-members-wrap { display: flex; flex-direction: column; gap: 16px; }
.members-header { display: flex; align-items: center; gap: 12px; }
.members-header .count { font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
.header-line { flex: 1; height: 1px; background: rgba(48,54,61,0.3); }

.members-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 14px; }
.member-item { display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }

.member-avatar-wrap { position: relative; }
.member-avatar { width: 44px; height: 44px; border-radius: 12px; object-fit: cover; border: 2px solid rgba(48,54,61,0.6); transition: all 0.2s; }
.member-item:hover .member-avatar { border-color: #388bfd; transform: scale(1.05); }

.lead-badge { position: absolute; top: -6px; right: -6px; background: #f2bc1b; color: #010409; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(242,188,27,0.4); }
.lead-badge svg { width: 10px; height: 10px; }

.member-meta { display: flex; flex-direction: column; align-items: center; min-width: 0; width: 100%; }
.member-meta .name { font-size: 11px; font-weight: 700; color: #c9d1d9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; opacity: 0.8; }
.btn-remove { font-size: 9px; font-weight: 800; color: #484f58; background: transparent; border: none; text-transform: uppercase; cursor: pointer; visibility: hidden; opacity: 0; transition: all 0.2s; }
.member-item:hover .btn-remove { visibility: visible; opacity: 1; }
.btn-remove:hover { color: #f85149; }

/* RIGHT PANEL: POOL */
.pool-panel { position: sticky; top: 0; }
.pool-card { background: rgba(13,17,23,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 24px; backdrop-filter: blur(10px); }
.pool-header .title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.pool-title { font-size: 15px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; }
.pool-count { background: rgba(56,139,253,0.1); color: #388bfd; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(56,139,253,0.2); }
.pool-desc { font-size: 12px; color: #484f58; }

.pool-scroll { display: flex; flex-direction: column; gap: 10px; max-height: calc(100vh - 280px); overflow-y: auto; padding-right: 6px; }
.pool-scroll::-webkit-scrollbar { width: 3px; }
.pool-scroll::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.5); border-radius: 10px; }

.pool-item { 
  display: flex; align-items: center; gap: 12px; padding: 10px; background: rgba(22,27,34,0.6); 
  border: 1px solid rgba(48,54,61,0.4); border-radius: 12px; transition: all 0.2s; 
  cursor: grab; position: relative;
}
.pool-item:hover { border-color: rgba(56,139,253,0.3); background: rgba(56,139,253,0.03); transform: translateX(3px); }
.pool-item.is-dragging { opacity: 0.5; border-style: dashed; }

.pi-avatar-wrap { position: relative; flex-shrink: 0; }
.pi-avatar { width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(48,54,61,0.6); }
.pi-drag-handle { position: absolute; -top: 6px; -left: 6px; background: #010409; color: #484f58; width: 16px; height: 16px; border-radius: 4px; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
.pool-item:hover .pi-drag-handle { opacity: 1; }
.pi-drag-handle svg { width: 10px; height: 10px; }

.pi-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.pi-name { font-size: 13px; font-weight: 700; color: #f0f6fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pi-points { font-size: 10px; color: #3fb950; font-weight: 700; font-family: 'JetBrains Mono', monospace; }

.pi-quick-actions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
.qa-btn { background: rgba(13,17,23,0.8); border: 1px solid rgba(48,54,61,0.5); color: #8b949e; padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: all 0.15s; }
.qa-btn:hover { color: #fff; border-color: #388bfd; background: #388bfd; }

.pool-empty, .loading-state, .empty-state { padding: 40px; text-align: center; color: #484f58; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.pool-empty svg, .empty-state svg { width: 32px; height: 32px; opacity: 0.2; }
.pool-empty p, .empty-state p { font-size: 13px; }

.loader { width: 22px; height: 22px; border: 2px solid rgba(56,139,253,0.2); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Animate-in */
.animate-in { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>

