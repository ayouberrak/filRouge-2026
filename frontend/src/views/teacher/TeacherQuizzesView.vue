<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in">

        <!-- Header -->
        <header class="page-header" v-if="!selectedQuiz">
          <div class="header-left">
            <div class="header-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/></svg>
            </div>
            <div>
              <h1 class="header-title">Quiz</h1>
              <p class="header-sub">Créez et gérez vos évaluations théoriques</p>
            </div>
          </div>
          <router-link to="/teacher/quizzes/create" class="btn-create">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Nouveau Quiz
          </router-link>
        </header>

        <!-- Back Header (when viewing results) -->
        <header class="page-header" v-else>
          <div class="header-left">
            <button @click="selectedQuiz = null; selectedStudent = null" class="btn-back">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </button>
            <div>
              <h1 class="header-title">Résultats : {{ selectedQuiz.title }}</h1>
              <p class="header-sub">{{ selectedQuiz.classroom?.name }} — {{ submissions.length }} participation(s)</p>
            </div>
          </div>
        </header>

        <!-- Stats (Only show on main list) -->
        <div v-if="!selectedQuiz" class="stats-row animate-in" style="animation-delay: 0.1s">
          <div class="stat-chip">
            <span class="stat-number">{{ quizzes.length }}</span>
            <span class="stat-label">Total</span>
          </div>
          <div class="stat-chip stat-chip--active">
            <span class="stat-number">{{ activeCount }}</span>
            <span class="stat-label">Actifs</span>
          </div>
          <div class="stat-chip stat-chip--pending">
            <span class="stat-number">{{ pendingCount }}</span>
            <span class="stat-label">En attente</span>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loader-state">
          <div class="loader-ring"></div>
          <p>Chargement...</p>
        </div>

        <!-- Main Content Area -->
        <template v-else>
          
          <!-- VIEW 1: QUIZZES LIST -->
          <div v-if="!selectedQuiz">
            <!-- Empty State -->
            <div v-if="quizzes.length === 0" class="empty-state animate-in">
              <div class="empty-icon">⚡</div>
              <h2>Aucun quiz créé</h2>
              <p>Commencez par créer votre premier quiz indépendant pour vos classes.</p>
              <router-link to="/teacher/quizzes/create" class="btn-primary">Créer un quiz</router-link>
            </div>

            <!-- Quizzes Grid -->
            <div v-else class="quizzes-grid">
              <div v-for="quiz in quizzes" :key="quiz.id" class="quiz-card animate-in">
                
                <div class="quiz-card-header">
                  <div class="quiz-status" :class="quiz.status.toLowerCase()">
                    {{ quiz.status === 'PENDING' ? '⏳ En attente' : quiz.status === 'ACTIVE' ? '🟢 Actif' : '✅ Terminé' }}
                  </div>
                  <span class="quiz-date">{{ formatDate(quiz.created_at) }}</span>
                </div>

                <h3 class="quiz-title">{{ quiz.title }}</h3>
                <p class="quiz-desc">{{ quiz.description || 'Aucune description' }}</p>

                <div class="quiz-meta">
                  <div class="meta-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    {{ quiz.classroom?.name || 'Classe non définie' }}
                  </div>
                  <div class="meta-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ quiz.timer_minutes }} min
                  </div>
                </div>

                <div class="quiz-actions">
                  <button v-if="quiz.status === 'PENDING'" @click="handleStart(quiz)" class="action-btn action-btn--launch" :disabled="isStarting === quiz.id">
                    <span v-if="isStarting === quiz.id" class="mini-spinner"></span>
                    <template v-else>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                      Lancer
                    </template>
                  </button>
                  
                  <button v-if="quiz.status !== 'PENDING'" @click="viewResults(quiz)" class="action-btn action-btn--results">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Résultats
                  </button>

                  <button v-if="quiz.status === 'PENDING'" @click="router.push(`/teacher/quizzes/${quiz.id}/edit`)" class="action-btn action-btn--edit">
                    Modifier
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- VIEW 2: QUIZ RESULTS (Students list) -->
          <div v-else class="results-container">
            
            <div class="results-layout">
              <!-- Students List Sidebar -->
              <aside class="students-sidebar">
                <h3 class="sidebar-title">Étudiants ({{ submissions.length }})</h3>
                <div v-if="submissions.length === 0" class="no-subs">
                  Aucune participation pour le moment.
                </div>
                <div v-else class="students-list">
                  <div 
                    v-for="sub in submissions" 
                    :key="sub.id" 
                    class="student-item" 
                    :class="{ 'active': selectedStudent?.id === sub.id }"
                    @click="viewStudentDetails(sub)"
                  >
                    <div class="student-avatar">{{ sub.first_name[0] }}{{ sub.last_name[0] }}</div>
                    <div class="student-info">
                      <span class="student-name">{{ sub.first_name }} {{ sub.last_name }}</span>
                      <span class="student-meta">{{ sub.answered_count }}/{{ sub.total_questions }} questions</span>
                    </div>
                    <div class="student-score" :class="getScoreClass(sub.score)">
                      {{ sub.score }}%
                    </div>
                  </div>
                </div>
              </aside>

              <!-- Detail Content -->
              <section class="results-detail">
                <div v-if="!selectedStudent" class="detail-empty">
                  <div class="empty-icon-large">📊</div>
                  <h3>Sélectionnez un étudiant</h3>
                  <p>Cliquez sur un étudiant dans la liste de gauche pour voir le détail de ses réponses.</p>
                </div>

                <div v-else-if="detailsLoading" class="loader-state">
                  <div class="loader-ring"></div>
                  <p>Chargement des réponses...</p>
                </div>

                <div v-else class="responses-list">
                  <div class="student-header-detail">
                    <div class="sh-left">
                      <div class="sh-avatar">{{ selectedStudent.first_name[0] }}{{ selectedStudent.last_name[0] }}</div>
                      <div>
                        <h2>{{ selectedStudent.first_name }} {{ selectedStudent.last_name }}</h2>
                        <p>{{ selectedStudent.email }}</p>
                      </div>
                    </div>
                    <div class="sh-right">
                      <div class="sh-score-badge" :class="getScoreClass(selectedStudent.score)">
                        <span class="sh-score-val">{{ selectedStudent.score }}%</span>
                        <span class="sh-score-label">Score Global</span>
                      </div>
                    </div>
                  </div>

                  <div v-for="(resp, idx) in studentResponses" :key="resp.id" class="response-card" :class="{ 'correct': resp.is_correct, 'incorrect': !resp.is_correct && resp.question_type !== 'open_ended', 'open': resp.question_type === 'open_ended' }">
                    <div class="rc-header">
                      <span class="rc-num">Question {{ idx + 1 }}</span>
                      <span class="rc-type">{{ resp.question_type === 'open_ended' ? 'Ouverte' : 'QCM' }}</span>
                      <span v-if="resp.question_type !== 'open_ended'" class="rc-verdict">
                        {{ resp.is_correct ? 'Correct' : 'Incorrect' }}
                      </span>
                    </div>
                    <p class="rc-question">{{ resp.question_content }}</p>
                    
                    <div class="rc-answer">
                      <span class="rc-label">Réponse de l'étudiant :</span>
                      <p class="rc-text">{{ resp.response_text }}</p>
                    </div>

                    <div v-if="resp.ai_feedback" class="rc-feedback">
                      <div class="rc-feedback-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        Analyse IA
                        <span class="rc-score-small" v-if="resp.score !== null">({{ resp.score }}%)</span>
                      </div>
                      <p class="rc-feedback-text">{{ resp.ai_feedback }}</p>
                    </div>
                  </div>
                </div>
              </section>
            </div>

          </div>

        </template>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import { QuizService } from '../../services/ApiService';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Coach' });
const quizzes = ref([]);
const loading = ref(true);
const isStarting = ref(null);

const selectedQuiz = ref(null);
const submissions = ref([]);
const selectedStudent = ref(null);
const studentResponses = ref([]);
const detailsLoading = ref(false);

const activeCount = computed(() => quizzes.value.filter(q => q.status === 'ACTIVE').length);
const pendingCount = computed(() => quizzes.value.filter(q => q.status === 'PENDING').length);

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' });
};

const fetchQuizzes = async () => {
  loading.value = true;
  try {
    const response = await QuizService.getMyQuizzes();
    quizzes.value = response.data || [];
  } catch (err) {
    console.error("Erreur chargement quiz:", err);
  } finally {
    loading.value = false;
  }
};

const handleStart = async (quiz) => {
  if (!confirm(`Voulez-vous lancer "${quiz.title}" pour ${quiz.classroom?.name || 'la classe'} ?`)) return;
  isStarting.value = quiz.id;
  try {
    await QuizService.startSession(quiz.id);
    await fetchQuizzes();
  } catch (err) {
    console.error("Erreur lancement:", err);
    alert("Impossible de lancer le quiz.");
  } finally {
    isStarting.value = null;
  }
};

const viewResults = async (quiz) => {
  selectedQuiz.value = quiz;
  loading.value = true;
  try {
    const response = await QuizService.getSessionSubmissions(quiz.id);
    submissions.value = response.data || [];
  } catch (err) {
    console.error("Erreur chargement soumissions:", err);
  } finally {
    loading.value = false;
  }
};

const viewStudentDetails = async (student) => {
  selectedStudent.value = student;
  detailsLoading.value = true;
  try {
    const response = await QuizService.getStudentResponses(selectedQuiz.value.id, student.id);
    studentResponses.value = response.data || [];
  } catch (err) {
    console.error("Erreur chargement détails:", err);
  } finally {
    detailsLoading.value = false;
  }
};

const getScoreClass = (score) => {
  if (score >= 80) return 'score-high';
  if (score >= 50) return 'score-mid';
  return 'score-low';
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(fetchQuizzes);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.5) transparent; }
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }
.content { max-width: 1200px; margin: 0 auto; padding: 40px 48px; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.header-left { display: flex; align-items: center; gap: 18px; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(110,64,201,0.1); border: 1px solid rgba(110,64,201,0.2); display: flex; align-items: center; justify-content: center; color: #9f6eff; flex-shrink: 0; }
.header-icon svg { width: 22px; height: 22px; }
.header-title { font-size: 26px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
.header-sub { font-size: 14px; color: #8b949e; margin-top: 2px; }

.btn-create { display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #238636 0%, #2ea043 100%); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 14px; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(35,134,54,0.3); font-family: inherit; }
.btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(35,134,54,0.4); }

.btn-back { width: 40px; height: 40px; border-radius: 10px; background: rgba(48,54,61,0.3); border: 1px solid rgba(48,54,61,0.6); color: #c9d1d9; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-back:hover { background: rgba(48,54,61,0.5); transform: translateX(-3px); }
.btn-back svg { width: 18px; height: 18px; }

/* Stats */
.stats-row { display: flex; gap: 16px; margin-bottom: 32px; }
.stat-chip { background: rgba(22,27,34,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 12px; padding: 14px 24px; display: flex; align-items: center; gap: 12px; }
.stat-chip--active { border-color: rgba(63,185,80,0.3); }
.stat-chip--pending { border-color: rgba(210,153,34,0.3); }
.stat-number { font-size: 24px; font-weight: 800; color: #fff; font-family: 'JetBrains Mono', monospace; }
.stat-label { font-size: 12px; color: #8b949e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }

/* Loader */
.loader-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 0; gap: 20px; color: #8b949e; }
.loader-ring { width: 40px; height: 40px; border: 3px solid rgba(110,64,201,0.2); border-top-color: #9f6eff; border-radius: 50%; animation: spin 0.9s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Grid */
.quizzes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }
.quiz-card { background: rgba(22,27,34,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 18px; padding: 24px; display: flex; flex-direction: column; gap: 16px; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
.quiz-card:hover { border-color: rgba(110,64,201,0.4); transform: translateY(-4px); box-shadow: 0 12px 35px rgba(0,0,0,0.3); }

.quiz-card-header { display: flex; justify-content: space-between; align-items: center; }
.quiz-status { font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.quiz-status.pending { background: rgba(210,153,34,0.1); color: #d29922; border: 1px solid rgba(210,153,34,0.2); }
.quiz-status.active { background: rgba(63,185,80,0.1); color: #3fb950; border: 1px solid rgba(63,185,80,0.2); }
.quiz-status.completed { background: rgba(139,148,158,0.1); color: #8b949e; border: 1px solid rgba(139,148,158,0.2); }
.quiz-date { font-size: 11px; color: #484f58; font-weight: 600; }

.quiz-title { font-size: 18px; font-weight: 700; color: #fff; line-height: 1.3; }
.quiz-desc { font-size: 13px; color: #8b949e; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.quiz-meta { display: flex; flex-wrap: wrap; gap: 10px; }
.meta-chip { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #8b949e; font-weight: 500; }
.meta-chip svg { width: 13px; height: 13px; color: #6e40c9; }

.quiz-actions { display: flex; gap: 10px; margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(48,54,61,0.3); }
.action-btn { 
  flex: 1; padding: 10px 16px; border-radius: 10px; 
  font-size: 13px; font-weight: 700; cursor: pointer; 
  transition: all 0.25s cubic-bezier(0.4,0,0.2,1); 
  display: flex; align-items: center; justify-content: center; 
  gap: 8px; border: none; font-family: inherit;
  min-height: 42px;
}
.action-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

.action-btn--launch { background: linear-gradient(135deg, #6e40c9, #8b5cf6); color: white; box-shadow: 0 4px 12px rgba(110,64,201,0.2); }
.action-btn--launch:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(110,64,201,0.3); }

.action-btn--results { background: rgba(56,139,253,0.1); color: #58a6ff; border: 1px solid rgba(56,139,253,0.2); }
.action-btn--results:hover { background: rgba(56,139,253,0.2); border-color: #58a6ff; transform: translateY(-2px); }

.action-btn--edit { background: rgba(48,54,61,0.4); color: #c9d1d9; border: 1px solid rgba(48,54,61,0.6); }
.action-btn--edit:hover { background: rgba(48,54,61,0.6); color: #fff; transform: translateY(-2px); }

/* Results Layout */
.results-container { background: rgba(22,27,34,0.4); border-radius: 20px; border: 1px solid rgba(48,54,61,0.5); overflow: hidden; height: calc(100vh - 200px); }
.results-layout { display: flex; height: 100%; }

.students-sidebar { width: 300px; border-right: 1px solid rgba(48,54,61,0.5); background: rgba(13,17,23,0.6); display: flex; flex-direction: column; }
.sidebar-title { padding: 20px; font-size: 14px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid rgba(48,54,61,0.3); }
.no-subs { padding: 30px 20px; color: #484f58; font-size: 14px; text-align: center; }
.students-list { flex: 1; overflow-y: auto; }
.student-item { padding: 16px 20px; display: flex; align-items: center; gap: 12px; cursor: pointer; border-bottom: 1px solid rgba(48,54,61,0.2); transition: all 0.2s; }
.student-item:hover { background: rgba(48,54,61,0.2); }
.student-item.active { background: rgba(110,64,201,0.1); border-right: 3px solid #6e40c9; }

.student-avatar, .sh-avatar { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #30363d, #161b22); border: 1px solid rgba(48,54,61,1); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; }
.student-info { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.student-name { font-size: 14px; font-weight: 600; color: #e6edf3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-meta { font-size: 11px; color: #8b949e; }

.student-score { font-size: 12px; font-weight: 800; padding: 4px 8px; border-radius: 6px; }
.score-high { background: rgba(63,185,80,0.1); color: #3fb950; }
.score-mid { background: rgba(210,153,34,0.1); color: #d29922; }
.score-low { background: rgba(248,81,73,0.1); color: #f85149; }

.results-detail { flex: 1; overflow-y: auto; background: rgba(1,4,9,0.3); }
.detail-empty { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #8b949e; padding: 40px; }
.empty-icon-large { font-size: 64px; margin-bottom: 20px; }

.responses-list { padding: 32px; }
.student-header-detail { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid rgba(48,54,61,0.5); }
.sh-left { display: flex; align-items: center; gap: 16px; }
.sh-avatar { width: 48px; height: 48px; font-size: 16px; }
.sh-left h2 { font-size: 22px; font-weight: 800; color: #fff; }
.sh-left p { color: #8b949e; font-size: 14px; }

.sh-score-badge { display: flex; flex-direction: column; align-items: center; padding: 12px 24px; border-radius: 16px; border: 1px solid rgba(48,54,61,0.5); }
.sh-score-val { font-size: 24px; font-weight: 900; }
.sh-score-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8; }

.response-card { background: rgba(22,27,34,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 16px; padding: 20px; margin-bottom: 16px; }
.response-card.correct { border-left: 4px solid #3fb950; }
.response-card.incorrect { border-left: 4px solid #f85149; }
.response-card.open { border-left: 4px solid #6e40c9; }

.rc-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.rc-num { font-size: 12px; font-weight: 800; color: #8b949e; }
.rc-type { font-size: 10px; font-weight: 700; background: rgba(48,54,61,0.5); padding: 2px 8px; border-radius: 4px; color: #c9d1d9; }
.rc-verdict { font-size: 11px; font-weight: 700; margin-left: auto; }
.correct .rc-verdict { color: #3fb950; }
.incorrect .rc-verdict { color: #f85149; }

.rc-question { font-size: 16px; font-weight: 600; color: #fff; line-height: 1.4; margin-bottom: 16px; }
.rc-answer { background: rgba(1,4,9,0.4); padding: 16px; border-radius: 12px; border: 1px solid rgba(48,54,61,0.3); }
.rc-label { font-size: 11px; font-weight: 700; color: #8b949e; display: block; margin-bottom: 4px; }
.rc-text { color: #e6edf3; font-size: 14px; line-height: 1.5; }

.rc-feedback { margin-top: 16px; background: rgba(110,64,201,0.05); border: 1px solid rgba(110,64,201,0.1); padding: 16px; border-radius: 12px; }
.rc-feedback-header { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #a371f7; margin-bottom: 8px; }
.rc-feedback-header svg { width: 14px; height: 14px; }
.rc-feedback-text { color: #d2a8ff; font-size: 13px; line-height: 1.5; }
.rc-score-small { font-size: 11px; color: #8b949e; margin-left: 4px; }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
