<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in">
        <!-- Minimal Header -->
        <header class="header">
          <div>
            <h1 class="title">Absences & Retards</h1>
            <p class="subtitle">Suivi de votre assiduité en formation</p>
          </div>
          <div class="header-stats">
            <div class="simple-stat">
              <span class="stat-val">{{ attendanceRate }}%</span>
              <span class="stat-lbl">Présence</span>
            </div>
            <div class="divider"></div>
            <div class="simple-stat">
              <span class="stat-val">{{ totalAbsenceHours }}h</span>
              <span class="stat-lbl">Absence</span>
            </div>
          </div>
        </header>

        <div class="main-grid">
          <!-- Simple History List -->
          <section class="history-card">
            <h2 class="card-title">Historique</h2>
            <div class="minimal-list">
              <div v-for="abs in absences" :key="abs.id" class="minimal-item">
                <div class="item-date">
                  <span class="day">{{ abs.day }}</span>
                  <span class="month">{{ abs.month }}</span>
                </div>
                <div class="item-info">
                  <div class="item-top">
                    <span class="item-reason">{{ abs.reason }}</span>
                    <span class="item-status" :class="abs.status">
                      {{ abs.status === 'justified' ? 'Justifiée' : abs.status === 'rejected' ? 'Rejetée' : 'En attente' }}
                    </span>
                  </div>
                  <div class="item-meta">
                    <span>{{ abs.type }}</span>
                    <span>•</span>
                    <span>{{ abs.duration }}h</span>
                  </div>
                </div>
              </div>

              <div v-if="absences.length === 0" class="empty">
                Aucun enregistrement pour le moment.
              </div>
            </div>
          </section>

          <!-- Simple Justification Form -->
          <section class="form-card">
            <h2 class="card-title">Justifier une absence</h2>
            
            <div class="form-body">
              <div class="field">
                <label>Sélectionner l'événement</label>
                <select v-model="justifyForm.absenceId">
                  <option value="" disabled selected>Choisir...</option>
                  <option v-for="abs in pendingAbsences" :key="abs.id" :value="abs.id">
                    {{ abs.day }} {{ abs.month }} — {{ abs.reason }}
                  </option>
                </select>
              </div>

              <div class="field">
                <label>Fichier (PDF ou Image)</label>
                <div class="upload-box" @click="triggerFileInput">
                  <input ref="fileInput" type="file" hidden @change="handleFileChange" accept=".pdf,image/*">
                  <span v-if="!justifyForm.file">Cliquez pour ajouter un fichier</span>
                  <span v-else class="file-name">{{ justifyForm.file.name }}</span>
                </div>
              </div>

              <div class="field">
                <label>Note additionnelle</label>
                <textarea v-model="justifyForm.note" placeholder="Optionnel..." rows="2"></textarea>
              </div>

              <button 
                class="btn-send" 
                :disabled="!justifyForm.absenceId || !justifyForm.file || isSubmitting"
                @click="submitJustification"
              >
                {{ isSubmitting ? 'Envoi...' : 'Envoyer la justification' }}
              </button>

              <div v-if="submitSuccess" class="success-msg">Justificatif envoyé.</div>
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

const attendanceRate = computed(() => {
  const totalHours = 1400;
  if (totalAbsenceHours.value === 0) return 100;
  return Math.max(0, Math.round((100 - (totalAbsenceHours.value / totalHours * 100)) * 10) / 10);
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
        month: d.toLocaleString('fr-FR', { month: 'short' }),
        reason: a.reason || 'Cours',
        type: a.duration >= 240 ? 'Absence' : 'Retard',
        duration: Math.round(a.duration / 60),
        status: a.status
      };
    });
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const triggerFileInput = () => fileInput.value?.click();
const handleFileChange = (e) => {
  const file = e.target.files?.[0];
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
    console.error(err);
  } finally {
    isSubmitting.value = false;
  }
};

const handleLogout = () => {
  localStorage.clear();
  router.push('/login');
};

onMounted(fetchAbsences);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #0d1117; color: #c9d1d9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; padding: 40px; }
.content { max-width: 1000px; margin: 0 auto; }

.header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px; border-bottom: 1px solid #30363d; padding-bottom: 24px; }
.title { font-size: 28px; font-weight: 600; color: #f0f6fc; }
.subtitle { color: #8b949e; font-size: 14px; margin-top: 4px; }

.header-stats { display: flex; align-items: center; gap: 24px; }
.simple-stat { display: flex; flex-direction: column; text-align: right; }
.stat-val { font-size: 20px; font-weight: 600; color: #58a6ff; }
.stat-lbl { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; }
.divider { width: 1px; height: 32px; background: #30363d; }

.main-grid { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }

.card-title { font-size: 14px; font-weight: 600; color: #8b949e; text-transform: uppercase; margin-bottom: 20px; }

/* List */
.minimal-list { display: flex; flex-direction: column; gap: 12px; }
.minimal-item { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 16px; display: flex; gap: 16px; align-items: center; }
.item-date { width: 44px; text-align: center; border-right: 1px solid #30363d; padding-right: 16px; }
.item-date .day { display: block; font-size: 18px; font-weight: 600; }
.item-date .month { font-size: 11px; color: #8b949e; text-transform: uppercase; }

.item-info { flex: 1; }
.item-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.item-reason { font-weight: 600; color: #f0f6fc; }
.item-status { font-size: 11px; font-weight: 600; text-transform: uppercase; }
.item-status.justified { color: #3fb950; }
.item-status.pending { color: #d29922; }
.item-status.rejected { color: #f85149; }
.item-meta { font-size: 12px; color: #8b949e; display: flex; gap: 8px; }

/* Form */
.form-body { display: flex; flex-direction: column; gap: 20px; background: #161b22; padding: 24px; border-radius: 12px; border: 1px solid #30363d; }
.field { display: flex; flex-direction: column; gap: 8px; }
.field label { font-size: 12px; font-weight: 600; color: #8b949e; }

select, textarea { background: #0d1117; border: 1px solid #30363d; border-radius: 6px; padding: 10px; color: #c9d1d9; font-family: inherit; font-size: 14px; }
select:focus, textarea:focus { outline: none; border-color: #58a6ff; }

.upload-box { border: 1px dashed #30363d; border-radius: 6px; padding: 20px; text-align: center; cursor: pointer; color: #8b949e; font-size: 13px; transition: all 0.2s; }
.upload-box:hover { border-color: #58a6ff; color: #c9d1d9; }
.file-name { color: #3fb950; font-weight: 600; }

.btn-send { background: #238636; color: #fff; border: none; border-radius: 6px; padding: 12px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
.btn-send:hover:not(:disabled) { opacity: 0.9; }
.btn-send:disabled { opacity: 0.5; cursor: not-allowed; }

.success-msg { font-size: 13px; color: #3fb950; text-align: center; font-weight: 600; }
.empty { text-align: center; padding: 40px; color: #484f58; font-style: italic; }

.animate-in { animation: fadeIn 0.4s ease-out both; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 800px) {
  .main-grid { grid-template-columns: 1fr; }
  .header { flex-direction: column; align-items: flex-start; gap: 20px; }
  .header-stats { width: 100%; justify-content: space-between; }
}
</style>
