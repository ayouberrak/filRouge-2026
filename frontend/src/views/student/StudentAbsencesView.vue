<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Assiduité & Présence</h1>
            <p class="topbar-sub">Rapport officiel de temps de formation</p>
          </div>
        </div>
      </header>

      <!-- Content -->
      <div class="content">

        <!-- Stats -->
        <div class="stats-grid">

          <!-- Rate ring -->
          <div class="stat-card stat-card--ring animate-in">
            <div class="ring-wrap" :class="attendanceRate < 80 ? 'ring-wrap--danger' : 'ring-wrap--ok'">
              <div class="ring-glow"></div>
              <svg class="ring-svg" viewBox="0 0 100 100">
                <circle class="ring-track" cx="50" cy="50" r="44"/>
                <circle
                  class="ring-fill"
                  cx="50" cy="50" r="44"
                  stroke-dasharray="276.46"
                  :stroke-dashoffset="ringOffset"
                  stroke-linecap="round"
                />
              </svg>
              <div class="ring-value-box">
                <span class="ring-value">{{ attendanceRate }}<span class="ring-percent">%</span></span>
              </div>
            </div>
            <div class="stat-meta">
              <p class="stat-label">Taux d'assiduité</p>
              <p class="stat-desc">Votre présence globale estimée.</p>
            </div>
          </div>

          <!-- Warnings -->
          <div class="stat-card stat-card--warning animate-in" style="animation-delay: 0.2s" :class="{ 'has-warnings': warnings > 0 }">
            <div class="stat-meta">
              <p class="stat-label">Avertissements Pédagogiques</p>
              <p class="stat-desc">{{ warnings === 0 ? 'Aucune sanction enregistrée.' : 'Veuillez contacter le staff académique.' }}</p>
            </div>
            <div class="stat-number-row">
              <span class="stat-big">{{ warnings }}</span>
              <div class="warning-icon" :class="warnings === 0 ? 'warning-icon--ok' : 'warning-icon--alert'">
                <div class="icon-glow"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path v-if="warnings === 0" d="M5 13l4 4L19 7"/>
                  <path v-else d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
              </div>
            </div>
          </div>

        </div>

        <!-- Two-column layout -->
        <div class="two-col">

          <!-- Absence history -->
          <div class="col-left animate-in" style="animation-delay: 0.3s">
            <div class="section-header">
              <h2 class="section-title">Chronologie et Justificatifs</h2>
              <span class="section-count">{{ absences.length }} enregistrement{{ absences.length !== 1 ? 's' : '' }}</span>
            </div>

            <div class="card table-card">
              <table class="abs-table">
                <thead>
                  <tr>
                    <th>Date d'événement</th>
                    <th>Impact Horaire</th>
                    <th>Nature</th>
                    <th>Statut administratif</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="abs in absences" :key="abs.id" :class="{ 'tr-pending': abs.status === 'pending' }">
                    <td>
                      <div class="date-block">
                        <span class="abs-day">{{ abs.day }}</span>
                        <span class="abs-month">{{ abs.month }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="impact-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <span>{{ abs.duration }} {{ abs.duration > 1 ? 'Heures' : 'Heure' }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="type-pill">{{ abs.type }}</span>
                    </td>
                    <td>
                      <span
                        class="status-pill"
                        :class="abs.status === 'justified' ? 'status-pill--ok' : 'status-pill--pending'"
                      >
                        <span class="status-dot"></span>
                        {{ abs.status === 'justified' ? 'Validée (Justifiée)' : 'Justificatif Requis' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="absences.length === 0">
                    <td colspan="4" class="empty-row">
                      <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 13l4 4L19 7"/></svg>
                      </div>
                      Aucune absence n'a été enregistrée par le staff.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Justification form -->
          <div class="col-right animate-in" style="animation-delay: 0.4s">
            <div class="section-header">
              <h2 class="section-title">Régularisation</h2>
            </div>

            <div class="card form-card">

              <div class="form-group">
                <label class="form-label">Absence nécessitant justificatif</label>
                <div class="select-wrapper">
                  <select v-model="justifyForm.absenceId" class="nadi-select">
                    <option value="" disabled selected>Sélectionnez une absence en attente...</option>
                    <option
                      v-for="abs in pendingAbsences"
                      :key="abs.id"
                      :value="abs.id"
                    >{{ abs.day }} {{ abs.month }} — {{ abs.reason }}</option>
                  </select>
                  <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </div>
              </div>

              <div class="form-group mt-2">
                <label class="form-label">Preuve numérique (PDF, scan certifié)</label>
                <div
                  class="dropzone"
                  :class="{ 'dropzone--has-file': justifyForm.file }"
                  @click="triggerFileInput"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                >
                  <input
                    ref="fileInput"
                    type="file"
                    accept=".pdf,image/*"
                    class="file-input"
                    @change="handleFileChange"
                  />
                  <div v-if="!justifyForm.file" class="dropzone-idle">
                    <div class="dropzone-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                      </svg>
                    </div>
                    <p class="dropzone-label">Déposez votre document ici</p>
                    <p class="dropzone-hint">Formats autorisés : PDF, JPG, PNG (10 Mo max)</p>
                  </div>
                  <div v-else class="dropzone-file">
                    <div class="file-icon-box">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </div>
                    <span class="file-name">{{ justifyForm.file.name }}</span>
                    <button class="remove-file" @click.stop="justifyForm.file = null">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M18 6L6 18M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-group mt-2">
                <label class="form-label">Message au coordinateur pédagogique</label>
                <textarea
                  v-model="justifyForm.note"
                  class="nadi-textarea"
                  rows="3"
                  placeholder="Écrivez ici pour apporter du contexte additionnel à votre justificatif..."
                ></textarea>
              </div>

              <button
                class="btn-nadi-primary submit-btn-long mt-3"
                :disabled="!justifyForm.absenceId || !justifyForm.file || isSubmitting"
                @click="submitJustification"
              >
                <div v-if="isSubmitting" class="spinner-sm"></div>
                <span>{{ isSubmitting ? 'Téléversement sécurisé...' : 'Transmettre au staff académique' }}</span>
                <svg v-if="!isSubmitting" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>

              <!-- Success Toast -->
              <Transition name="toast">
                <div v-if="submitSuccess" class="success-toast">
                  <div class="toast-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                  </div>
                  <span>Le justificatif a bien été archivé et transmis.</span>
                </div>
              </Transition>

            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

// ─── State ────────────────────────────────────────────────────────────────────
const router      = useRouter();
const user        = ref(null);
const isSubmitting = ref(false);
const submitSuccess = ref(false);
const fileInput   = ref(null);
const isLoading   = ref(true);

const justifyForm = ref({
  absenceId: '',
  file:      null,
  note:      '',
});

const absences = ref([]);

// ─── File Handling ────────────────────────────────────────────────────────────
const triggerFileInput = () => fileInput.value?.click();

const handleFileChange = (e) => {
  const file = e.target.files?.[0];
  if (file) justifyForm.value.file = file;
};

const handleDrop = (e) => {
  const file = e.dataTransfer.files?.[0];
  if (file) justifyForm.value.file = file;
};

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatAbsence = (abs) => {
  const dateObj = new Date(abs.date);
  const day = dateObj.toLocaleDateString('fr-FR', { day: '2-digit' });
  const month = dateObj.toLocaleDateString('fr-FR', { month: 'short' });

  // Si la durée est stockée en minutes (ex: 480), on la convertit en heures
  const hours = abs.duration >= 30 ? Math.round(abs.duration / 60) : abs.duration;

  return {
    id: abs.id,
    day: day,
    month: month,
    reason: 'Non spécifié',
    type: hours < 4 ? 'Retard' : 'Absence',
    status: abs.status,
    duration: hours,
    justification_file: abs.justification_file,
  };
};

const fetchAbsences = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/absences/my');
    const data = res.data.absences || [];
    absences.value = data.map(formatAbsence);
  } catch (err) {
    console.error('Error fetching absences:', err);
  } finally {
    isLoading.value = false;
  }
};

// ─── Computed Stats ───────────────────────────────────────────────────────────
const pendingAbsences = computed(() =>
  absences.value.filter(a => a.status === 'pending' && !a.justification_file)
);

const totalAbsenceHours = computed(() => {
  return absences.value.reduce((total, abs) => total + abs.duration, 0);
});

const warnings = computed(() => {
  return absences.value.filter(a => a.status === 'rejected').length;
});

const attendanceRate = computed(() => {
  const totalTrainingHours = 1400; // Base total hours for the year
  if (totalAbsenceHours.value === 0) return 100;
  
  const rate = 100 - ((totalAbsenceHours.value / totalTrainingHours) * 100);
  return Math.max(0, Math.round(rate * 10) / 10); // 1 decimal point e.g 98.5
});

const ringOffset = computed(() => {
  const circumference = 276.46; // 2 * pi * 44
  return circumference - (circumference * attendanceRate.value) / 100;
});

// ─── Submit ───────────────────────────────────────────────────────────────────
const submitJustification = async () => {
  if (!justifyForm.value.absenceId || !justifyForm.value.file) return;

  isSubmitting.value = true;
  try {
    const formData = new FormData();
    // formData.append('absence_id', justifyForm.value.absenceId); 
    // The backend endpoint is /absences/{id}/justify
    formData.append('justification_file', justifyForm.value.file);
    if(justifyForm.value.note) {
        formData.append('note', justifyForm.value.note);
    }

    await api.post(`/absences/${justifyForm.value.absenceId}/justify`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    justifyForm.value = { absenceId: '', file: null, note: '' };
    submitSuccess.value = true;
    
    await fetchAbsences(); // Refresh the list

    setTimeout(() => { submitSuccess.value = false; }, 3500);
  } catch (err) {
    console.error('Justification submit error:', err);
    alert(err.response?.data?.message || 'Erreur lors de l\'envoi réseau. Veuillez vérifier votre document.');
  } finally {
    isSubmitting.value = false;
  }
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  fetchAbsences();
});

// ─── Logout ───────────────────────────────────────────────────────────────────
const handleLogout = async () => {
  try { await api.post('/logout'); } catch {}
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};
</script>

<style scoped>
/* ─── Reset ─────────────────────────────────────────────────────────────────── */
* { box-sizing: border-box; }

/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif;
}
.main {
  flex: 1; display: flex; flex-direction: column; overflow-y: auto;
  scrollbar-width: thin; scrollbar-color: #21262d transparent;
}
.main::-webkit-scrollbar { width: 5px; }
.main::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar {
  height: 80px; display: flex; align-items: center; justify-content: space-between;
  padding: 0 40px; background: rgba(1, 4, 9, 0.8); backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; position: sticky; top: 0; z-index: 20;
}
.topbar-left { display: flex; align-items: center; gap: 16px; }
.topbar-icon {
  width: 44px; height: 44px; border-radius: 12px; background: rgba(56,139,253,0.1);
  border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center;
  color: #79c0ff; transition: all 0.2s; box-shadow: 0 0 15px rgba(56,139,253,0.15);
}
.topbar-icon svg { width: 22px; height: 22px; }
.topbar-title { font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.01em;}
.topbar-sub   { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 2px; }

/* ─── Content ───────────────────────────────────────────────────────────────── */
.content {
  padding: 40px; max-width: 1400px; width: 100%; margin: 0 auto;
  display: flex; flex-direction: column; gap: 32px; padding-bottom: 100px;
}

/* ─── Stats grid ────────────────────────────────────────────────────────────── */
.stats-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }

.stat-card {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.06);
  border-radius: 20px; padding: 32px; display: flex; align-items: center; justify-content: space-between;
  text-align: left; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; overflow: hidden;
}
.stat-card::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.03), transparent 60%); z-index: 0; pointer-events: none;}

.stat-meta { display: flex; flex-direction: column; gap: 4px; z-index: 1;}
.stat-label { font-size: 13px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 800; }
.stat-desc { font-size: 12px; color: #484f58; font-weight: 500; }

.stat-number-row { display: flex; align-items: baseline; gap: 8px; z-index: 1;}
.stat-big  { font-size: 48px; font-weight: 800; color: #fff; letter-spacing: -0.04em; line-height: 1; }
.stat-unit { font-size: 16px; color: #8b949e; font-weight: 700; text-transform: uppercase; }

/* Ring logic */
.stat-card--ring { padding: 24px 32px; justify-content: flex-start; gap: 24px;}
.ring-wrap { position: relative; width: 100px; height: 100px; flex-shrink: 0; z-index: 1;}
.ring-glow { position: absolute; inset: 10px; background: #56d364; filter: blur(20px); opacity: 0.15; border-radius: 50%; z-index: 0;}
.ring-wrap--danger .ring-glow { background: #f85149; }
.ring-wrap--danger .ring-fill { stroke: #f85149; }
.ring-wrap--danger .ring-value { color: #ff7b72; }

.ring-svg { transform: rotate(-90deg); position: relative; z-index: 1; width: 100%; height: 100%;}
.ring-track { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 8; }
.ring-fill { fill: none; stroke: #56d364; stroke-width: 8; transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 0 4px rgba(86,211,100,0.5));}
.ring-value-box { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 1;}
.ring-value { font-size: 24px; font-weight: 800; color: #fff; display: flex; align-items: baseline; letter-spacing: -0.02em;}
.ring-percent { font-size: 12px; color: #8b949e; margin-left: 2px;}

/* Warnings */
.stat-card--warning.has-warnings { border-color: rgba(210,153,34,0.3); background: rgba(210,153,34,0.02); }
.stat-card--warning.has-warnings::before { background: radial-gradient(circle at 100% 50%, rgba(210,153,34,0.05), transparent 70%); }
.stat-card--warning.has-warnings .stat-big { color: #e3b341; }
.warning-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; position: relative;}
.icon-glow { position: absolute; inset: 0; border-radius: inherit; filter: blur(10px); opacity: 0.3; z-index: 0;}
.warning-icon--ok .icon-glow { background: #3fb950; }
.warning-icon--ok { background: rgba(63, 185, 80, 0.1); border: 1px solid rgba(63, 185, 80, 0.2); color: #56d364; }
.warning-icon--alert .icon-glow { background: #d29922; animation: pulse-gold 2s infinite; }
.warning-icon--alert { background: rgba(210, 153, 34, 0.1); border: 1px solid rgba(210, 153, 34, 0.3); color: #e3b341; }
.warning-icon svg { width: 24px; height: 24px; z-index: 1;}

/* ─── Two-column ────────────────────────────────────────────────────────────── */
.two-col { display: grid; grid-template-columns: 1fr 400px; gap: 32px; align-items: start; }
.col-left, .col-right { display: flex; flex-direction: column; gap: 20px; }

.section-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px;}
.section-title { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em;}
.section-count { font-size: 12px; font-weight: 600; color: #8b949e; background: rgba(255,255,255,0.05); padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);}

.card { background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);}

/* ─── Table ─────────────────────────────────────────────────────────────────── */
.abs-table { width: 100%; border-collapse: collapse; text-align: left; }
.abs-table thead tr { background: rgba(1, 4, 9, 0.5); border-bottom: 1px solid rgba(255,255,255,0.06); }
.abs-table th { padding: 18px 24px; font-size: 11px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.15em; }
.abs-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.03); transition: background 0.2s; }
.abs-table tbody tr:last-child { border-bottom: none; }
.abs-table tbody tr:hover { background: rgba(255,255,255,0.03); }
.tr-pending { background: rgba(210,153,34,0.02); }
.tr-pending:hover { background: rgba(210,153,34,0.04) !important; border-color: rgba(210,153,34,0.1); }
.abs-table td { padding: 20px 24px; vertical-align: middle; }

.date-block { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; width: 44px; height: 44px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; text-align: center; }
.abs-day { font-size: 15px; font-weight: 800; color: #fff; width: 100%; line-height: 1.2; font-family: 'JetBrains Mono', monospace;}
.abs-month { font-size: 10px; font-weight: 700; color: #8b949e; text-transform: uppercase; width: 100%; }

.impact-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: rgba(56,139,253,0.05); border: 1px dashed rgba(56,139,253,0.2); border-radius: 8px; font-size: 12px; font-weight: 700; color: #79c0ff; }
.impact-pill svg { width: 16px; height: 16px; opacity: 0.8; }

.type-pill { display: inline-block; padding: 4px 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; font-size: 10px; font-weight: 700; color: #c9d1d9; text-transform: uppercase; letter-spacing: 0.1em; }

.status-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; border: 1px solid transparent; }
.status-pill--ok { background: rgba(63, 185, 80, 0.1); color: #56d364; border-color: rgba(63, 185, 80, 0.2); }
.status-pill--pending { background: rgba(210, 153, 34, 0.1); color: #e3b341; border-color: rgba(210, 153, 34, 0.3); }

.status-dot { width: 6px; height: 6px; border-radius: 50%; box-shadow: 0 0 10px currentColor;}
.status-pill--ok .status-dot { background: #56d364; }
.status-pill--pending .status-dot { background: #e3b341; animation: pulse-gold 1.5s infinite; }

.empty-row { text-align: center; padding: 60px; font-size: 14px; color: #8b949e; font-weight: 500;}
.empty-icon { width: 48px; height: 48px; background: rgba(255,255,255,0.03); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #484f58;}
.empty-icon svg { width: 24px; height: 24px; }

/* ─── Form ──────────────────────────────────────────────────────────────────── */
.form-card { padding: 32px; display: flex; flex-direction: column; gap: 20px; }

.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-label { font-size: 11px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; }

.select-wrapper { position: relative; }
.nadi-select {
  width: 100%; background: #010409; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
  padding: 14px 40px 14px 16px; font-size: 13px; color: #fff; appearance: none;
  cursor: pointer; transition: all 0.2s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2); outline: none;
}
.nadi-select:focus { border-color: #388bfd; box-shadow: 0 0 0 4px rgba(56, 139, 253, 0.15), inset 0 2px 4px rgba(0,0,0,0.2); }
.select-arrow { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #8b949e; pointer-events: none;}

.nadi-textarea {
  width: 100%; background: #010409; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
  padding: 16px; font-size: 13px; color: #fff; resize: vertical; outline: none; transition: all 0.2s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2); font-family: inherit; line-height: 1.6;
}
.nadi-textarea::placeholder { color: #484f58; font-weight: 500;}
.nadi-textarea:focus { border-color: #388bfd; box-shadow: 0 0 0 4px rgba(56, 139, 253, 0.15), inset 0 2px 4px rgba(0,0,0,0.2); }

/* Dropzone */
.dropzone {
  border: 1px dashed rgba(255,255,255,0.15); border-radius: 14px; padding: 28px; cursor: pointer;
  transition: all 0.2s; background: rgba(1, 4, 9, 0.5); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.dropzone:hover { border-color: #79c0ff; background: rgba(56,139,253,0.03); }
.dropzone--has-file { border-style: solid; border-color: rgba(56,139,253,0.3); background: rgba(56,139,253,0.05); }

.dropzone-idle { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.dropzone-icon { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: #8b949e; margin-bottom: 4px; transition: color 0.2s, border-color 0.2s;}
.dropzone:hover .dropzone-icon { color: #79c0ff; border-color: rgba(56,139,253,0.2); }
.dropzone-label { font-size: 13px; font-weight: 700; color: #e6edf3; }
.dropzone-hint { font-size: 11px; color: #484f58; font-weight: 500;}

.dropzone-file { display: flex; align-items: center; gap: 12px; width: 100%; padding: 0 10px; }
.file-icon-box { width: 32px; height: 32px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #79c0ff; flex-shrink: 0;}
.file-icon-box svg { width: 16px; height: 16px; stroke-width: 2.5;}
.file-name { font-size: 13px; font-weight: 600; color: #fff; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: left;}

.remove-file { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 4px; color: #8b949e; cursor: pointer; transition: all 0.2s; flex-shrink: 0;}
.remove-file:hover { background: rgba(248,81,73,0.1); border-color: rgba(248,81,73,0.3); color: #ff7b72; }
.remove-file svg { width: 14px; height: 14px; }
.file-input { display: none; }

/* Actions */
.submit-btn-long { width: 100%; padding: 16px; font-size: 14px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; }
.btn-nadi-primary { background: #238636; color: white; border: 1px solid #2ea043; font-weight: 800; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(35, 134, 54, 0.2); text-transform: uppercase; letter-spacing: 0.05em;}
.btn-nadi-primary:hover:not(:disabled) { background: #2ea043; border-color: #3fb950; box-shadow: 0 8px 20px rgba(35, 134, 54, 0.3); transform: translateY(-1px); }
.btn-nadi-primary:disabled { opacity: 0.5; background: rgba(255,255,255,0.05); border-color: transparent; color: #8b949e; cursor: not-allowed; box-shadow: none;}
.btn-nadi-primary svg { width: 18px; height: 18px; }

/* Toasts */
.success-toast { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-radius: 12px; background: rgba(63, 185, 80, 0.1); border: 1px solid rgba(63, 185, 80, 0.3); color: #56d364; font-size: 13px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-top: 10px;}
.toast-icon { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; background: #56d364; color: #010409; border-radius: 50%; flex-shrink: 0;}
.toast-icon svg { width: 16px; height: 16px; }

/* ─── Helpers ───────────────────────────────────────────────────────────────── */
.mt-2 { margin-top: 16px; }
.mt-3 { margin-top: 24px; }
.spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes spin  { to { transform: rotate(360deg); } }
@keyframes pulse-gold { 0%, 100% { box-shadow: 0 0 15px rgba(210,153,34,0.3); } 50% { box-shadow: 0 0 5px rgba(210,153,34,0.1); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-enter-from, .toast-leave-to       { opacity: 0; transform: translateY(10px) scale(0.98); }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .two-col   { grid-template-columns: 1fr; }
  .col-right { order: -1; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .stat-card--ring { grid-column: 1 / -1; }
}
@media (max-width: 720px) {
  .content      { padding: 20px; }
  .stats-grid   { grid-template-columns: 1fr; }
  .stat-card--ring { grid-column: span 1; }
  .abs-table th:nth-child(3), .abs-table td:nth-child(3) { display: none; }
}
</style>