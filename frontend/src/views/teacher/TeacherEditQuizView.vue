<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="content animate-in">

        <!-- Header -->
        <header class="studio-header">
          <div class="studio-header-left">
            <button class="btn-back" @click="router.push('/teacher/quizzes')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </button>
            <div>
              <div class="studio-breadcrumb">Quiz <span>/</span> {{ isEdit ? 'Modifier' : 'Nouveau' }}</div>
              <h1 class="studio-title">{{ isEdit ? form.title || 'Modifier le Quiz' : 'Créer un Quiz' }}</h1>
            </div>
          </div>
          <button class="btn-save" @click="saveQuiz" :disabled="isSaving">
            <svg v-if="!isSaving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            <span v-if="isSaving" class="spinner-sm"></span>
            {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </header>

        <!-- Editor Grid -->
        <div class="editor-grid">
          
          <!-- Left: Settings -->
          <aside class="settings-col">
            <div class="card">
              <div class="card-header">
                <div class="card-num">01</div>
                <div class="card-label">Informations</div>
              </div>

              <div class="field-group">
                <label class="field-label">Titre du Quiz <span class="req">*</span></label>
                <input v-model="form.title" type="text" placeholder="Ex: Évaluation Algorithmique" class="field-input" />
              </div>

              <div class="field-group">
                <label class="field-label">Description</label>
                <textarea v-model="form.description" rows="3" placeholder="Instructions pour les étudiants..." class="field-textarea"></textarea>
              </div>

              <div class="field-group">
                <label class="field-label">Classe Assignée <span class="req">*</span></label>
                <select v-model="form.classroom_id" class="field-input">
                  <option value="" disabled>Sélectionner une classe</option>
                  <option v-for="cls in classrooms" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>

              <div class="field-row">
                <div class="field-group">
                  <label class="field-label">Durée (min)</label>
                  <input v-model.number="form.timer_minutes" type="number" min="1" class="field-input" />
                </div>
                <div class="field-group">
                  <label class="field-label">Score Réussite (%)</label>
                  <input v-model.number="form.passing_score" type="number" min="0" max="100" class="field-input" />
                </div>
              </div>
            </div>

            <div class="card card--tips">
              <div class="tips-title">💡 Conseils</div>
              <ul class="tips-list">
                <li>Les QCM sont corrigés automatiquement</li>
                <li>Les questions ouvertes sont évaluées par l'IA</li>
                <li>Chaque question pèse un pourcentage égal</li>
              </ul>
            </div>
          </aside>

          <!-- Right: Questions -->
          <div class="questions-col">
            <div class="questions-toolbar">
              <h2 class="questions-count">Questions <span>({{ form.questions.length }})</span></h2>
              <div class="add-btns">
                <button @click="addQuestion('multiple_choice')" class="btn-add-q">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
                  + QCM
                </button>
                <button @click="addQuestion('open_ended')" class="btn-add-q btn-add-q--alt">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V9.5L14.5 3z"/></svg>
                  + Ouverte
                </button>
              </div>
            </div>

            <!-- Empty -->
            <div v-if="form.questions.length === 0" class="empty-questions">
              <div class="empty-q-icon">📝</div>
              <p>Ajoutez votre première question avec les boutons ci-dessus</p>
            </div>

            <!-- Questions List -->
            <div v-else class="questions-stack">
              <div v-for="(q, idx) in form.questions" :key="idx" class="q-card animate-in">
                <div class="q-card-header">
                  <div class="q-badge">Q{{ idx + 1 }}</div>
                  <span class="q-type-tag" :class="q.type">{{ q.type === 'multiple_choice' ? 'QCM' : 'OUVERTE' }}</span>
                  <button @click="removeQuestion(idx)" class="q-remove-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                  </button>
                </div>

                <div class="field-group">
                  <label class="field-label">Intitulé</label>
                  <input v-model="q.content" type="text" placeholder="Posez votre question ici..." class="field-input" />
                </div>

                <!-- QCM Options -->
                <div v-if="q.type === 'multiple_choice'" class="qcm-options">
                  <label class="field-label">Options <span class="field-hint-inline">(cliquez le radio pour la bonne réponse)</span></label>
                  <div v-for="(opt, oIdx) in q.options" :key="oIdx" class="opt-row">
                    <label class="opt-radio-wrap" :class="{ 'opt-radio--active': q.correct === oIdx }">
                      <input type="radio" :name="'correct-'+idx" :checked="q.correct === oIdx" @change="q.correct = oIdx" />
                      <span class="opt-radio-dot"></span>
                    </label>
                    <input v-model="q.options[oIdx]" type="text" :placeholder="'Option ' + String.fromCharCode(65 + oIdx)" class="field-input opt-input" />
                    <button v-if="q.options.length > 2" @click="q.options.splice(oIdx, 1)" class="opt-del">×</button>
                  </div>
                  <button @click="q.options.push('')" class="btn-add-opt">+ Ajouter une option</button>
                </div>

                <!-- Open-Ended -->
                <div v-if="q.type === 'open_ended'" class="open-section">
                  <div class="field-group">
                    <label class="field-label">Contexte / Scénario IA</label>
                    <textarea v-model="q.scenario" rows="3" placeholder="Donnez du contexte pour l'évaluation IA..." class="field-textarea"></textarea>
                  </div>
                  <div class="ai-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    L'IA évaluera la réponse de l'étudiant
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import api from '../../services/api';
import { QuizService } from '../../services/ApiService';

const route = useRoute();
const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Coach' });
const isEdit = computed(() => !!route.params.id);

const classrooms = ref([]);
const isSaving = ref(false);

const form = ref({
  title: '',
  description: '',
  classroom_id: '',
  timer_minutes: 30,
  passing_score: 75,
  questions: []
});

onMounted(async () => {
  // Load Classrooms
  try {
    const response = await api.get('/admin/classrooms');
    classrooms.value = response.data.data || [];
  } catch {
    try {
      const response = await api.get('/classrooms/my');
      classrooms.value = response.data.data || response.data || [];
    } catch (err) {
      console.error("Erreur chargement classes:", err);
    }
  }

  // Load Quiz if editing
  if (isEdit.value) {
    try {
      const res = await QuizService.getSessionById(route.params.id);
      const quiz = res.data;
      form.value = {
        title: quiz.title,
        description: quiz.description,
        classroom_id: quiz.classroom_id,
        timer_minutes: quiz.timer_minutes,
        passing_score: quiz.passing_score,
        questions: quiz.questions.map(q => {
          const options = q.context_data?.options || ['', '', '', ''];
          let correctIdx = 0;
          if (q.type === 'multiple_choice') {
            correctIdx = options.indexOf(q.correct_answer);
            if (correctIdx === -1) correctIdx = 0;
          }
          return {
            id: q.id,
            type: q.type,
            content: q.content,
            options: options,
            correct: correctIdx,
            scenario: q.context_data?.scenario || ''
          };
        })
      };
    } catch (err) {
      console.error("Erreur chargement quiz:", err);
      alert("Impossible de charger les données du quiz.");
    }
  }
});

const addQuestion = (type) => {
  form.value.questions.push({
    type,
    content: '',
    options: type === 'multiple_choice' ? ['', '', '', ''] : [],
    correct: 0,
    scenario: ''
  });
};

const removeQuestion = (idx) => form.value.questions.splice(idx, 1);

const saveQuiz = async () => {
  if (!form.value.title || !form.value.classroom_id || form.value.questions.length === 0) {
    alert("Veuillez remplir le titre, choisir une classe et ajouter au moins une question.");
    return;
  }

  isSaving.value = true;
  try {
    const payload = {
      title: form.value.title,
      description: form.value.description,
      classroom_id: form.value.classroom_id,
      timer_minutes: form.value.timer_minutes,
      passing_score: form.value.passing_score,
      questions: form.value.questions.map(q => ({
        content: q.content,
        type: q.type,
        correct_answer: q.type === 'multiple_choice' ? (q.options[q.correct] || '') : '',
        context_data: JSON.stringify(
          q.type === 'multiple_choice'
            ? { options: q.options }
            : { scenario: q.scenario || q.content }
        )
      }))
    };

    if (isEdit.value) {
      await QuizService.updateSession(route.params.id, payload);
    } else {
      await QuizService.createSession(payload);
    }
    router.push('/teacher/quizzes');
  } catch (err) {
    console.error("Erreur sauvegarde:", err);
    const msg = err.response?.data?.message || err.response?.data?.error || err.message;
    alert("Erreur : " + msg);
  } finally {
    isSaving.value = false;
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.5) transparent; }
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }
.content { max-width: 1200px; margin: 0 auto; padding: 40px 48px; }

/* Studio Header */
.studio-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 32px; border-bottom: 1px solid rgba(48,54,61,0.3); margin-bottom: 36px; }
.studio-header-left { display: flex; align-items: center; gap: 20px; }
.btn-back { width: 36px; height: 36px; border-radius: 9px; background: rgba(48,54,61,0.3); border: 1px solid rgba(48,54,61,0.5); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; flex-shrink: 0; color: #8b949e; }
.btn-back:hover { background: rgba(48,54,61,0.5); }
.btn-back svg { width: 16px; height: 16px; }
.studio-breadcrumb { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
.studio-breadcrumb span { color: #6e40c9; }
.studio-title { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }

.btn-save { display: flex; align-items: center; gap: 8px; background: #238636; color: #fff; border: 1px solid #2ea043; padding: 10px 20px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-save svg { width: 15px; height: 15px; }
.btn-save:hover:not(:disabled) { background: #2ea043; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(35,134,54,0.3); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* Editor Grid */
.editor-grid { display: grid; grid-template-columns: 340px 1fr; gap: 32px; align-items: start; }

/* Cards */
.card { background: rgba(22,27,34,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 16px; padding: 24px; margin-bottom: 20px; }
.card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.card-num { font-size: 11px; font-weight: 900; color: rgba(110,64,201,0.5); font-family: 'JetBrains Mono', monospace; }
.card-label { font-size: 14px; font-weight: 800; color: #f0f6fc; }

.card--tips { border-color: rgba(210,153,34,0.2); background: rgba(210,153,34,0.04); }
.tips-title { font-size: 14px; font-weight: 700; color: #d29922; margin-bottom: 12px; }
.tips-list { padding-left: 18px; margin: 0; color: #8b949e; font-size: 13px; line-height: 1.8; }

/* Fields */
.field-group { margin-bottom: 18px; }
.field-label { display: block; font-size: 10px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.14em; margin-bottom: 7px; }
.req { color: #f85149; }
.field-hint-inline { font-size: 9px; color: #484f58; text-transform: none; letter-spacing: 0; font-weight: 600; }

.field-input { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 9px; padding: 11px 14px; color: #fff; width: 100%; outline: none; font-size: 14px; font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s; }
.field-input:focus { border-color: rgba(110,64,201,0.5); box-shadow: 0 0 0 3px rgba(110,64,201,0.08); }
.field-input::placeholder { color: #484f58; }
select.field-input { cursor: pointer; }
select.field-input option { background: #161b22; }

.field-textarea { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 9px; padding: 14px; color: #fff; width: 100%; outline: none; font-size: 14px; font-family: inherit; line-height: 1.7; resize: vertical; transition: border-color 0.2s; }
.field-textarea:focus { border-color: rgba(110,64,201,0.5); }
.field-textarea::placeholder { color: #484f58; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* Questions Column */
.questions-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.questions-count { font-size: 18px; font-weight: 800; color: #f0f6fc; }
.questions-count span { color: #6e40c9; font-weight: 600; }

.add-btns { display: flex; gap: 10px; }
.btn-add-q { display: flex; align-items: center; gap: 6px; background: #238636; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-add-q svg { width: 15px; height: 15px; }
.btn-add-q:hover { background: #2ea043; }
.btn-add-q--alt { background: rgba(48,54,61,0.5); border: 1px solid rgba(48,54,61,0.6); color: #c9d1d9; }
.btn-add-q--alt:hover { background: rgba(48,54,61,0.7); }

.empty-questions { background: rgba(22,27,34,0.5); border: 2px dashed rgba(48,54,61,0.5); border-radius: 16px; padding: 80px 40px; text-align: center; }
.empty-q-icon { font-size: 40px; margin-bottom: 16px; }
.empty-questions p { color: #8b949e; font-size: 14px; }

/* Question Cards */
.questions-stack { display: flex; flex-direction: column; gap: 16px; }
.q-card { background: rgba(22,27,34,0.8); border: 1px solid rgba(48,54,61,0.5); border-radius: 14px; padding: 24px; }
.q-card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.q-badge { font-size: 9px; font-weight: 900; color: #9f6eff; background: rgba(110,64,201,0.1); border: 1px solid rgba(110,64,201,0.2); padding: 3px 10px; border-radius: 20px; font-family: 'JetBrains Mono', monospace; }
.q-type-tag { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
.q-type-tag.multiple_choice { background: rgba(56,139,253,0.1); color: #388bfd; border: 1px solid rgba(56,139,253,0.2); }
.q-type-tag.open_ended { background: rgba(210,153,34,0.1); color: #d29922; border: 1px solid rgba(210,153,34,0.2); }
.q-remove-btn { margin-left: auto; width: 28px; height: 28px; border-radius: 6px; background: transparent; border: 1px solid rgba(248,81,73,0.2); color: #f85149; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; }
.q-remove-btn svg { width: 13px; height: 13px; }
.q-remove-btn:hover { background: rgba(248,81,73,0.08); border-color: rgba(248,81,73,0.4); }

/* QCM Options */
.qcm-options { display: flex; flex-direction: column; gap: 10px; }
.opt-row { display: flex; align-items: center; gap: 10px; }
.opt-radio-wrap { position: relative; width: 20px; height: 20px; flex-shrink: 0; cursor: pointer; }
.opt-radio-wrap input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
.opt-radio-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid rgba(48,54,61,0.7); display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.opt-radio--active .opt-radio-dot { border-color: #3fb950; background: rgba(63,185,80,0.15); }
.opt-radio--active .opt-radio-dot::after { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #3fb950; }
.opt-input { flex: 1; }
.opt-del { width: 28px; height: 28px; border-radius: 6px; background: transparent; border: 1px solid rgba(248,81,73,0.15); color: #f85149; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; flex-shrink: 0; transition: all 0.15s; }
.opt-del:hover { background: rgba(248,81,73,0.08); }
.btn-add-opt { align-self: flex-start; background: transparent; border: 1px dashed rgba(48,54,61,0.5); color: #8b949e; padding: 6px 14px; border-radius: 7px; cursor: pointer; font-size: 12px; font-weight: 600; margin-top: 6px; transition: all 0.15s; font-family: inherit; }
.btn-add-opt:hover { color: #fff; border-color: rgba(110,64,201,0.4); }

/* Open-Ended */
.open-section { display: flex; flex-direction: column; gap: 12px; }
.ai-hint { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #d29922; font-weight: 600; opacity: 0.8; }
.ai-hint svg { width: 14px; height: 14px; flex-shrink: 0; }

/* Spinner */
.spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.2); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Animations */
.animate-in { animation: fadeInUp 0.4s cubic-bezier(0.16,1,0.3,1); }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>
