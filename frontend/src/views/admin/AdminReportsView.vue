<template>
  <div class="layout">
    <SidebarAdmin :user="currentUser" @logout="handleLogout" />

    <main class="main">
      <div class="content">
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Rapports Daily</h1>
            <p class="topbar-sub">Navigation par classe et historique journalier</p>
          </div>
        </header>

        <div v-if="isLoading" class="panel empty-panel">Chargement en cours...</div>

        <div v-else class="workspace animate-in">
          <aside class="classes-panel panel">
            <div class="panel-header">
              <h2 class="panel-title">Classes</h2>
            </div>
            <div v-if="classrooms.length === 0" class="empty-panel">Aucune classe trouvée.</div>
            <div v-else class="classes-list">
              <button
                v-for="cls in classrooms"
                :key="cls.id"
                class="class-item"
                :class="{ active: selectedClassroomId === cls.id }"
                @click="selectClassroom(cls)"
              >
                <span>{{ cls.name }}</span>
              </button>
            </div>
          </aside>

          <section class="reports-panel panel">
            <div class="panel-header">
              <h2 class="panel-title">Rapports — {{ selectedClassroomName }}</h2>
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge">{{ reports.length }} jours</span>
                <button
                  class="btn-pdf"
                  :disabled="!selectedReport || isGeneratingPdf"
                  @click="generatePdf"
                >
                  {{ isGeneratingPdf ? 'Génération...' : 'Télécharger PDF' }}
                </button>
              </div>
            </div>

            <div class="stats-row">
              <div class="stat-box">
                <span class="lbl">Total rapports</span>
                <span class="val">{{ stats.total_reports || 0 }}</span>
              </div>
              <div class="stat-box">
                <span class="lbl">Moy. absences</span>
                <span class="val">{{ stats.avg_absences || 0 }}</span>
              </div>
            </div>

            <div v-if="reports.length === 0" class="empty-panel">Aucun rapport pour cette classe.</div>

            <div v-else class="reports-list">
              <article
                v-for="rep in sortedReports"
                :key="rep.id"
                class="report-card"
                :class="{ active: selectedReport?.id === rep.id }"
                @click="selectedReport = rep"
              >
                <div class="report-head">
                  <strong>{{ formatDate(rep.date) }}</strong>
                  <span class="chip">Mood {{ rep.class_mood || '-' }}/5</span>
                </div>
                <div class="report-info-grid">
                  <div class="info-box">
                    <span class="info-label">Brief</span>
                    <span class="info-value">{{ rep.brief_status || '—' }}</span>
                  </div>
                  <div class="info-box">
                    <span class="info-label">Absences</span>
                    <span class="info-value">{{ rep.absences_count || 0 }}</span>
                  </div>
                  <div class="info-box">
                    <span class="info-label">Objectifs</span>
                    <span class="info-value" :class="rep.objectives_met ? 'ok' : 'ko'">
                      {{ rep.objectives_met ? 'Atteints' : 'Non atteints' }}
                    </span>
                  </div>
                </div>
                <p class="note">{{ rep.note || rep.technical_topics || 'Aucune note.' }}</p>
              </article>
            </div>
          </section>
        </div>
      </div>
    </main>

    <!-- PDF TEMPLATE (off-screen) -->
    <div
      id="admin-report-pdf-content"
      style="position:fixed;top:-9999px;left:0;width:794px;background:#fff;color:#111;font-family:Arial,sans-serif;padding:40px;"
      v-if="selectedReport"
    >
      <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:20px;">
        <div>
          <h1 style="margin:0;font-size:20px;font-weight:800;letter-spacing:.2px;">Rapport Journalier de Classe</h1>
          <p style="margin:3px 0 0;color:#555;font-size:12px;">Classe: {{ selectedClassroomName }}</p>
        </div>
        <div style="text-align:right;">
          <div style="font-size:11px;color:#666;text-transform:uppercase;letter-spacing:.7px;">Date</div>
          <div style="font-size:16px;font-weight:700;">{{ formatDate(selectedReport.date) }}</div>
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;">1. Indicateurs de la journée</h2>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
          <tr>
            <td style="border:1px solid #d9d9d9;padding:8px;font-weight:700;width:28%;">Absences</td>
            <td style="border:1px solid #d9d9d9;padding:8px;">{{ selectedReport.absences_count || 0 }}</td>
            <td style="border:1px solid #d9d9d9;padding:8px;font-weight:700;width:28%;">Climat de classe</td>
            <td style="border:1px solid #d9d9d9;padding:8px;">{{ selectedReport.class_mood || '-' }}/5</td>
          </tr>
          <tr>
            <td style="border:1px solid #d9d9d9;padding:8px;font-weight:700;">Objectifs</td>
            <td colspan="3" style="border:1px solid #d9d9d9;padding:8px;">{{ selectedReport.objectives_met ? 'Atteints' : 'Non atteints' }}</td>
          </tr>
        </table>
      </div>

      <div style="margin-bottom:14px;">
        <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;">2. Brief du jour</h2>
        <p style="border:1px solid #ddd;padding:10px 12px;margin:0;font-weight:700;font-size:13px;">{{ selectedReport.brief_status || '—' }}</p>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;">3. Séance technique</h2>
          <p style="border:1px solid #ddd;padding:10px 12px;margin:0;min-height:62px;font-size:12px;line-height:1.45;">{{ selectedReport.technical_topics || '—' }}</p>
        </div>
        <div>
          <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;">4. Workshops</h2>
          <p style="border:1px solid #ddd;padding:10px 12px;margin:0;min-height:62px;font-size:12px;line-height:1.45;">{{ selectedReport.workshops_done || '—' }}</p>
        </div>
      </div>
      <div>
        <h2 style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;">5. Observations</h2>
        <p style="border:1px solid #ddd;padding:10px 12px;margin:0;min-height:64px;font-size:12px;line-height:1.45;">{{ selectedReport.note || 'Aucune note.' }}</p>
      </div>
      <div style="margin-top:22px;padding-top:10px;border-top:1px solid #ddd;display:flex;justify-content:space-between;color:#888;font-size:10px;">
        <span>Généré le {{ new Date().toLocaleDateString('fr-FR') }}</span>
        <span>YouCode — Admin</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
import api from '../../services/api';
import { DailyReportService } from '../../services/ApiService';

// --- VARIABLES D'ÉTAT (REFS) ---
// On utilise 'ref' pour créer des variables réactives que Vue surveille
const router = useRouter();
const currentUser = ref(JSON.parse(localStorage.getItem('user')) || {});
const isLoading = ref(true); // État de chargement initial
const classrooms = ref([]); // Liste des classes
const selectedClassroomId = ref(null); // ID de la classe sélectionnée
const reports = ref([]); // Liste des rapports daily de la classe
const stats = ref({}); // Statistiques de la classe
const selectedReport = ref(null); // Rapport sélectionné pour voir les détails/PDF
const isGeneratingPdf = ref(false); // État lors de la génération du PDF

// --- LOGIQUE CALCULÉE (COMPUTED) ---

// Récupère le nom de la classe actuellement sélectionnée
const selectedClassroomName = computed(() => {
  const cls = classrooms.value.find(c => c.id === selectedClassroomId.value);
  return cls?.name || '—';
});

// Trie les rapports par date décroissante (plus récent en haut)
const sortedReports = computed(() =>
  [...reports.value].sort((a, b) => (b.date || '').localeCompare(a.date || ''))
);

// --- ACTIONS (MÉTHODES) ---

// 1. Récupérer toutes les classes au démarrage
const fetchClassrooms = async () => {
  // Cache
  const cached = localStorage.getItem('admin_reports_classrooms_cache');
  if (cached) {
    classrooms.value = JSON.parse(cached);
  }

  try {
    const res = await api.get('/classrooms');
    classrooms.value = res.data?.data || [];
    localStorage.setItem('admin_reports_classrooms_cache', JSON.stringify(classrooms.value));
  } catch (err) {
    console.error("Erreur Classrooms:", err);
  }
};

// 2. Récupérer les rapports et stats d'une classe précise
const fetchReports = async (classroomId) => {
  // Cache par classe
  const cacheKey = `admin_reports_list_cache_${classroomId}`;
  const cached = localStorage.getItem(cacheKey);
  if (cached) {
    const cacheData = JSON.parse(cached);
    reports.value = cacheData.reports;
    stats.value = cacheData.stats;
    selectedReport.value = reports.value[0] || null;
  }

  try {
    const [reportsRes, statsRes] = await Promise.all([
      DailyReportService.getByClassroom(classroomId),
      DailyReportService.getStats(classroomId),
    ]);

    reports.value = reportsRes.data?.data || [];
    stats.value = statsRes.data?.data || {};
    // Par défaut, on sélectionne le premier rapport (le plus récent)
    selectedReport.value = reports.value[0] || null;

    localStorage.setItem(cacheKey, JSON.stringify({
      reports: reports.value,
      stats: stats.value
    }));
  } catch (err) {
    console.error("Erreur Reports:", err);
  }
};

// 3. Quand l'utilisateur clique sur une classe
const selectClassroom = async (cls) => {
  selectedClassroomId.value = cls.id;
  await fetchReports(cls.id);
};

// 4. Formater la date pour l'affichage (ex: 23 avril 2026)
const formatDate = (dateStr) => {
  const d = new Date((dateStr || '').substring(0, 10));
  if (Number.isNaN(d.getTime())) return 'Date invalide';
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

const generatePdf = async () => {
  if (!selectedReport.value) return;
  isGeneratingPdf.value = true;
  await new Promise(r => setTimeout(r, 120));
  const { default: html2canvas } = await import('html2canvas');
  const { default: jsPDF } = await import('jspdf');
  const el = document.getElementById('admin-report-pdf-content');
  if (!el) throw new Error('PDF template not found');

  el.style.top = '0';
  el.style.zIndex = '9999';
  const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#fff' });
  el.style.top = '-9999px';
  el.style.zIndex = '';

  const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
  const imgH = (canvas.height * 210) / canvas.width;
  pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, 210, imgH);
  pdf.save(`daily-report-${selectedClassroomName.value}-${selectedReport.value.date}.pdf`);
  isGeneratingPdf.value = false;
};

onMounted(async () => {
  isLoading.value = true;
  await fetchClassrooms();
  if (classrooms.value.length > 0) {
    await selectClassroom(classrooms.value[0]);
  }
  isLoading.value = false;
});
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }
.topbar-title { font-size: 32px; font-weight: 800; letter-spacing: -0.04em; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }
.workspace { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
.panel { background: #0d1117; border: 1px solid #21262d; border-radius: 18px; overflow: hidden; }
.panel-header { padding: 18px 20px; border-bottom: 1px solid #21262d; display: flex; justify-content: space-between; align-items: center; }
.panel-title { font-size: 16px; font-weight: 700; }
.classes-list { padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.class-item { text-align: left; padding: 10px 12px; border-radius: 10px; border: 1px solid transparent; background: transparent; color: #c9d1d9; cursor: pointer; }
.class-item:hover { background: rgba(163, 113, 247, 0.08); }
.class-item.active { background: rgba(163, 113, 247, 0.12); border-color: rgba(163, 113, 247, 0.25); color: #a371f7; }
.reports-panel { min-height: 420px; }
.stats-row { display: flex; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #21262d; }
.stat-box { background: #161b22; border: 1px solid #21262d; border-radius: 10px; padding: 10px 12px; display: flex; flex-direction: column; min-width: 140px; }
.stat-box .lbl { font-size: 11px; color: #8b949e; }
.stat-box .val { font-size: 20px; font-weight: 800; color: #fff; }
.reports-list { padding: 14px; display: flex; flex-direction: column; gap: 10px; max-height: 62vh; overflow-y: auto; }
.report-card { background: #161b22; border: 1px solid #21262d; border-radius: 14px; padding: 14px; cursor: pointer; transition: all .18s ease; }
.report-card:hover { border-color: rgba(163,113,247,0.3); transform: translateY(-1px); }
.report-card.active { border-color: rgba(163,113,247,0.45); box-shadow: 0 0 0 1px rgba(163,113,247,0.25) inset; }
.report-head { display: flex; justify-content: space-between; margin-bottom: 8px; }
.chip, .badge { font-size: 10px; font-weight: 700; color: #a371f7; background: rgba(163,113,247,0.1); border: 1px solid rgba(163,113,247,0.2); padding: 2px 8px; border-radius: 999px; }
.btn-pdf { background: #238636; color: #fff; border: 1px solid #2ea043; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; }
.btn-pdf:disabled { opacity: .55; cursor: not-allowed; }
.report-info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 10px 0; }
.info-box { background: rgba(13,17,23,0.5); border: 1px solid rgba(48,54,61,0.55); border-radius: 10px; padding: 8px 10px; display: flex; flex-direction: column; gap: 4px; min-height: 56px; }
.info-label { font-size: 10px; color: #8b949e; text-transform: uppercase; letter-spacing: .05em; }
.info-value { font-size: 12px; color: #e6edf3; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.info-value.ok { color: #3fb950; }
.info-value.ko { color: #f85149; }
.note { margin-top: 10px; font-size: 12px; color: #9aa4b2; background: rgba(1,4,9,0.45); border: 1px solid rgba(48,54,61,0.45); border-radius: 10px; padding: 10px; line-height: 1.5; }
.empty-panel { padding: 26px; color: #8b949e; text-align: center; }
.animate-in { animation: fadeInUp .35s ease both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(12px);} to { opacity:1; transform: translateY(0);} }

@media (max-width: 1200px) {
  .report-info-grid { grid-template-columns: 1fr; }
}
</style>

