<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in">

        <!-- Header -->
        <header class="page-header">
          <div class="header-left">
            <div class="header-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/></svg>
            </div>
            <div>
              <h1 class="header-title">Mes Quiz</h1>
              <p class="header-sub">Évaluations théoriques assignées par vos formateurs</p>
            </div>
          </div>
        </header>

        <!-- Loading -->
        <div v-if="loading" class="loader-state">
          <div class="loader-ring"></div>
          <p>Chargement de vos quiz...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="quizzes.length === 0" class="empty-state animate-in">
          <div class="empty-icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/>
            </svg>
          </div>
          <h2>Aucun quiz pour le moment</h2>
          <p>Vous n'avez aucun quiz actif ou terminé assigné à votre classe.</p>
        </div>

        <!-- Quizzes List -->
        <div v-else class="quizzes-grid">
          <div
            v-for="(quiz, idx) in quizzes"
            :key="quiz.id"
            class="quiz-card animate-in"
            :class="quiz.status.toLowerCase()"
            :style="{ animationDelay: (idx * 0.06) + 's' }"
          >
            <!-- Status Badge -->
            <div class="quiz-status-badge" :class="quiz.is_completed ? 'completed' : quiz.status.toLowerCase()">
              <span class="status-dot"></span>
              {{ 
                quiz.is_completed ? 'Terminé' :
                quiz.status === 'ACTIVE' ? 'En cours' : 
                quiz.status === 'PENDING' ? 'À venir' : 
                'Terminé' 
              }}
            </div>

            <!-- Card Body -->
            <div class="quiz-body">
              <div class="quiz-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/>
                </svg>
              </div>
              <div class="quiz-info">
                <h3 class="quiz-title">{{ quiz.title || 'Quiz sans titre' }}</h3>
                <p class="quiz-desc">{{ quiz.description || 'Évaluation de vos compétences théoriques.' }}</p>
              </div>
            </div>

            <!-- Meta -->
            <div class="quiz-meta">
              <div class="meta-chip" title="Formateur">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                {{ quiz.formateur }}
              </div>
              <div class="meta-chip" title="Nombre de questions">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ quiz.questions_count }} questions
              </div>
              <div class="meta-chip" title="Temps imparti">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                {{ quiz.timer_minutes }} min
              </div>
              <div class="meta-chip" title="Score de réussite">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                </svg>
                Requis : {{ quiz.passing_score }}%
              </div>
            </div>

            <!-- Footer -->
            <div class="quiz-footer">
              <button
                v-if="quiz.status === 'ACTIVE' && !quiz.is_completed"
                @click="startQuiz(quiz)"
                class="btn-start"
              >
                Commencer l'évaluation
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
              <div v-else-if="quiz.status === 'PENDING'" class="pending-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Bientôt disponible
              </div>
              <div v-else class="completed-tag">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Quiz déjà complété
              </div>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarStudent from '../../components/SidebarStudent.vue';
import { QuizService } from '../../services/ApiService';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Apprenant' });
const quizzes = ref([]);
const loading = ref(true);

const fetchQuizzes = async () => {
  loading.value = true;
  try {
    const response = await QuizService.getAssignedQuizzes();
    quizzes.value = response.data || [];
  } catch (err) {
    console.error("Erreur récupération quiz:", err);
  } finally {
    loading.value = false;
  }
};

const startQuiz = (quiz) => {
  router.push(`/student/quiz/${quiz.id}`);
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
.content { max-width: 1000px; margin: 0 auto; padding: 40px 48px; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.header-left { display: flex; align-items: center; gap: 18px; }
.header-icon {
  width: 52px; height: 52px; border-radius: 16px;
  background: linear-gradient(135deg, rgba(56,139,253,0.12), rgba(56,139,253,0.04));
  border: 1px solid rgba(56,139,253,0.2);
  display: flex; align-items: center; justify-content: center;
  color: #388bfd; flex-shrink: 0;
}
.header-icon svg { width: 24px; height: 24px; }
.header-title { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.03em; }
.header-sub { font-size: 14px; color: #8b949e; margin-top: 4px; }

/* Loader */
.loader-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 140px 0; gap: 20px; color: #8b949e; }
.loader-ring { width: 44px; height: 44px; border: 3px solid rgba(56,139,253,0.15); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.9s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty */
.empty-state { text-align: center; padding: 120px 0; }
.empty-icon-wrap {
  width: 88px; height: 88px; margin: 0 auto 24px;
  border-radius: 24px; background: rgba(22,27,34,0.8);
  border: 1px solid rgba(48,54,61,0.5);
  display: flex; align-items: center; justify-content: center; color: #484f58;
}
.empty-icon-wrap svg { width: 40px; height: 40px; }
.empty-state h2 { font-size: 22px; font-weight: 700; color: #e6edf3; margin-bottom: 8px; }
.empty-state p { font-size: 15px; color: #8b949e; max-width: 380px; margin: 0 auto; line-height: 1.6; }

/* Grid */
.quizzes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 22px; }

/* Quiz Card */
.quiz-card {
  background: rgba(22,27,34,0.75);
  border: 1px solid rgba(48,54,61,0.5);
  border-radius: 20px;
  padding: 28px;
  display: flex; flex-direction: column; gap: 20px;
  position: relative;
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.quiz-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 40px rgba(0,0,0,0.35);
}
.quiz-card.active:hover { border-color: rgba(56,139,253,0.4); }
.quiz-card.completed { opacity: 0.7; }
.quiz-card.completed:hover { opacity: 0.85; }

/* Status Badge */
.quiz-status-badge {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 5px 14px; border-radius: 20px;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  align-self: flex-start;
}
.quiz-status-badge.active {
  background: rgba(56,139,253,0.1); color: #58a6ff;
  border: 1px solid rgba(56,139,253,0.2);
}
.quiz-status-badge.pending {
  background: rgba(210,153,34,0.1); color: #d29922;
  border: 1px solid rgba(210,153,34,0.2);
}
.quiz-status-badge.completed {
  background: rgba(139,148,158,0.08); color: #8b949e;
  border: 1px solid rgba(139,148,158,0.15);
}
.status-dot { width: 7px; height: 7px; border-radius: 50%; }
.quiz-status-badge.active .status-dot { background: #58a6ff; box-shadow: 0 0 8px rgba(88,166,255,0.5); animation: blink 2s ease-in-out infinite; }
.quiz-status-badge.pending .status-dot { background: #d29922; }
.quiz-status-badge.completed .status-dot { background: #8b949e; }
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }

/* Card Body */
.quiz-body { display: flex; gap: 18px; }
.quiz-icon-wrap {
  width: 50px; height: 50px; border-radius: 14px;
  background: linear-gradient(135deg, rgba(56,139,253,0.1), rgba(110,64,201,0.08));
  border: 1px solid rgba(56,139,253,0.15);
  display: flex; align-items: center; justify-content: center;
  color: #58a6ff; flex-shrink: 0;
}
.quiz-icon-wrap svg { width: 22px; height: 22px; }
.quiz-info { flex: 1; min-width: 0; }
.quiz-title { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px; line-height: 1.3; }
.quiz-desc { font-size: 13px; color: #8b949e; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

/* Meta */
.quiz-meta { display: flex; gap: 18px; flex-wrap: wrap; }
.meta-chip { display: flex; align-items: center; gap: 7px; font-size: 13px; color: #8b949e; font-weight: 500; }
.meta-chip svg { width: 14px; height: 14px; color: #388bfd; flex-shrink: 0; }

/* Footer */
.quiz-footer { padding-top: 8px; border-top: 1px solid rgba(48,54,61,0.3); }
.btn-start {
  width: 100%; padding: 14px;
  background: linear-gradient(135deg, #238636, #2ea043);
  color: white; border: none; border-radius: 12px;
  font-weight: 700; font-size: 14px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
  font-family: inherit;
  box-shadow: 0 4px 14px rgba(35,134,54,0.25);
}
.btn-start:hover {
  background: linear-gradient(135deg, #2ea043, #3fb950);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(35,134,54,0.35);
}
.btn-start svg { width: 16px; height: 16px; }

.completed-tag, .pending-tag {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 13px; background: rgba(13,17,23,0.5);
  border: 1px solid rgba(48,54,61,0.3); border-radius: 12px;
  font-size: 13px; color: #8b949e; font-weight: 600;
}
.completed-tag svg, .pending-tag svg { width: 16px; height: 16px; color: #484f58; }
.pending-tag { color: #d29922; border-color: rgba(210,153,34,0.2); background: rgba(210,153,34,0.05); }
.pending-tag svg { color: #d29922; }

/* Animations */
.animate-in { animation: fadeInUp 0.5s cubic-bezier(0.16,1,0.3,1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

/* Responsive */
@media (max-width: 600px) {
  .content { padding: 24px 20px; }
  .quizzes-grid { grid-template-columns: 1fr; }
  .header-title { font-size: 22px; }
}
</style>
