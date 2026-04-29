<template>
  <div class="layout">
    <SidebarAdmin :user="currentUser" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Contrôle des Absences</h1>
            <p class="topbar-sub">Supervision de l'assiduité globale</p>
          </div>
          <div class="topbar-right">
             <div class="stat-bubble">
               <span class="bubble-v">{{ totalPending }}</span>
               <span class="bubble-l">Justifications à traiter</span>
             </div>
          </div>
        </header>

        
        <div class="panel animate-in" style="animation-delay: 0.1s">
          <div class="table-container">
            <table class="nadi-table">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Date & Heure</th>
                  <th>Type / Raison</th>
                  <th>Justification</th>
                  <th>Status</th>
                  <th class="actions-col">Validation</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="abs in absences" :key="abs.id">
                  <td>
                    <div class="user-cell">
                      <img :src="getAvatar(abs.student?.first_name)" class="u-avatar" />
                      <div class="u-info">
                        <span class="u-name">{{ abs.student?.first_name }} {{ abs.student?.last_name }}</span>
                        <span class="u-meta">{{ abs.student?.classroom?.name || 'Inconnu' }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="date-cell">
                      <span class="date-v">{{ formatDate(abs.date) }}</span>
                      <span class="time-v">{{ formatTime(abs.date) }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="reason-val">{{ abs.reason || 'Non spécifiée' }}</span>
                  </td>
                  <td>
                    <div v-if="abs.justification_file" class="justif-link" @click="viewJustification(abs)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                      <span>Voir le document</span>
                    </div>
                    <span v-else class="text-muted">Aucun doc</span>
                  </td>
                  <td>
                    <span class="status-badge" :class="abs.status">
                      {{ 
                        abs.status === 'pending' ? 'À valider' : 
                        abs.status === 'justified' ? 'Justifié' : 
                        abs.status === 'rejected' ? 'Rejeté' : 'Non justifié'
                      }}
                    </span>
                  </td>
                   <td class="actions-col">
                    <div v-if="(abs.status === 'pending' || abs.status === 'unjustified') && abs.justification_file" class="validation-btns">
                      <button class="btn-approve" @click="updateStatus(abs.id, 'approve')" title="Approuver">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                      <button class="btn-reject" @click="updateStatus(abs.id, 'reject')" title="Rejeter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      </button>
                    </div>
                    <span v-else-if="abs.status === 'justified'" class="status-final approved">Accepté</span>
                    <span v-else-if="abs.status === 'rejected'" class="status-final rejected">Refusé</span>
                    <span v-else class="status-final">—</span>
                  </td>
                </tr>
                <tr v-if="!isLoading && !absences.length">
                  <td colspan="6" class="empty-state">Tout est en ordre ! Aucune absence à traiter.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

    
    <Transition name="fade">
      <div v-if="selectedJustif" class="modal-overlay" @click="selectedJustif = null">
        <div class="modal-content preview" @click.stop>
          <div class="preview-header">
            <h3>Justificatif: {{ selectedJustif.student?.first_name }}</h3>
            <button @click="selectedJustif = null">×</button>
          </div>
          <div class="preview-body">
             <template v-if="selectedJustif.justification_file?.toLowerCase().endsWith('.pdf')">
               <embed :src="selectedJustif.justification_file" type="application/pdf" width="100%" height="600px" />
             </template>
             <template v-else>
               <img :src="selectedJustif.justification_file" alt="justification" />
             </template>
          </div>
          <div class="preview-footer">
            <a :href="selectedJustif.justification_file" download class="btn-download">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Télécharger le document
            </a>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
import api from '../../services/api';

const router = useRouter();
const currentUser = ref(JSON.parse(localStorage.getItem('user')) || {});
const absences = ref([]); 
const isLoading = ref(true); 
const selectedJustif = ref(null); 

const totalPending = computed(() => {
  return absences.value.filter(a => a.status === 'pending' && a.justification_file).length;
});

const fetchAbsences = async () => {

  const cached = localStorage.getItem('admin_absences_cache');
  if (cached) {
    absences.value = JSON.parse(cached);
    isLoading.value = false;
  } else {
    isLoading.value = true;
  }

  try {
    const res = await api.get('/absences');
    absences.value = res.data.absences?.data || res.data.absences || res.data;
    localStorage.setItem('admin_absences_cache', JSON.stringify(absences.value));
  } catch (err) {
    console.error("Erreur Absences:", err);
  }
  
  isLoading.value = false;
};

const updateStatus = async (id, action) => {
  await api.patch(`/absences/${id}/${action}`);
  fetchAbsences();
};
const viewJustification = (abs) => {
  selectedJustif.value = abs;
};

const getAvatar = (name) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=21262d&color=a371f7&bold=true`;
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
const formatTime = (dateStr) => new Date(dateStr).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};


onMounted(fetchAbsences);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 40px; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: center; }
.topbar-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }

.stat-bubble { display: flex; flex-direction: column; align-items: flex-end; }
.bubble-v { font-size: 24px; font-weight: 800; color: #a371f7; line-height: 1; }
.bubble-l { font-size: 10px; color: #8b949e; font-weight: 700; text-transform: uppercase; margin-top: 4px; }

/* Panel Table */
.panel { background: #0d1117; border: 1px solid #21262d; border-radius: 20px; overflow: hidden; }
.table-container { width: 100%; overflow-x: auto; }
.nadi-table { width: 100%; border-collapse: collapse; text-align: left; }
.nadi-table th { background: rgba(22, 27, 34, 0.5); padding: 16px 24px; font-size: 11px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #21262d; }
.nadi-table td { padding: 16px 24px; border-bottom: 1px solid rgba(48, 54, 61, 0.4); }

.user-cell { display: flex; align-items: center; gap: 12px; }
.u-avatar { width: 32px; height: 32px; border-radius: 8px; }
.u-name { display: block; font-weight: 700; color: #f0f6fc; }
.u-meta { display: block; font-size: 10px; color: #484f58; }

.date-cell { display: flex; flex-direction: column; }
.date-v { font-size: 13px; font-weight: 600; color: #e6edf3; }
.time-v { font-size: 11px; color: #8b949e; }

.justif-link { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #a371f7; font-weight: 700; cursor: pointer; transition: opacity 0.2s; }
.justif-link:hover { opacity: 0.8; }
.justif-link svg { width: 14px; height: 14px; }

.status-badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.status-badge.pending { background: rgba(210, 153, 34, 0.1); color: #d29922; }
.status-badge.justified { background: rgba(35, 134, 54, 0.1); color: #3fb950; }
.status-badge.rejected { background: rgba(248, 81, 73, 0.1); color: #f85149; }
.status-badge.unjustified { background: rgba(48, 54, 61, 0.2); color: #8b949e; }

.status-final { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
.status-final.approved { color: #3fb950; }
.status-final.rejected { color: #f85149; }

.actions-col { text-align: right; width: 120px; }
.validation-btns { display: flex; gap: 8px; justify-content: flex-end; }
.btn-approve { width: 32px; height: 32px; border-radius: 8px; background: rgba(35, 134, 54, 0.1); border: 1px solid rgba(35, 134, 54, 0.2); color: #3fb950; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-approve:hover { background: #3fb950; color: white; }
.btn-reject { width: 32px; height: 32px; border-radius: 8px; background: rgba(248, 81, 73, 0.1); border: 1px solid rgba(248, 81, 73, 0.2); color: #f85149; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-reject:hover { background: #f85149; color: white; }
.btn-approve svg, .btn-reject svg { width: 14px; height: 14px; }

.empty-state { text-align: center; padding: 60px; color: #484f58; font-style: italic; }

/* Modal Preview */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 40px; }
.modal-content.preview { background: #0d1117; border: 1px solid #30363d; border-radius: 20px; width: 100%; max-width: 800px; display: flex; flex-direction: column; overflow: hidden; }
.preview-header { padding: 20px; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center; }
.preview-header button { background: none; border: none; font-size: 24px; color: #484f58; cursor: pointer; }
.preview-body { padding: 20px; overflow-y: auto; display: flex; justify-content: center; background: #161b22; }
.preview-body img { max-width: 100%; border-radius: 12px; }

.preview-footer { padding: 16px; border-top: 1px solid #21262d; display: flex; justify-content: center; background: #0d1117; }
.btn-download { display: flex; align-items: center; gap: 8px; background: #238636; color: white; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; transition: 0.2s; }
.btn-download:hover { background: #2ea043; transform: translateY(-2px); }
.btn-download svg { width: 16px; height: 16px; }

.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
