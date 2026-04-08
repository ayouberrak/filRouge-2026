<template>
  <div class="layout" @click="closeMenu">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== HEADER ===== -->
        <header class="page-header animate-in">
          <div class="header-left">
            <div class="header-title-row">
              <div class="header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <div>
                <h1 class="page-title">Présence <span class="dim">/ Suivi</span></h1>
                <p class="page-sub">Contrôle de l'assiduité promotionnelle • Promotion 2026</p>
              </div>
            </div>
          </div>
          
          <div class="header-right">
            <!-- Legend -->
            <div class="legend">
              <div class="leg-item"><span class="leg-dot leg-dot--red"></span> Absence</div>
              <div class="leg-item"><span class="leg-dot leg-dot--amber"></span> Demi-jour</div>
              <div class="leg-item"><span class="leg-dot leg-dot--green"></span> Retard</div>
            </div>

            <!-- Month nav -->
            <div class="month-nav">
              <button class="month-btn" @click.stop="changeMonth(-1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
              </button>
              <div class="month-display">
                <span class="month-name">{{ currentMonthName }}</span>
                <span class="month-year">{{ currentYear }}</span>
              </div>
              <button class="month-btn" @click.stop="changeMonth(1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        </header>

        <!-- ===== ATTENDANCE GRID ===== -->
        <section class="grid-container animate-in" style="animation-delay: 0.1s" @scroll="closeMenu">
          <div class="scroll-wrapper">
            <table class="elite-att-table">
              <thead>
                <tr>
                  <th class="th-sticky th-student">Membres</th>
                  <th
                    v-for="day in daysInMonth"
                    :key="day"
                    class="th-day"
                    :class="{ 'th-day--weekend': isWeekend(day) }"
                  >
                    <span class="day-num">{{ String(day).padStart(2, '0') }}</span>
                    <span class="day-letter">{{ getDayLetter(day) }}</span>
                  </th>
                  <th class="th-sticky th-totals">Synthèse</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="student in students" :key="student.id" class="att-row">
                  <td class="th-sticky td-student">
                    <img class="student-avatar" :src="student.avatar" :alt="student.name" />
                    <div class="student-info">
                      <span class="student-name">{{ student.name }}</span>
                      <span class="student-pts">{{ student.points }} pts</span>
                    </div>
                  </td>

                  <td
                    v-for="day in daysInMonth"
                    :key="day"
                    class="td-day"
                    :class="[
                      cellClass(student.id, day),
                      { 'td-day--weekend': isWeekend(day) }
                    ]"
                    @click.stop="openMenu($event, student.id, day)"
                  >
                    <span class="cell-tag">{{ cellTag(student.id, day) }}</span>
                  </td>

                  <td class="th-sticky td-totals">
                    <div class="totals-cluster">
                      <span class="total-pill total-pill--red">{{ totals(student.id).A }}</span>
                      <span class="total-pill total-pill--amber">{{ totals(student.id).D }}</span>
                      <span class="total-pill total-pill--green">{{ totals(student.id).R }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- ===== ACTION MENU (POPOVER) ===== -->
        <Transition name="fade-in">
          <div
            v-if="activeMenu"
            class="elite-action-menu"
            :style="menuStyle"
            @click.stop
          >
            <div class="menu-head">Modifier le statut</div>
            
            <button class="menu-opt menu-opt--clear" @click="setStatus('')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
              Marquer Présent
            </button>

            <div class="menu-divider"></div>

            <button class="menu-opt" @click="setStatus('A')">
              <span class="opt-dot dot--red"></span> Absence complète
            </button>
            <button class="menu-opt" @click="setStatus('D')">
              <span class="opt-dot dot--amber"></span> Demi-journée
            </button>

            <div class="menu-divider"></div>

            <div class="menu-label">Déclarer un retard</div>
            <div class="late-options">
              <button v-for="d in ['5\'', '10\'', '15\'', '30\'', '1h+']" :key="d" class="late-chip" @click="setStatus('R', d)">
                {{ d }}
              </button>
            </div>
          </div>
        </Transition>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import AbsenceService from '../../services/AbsenceService';
import api from '../../services/api';

const router      = useRouter();
const user        = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Formateur', last_name: '' });
const currentDate = ref(new Date());
const activeMenu  = ref(null);
const menuStyle   = ref({ top: '0px', left: '0px' });
const attendanceData = reactive({});
const students = ref([]);
const isLoadingData = ref(false);
const classroomId = ref(1);

// ─── DATE UTILS ───────────────────────────────────────────────────────────────

const currentMonthName = computed(() =>
  new Intl.DateTimeFormat('fr-FR', { month: 'long' })
    .format(currentDate.value)
    .replace(/^\w/, c => c.toUpperCase())
);

const currentYear = computed(() => currentDate.value.getFullYear());

const daysInMonth = computed(() =>
  new Date(currentYear.value, currentDate.value.getMonth() + 1, 0).getDate()
);

const isWeekend = (day) => {
  const d = new Date(currentYear.value, currentDate.value.getMonth(), day);
  return d.getDay() === 0 || d.getDay() === 6;
};

const getDayLetter = (day) => {
  const d = new Date(currentYear.value, currentDate.value.getMonth(), day);
  return ['D', 'L', 'M', 'M', 'J', 'V', 'S'][d.getDay()];
};

const changeMonth = (delta) => {
  const d = new Date(currentDate.value);
  d.setMonth(d.getMonth() + delta);
  currentDate.value = d;
  activeMenu.value  = null;
  fetchAttendance();
};

const monthKey = () => `${currentYear.value}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}`;
const entryKey = (studentId, day) => `${studentId}-${monthKey()}-${day}`;

// ─── ATTENDANCE LOGIC ─────────────────────────────────────────────────────────

const cellClass = (studentId, day) => {
  const entry = attendanceData[entryKey(studentId, day)];
  if (!entry) return '';
  return { A: 'is-absent', D: 'is-half', R: 'is-late' }[entry.type] ?? '';
};

const cellTag = (studentId, day) => {
  const entry = attendanceData[entryKey(studentId, day)];
  if (!entry) return '';
  return entry.type === 'R' && entry.duration ? `R${entry.duration}` : entry.type;
};

const totals = (studentId) => {
  const mk  = monthKey();
  const acc = { A: 0, D: 0, R: 0 };
  Object.keys(attendanceData).forEach(k => {
    if (k.startsWith(`${studentId}-${mk}`)) {
      const entry = attendanceData[k];
      if (entry && entry.type in acc) acc[entry.type]++;
    }
  });
  return acc;
};

const openMenu = (event, studentId, day) => {
  if (isWeekend(day)) return;
  const rect = event.currentTarget.getBoundingClientRect();
  const parentRect = event.currentTarget.closest('.content').getBoundingClientRect();
  menuStyle.value = {
    top:  `${rect.bottom - parentRect.top + 8}px`,
    left: `${rect.left  - parentRect.left}px`,
  };
  activeMenu.value = { studentId, day };
};

const setStatus = async (type, duration = '') => {
  if (!activeMenu.value) return;
  const { studentId, day } = activeMenu.value;
  const key = entryKey(studentId, day);
  const currentEntry = attendanceData[key];

  try {
    if (!type) {
      if (currentEntry?.id) {
        await AbsenceService.delete(currentEntry.id);
      }
      delete attendanceData[key];
    } else {
      let durationMins = 0;
      if (type === 'A') durationMins = 480;
      else if (type === 'D') durationMins = 240;
      else durationMins = parseInt(duration) || 5;

      const dateStr = `${currentYear.value}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const response = await AbsenceService.create({ 
        student_id: studentId, 
        date: dateStr, 
        duration: durationMins 
      });

      attendanceData[key] = { id: response.data.absence.id, type, duration };
    }
  } catch (error) {
    console.error("Attendance sync error:", error);
    alert('Erreur de synchronisation.');
  } finally {
    activeMenu.value = null;
  }
};

const closeMenu = () => { activeMenu.value = null; };

const fetchStudents = async () => {
  try {
    const response = await api.get('/students', { params: { classroom_id: classroomId.value } });
    students.value = response.data.data.map(s => ({
      id: s.id,
      name: `${s.first_name} ${s.last_name}`,
      points: s.total_points || 0,
      avatar: s.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(s.first_name + ' ' + s.last_name)}&background=161b22&color=388bfd&bold=true`
    })).sort((a, b) => a.name.localeCompare(b.name));
  } catch (error) { console.error("Load students error:", error); }
};

const fetchAttendance = async () => {
  isLoadingData.value = true;
  Object.keys(attendanceData).forEach(k => delete attendanceData[k]);
  try {
    const mk = monthKey();
    const response = await AbsenceService.getByClassroom(classroomId.value, mk);
    response.data.absences.forEach(abs => {
      let type = 'R';
      let dStr = abs.duration + "'";
      if (abs.duration === 480) { type = 'A'; dStr = ''; }
      else if (abs.duration === 240) { type = 'D'; dStr = ''; }
      const day = parseInt(abs.date.split('-')[2]);
      attendanceData[`${abs.student_id}-${mk}-${day}`] = { id: abs.id, type, duration: dStr };
    });
  } finally { isLoadingData.value = false; }
};

onMounted(async () => {
  await fetchStudents();
  await fetchAttendance();
});

const handleLogout = () => router.push('/login');
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.content { padding: 44px 52px; display: flex; flex-direction: column; height: 100%; position: relative; gap: 32px; }

/* ===== HEADER ===== */
.page-header { display: flex; justify-content: space-between; align-items: center; }
.header-title-row { display: flex; align-items: center; gap: 18px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #388bfd; }
.header-icon svg { width: 22px; height: 22px; }
.page-title { font-size: 26px; font-weight: 900; color: #fff; letter-spacing: -0.03em; line-height: 1; }
.dim { color: #484f58; font-weight: 500; font-size: 20px; }
.page-sub { font-size: 13px; color: #8b949e; margin-top: 6px; }

.header-right { display: flex; align-items: center; gap: 40px; }

/* Legend */
.legend { display: flex; gap: 20px; }
.leg-item { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; }
.leg-dot { width: 8px; height: 8px; border-radius: 3px; }
.leg-dot--red { background: #f85149; }
.leg-dot--amber { background: #d29922; }
.leg-dot--green { background: #3fb950; }

/* Month Nav */
.month-nav { display: flex; align-items: center; gap: 16px; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.5); padding: 6px 14px; border-radius: 12px; }
.month-btn { width: 28px; height: 28px; border-radius: 8px; border: none; background: transparent; color: #484f58; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.month-btn:hover { background: rgba(56,139,253,0.1); color: #388bfd; }
.month-btn svg { width: 14px; height: 14px; }
.month-display { display: flex; align-items: baseline; gap: 8px; min-width: 140px; justify-content: center; }
.month-name { font-size: 14px; font-weight: 800; color: #fff; }
.month-year { font-size: 11px; color: #484f58; font-weight: 600; }

/* ===== GRID SECTION ===== */
.grid-container { flex: 1; background: rgba(13,17,23,0.4); border: 1px solid rgba(48,54,61,0.4); border-radius: 20px; overflow: hidden; }
.scroll-wrapper { width: 100%; height: 100%; overflow: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.4) transparent; }
.scroll-wrapper::-webkit-scrollbar { width: 4px; height: 4px; }
.scroll-wrapper::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }

.elite-att-table { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; }

/* Sticky Headers */
.th-sticky { position: sticky; z-index: 10; background: #0d1117; }
.th-student { left: 0; width: 240px; border-right: 1px solid rgba(48,54,61,0.6); text-align: left; padding-left: 24px !important; }
.th-totals { right: 0; width: 110px; border-left: 1px solid rgba(48,54,61,0.6); text-align: center; }

.elite-att-table th { background: rgba(22,27,34,0.95); padding: 14px 0; font-size: 10px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid rgba(48,54,61,0.6); }

.th-day { width: 38px; text-align: center; border-right: 1px solid rgba(48,54,61,0.3); }
.th-day--weekend { background: rgba(13,17,23,0.8) !important; color: #21262d; }
.day-num { display: block; font-size: 12px; font-weight: 900; color: #fff; line-height: 1; }
.day-letter { display: block; font-size: 8px; opacity: 0.5; margin-top: 2px; }

/* Body rows */
.att-row td { border-bottom: 1px solid rgba(48,54,61,0.3); vertical-align: middle; height: 52px; transition: background 0.1s; border-right: 1px solid rgba(48,54,61,0.2); }
.att-row:hover td { background: rgba(56,139,253,0.02); }

.td-student { left: 0; display: flex; align-items: center; gap: 12px; padding: 0 16px; background: #010409 !important; border-right: 1px solid rgba(48,54,61,0.6) !important; }
.student-avatar { width: 28px; height: 28px; border-radius: 8px; border: 1px solid rgba(48,54,61,0.6); }
.student-info { display: flex; flex-direction: column; overflow: hidden; }
.student-name { font-size: 12px; font-weight: 700; color: #e6edf3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-pts { font-size: 9px; font-weight: 700; color: #388bfd; font-family: 'JetBrains Mono', monospace; }

.td-day { text-align: center; cursor: pointer; position: relative; transition: all 0.2s; }
.td-day:not(.td-day--weekend):hover { background: rgba(56,139,253,0.08); }
.td-day--weekend { background: rgba(1,4,9,0.4) !important; cursor: default; }

.cell-tag { font-size: 10px; font-weight: 900; color: rgba(255,255,255,0.9); text-shadow: 0 1px 2px rgba(0,0,0,0.5); }

/* Fills */
.is-absent { background: #f85149 !important; }
.is-half { background: #d29922 !important; }
.is-late { background: #3fb950 !important; }

.td-totals { right: 0; background: #010409 !important; border-left: 1px solid rgba(48,54,61,0.6) !important; border-right: none !important; }
.totals-cluster { display: flex; justify-content: center; gap: 4px; }
.total-pill { font-size: 9px; font-weight: 900; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
.total-pill--red { color: #f85149; background: rgba(248,81,73,0.1); }
.total-pill--amber { color: #d29922; background: rgba(210,153,34,0.1); }
.total-pill--green { color: #3fb950; background: rgba(63,185,80,0.1); }

/* ===== ACTION MENU ===== */
.elite-action-menu { position: absolute; z-index: 100; background: rgba(22,27,34,0.95); border: 1px solid rgba(48,54,61,1); border-radius: 12px; padding: 10px; width: 190px; box-shadow: 0 12px 32px rgba(0,0,0,0.6); backdrop-filter: blur(8px); display: flex; flex-direction: column; gap: 4px; }
.menu-head { font-size: 10px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; padding: 4px 10px 8px; }
.menu-opt { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: none; background: transparent; color: #c9d1d9; font-size: 12px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-align: left; width: 100%; }
.menu-opt:hover { background: rgba(56,139,253,0.1); color: #fff; }
.menu-opt--clear svg { width: 14px; height: 14px; color: #3fb950; }
.opt-dot { width: 7px; height: 7px; border-radius: 2px; }
.dot--red { background: #f85149; }
.dot--amber { background: #d29922; }
.menu-divider { height: 1px; background: rgba(48,54,61,0.5); margin: 4px 0; }
.menu-label { font-size: 9px; font-weight: 800; color: #3fb950; opacity: 0.6; padding: 4px 10px; text-transform: uppercase; }
.late-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; padding: 0 4px; }
.late-chip { background: rgba(63,185,80,0.1); border: 1px solid rgba(63,185,80,0.2); border-radius: 6px; color: #3fb950; font-size: 10px; font-weight: 700; padding: 5px 0; cursor: pointer; transition: all 0.2s; }
.late-chip:hover { background: #3fb950; color: #010409; }

/* Anims */
.animate-in { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.fade-in-enter-active, .fade-in-leave-active { transition: all 0.2s ease; }
.fade-in-enter-from, .fade-in-leave-to { opacity: 0; transform: translateY(-10px) scale(0.95); }
</style>