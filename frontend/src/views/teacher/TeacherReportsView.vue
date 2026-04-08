<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Loading -->
      <div v-if="isLoading" class="loading-screen">
        <div class="spinner"></div>
        <span>Chargement des rapports...</span>
      </div>

      <!-- No Classroom -->
      <div v-else-if="!classroomId" class="empty-screen">
        <div class="empty-ico">📊</div>
        <h3>Aucune classe assignée</h3>
        <p>Vous devez être assigné à une classe active.</p>
        <button class="btn-elite-primary" @click="router.push('/teacher/dashboard')">Dashboard</button>
      </div>

      <!-- Main -->
      <div v-else class="content animate-in">

        <!-- HEADER -->
        <header class="page-header">
          <div class="header-left">
            <div class="header-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
              <h1 class="page-title">Rapports Journaliers <span class="dim">/ Pilotage</span></h1>
              <p class="page-sub">Suivi de performance pédagogique — {{ selectedClass?.name }}</p>
            </div>
          </div>
          <div class="header-right">
            <div class="stats-mini">
              <div class="stat-item">
                <span class="val">{{ stats.total_reports || 0 }}</span>
                <span class="lbl">Rapports</span>
              </div>
              <div class="stat-divider"></div>
              <div class="stat-item">
                <span class="val">{{ stats.avg_absences || 0 }}</span>
                <span class="lbl">Abs. moy</span>
              </div>
              <div class="stat-divider"></div>
              <div class="stat-item">
                <span class="val" :style="{ color: todayReport ? '#3fb950' : '#d29922' }">
                  {{ todayReport ? '✓' : '○' }}
                </span>
                <span class="lbl">Aujourd'hui</span>
              </div>
            </div>
            <button v-if="todayReport" class="btn-elite-primary" @click="generatePdf" :disabled="isGeneratingPdf">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
              {{ isGeneratingPdf ? 'Génération...' : 'Exporter PDF' }}
            </button>
          </div>
        </header>

        <!-- MAIN GRID -->
        <div class="main-grid">

          <!-- LEFT: Form Panel -->
          <div class="form-panel elite-panel">

            <!-- TODAY SUBMITTED NOTICE -->
            <div v-if="todayReport" class="submitted-notice">
              <div class="notice-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div class="notice-body">
                <strong>Rapport du {{ formatFrDate(todayReport.date) }} déjà soumis</strong>
                <span>Les champs sont verrouillés. Cliquez sur "Modifier" pour changer.</span>
              </div>
              <button class="btn-elite-ghost" @click="unlockForm">Modifier</button>
            </div>

            <!-- FORM HEADER -->
            <div class="form-section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
              {{ formIsLocked ? 'Rapport du Jour (Verrouillé)' : 'Nouveau Rapport Journalier' }}
            </div>

            <form @submit.prevent="handleSubmit" class="report-form">
              <!-- ROW 1: Date + Counters -->
              <div class="field-row">
                <div class="field-group">
                  <label class="field-label">📅 Date</label>
                  <div class="field-input" style="color:#8b949e; cursor:default;">{{ formatFrDate(today) }}</div>
                </div>
                <div class="field-group">
                  <label class="field-label">👤 <span style="color:#f85149">Absents</span></label>
                  <div class="counter" :class="{ locked: formIsLocked, 'c-red': true }">
                    <button type="button" @click="!formIsLocked && (form.absences_count = Math.max(0, form.absences_count - 1))" :disabled="formIsLocked">−</button>
                    <span>{{ form.absences_count }}</span>
                    <button type="button" @click="!formIsLocked && form.absences_count++" :disabled="formIsLocked">+</button>
                  </div>
                </div>
                <div class="field-group">
                  <label class="field-label">⏱ <span style="color:#d29922">Retards</span></label>
                  <div class="counter" :class="{ locked: formIsLocked, 'c-yellow': true }">
                    <button type="button" @click="!formIsLocked && (form.tardies_count = Math.max(0, form.tardies_count - 1))" :disabled="formIsLocked">−</button>
                    <span>{{ form.tardies_count }}</span>
                    <button type="button" @click="!formIsLocked && form.tardies_count++" :disabled="formIsLocked">+</button>
                  </div>
                </div>
              </div>

              <!-- ROW 2: Mood + Objectives -->
              <div class="field-row">
                <div class="field-group" style="flex:2">
                  <label class="field-label">Climat de Classe</label>
                  <div class="mood-row">
                    <button v-for="m in 5" :key="m" type="button"
                      :class="['mood-btn', { active: form.class_mood === m, locked: formIsLocked }]"
                      :disabled="formIsLocked"
                      @click="!formIsLocked && (form.class_mood = m)">
                      {{ moodEmoji(m) }}
                    </button>
                  </div>
                </div>
                <div class="field-group" style="flex:1">
                  <label class="field-label">Objectifs 🎯</label>
                  <div class="toggle-row">
                    <button type="button" :class="['tgl', { active: form.objectives_met, locked: formIsLocked }]" :disabled="formIsLocked" @click="!formIsLocked && (form.objectives_met = true)">✓ Oui</button>
                    <button type="button" :class="['tgl', 'tgl-no', { active: !form.objectives_met, locked: formIsLocked }]" :disabled="formIsLocked" @click="!formIsLocked && (form.objectives_met = false)">✗ Non</button>
                  </div>
                </div>
              </div>

              <!-- Brief -->
              <div class="field-group">
                <label class="field-label">📌 Brief / Activité principale</label>
                <input type="text" v-model="form.brief_status" class="field-input" :disabled="formIsLocked" placeholder="Ex: Brief Red-Line – Phase exploration" required />
              </div>

              <!-- Technique + Workshops -->
              <div class="field-row">
                <div class="field-group">
                  <label class="field-label">💡 Séance Technique</label>
                  <textarea v-model="form.technical_topics" class="field-input" rows="3" :disabled="formIsLocked" placeholder="Concepts abordés, outils..."></textarea>
                </div>
                <div class="field-group">
                  <label class="field-label">🔧 Workshops & Exercices</label>
                  <textarea v-model="form.workshops_done" class="field-input" rows="3" :disabled="formIsLocked" placeholder="Travaux pratiques, challenges..."></textarea>
                </div>
              </div>

              <!-- Notes -->
              <div class="field-group">
                <label class="field-label">📝 Observations</label>
                <textarea v-model="form.note" class="field-input" rows="3" :disabled="formIsLocked" placeholder="Points d'attention, ambiance de classe..."></textarea>
              </div>

              <div class="form-footer">
                <button v-if="formIsLocked && todayReport" type="button" class="btn-elite-ghost" @click="generatePdf">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                  Télécharger PDF
                </button>
                <button v-if="!formIsLocked" type="submit" class="btn-elite-primary" :disabled="isSubmitting">
                  <svg v-if="!isSubmitting" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:14px;height:14px"><path d="M5 13l4 4L19 7"/></svg>
                  <div v-else class="spin-sm"></div>
                  {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer le rapport' }}
                </button>
              </div>
            </form>
          </div>

          <!-- RIGHT: History -->
          <div class="history-panel elite-panel">
            <div class="history-header">
              <span class="form-section-title" style="margin:0">Historique Récent</span>
              <span class="history-count">{{ sortedReports.length }}</span>
            </div>

            <!-- DEBUG INFO (remove in prod) -->
            <div v-if="debugInfo" style="font-size:10px;color:#484f58;padding:8px 0;border-bottom:1px solid #21262d;margin-bottom:12px;">
              Reports raw count: {{ reports.length }} | Today: {{ today }}
            </div>

            <div v-if="sortedReports.length > 0" class="timeline">
              <div v-for="rep in sortedReports.slice(0, 10)" :key="rep.id"
                   class="timeline-item"
                   :class="{ 'is-today': isToday(rep.date) }"
                   @click="selectReport(rep)">
                <!-- Date pill -->
                <div class="tl-date">
                  <span class="tl-d">{{ getDay(rep.date) }}</span>
                  <span class="tl-m">{{ getMonth(rep.date) }}</span>
                </div>
                <!-- Connector -->
                <div class="tl-line">
                  <div class="tl-dot" :class="{ today: isToday(rep.date) }"></div>
                </div>
                <!-- Content -->
                <div class="tl-content">
                  <div class="tl-title">{{ rep.brief_status || 'Sans titre' }}</div>
                  <div class="tl-sub">{{ rep.note || rep.technical_topics || 'Aucune observation' }}</div>
                  <div class="tl-badges">
                    <span class="badge red" v-if="rep.absences_count > 0">{{ rep.absences_count }} abs</span>
                    <span class="badge yellow" v-if="rep.tardies_count > 0">{{ rep.tardies_count }} ret</span>
                    <span class="badge mood">{{ moodEmoji(rep.class_mood) }}</span>
                    <span class="badge" :class="rep.objectives_met ? 'green' : 'red'">{{ rep.objectives_met ? '✓ OK' : '✗' }}</span>
                    <span class="badge today-pill" v-if="isToday(rep.date)">Aujourd'hui</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="history-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <p>Aucun rapport soumis</p>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- PDF TEMPLATE (off-screen) -->
    <div id="pdf-content" style="position:fixed;top:-9999px;left:0;width:794px;background:#fff;color:#111;font-family:Arial,sans-serif;padding:40px;" v-if="pdfReport">
      <div style="display:flex;justify-content:space-between;border-bottom:3px solid #111;padding-bottom:20px;margin-bottom:28px;">
        <div>
          <h1 style="margin:0;font-size:22px;font-weight:900;">Rapport Journalier</h1>
          <p style="margin:4px 0 0;color:#555;font-size:13px;">{{ selectedClass?.name }} · {{ user.first_name }} {{ user.last_name }}</p>
        </div>
        <div style="font-size:22px;font-weight:900;">{{ formatFrDate(pdfReport.date) }}</div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;">
        <div style="background:#f4f4f4;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:26px;font-weight:900;color:#dc2626;">{{ pdfReport.absences_count }}</div>
          <div style="font-size:10px;text-transform:uppercase;color:#666;">Absences</div>
        </div>
        <div style="background:#f4f4f4;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:26px;font-weight:900;color:#d97706;">{{ pdfReport.tardies_count }}</div>
          <div style="font-size:10px;text-transform:uppercase;color:#666;">Retards</div>
        </div>
        <div style="background:#f4f4f4;border-radius:10px;padding:14px;text-align:center;">
          <div style="font-size:26px;">{{ moodEmoji(pdfReport.class_mood) }}</div>
          <div style="font-size:10px;text-transform:uppercase;color:#666;">Climat {{ pdfReport.class_mood }}/5</div>
        </div>
        <div :style="{ background: pdfReport.objectives_met ? '#dcfce7' : '#fee2e2', borderRadius:'10px', padding:'14px', textAlign:'center' }">
          <div style="font-size:26px;">{{ pdfReport.objectives_met ? '✓' : '✗' }}</div>
          <div :style="{ fontSize:'10px', textTransform:'uppercase', color: pdfReport.objectives_met ? '#16a34a' : '#dc2626' }">Objectifs</div>
        </div>
      </div>
      <div v-if="pdfReport.brief_status" style="margin-bottom:20px;">
        <div style="font-size:11px;text-transform:uppercase;color:#888;margin-bottom:6px;font-weight:700;">Brief</div>
        <p style="background:#f4f4f4;padding:12px;border-radius:8px;border-left:4px solid #111;margin:0;font-weight:700;">{{ pdfReport.brief_status }}</p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        <div v-if="pdfReport.technical_topics">
          <div style="font-size:11px;text-transform:uppercase;color:#888;margin-bottom:6px;font-weight:700;">Séance Technique</div>
          <p style="background:#f4f4f4;padding:12px;border-radius:8px;margin:0;min-height:60px;">{{ pdfReport.technical_topics }}</p>
        </div>
        <div v-if="pdfReport.workshops_done">
          <div style="font-size:11px;text-transform:uppercase;color:#888;margin-bottom:6px;font-weight:700;">Workshops</div>
          <p style="background:#f4f4f4;padding:12px;border-radius:8px;margin:0;min-height:60px;">{{ pdfReport.workshops_done }}</p>
        </div>
      </div>
      <div v-if="pdfReport.note">
        <div style="font-size:11px;text-transform:uppercase;color:#888;margin-bottom:6px;font-weight:700;">Observations</div>
        <p style="background:#f4f4f4;padding:12px;border-radius:8px;margin:0;">{{ pdfReport.note }}</p>
      </div>
      <div style="margin-top:36px;padding-top:12px;border-top:1px solid #ddd;display:flex;justify-content:space-between;color:#aaa;font-size:10px;">
        <span>Généré le {{ new Date().toLocaleDateString('fr-FR') }}</span>
        <span>YouCode Morocco</span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import DailyReportService from '../../services/DailyReportService';
import api from '../../services/api';

const router = useRouter();

const getUser = () => {
  try { return JSON.parse(localStorage.getItem('user') || '{}') || { first_name: 'Formateur' }; }
  catch { return { first_name: 'Formateur', last_name: '' }; }
};

const user = ref(getUser());
const classroomId = ref(null);
const selectedClass = ref(null);
const isLoading = ref(true);
const isSubmitting = ref(false);
const isGeneratingPdf = ref(false);
const formIsLocked = ref(false); // true when today's report exists and we haven't clicked "Modifier"
const debugInfo = ref(false); // set to true to debug
const pdfReport = ref(null);

const reports = ref([]);
const stats = ref({ avg_absences: 0, total_reports: 0, last_report: null });
const today = new Date().toISOString().split('T')[0];

const form = ref({
  date: today, absences_count: 0, tardies_count: 0,
  brief_status: '', technical_topics: '', workshops_done: '',
  class_mood: 3, objectives_met: true, note: ''
});

// ─── Computed ────────────────────────────────────────
const todayReport = computed(() =>
  reports.value.find(r => {
    const d = typeof r.date === 'string' ? r.date.substring(0, 10) : '';
    return d === today;
  }) || null
);

const sortedReports = computed(() =>
  [...reports.value].sort((a, b) => {
    const da = typeof a.date === 'string' ? a.date.substring(0, 10) : '';
    const db = typeof b.date === 'string' ? b.date.substring(0, 10) : '';
    return db.localeCompare(da);
  })
);

// ─── Lifecycle ────────────────────────────────────────
onMounted(async () => {
  isLoading.value = true;
  await fetchClassroom();
  if (classroomId.value) {
    await fetchData();
  }
  isLoading.value = false;
});

// ─── Data Fetching ────────────────────────────────────
const fetchClassroom = async () => {
  try {
    const res = await api.get('/classrooms/my');
    const classes = res.data?.data || res.data || [];
    if (classes.length > 0) {
      selectedClass.value = classes[0];
      classroomId.value = classes[0].id;
    }
  } catch (err) { 
    console.error('Classroom fetch error', err); 
  }
};

const fetchData = async () => {
  if (!classroomId.value) return;
  try {
    const [statsRes, reportsRes] = await Promise.all([
      DailyReportService.getStats(classroomId.value),
      DailyReportService.getByClassroom(classroomId.value)
    ]);
    
    stats.value = statsRes.data?.data ?? statsRes.data ?? stats.value;
    
    // Normalize reports array
    const rawData = reportsRes.data?.data ?? reportsRes.data ?? [];
    reports.value = (Array.isArray(rawData) ? rawData : []).map(r => ({
      ...r,
      date: typeof r.date === 'string' ? r.date.substring(0, 10) : r.date,
    }));

    // Detect today's report accurately
    const foundToday = reports.value.find(r => {
      const reportDate = typeof r.date === 'string' ? r.date.substring(0, 10) : '';
      return reportDate === today;
    });

    if (foundToday) {
      formIsLocked.value = true;
      populateForm(foundToday);
      pdfReport.value = foundToday;
    } else {
      // If no report today, ensure form is unlocked (unless user manually modified)
      // but only if we were previously locked
      if (!isSubmitting.value) {
        formIsLocked.value = false;
      }
    }
  } catch (err) { 
    console.error('Fetch data error', err); 
  }
};

// Populate form with existing report data
const populateForm = (rep) => {
  if (!rep) return;
  Object.assign(form.value, {
    absences_count: rep.absences_count ?? 0,
    tardies_count: rep.tardies_count ?? 0,
    brief_status: rep.brief_status ?? '',
    technical_topics: rep.technical_topics ?? '',
    workshops_done: rep.workshops_done ?? '',
    class_mood: rep.class_mood ?? 3,
    objectives_met: rep.objectives_met ? true : false,
    note: rep.note ?? ''
  });
};

// ─── Actions ─────────────────────────────────────────
const unlockForm = () => { 
  formIsLocked.value = false; 
};

const handleSubmit = async () => {
  if (!classroomId.value || isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const payload = {
      classroom_id: Number(classroomId.value),
      date: today,
      absences_count: Number(form.value.absences_count),
      tardies_count: Number(form.value.tardies_count),
      brief_status: form.value.brief_status,
      technical_topics: form.value.technical_topics || null,
      workshops_done: form.value.workshops_done || null,
      class_mood: Number(form.value.class_mood),
      objectives_met: !!form.value.objectives_met,
      note: form.value.note || null,
    };

    await DailyReportService.submitReport(payload);
    await fetchData();
    alert('✅ Rapport enregistré avec succès !');
  } catch (err) {
    console.error('Submit error:', err);
    const errors = err?.response?.data?.errors;
    if (errors && typeof errors === 'object') {
      const errorLines = Object.entries(errors).map(([k, v]) => `• ${k}: ${Array.isArray(v) ? v.join(', ') : v}`).join('\n');
      alert(`❌ Erreur de validation :\n${errorLines}`);
    } else {
      alert(`❌ ${err?.response?.data?.message || 'Erreur lors de l\'enregistrement.'}`);
    }
  } finally {
    isSubmitting.value = false;
  }
};

const selectReport = (rep) => { 
  pdfReport.value = rep; 
};

const generatePdf = async () => {
  const targetReport = pdfReport.value || todayReport.value;
  if (!targetReport) return;
  
  isGeneratingPdf.value = true;
  try {
    await new Promise(r => setTimeout(r, 200));
    const { default: html2canvas } = await import('html2canvas');
    const { default: jsPDF } = await import('jspdf');
    const el = document.getElementById('pdf-content');
    if (!el) throw new Error('Template PDF non trouvé');
    
    el.style.top = '0'; el.style.zIndex = '9999';
    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#fff' });
    el.style.top = '-9999px'; el.style.zIndex = '';
    
    const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
    const imgH = (canvas.height * 210) / canvas.width;
    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, 210, imgH);
    pdf.save(`rapport-${targetReport.date}-${selectedClass.value?.name || 'classe'}.pdf`);
  } catch (err) {
    console.error('PDF error', err);
    alert('Erreur lors de la génération du PDF.');
  } finally { 
    isGeneratingPdf.value = false; 
  }
};

const handleLogout = () => {
  localStorage.clear();
  router.push('/login');
};

// ─── Helpers ──────────────────────────────────────────
const isToday = (dateStr) => (typeof dateStr === 'string' ? dateStr.substring(0, 10) : '') === today;

const getDay = (dateStr) => {
  const d = new Date((dateStr?.substring(0, 10) ?? '') + 'T12:00:00');
  return isNaN(d) ? '?' : d.getDate();
};

const getMonth = (dateStr) => {
  const d = new Date((dateStr?.substring(0, 10) ?? '') + 'T12:00:00');
  return isNaN(d) ? '' : d.toLocaleDateString('fr-FR', { month: 'short' });
};

const formatFrDate = (dateStr) => {
  const d = new Date((dateStr?.substring(0, 10) ?? '') + 'T12:00:00');
  return isNaN(d) ? '' : d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const moodEmoji = (m) => ({ 1: '😟', 2: '😐', 3: '🙂', 4: '😊', 5: '🔥' }[m] ?? '🙂');
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

/* ─── LAYOUT ─── */
.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', sans-serif; overflow: hidden; }
.main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 44px 52px; display: flex; flex-direction: column; gap: 40px; }
.animate-in { animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both; }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }

/* ─── LOADING / EMPTY ─── */
.loading-screen { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; gap: 20px; color: #8b949e; }
.spinner { width: 36px; height: 36px; border: 3px solid rgba(56,139,253,0.15); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.7s linear infinite; }
.spin-sm { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.25); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-screen { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; gap: 14px; text-align: center; }
.empty-ico { font-size: 60px; }
.empty-screen h3 { font-size: 20px; font-weight: 800; color: #fff; margin: 0; }
.empty-screen p { color: #8b949e; margin: 0; }

/* ─── ELITE PANEL ─── */
.elite-panel { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 20px; padding: 32px; backdrop-filter: blur(10px); }

/* ─── HEADER ─── */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.header-left { display: flex; align-items: center; gap: 20px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #388bfd; }
.header-icon svg { width: 22px; height: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; }
.dim { color: #484f58; font-weight: 500; }
.page-sub { font-size: 13px; color: #8b949e; margin-top: 6px; }
.header-right { display: flex; align-items: center; gap: 24px; }

/* ─── STATS MINI ─── */
.stats-mini { display: flex; align-items: center; gap: 24px; background: rgba(22,27,34,0.6); padding: 8px 20px; border-radius: 12px; border: 1px solid rgba(48,54,61,0.5); }
.stat-item { display: flex; flex-direction: column; }
.stat-item .val { font-size: 16px; font-weight: 800; color: #fff; }
.stat-item .lbl { font-size: 10px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-divider { width: 1px; height: 24px; background: rgba(48,54,61,0.5); }

/* ─── BUTTONS ─── */
.btn-elite-primary { background: #388bfd; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s; box-shadow: 0 4px 15px rgba(56,139,253,0.25); }
.btn-elite-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(56,139,253,0.35); filter: brightness(1.1); }
.btn-elite-primary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }
.btn-elite-primary svg { width: 14px; height: 14px; }
.btn-elite-ghost { background: transparent; border: 1px solid rgba(48,54,61,0.8); color: #8b949e; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
.btn-elite-ghost:hover { border-color: #388bfd; color: #fff; }
.btn-elite-ghost svg { width: 14px; height: 14px; }

/* ─── MAIN GRID ─── */
.main-grid { display: grid; grid-template-columns: 1fr 360px; gap: 32px; align-items: start; }

/* ─── SUBMITTED NOTICE ─── */
.submitted-notice { display: flex; align-items: center; gap: 16px; background: rgba(63,185,80,0.06); border: 1px solid rgba(63,185,80,0.2); border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; }
.notice-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(63,185,80,0.15); color: #3fb950; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notice-icon svg { width: 18px; height: 18px; }
.notice-body { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.notice-body strong { font-size: 14px; font-weight: 800; color: #f0f6fc; }
.notice-body span { font-size: 12px; color: #8b949e; }

/* ─── FORM SECTION TITLE ─── */
.form-section-title { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 800; color: #388bfd; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 24px; }
.form-section-title svg { width: 16px; height: 16px; }

/* ─── FORM FIELDS ─── */
.report-form { display: flex; flex-direction: column; gap: 20px; }
.field-row { display: flex; gap: 16px; }
.field-group { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.field-label { font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.06em; }
.field-input { background: rgba(1,4,9,0.5); border: 1px solid rgba(48,54,61,0.8); border-radius: 12px; padding: 13px 16px; color: #fff; font-size: 14px; font-family: inherit; outline: none; transition: all 0.2s; width: 100%; resize: vertical; }
.field-input:focus { border-color: #388bfd; background: rgba(1,4,9,0.8); box-shadow: 0 0 0 3px rgba(56,139,253,0.1); }
.field-input:disabled { opacity: 0.45; cursor: not-allowed; }
.field-input::placeholder { color: #484f58; }

/* ─── COUNTER ─── */
.counter { display: flex; align-items: center; background: rgba(1,4,9,0.5); border: 1px solid rgba(48,54,61,0.8); border-radius: 12px; height: 46px; overflow: hidden; }
.counter.c-red { border-color: rgba(248,81,73,0.2); }
.counter.c-yellow { border-color: rgba(210,153,34,0.2); }
.counter.locked { opacity: 0.45; }
.counter button { width: 44px; height: 100%; background: transparent; border: none; color: #8b949e; font-size: 20px; cursor: pointer; transition: all 0.15s; flex-shrink: 0; }
.counter button:hover:not(:disabled) { background: rgba(48,54,61,0.5); color: #fff; }
.counter button:disabled { cursor: not-allowed; }
.counter span { flex: 1; text-align: center; font-size: 17px; font-weight: 800; color: #fff; }

/* ─── MOOD ─── */
.mood-row { display: flex; gap: 6px; }
.mood-btn { flex: 1; height: 46px; background: rgba(1,4,9,0.5); border: 1px solid rgba(48,54,61,0.8); border-radius: 12px; font-size: 18px; cursor: pointer; transition: all 0.15s; }
.mood-btn:hover:not(:disabled) { border-color: #388bfd; transform: scale(1.08); }
.mood-btn.active { border-color: #388bfd; background: rgba(56,139,253,0.15); box-shadow: 0 0 12px rgba(56,139,253,0.2); }
.mood-btn.locked { opacity: 0.45; cursor: not-allowed; transform: none !important; }

/* ─── TOGGLE ─── */
.toggle-row { display: flex; gap: 6px; }
.tgl { flex: 1; height: 46px; background: rgba(1,4,9,0.5); border: 1px solid rgba(48,54,61,0.8); border-radius: 12px; font-size: 13px; font-weight: 700; color: #8b949e; cursor: pointer; transition: all 0.15s; }
.tgl:hover:not(:disabled) { border-color: #484f58; color: #fff; }
.tgl.active { background: rgba(63,185,80,0.12); border-color: #3fb950; color: #3fb950; }
.tgl.tgl-no.active { background: rgba(248,81,73,0.12); border-color: #f85149; color: #f85149; }
.tgl.locked { opacity: 0.45; cursor: not-allowed; }

/* ─── FORM FOOTER ─── */
.form-footer { display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid rgba(48,54,61,0.4); margin-top: 4px; }

/* ─── HISTORY PANEL ─── */
.history-panel { position: sticky; top: 24px; display: flex; flex-direction: column; gap: 0; }
.history-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.history-count { background: rgba(48,54,61,0.4); border: 1px solid rgba(48,54,61,0.6); color: #8b949e; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; }
.history-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 40px 0; color: #484f58; }
.history-empty svg { width: 32px; height: 32px; }
.history-empty p { font-size: 13px; margin: 0; }

/* ─── TIMELINE ─── */
.timeline { display: flex; flex-direction: column; gap: 0; max-height: 560px; overflow-y: auto; overflow-x: hidden; padding-right: 4px; }
.timeline-item { display: flex; align-items: flex-start; gap: 0; padding: 12px 8px; border-radius: 12px; cursor: pointer; transition: background 0.15s; }
.timeline-item:hover, .timeline-item.is-today { background: rgba(56,139,253,0.05); }
.tl-date { width: 42px; text-align: center; flex-shrink: 0; padding-top: 2px; }
.tl-d { display: block; font-size: 18px; font-weight: 900; color: #f0f6fc; line-height: 1.1; }
.tl-m { display: block; font-size: 9px; font-weight: 700; color: #484f58; text-transform: uppercase; }
.timeline-item.is-today .tl-d { color: #388bfd; }
.tl-line { width: 20px; display: flex; flex-direction: column; align-items: center; padding-top: 6px; flex-shrink: 0; }
.tl-dot { width: 8px; height: 8px; border-radius: 50%; background: #30363d; flex-shrink: 0; }
.tl-dot.today { background: #388bfd; box-shadow: 0 0 8px rgba(56,139,253,0.6); }
.tl-content { flex: 1; min-width: 0; }
.tl-title { font-size: 13px; font-weight: 700; color: #e6edf3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 2px; }
.tl-sub { font-size: 11px; color: #8b949e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px; }
.tl-badges { display: flex; flex-wrap: wrap; gap: 4px; }
.badge { font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
.badge.red { background: rgba(248,81,73,0.1); color: #f85149; }
.badge.yellow { background: rgba(210,153,34,0.1); color: #d29922; }
.badge.green { background: rgba(63,185,80,0.1); color: #3fb950; }
.badge.mood { background: rgba(255,255,255,0.05); color: #8b949e; }
.badge.today-pill { background: rgba(56,139,253,0.15); color: #388bfd; }

/* ─── RESPONSIVE ─── */
@media (max-width: 1200px) {
  .main-grid { grid-template-columns: 1fr; }
  .history-panel { position: static; }
  .content { padding: 24px 28px; gap: 24px; }
}
</style>