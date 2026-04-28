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
        <!-- Dashboard Stats -->
        <div class="stats-row animate-in">
          <div class="stat-premium-card stat-attendance">
            <div class="stat-content">
              <span class="stat-label">Taux d'assiduité</span>
              <div class="stat-value-group">
                <span class="stat-value">{{ attendanceRate }}%</span>
                <div class="stat-trend" :class="attendanceRate >= 90 ? 'up' : 'down'">
                  <svg v-if="attendanceRate >= 90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 15l-6-6-6 6"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>
                </div>
              </div>
              <p class="stat-info">Objectif : 95% minimum</p>
            </div>
            <div class="stat-visual">
              <svg class="progress-ring" width="80" height="80">
                <circle class="ring-bg" cx="40" cy="40" r="34" />
                <circle class="ring-fill" cx="40" cy="40" r="34" :style="{ strokeDashoffset: ringOffset }" />
              </svg>
            </div>
          </div>

          <div class="stat-premium-card stat-absences">
            <div class="stat-content">
              <span class="stat-label">Heures d'absence</span>
              <div class="stat-value-group">
                <span class="stat-value">{{ totalAbsenceHours }}h</span>
              </div>
              <p class="stat-info">{{ absences.length }} événement(s) enregistré(s)</p>
            </div>
            <div class="stat-icon-bg">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>

          <div class="stat-premium-card stat-warnings" :class="{ 'warning-active': warnings > 0 }">
            <div class="stat-content">
              <span class="stat-label">Avertissements</span>
              <div class="stat-value-group">
                <span class="stat-value">{{ warnings }}</span>
              </div>
              <p class="stat-info">{{ warnings === 0 ? 'Dossier académique impeccable' : 'Action requise immédiatement' }}</p>
            </div>
            <div class="stat-icon-bg">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
          </div>
        </div>

        <div class="grid-layout">
          <!-- History Section -->
          <section class="section history-section animate-in" style="animation-delay: 0.2s">
            <div class="section-head">
              <h2 class="section-title">Historique des présences</h2>
            </div>
            
            <div class="absence-list">
              <div v-for="abs in absences" :key="abs.id" class="absence-item" :class="abs.status">
                <div class="abs-date">
                  <span class="day">{{ abs.day }}</span>
                  <span class="month">{{ abs.month }}</span>
                </div>
                <div class="abs-main">
                  <div class="abs-info">
                    <h3 class="abs-reason">{{ abs.reason }}</h3>
                    <div class="abs-meta">
                      <span class="meta-tag type">{{ abs.type }}</span>
                      <span class="meta-tag duration">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        {{ abs.duration }}h
                      </span>
                    </div>
                  </div>
                  <div class="abs-status">
                    <span class="status-badge" :class="abs.status">
                      {{ abs.status === 'justified' ? 'Justifiée' : abs.status === 'rejected' ? 'Rejetée' : 'En attente' }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-if="absences.length === 0" class="empty-state">
                <div class="empty-icon">✨</div>
                <h3>Tout est en ordre</h3>
                <p>Aucune absence ou retard n'a été enregistré sur votre compte.</p>
              </div>
            </div>
          </section>

          <!-- Justification Section -->
          <section class="section justify-section animate-in" style="animation-delay: 0.3s">
            <div class="section-head">
              <h2 class="section-title">Régularisation</h2>
            </div>

            <div class="glass-card form-card">
              <div class="form-group">
                <label>Événement à justifier</label>
                <div class="select-modern">
                  <select v-model="justifyForm.absenceId">
                    <option value="" disabled selected>Choisir une absence...</option>
                    <option v-for="abs in pendingAbsences" :key="abs.id" :value="abs.id">
                      {{ abs.day }} {{ abs.month }} — {{ abs.reason }} ({{ abs.duration }}h)
                    </option>
                  </select>
                  <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </div>
              </div>

              <div class="form-group">
                <label>Justificatif (PDF, Image)</label>
                <div 
                  class="upload-zone" 
                  :class="{ 'has-file': justifyForm.file }"
                  @click="triggerFileInput"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                >
                  <input ref="fileInput" type="file" hidden @change="handleFileChange" accept=".pdf,image/*">
                  <template v-if="!justifyForm.file">
                    <div class="upload-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <p>Cliquez ou déposez votre fichier</p>
                    <span>PDF ou Image (Max 10Mo)</span>
                  </template>
                  <template v-else>
                    <div class="file-preview">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                      <div class="file-info">
                        <span class="name">{{ justifyForm.file.name }}</span>
                        <span class="size">{{ (justifyForm.file.size / 1024 / 1024).toFixed(2) }} MB</span>
                      </div>
                      <button class="btn-remove" @click.stop="justifyForm.file = null">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                  </template>
                </div>
              </div>

              <div class="form-group">
                <label>Commentaire (Optionnel)</label>
                <textarea v-model="justifyForm.note" placeholder="Détails additionnels..." rows="3"></textarea>
              </div>

              <button 
                class="btn-submit" 
                :disabled="!justifyForm.absenceId || !justifyForm.file || isSubmitting"
                @click="submitJustification"
              >
                <span v-if="!isSubmitting">Soumettre le justificatif</span>
                <span v-else class="loader"></span>
              </button>

              <Transition name="fade">
                <div v-if="submitSuccess" class="success-alert">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                  Justificatif envoyé avec succès !
                </div>
              </Transition>
            </div>
          </section>
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

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Étudiant' });
const absences = ref([]);
const loading = ref(true);
const isSubmitting = ref(false);
const submitSuccess = ref(false);
const fileInput = ref(null);

const justifyForm = ref({
  absenceId: '',
  file: null,
  note: ''
});

const pendingAbsences = computed(() => 
  absences.value.filter(a => a.status === 'pending')
);

const totalAbsenceHours = computed(() => 
  absences.value.reduce((acc, a) => acc + a.duration, 0)
);

const warnings = computed(() => 
  absences.value.filter(a => a.status === 'rejected').length
);

const attendanceRate = computed(() => {
  const totalHours = 1400; // Total annual hours
  if (totalAbsenceHours.value === 0) return 100;
  const rate = 100 - ((totalAbsenceHours.value / totalHours) * 100);
  return Math.max(0, Math.round(rate * 10) / 10);
});

const ringOffset = computed(() => {
  const circumference = 2 * Math.PI * 34;
  return circumference - (attendanceRate.value / 100) * circumference;
});

const fetchAbsences = async () => {
  try {
    const res = await api.get('/absences/my');
    const data = res.data.absences || [];
    absences.value = data.map(a => {
      const d = new Date(a.date);
      return {
        id: a.id,
        day: d.getDate().toString().padStart(2, '0'),
        month: d.toLocaleString('fr-FR', { month: 'short' }).replace('.', ''),
        reason: a.reason || 'Non spécifié',
        type: a.duration >= 240 ? 'Absence' : 'Retard',
        duration: Math.round(a.duration / 60),
        status: a.status
      };
    });
  } catch (err) {
    console.error("Fetch error:", err);
  } finally {
    loading.value = false;
  }
};

const triggerFileInput = () => fileInput.value?.click();
const handleFileChange = (e) => {
  const file = e.target.files?.[0];
  if (file) justifyForm.value.file = file;
};
const handleDrop = (e) => {
  const file = e.dataTransfer.files?.[0];
  if (file) justifyForm.value.file = file;
};

const submitJustification = async () => {
  isSubmitting.value = true;
  const formData = new FormData();
  formData.append('justification_file', justifyForm.value.file);
  if (justifyForm.value.note) formData.append('note', justifyForm.value.note);

  try {
    await api.post(`/absences/${justifyForm.value.absenceId}/justify`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    submitSuccess.value = true;
    justifyForm.value = { absenceId: '', file: null, note: '' };
    setTimeout(() => submitSuccess.value = false, 3000);
    fetchAbsences();
  } catch (err) {
    console.error("Submit error:", err);
  } finally {
    isSubmitting.value = false;
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(fetchAbsences);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; font-family: 'Inter', sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
.topbar { padding: 30px 40px; background: rgba(1, 4, 9, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.05); }
.topbar-left { display: flex; align-items: center; gap: 20px; }
.topbar-icon { width: 48px; height: 48px; background: rgba(56,139,253,0.1); border-radius: 14px; border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #58a6ff; }
.topbar-title { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; }
.topbar-sub { font-size: 12px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 4px; }

.content { padding: 40px; max-width: 1300px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 40px; }

/* Stats */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
.stat-premium-card { background: rgba(22, 27, 34, 0.7); border: 1px solid rgba(48, 54, 61, 0.8); border-radius: 24px; padding: 28px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease; position: relative; overflow: hidden; }
.stat-premium-card:hover { transform: translateY(-5px); border-color: rgba(56, 139, 253, 0.4); box-shadow: 0 10px 30px rgba(0,0,0,0.4); }

.stat-label { font-size: 13px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value-group { display: flex; align-items: center; gap: 12px; margin: 8px 0; }
.stat-value { font-size: 36px; font-weight: 900; color: #fff; letter-spacing: -0.04em; }
.stat-info { font-size: 13px; color: #484f58; font-weight: 500; }

.stat-trend { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.stat-trend.up { background: rgba(63, 185, 80, 0.1); color: #3fb950; }
.stat-trend.down { background: rgba(248, 81, 73, 0.1); color: #f85149; }
.stat-trend svg { width: 14px; height: 14px; }

.stat-visual { position: relative; width: 80px; height: 80px; }
.progress-ring { transform: rotate(-90deg); }
.ring-bg { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 6; }
.ring-fill { fill: none; stroke: #58a6ff; stroke-width: 6; stroke-linecap: round; transition: stroke-dashoffset 1s ease-out; stroke-dasharray: 213.6; }

.stat-icon-bg { font-size: 48px; color: rgba(255,255,255,0.03); position: absolute; right: -10px; bottom: -10px; transform: rotate(-15deg); pointer-events: none; }
.stat-icon-bg svg { width: 80px; height: 80px; }

.stat-warnings.warning-active { border-color: rgba(210, 153, 34, 0.4); background: rgba(210, 153, 34, 0.05); }
.stat-warnings.warning-active .stat-value { color: #d29922; }

/* Grid Layout */
.grid-layout { display: grid; grid-template-columns: 1fr 420px; gap: 40px; }

.section-head { margin-bottom: 24px; }
.section-title { font-size: 20px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 12px; }

/* Absence List */
.absence-list { display: flex; flex-direction: column; gap: 16px; }
.absence-item { background: rgba(22, 27, 34, 0.5); border: 1px solid rgba(48, 54, 61, 0.5); border-radius: 18px; padding: 16px 20px; display: flex; align-items: center; gap: 24px; transition: all 0.2s ease; }
.absence-item:hover { background: rgba(22, 27, 34, 0.8); border-color: rgba(48, 54, 61, 1); transform: scale(1.01); }

.abs-date { width: 54px; height: 54px; background: rgba(255,255,255,0.03); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.05); }
.abs-date .day { font-size: 18px; font-weight: 800; line-height: 1; }
.abs-date .month { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #8b949e; margin-top: 2px; }

.abs-main { flex: 1; display: flex; align-items: center; justify-content: space-between; }
.abs-reason { font-size: 15px; font-weight: 600; color: #e6edf3; margin-bottom: 6px; }
.abs-meta { display: flex; align-items: center; gap: 12px; }
.meta-tag { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px; }
.meta-tag.type { background: rgba(56, 139, 253, 0.1); color: #58a6ff; }
.meta-tag.duration { background: rgba(139, 148, 158, 0.1); color: #8b949e; }
.meta-tag svg { width: 12px; height: 12px; }

.status-badge { font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; }
.status-badge.justified { background: rgba(63, 185, 80, 0.1); color: #3fb950; }
.status-badge.pending { background: rgba(210, 153, 34, 0.1); color: #d29922; }
.status-badge.rejected { background: rgba(248, 81, 73, 0.1); color: #f85149; }

/* Justification Card */
.glass-card { background: rgba(22, 27, 34, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(48, 54, 61, 1); border-radius: 24px; padding: 32px; display: flex; flex-direction: column; gap: 24px; }

.form-group label { display: block; font-size: 12px; font-weight: 700; color: #8b949e; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em; }

.select-modern { position: relative; }
.select-modern select { width: 100%; background: #0d1117; border: 1px solid #30363d; border-radius: 12px; padding: 14px 16px; color: #fff; appearance: none; font-family: inherit; font-size: 14px; cursor: pointer; outline: none; }
.select-modern .chevron { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #8b949e; pointer-events: none; }

.upload-zone { border: 2px dashed #30363d; border-radius: 16px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.2s ease; background: rgba(13, 17, 23, 0.4); }
.upload-zone:hover { border-color: #58a6ff; background: rgba(56, 139, 253, 0.05); }
.upload-zone.has-file { border-style: solid; border-color: #3fb950; background: rgba(63, 185, 80, 0.05); }

.upload-icon { width: 44px; height: 44px; background: rgba(255,255,255,0.03); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #8b949e; margin: 0 auto 12px; }
.upload-zone p { font-size: 14px; font-weight: 600; color: #c9d1d9; margin-bottom: 4px; }
.upload-zone span { font-size: 11px; color: #484f58; }

.file-preview { display: flex; align-items: center; gap: 16px; text-align: left; }
.file-preview svg { width: 32px; height: 32px; color: #3fb950; flex-shrink: 0; }
.file-info { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.file-info .name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-info .size { font-size: 11px; color: #8b949e; }
.btn-remove { background: none; border: none; color: #f85149; cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.2s; }
.btn-remove:hover { background: rgba(248, 81, 73, 0.1); }

textarea { width: 100%; background: #0d1117; border: 1px solid #30363d; border-radius: 12px; padding: 14px 16px; color: #fff; font-family: inherit; font-size: 14px; outline: none; resize: none; }
textarea:focus { border-color: #58a6ff; }

.btn-submit { background: #238636; color: #fff; border: none; border-radius: 14px; padding: 16px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(35, 134, 54, 0.3); }
.btn-submit:hover:not(:disabled) { background: #2ea043; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

.success-alert { display: flex; align-items: center; gap: 10px; color: #3fb950; font-size: 13px; font-weight: 700; background: rgba(63, 185, 80, 0.1); padding: 12px; border-radius: 10px; justify-content: center; margin-top: 8px; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 48px; margin-bottom: 20px; }
.empty-state h3 { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px; }
.empty-state p { font-size: 14px; color: #8b949e; }

.loader { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

@media (max-width: 1200px) {
  .grid-layout { grid-template-columns: 1fr; }
  .justify-section { order: -1; }
}
@media (max-width: 600px) {
  .content { padding: 20px; }
  .topbar { padding: 20px; }
  .abs-main { flex-direction: column; align-items: flex-start; gap: 12px; }
  .abs-status { width: 100%; }
  .status-badge { display: block; text-align: center; }
}
</style>
