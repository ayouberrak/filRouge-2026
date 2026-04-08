<template>
  <div class="quiz-focus">

    <!-- Header -->
    <header class="quiz-header">
      <div class="header-left animate-in">
        <button @click="confirmExit" class="btn-exit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 19l-7-7 7-7"/>
          </svg>
          <span class="exit-lbl">Abandonner</span>
        </button>
        <div class="header-divider"></div>
        <div class="quiz-meta">
          <span class="meta-label">Évaluation Théorique</span>
          <h1 class="meta-title">{{ briefTitle }}</h1>
        </div>
      </div>

      <div class="header-center animate-in" style="animation-delay: 0.1s">
        <div class="timer" :class="{ 'timer--low': timerSeconds < 60 }">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
          </svg>
          <span class="timer-value">{{ formatTime(timerSeconds) }}</span>
        </div>
      </div>

      <div class="header-right animate-in" style="animation-delay: 0.15s">
        <span class="q-label">Progression</span>
        <span class="q-counter">{{ currentQuestionIndex + 1 }}<span class="q-total"> / {{ questions.length }}</span></span>
      </div>
    </header>

    <!-- Progress bar -->
    <div class="progress-track animate-in">
      <div class="progress-fill" :style="{ width: progressPercentage + '%' }">
        <div class="progress-glow"></div>
      </div>
    </div>

    <!-- Main -->
    <main class="quiz-main">
      <Transition name="slide" mode="out-in">

        <!-- Question -->
        <div v-if="!showResult" :key="currentQuestionIndex" class="question-wrap">
          <div class="question-card">
            
            <div class="question-meta">
              <span class="q-type">{{ currentQuestion?.type === 'multiple_choice' ? 'QCM' : 'Évaluation IA : Mise en situation' }}</span>
              <span class="q-points">{{ currentQuestion?.points }} points de certification</span>
            </div>

            <h2 class="question-text">{{ currentQuestion?.content }}</h2>

            <!-- Multiple Choice -->
            <div v-if="currentQuestion?.type === 'multiple_choice'" class="options">
              <button
                v-for="(opt, idx) in currentQuestion?.options"
                :key="idx"
                class="option-item"
                :class="{ 'option-item--selected': selectedOpt === idx }"
                :disabled="isSubmitting"
                @click="selectOption(idx)"
              >
                <span class="option-letter">{{ String.fromCharCode(65 + idx) }}</span>
                <span class="option-text">{{ opt }}</span>
                <div class="option-ring">
                  <div class="option-dot"></div>
                </div>
              </button>
            </div>

            <!-- Open-Ended / Mise en situation -->
            <div v-else class="open-ended-area">
              <div v-if="currentQuestion?.context_data?.scenario" class="scenario-block">
                <div class="scenario-label">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                  Cas Pratique
                </div>
                <p class="scenario-text">{{ currentQuestion.context_data.scenario }}</p>
              </div>
              <div class="open-textarea-wrap">
                <div class="open-textarea-label">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Votre démonstration (Analyse IA)
                </div>
                <textarea
                  v-model="openEndedText"
                  class="nadi-textarea"
                  placeholder="Argumentez ici votre solution technique..."
                  :disabled="isSubmitting"
                  rows="8"
                ></textarea>
                <div class="open-textarea-hint">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                  L'intelligence artificielle YouCode va croiser votre réponse avec les compétences visées.
                </div>
              </div>
            </div>

            <!-- Footer Action -->
            <div class="question-footer">
              <button
                class="btn-nadi-primary btn-next-large"
                :class="{ 'btn-finish-mode': isLastQuestion }"
                :disabled="(currentQuestion?.type === 'open_ended' ? !openEndedText.trim() : selectedOpt === null) || isSubmitting"
                @click="handleNext"
              >
                <div v-if="isSubmitting" class="spinner-sm"></div>
                <span>{{ isLastQuestion ? 'Clôturer la certification' : 'Valider & continuer' }}</span>
                <svg v-if="!isSubmitting && !isLastQuestion" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14m-7-7l7 7-7 7"/>
                </svg>
                <svg v-if="!isSubmitting && isLastQuestion" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
              </button>
            </div>

          </div>
        </div>

        <!-- Result View -->
        <div v-else key="result" class="result-wrap">
          <div class="result-card">

            <div class="result-icon-hexa">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
              </svg>
            </div>

            <h2 class="result-title">Traitement IA en cours...</h2>
            <p class="result-subtitle">Vos données sont actuellement analysées. Vous pourrez consulter le verdict détaillé de l'IA sur la page de votre rendu.</p>

            <div class="score-ring">
              <span class="score-value">{{ totalScore }}</span>
              <span class="score-unit">Points YC</span>
            </div>

            <button @click="finishQuiz" class="btn-nadi-secondary">
              Quitter le flux d'évaluation
            </button>

          </div>
        </div>

      </Transition>
    </main>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';

// ─── Route & Router ───────────────────────────────────────────────────────────
const route     = useRoute();
const router    = useRouter();
const sessionId = route.params.id;

// ─── State ────────────────────────────────────────────────────────────────────
const questions            = ref([]);
const currentQuestionIndex = ref(0);
const selectedOpt          = ref(null);
const openEndedText        = ref('');
const isSubmitting         = ref(false);
const showResult           = ref(false);
const timerSeconds         = ref(0);
const timerInterval        = ref(null);
const briefTitle           = ref('Chargement des données...');
const totalScore           = ref(0);

// ─── Computed ─────────────────────────────────────────────────────────────────
const currentQuestion = computed(() =>
  questions.value[currentQuestionIndex.value]
);
const isLastQuestion = computed(() =>
  currentQuestionIndex.value === questions.value.length - 1
);
const progressPercentage = computed(() => {
  if (!questions.value.length) return 0;
  return ((currentQuestionIndex.value + 1) / questions.value.length) * 100;
});

// ─── Timer ────────────────────────────────────────────────────────────────────
const formatTime = (seconds) => {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
};

const startTimer = () => {
  timerInterval.value = setInterval(() => {
    if (timerSeconds.value > 0) {
      timerSeconds.value--;
    } else {
      clearInterval(timerInterval.value);
      showResult.value = true;
    }
  }, 1000);
};

// ─── Methods ──────────────────────────────────────────────────────────────────
const selectOption = (idx) => {
  if (!isSubmitting.value) selectedOpt.value = idx;
};

const handleNext = async () => {
  const isOpenEnded = currentQuestion.value?.type === 'open_ended';
  if (isOpenEnded) {
    if (!openEndedText.value.trim() || isSubmitting.value) return;
  } else {
    if (selectedOpt.value === null || isSubmitting.value) return;
  }

  isSubmitting.value = true;
  try {
    const responseText = isOpenEnded
      ? openEndedText.value
      : currentQuestion.value.options[selectedOpt.value];

    await api.post('/quizzes/responses', {
      question_id:   currentQuestion.value.id,
      response_text: responseText,
    });

    if (isLastQuestion.value) {
      const briefId = route.query.briefId || localStorage.getItem('current_brief_id') || 1;
      const res     = await api.get(`/quizzes/briefs/${briefId}/validate`);
      totalScore.value = res.data.status?.score ?? '--';
      clearInterval(timerInterval.value);
      showResult.value = true;
    } else {
      currentQuestionIndex.value++;
      selectedOpt.value = null;
      openEndedText.value = '';
    }
  } catch (err) {
    const errorMsg = err.response?.data?.error || err.message || 'Erreur critique interne';
    console.error('Submit response error:', err);
    alert(`Échec de communication réseau : ${errorMsg}`);
  } finally {
    isSubmitting.value = false;
  }
};

const confirmExit = () => {
  if (confirm('Voulez-vous vraiment aborter cette certification ? Vos calculs IA non soumis seront perdus.')) {
    router.push('/submissions');
  }
};

const finishQuiz = () => {
  router.push('/submissions');
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
const loadQuizData = async () => {
  try {
    const res          = await api.get(`/quizzes/sessions/${sessionId}/questions`);
    questions.value    = res.data.data;
    timerSeconds.value = 15 * 60;
    
    // Simulate getting brief title
    briefTitle.value = 'Mise en situation technique';

    startTimer();
  } catch (err) {
    console.error('Quiz fetch error:', err);
    alert('Accès refusé ou session obsolète.');
    router.push('/submissions');
  }
};

onMounted(loadQuizData);
onUnmounted(() => { if (timerInterval.value) clearInterval(timerInterval.value); });
</script>

<style scoped>
/* ─── Layout ────────────────────────────────────────────────────────────────── */
.quiz-focus {
  position: fixed; inset: 0; background: #010409; color: #c9d1d9; z-index: 9999;
  display: flex; flex-direction: column; font-family: 'Inter', system-ui, sans-serif;
}

/* ─── Header ────────────────────────────────────────────────────────────────── */
.quiz-header {
  height: 80px; display: flex; align-items: center; justify-content: space-between;
  padding: 0 40px; background: rgba(1, 4, 9, 0.8); backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; position: relative; z-index: 10;
}

.header-left { display: flex; align-items: center; gap: 24px; }
.btn-exit {
  display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; color: #8b949e;
  font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
  cursor: pointer; transition: all 0.2s;
}
.btn-exit:hover { background: rgba(248, 81, 73, 0.1); color: #ff7b72; border-color: rgba(248, 81, 73, 0.3); }
.btn-exit svg { width: 16px; height: 16px; }

.header-divider { width: 1px; height: 32px; background: rgba(255,255,255,0.1); flex-shrink: 0; }
.quiz-meta { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 10px; font-weight: 800; color: #79c0ff; text-transform: uppercase; letter-spacing: 0.15em; }
.meta-title { font-size: 18px; font-weight: 700; color: #fff; letter-spacing: -0.01em;}

/* Timer */
.timer {
  display: flex; align-items: center; gap: 10px; padding: 8px 24px; background: rgba(56, 139, 253, 0.05);
  border: 1px solid rgba(56, 139, 253, 0.15); border-radius: 20px; color: #79c0ff; transition: all 0.3s;
}
.timer--low { background: rgba(248, 81, 73, 0.08); border-color: rgba(248, 81, 73, 0.3); color: #ff7b72; animation: pulse-red 1.5s infinite; }
.timer svg { width: 18px; height: 18px; stroke-width: 2.5;}
.timer-value { font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 800; letter-spacing: 0.05em; }

.header-right { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;}
.q-label  { font-size: 10px; color: #484f58; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; }
.q-counter { font-size: 24px; font-weight: 800; color: #fff; font-family: 'JetBrains Mono', monospace;}
.q-total  { font-size: 16px; color: #484f58; font-weight: 600;}

/* ─── Progress bar ───────────────────────────────────────────────────────────── */
.progress-track { height: 4px; background: rgba(255,255,255,0.05); flex-shrink: 0; width: 100%; position: relative; }
.progress-fill { height: 100%; background: #388bfd; transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1); position: relative; }
.progress-glow { position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 20px; height: 10px; background: #79c0ff; filter: blur(5px); border-radius: 50%; }

/* ─── Main Content ──────────────────────────────────────────────────────────── */
.quiz-main { flex: 1; display: flex; align-items: flex-start; justify-content: center; padding: 60px 40px; overflow-y: auto; scrollbar-width: none;}
.quiz-main::-webkit-scrollbar { display: none; }
.question-wrap { width: 100%; max-width: 800px; margin: 0 auto;}

.question-card { background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 48px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); backdrop-filter: blur(10px);}
.question-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.q-type { font-size: 12px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.15em; display: flex; align-items: center; gap: 8px;}
.q-type::before { content: ''; width: 8px; height: 8px; background: #388bfd; border-radius: 50%; box-shadow: 0 0 10px rgba(56,139,253,0.5);}
.q-points { font-size: 12px; font-weight: 800; color: #56d364; background: rgba(63,185,80,0.1); padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(63,185,80,0.2);}

.question-text { font-size: 26px; font-weight: 700; color: #fff; line-height: 1.5; letter-spacing: -0.02em; margin-bottom: 40px; }

/* ─── Options ───────────────────────────────────────────────────────────────── */
.options { display: flex; flex-direction: column; gap: 16px; margin-bottom: 48px; }

.option-item {
  display: flex; align-items: center; gap: 20px; padding: 20px 24px; background: #010409;
  border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; text-align: left; cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden;
}
.option-item::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent); transform: translateX(-100%); transition: transform 0.5s; }
.option-item:hover:not(:disabled) { border-color: rgba(56,139,253,0.4); box-shadow: 0 4px 20px rgba(56,139,253,0.1); transform: translateY(-2px); }
.option-item:hover:not(:disabled)::before { transform: translateX(100%); }

.option-item--selected {
  background: rgba(56,139,253,0.05); border-color: #388bfd !important; box-shadow: 0 0 0 1px #388bfd, 0 8px 30px rgba(56,139,253,0.15);
}

.option-letter {
  width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.05);
  display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; font-family: 'JetBrains Mono', monospace;
  color: #8b949e; flex-shrink: 0; transition: all 0.2s; border: 1px solid rgba(255,255,255,0.05);
}
.option-item--selected .option-letter { background: #388bfd; color: #fff; border-color: #79c0ff; box-shadow: 0 0 15px rgba(56,139,253,0.5);}

.option-text { font-size: 16px; font-weight: 500; color: #c9d1d9; flex: 1; line-height: 1.5; transition: color 0.2s;}
.option-item--selected .option-text { color: #fff; font-weight: 600; }

.option-ring {
  width: 24px; height: 24px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s;
}
.option-item--selected .option-ring { border-color: #388bfd; }
.option-dot { width: 12px; height: 12px; border-radius: 50%; background: #388bfd; opacity: 0; transform: scale(0.5); transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.option-item--selected .option-dot { opacity: 1; transform: scale(1); box-shadow: 0 0 10px rgba(56,139,253,0.5);}

/* ─── Open-Ended ─────────────────────────────────────────────────────────────── */
.open-ended-area { display: flex; flex-direction: column; gap: 24px; margin-bottom: 48px;}
.scenario-block { padding: 24px; background: rgba(56,139,253,0.05); border: 1px solid rgba(56,139,253,0.15); border-radius: 14px; border-left: 4px solid #388bfd;}
.scenario-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: #79c0ff; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 12px; }
.scenario-label svg { width: 16px; height: 16px; stroke-width: 2.5;}
.scenario-text { font-size: 15px; color: #e6edf3; line-height: 1.8; font-weight: 400;}

.open-textarea-wrap { display: flex; flex-direction: column; gap: 12px; }
.open-textarea-label { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; }
.open-textarea-label svg { width: 16px; height: 16px; color: #56d364; stroke-width: 2.5; }

.nadi-textarea {
  background: #010409; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
  padding: 20px; color: #fff; font-size: 15px; font-family: 'Inter', sans-serif; line-height: 1.7; width: 100%; resize: vertical; outline: none; transition: all 0.2s; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
}
.nadi-textarea:focus { border-color: #56d364; box-shadow: 0 0 0 4px rgba(86,211,100,0.15), inset 0 2px 5px rgba(0,0,0,0.2); }
.nadi-textarea::placeholder { color: #484f58; font-weight: 500; font-style: italic;}

.open-textarea-hint { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #56d364; font-weight: 600;}
.open-textarea-hint svg { width: 14px; height: 14px; }

/* ─── Actions ───────────────────────────────────────────────────────────────── */
.question-footer { padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: flex-end;}
.btn-nadi-primary {
  display: flex; align-items: center; justify-content: center; gap: 12px; padding: 16px 32px;
  background: #238636; color: white; border: 1px solid #2ea043; border-radius: 12px;
  font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(35,134,54,0.2);
}
.btn-nadi-primary:hover:not(:disabled) { background: #2ea043; border-color: #3fb950; box-shadow: 0 8px 25px rgba(35,134,54,0.3); transform: translateY(-2px); }
.btn-nadi-primary:disabled { opacity: 0.5; background: rgba(255,255,255,0.05); border-color: transparent; color: #8b949e; cursor: not-allowed; box-shadow: none; }
.btn-nadi-primary svg { width: 18px; height: 18px; }

.btn-finish-mode { background: #d29922; border-color: #e3b341; box-shadow: 0 4px 15px rgba(210,153,34,0.2); color: #010409;}
.btn-finish-mode:hover:not(:disabled) { background: #e3b341; border-color: #f8e3a1; box-shadow: 0 8px 25px rgba(210,153,34,0.3); }

/* ─── Result View ────────────────────────────────────────────────────────────── */
.result-wrap { width: 100%; max-width: 500px; margin: 0 auto; }
.result-card {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(56,139,253,0.3); border-radius: 20px;
  padding: 60px 40px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 20px; box-shadow: 0 0 50px rgba(56,139,253,0.1);
}

.result-icon-hexa {
  width: 80px; height: 80px; border-radius: 20px; background: rgba(56,139,253,0.1);
  border: 1px solid rgba(56,139,253,0.3); display: flex; align-items: center; justify-content: center; color: #79c0ff; margin-bottom: 10px; animation: pulse-blue 2s infinite;
}
.result-icon-hexa svg { width: 40px; height: 40px; stroke-width: 2;}

.result-title { font-size: 26px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
.result-subtitle { font-size: 14px; color: #8b949e; line-height: 1.6; max-width: 320px; margin-bottom: 10px;}

.score-ring { display: none; } /* On cache l'ancien cercle de score pour ce loader */

.btn-nadi-secondary {
  padding: 14px 32px; background: rgba(255,255,255,0.03); color: #c9d1d9; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-top: 10px;
}
.btn-nadi-secondary:hover { background: rgba(255,255,255,0.08); color: #fff; border-color: rgba(255,255,255,0.2); }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes pulse-red  { 0%, 100% { opacity: 1; box-shadow: 0 0 10px rgba(248,81,73,0.3); } 50% { opacity: 0.6; box-shadow: 0 0 2px rgba(248,81,73,0.1); } }
@keyframes pulse-blue { 0%, 100% { box-shadow: 0 0 20px rgba(56,139,253,0.2); transform: scale(1); } 50% { box-shadow: 0 0 50px rgba(56,139,253,0.4); transform: scale(1.05); } }
@keyframes spin       { to { transform: rotate(360deg); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.spinner-sm { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.2); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }

/* ─── Transitions ───────────────────────────────────────────────────────────── */
.slide-enter-active, .slide-leave-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-enter-from { opacity: 0; transform: translateX(40px) scale(0.98); }
.slide-leave-to   { opacity: 0; transform: translateX(-40px) scale(0.98); }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .quiz-header { padding: 0 20px; }
  .header-divider, .meta-title { display: none; }
  .quiz-main { padding: 30px 20px; }
  .question-card { padding: 30px 24px; }
  .question-text { font-size: 20px; }
  .option-item { padding: 16px; }
  .btn-exit .exit-lbl { display: none; }
}
</style>