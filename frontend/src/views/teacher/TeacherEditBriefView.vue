<template>
  <div class="layout">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Full Loading -->
      <div v-if="isInitialLoading" class="loader-full">
        <div class="loader-ring"></div>
        <p>Chargement en cours...</p>
      </div>

      <div v-else class="studio-layout animate-in">

        <!-- ===== LEFT: FORM EDITOR ===== -->
        <div class="editor-col">

          <!-- Studio Header -->
          <header class="studio-header">
            <div class="studio-header-left">
              <button class="btn-back" @click="goBack">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              </button>
              <div>
                <div class="studio-breadcrumb">Briefs <span>/</span> {{ isNew ? 'Nouveau Brief' : 'Modifier le Brief' }}</div>
                <h1 class="studio-title">{{ isNew ? 'Créer un nouveau projet' : briefForm.title || 'Éditer le projet' }}</h1>
              </div>
            </div>
            <div class="studio-header-actions">
              <div class="status-select-wrapper">
                <span class="status-dot" :class="briefForm.status === 'PUBLISHED' ? 'dot-green' : 'dot-gray'"></span>
                <select v-model="briefForm.status" class="status-select">
                  <option value="DRAFT">Brouillon</option>
                  <option value="PUBLISHED">Publier</option>
                </select>
              </div>
              <button class="btn-save" @click="handleSave" :disabled="isSaving">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                {{ isSaving ? 'Publication...' : 'Publier le Brief' }}
              </button>
            </div>
          </header>

          <!-- Form Sections -->
          <div class="form-body">

            <!-- Section 1: Identity -->
            <div class="form-section">
              <div class="section-label">
                <div class="section-num">01</div>
                <div>
                  <div class="section-title">Identité du Projet</div>
                  <div class="section-desc">Titre, visuel et accroche</div>
                </div>
              </div>
              <div class="section-fields">
                <div class="field-group">
                  <label class="field-label">Titre du Projet <span class="required">*</span></label>
                  <input type="text" v-model="briefForm.title" placeholder="Ex: Clean Architecture avec Laravel" class="field-input" />
                </div>
                <div class="field-group">
                  <label class="field-label">Image de Couverture (URL)</label>
                  <div class="input-with-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM21 15l-5-5L5 21"/></svg>
                    <input type="text" v-model="briefForm.image_url" placeholder="https://..." class="field-input-icon" />
                  </div>
                </div>
                <div class="field-group">
                  <label class="field-label">Description Courte</label>
                  <input type="text" v-model="briefForm.description" placeholder="Une accroche percutante pour les étudiants..." class="field-input" />
                  <span class="field-hint">Visible sur la carte du brief · Max 120 caractères</span>
                </div>
              </div>
            </div>

            <!-- Section 2: Context & Config -->
            <div class="form-section">
              <div class="section-label">
                <div class="section-num">02</div>
                <div>
                  <div class="section-title">Contexte & Configuration</div>
                  <div class="section-desc">Scénario, difficulté et modalités</div>
                </div>
              </div>
              <div class="section-fields">
                <div class="field-group">
                  <label class="field-label">Contexte Détaillé</label>
                  <textarea v-model="briefForm.context" rows="5" class="field-textarea" placeholder="Décrivez le scénario pédagogique, les enjeux et le cadre de travail..."></textarea>
                </div>
                <div class="field-row">
                  <div class="field-group">
                    <label class="field-label">Modalité de Travail</label>
                    <div class="toggle-group">
                      <button class="toggle-btn" :class="{ 'toggle-btn--active': briefForm.modality === 'INDIVIDUAL' }" @click="briefForm.modality = 'INDIVIDUAL'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20v-2a8 8 0 0116 0v2"/></svg>
                        Solo
                      </button>
                      <button class="toggle-btn" :class="{ 'toggle-btn--active': briefForm.modality === 'GROUP' }" @click="briefForm.modality = 'GROUP'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        Squad
                      </button>
                    </div>
                  </div>
                  <div class="field-group">
                    <label class="field-label">Dates du Projet</label>
                    <div class="field-row-inner">
                      <input type="date" v-model="briefForm.date_start" class="field-input field-input--date" />
                      <span class="date-sep">→</span>
                      <input type="date" v-model="briefForm.date_end" class="field-input field-input--date" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section 3: Pedagogical Content -->
            <div class="form-section">
              <div class="section-label">
                <div class="section-num">03</div>
                <div>
                  <div class="section-title">Thématiques & Tags</div>
                  <div class="section-desc">Mots-clés et catégories du projet</div>
                </div>
              </div>
              <div class="section-fields">
                <!-- Tags Management -->
                <div class="field-group">
                  <label class="field-label">Mots-clés (Tags)</label>
                  <div class="tags-input-wrapper">
                    <input 
                      v-model="tagInput" 
                      @keydown.enter.prevent="addTag" 
                      placeholder="Ajouter un tag..." 
                      class="field-input" 
                    />
                    <button @click.prevent="addTag" class="btn-add-tag">Ajouter</button>
                  </div>
                  <div class="tags-display">
                    <div v-for="tag in briefForm.tags" :key="tag" class="tag-badge">
                      {{ tag }}
                      <button @click.prevent="removeTag(tag)" class="btn-remove-tag">×</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>


          </div>
        </div>

        <!-- ===== RIGHT: LIVE PREVIEW ===== -->
        <aside class="preview-col">
          <div class="preview-sticky">
            <div class="preview-label">
              <div class="preview-dot"></div>
              Aperçu Étudiant — Live
            </div>

            <!-- Brief Card Preview -->
            <div class="preview-card">
              <!-- Hero Image -->
              <div class="preview-hero">
                <img
                  :src="briefForm.image_url || 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80'"
                  class="preview-hero-img"
                  @error="$event.target.src = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80'"
                />
                <div class="preview-hero-overlay"></div>
                <div class="preview-hero-badges">
                  <span class="preview-badge preview-badge--modality">
                    {{ briefForm.modality === 'INDIVIDUAL' ? '👤 Solo' : '👥 Squad' }}
                  </span>
                  <span class="preview-badge preview-badge--status" :class="briefForm.status === 'PUBLISHED' ? 'badge-published' : 'badge-draft'">
                    {{ briefForm.status === 'PUBLISHED' ? '● Publié' : '○ Brouillon' }}
                  </span>
                </div>
              </div>

              <!-- Card Body -->
              <div class="preview-body">
                <div class="preview-meta-row">
                </div>

                <h2 class="preview-title">{{ briefForm.title || 'Titre du Projet' }}</h2>
                <p class="preview-desc">{{ briefForm.description || 'Description du projet en cours de rédaction...' }}</p>

                <div class="preview-dates" v-if="briefForm.date_start">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  {{ new Date(briefForm.date_start).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) }}
                  →
                  {{ new Date(briefForm.date_end).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                </div>

                <!-- Context Preview -->
                <div class="preview-section" v-if="briefForm.context">
                  <div class="preview-section-title">Contexte</div>
                  <p class="preview-context">{{ briefForm.context.slice(0, 160) }}{{ briefForm.context.length > 160 ? '...' : '' }}</p>
                </div>

                <!-- Tags Preview -->
                <div class="preview-section" v-if="briefForm.tags.length > 0">
                  <div class="preview-section-title">Mots-clés</div>
                  <div class="preview-tags">
                    <span v-for="tag in briefForm.tags" :key="tag" class="preview-tag-chip">#{{ tag }}</span>
                  </div>
                </div>




                <!-- CTA Preview -->
                <div class="preview-cta">
                  <button class="preview-btn-submit">Soumettre mon travail</button>
                </div>
              </div>
            </div>
          </div>
        </aside>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import SidebarTeacher from '../../components/SidebarTeacher.vue';
import { BriefService } from '../../services/ApiService';

const router = useRouter();
const route  = useRoute();
const user   = ref(JSON.parse(localStorage.getItem('user')) || { first_name: 'Coach', last_name: '' });
const isSaving = ref(false);
const isInitialLoading = ref(false);
const isNew = computed(() => !route.params.id);

const tagInput = ref('');

const briefForm = ref({
  title: '',
  image_url: '',
  description: '',
  context: '',
  modality: 'INDIVIDUAL',
  status: 'DRAFT',
  date_start: new Date().toISOString().split('T')[0],
  date_end: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0],
  tags: []
});

onMounted(async () => {
  if (isNew.value) return;
  isInitialLoading.value = true;
  try {
    const response = await BriefService.getById(route.params.id);
    const briefData = response.data?.data || response.data;
    Object.assign(briefForm.value, briefData);
    
  } catch (err) {
    console.error("[EditBrief] Erreur critique chargement:", err);
    alert("Erreur lors du chargement des données du projet.");
  } finally {
    isInitialLoading.value = false;
  }
});

const addItem = (key) => {
  if (key === 'questions') {
    briefForm.value.questions.push({ type: 'multiple_choice', content: '', options: ['', '', '', ''], correct: 0, scenario: '', ai_feedback: '' });
  } else {
    briefForm.value[key].push('');
  }
};

const removeItem = (key, idx) => briefForm.value[key].splice(idx, 1);

const addTag = () => {
  if (tagInput.value.trim() && !briefForm.value.tags.includes(tagInput.value.trim())) {
    briefForm.value.tags.push(tagInput.value.trim());
    tagInput.value = '';
  }
};

const removeTag = (t) => {
  briefForm.value.tags = briefForm.value.tags.filter(tag => tag !== t);
};

const handleSave = async () => {
  isSaving.value = true;
  const payload = { ...briefForm.value };

  let savedBrief;
  try {
    if (isNew.value) {
      const resp = await BriefService.create(payload);
      savedBrief = resp.data || resp;
    } else {
      const resp = await BriefService.update(route.params.id, payload);
      savedBrief = resp.data || resp;
    }

    const briefId = savedBrief?.id || route.params.id;

    router.push('/teacher/briefs');
  } catch (err) {
    console.error("Erreur Sauvegarde complète:", err);
    const apiError = err.response?.data?.message || err.response?.data?.error || err.message;
    
    if (err.response?.data?.errors) {
      console.table(err.response.data.errors);
      alert("Erreur de validation: " + Object.values(err.response.data.errors).flat().join('\n'));
    } else {
      alert("Erreur lors de l'enregistrement : " + apiError);
    }
  } finally {
    isSaving.value = false;
  }
};

const goBack = () => router.push('/teacher/briefs');
const handleLogout = () => router.push('/login');
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

* { box-sizing: border-box; }

.layout { display: flex; height: 100vh; background: #010409; font-family: 'Inter', system-ui, sans-serif; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: rgba(48,54,61,0.5) transparent; }
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: rgba(48,54,61,0.4); border-radius: 10px; }

/* Loader */
.loader-full { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 20px; color: #8b949e; }
.loader-ring { width: 40px; height: 40px; border: 3px solid rgba(56,139,253,0.2); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.9s linear infinite; }
 

/* Studio Layout */
.studio-layout { display: grid; grid-template-columns: 1fr 420px; min-height: 100%; }

/* Editor Column */
.editor-col { padding: 40px 48px; display: flex; flex-direction: column; gap: 0; border-right: 1px solid rgba(48,54,61,0.4); }

/* Studio Header */
.studio-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 32px; border-bottom: 1px solid rgba(48,54,61,0.3); margin-bottom: 40px; }
.studio-header-left { display: flex; align-items: center; gap: 20px; }
.btn-back { width: 36px; height: 36px; border-radius: 9px; background: rgba(48,54,61,0.3); border: 1px solid rgba(48,54,61,0.5); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
.btn-back:hover { background: rgba(48,54,61,0.5); }
.btn-back svg { width: 16px; height: 16px; color: #8b949e; }
.studio-breadcrumb { font-size: 11px; color: #484f58; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
.studio-breadcrumb span { color: #388bfd; }
.studio-title { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }

.studio-header-actions { display: flex; align-items: center; gap: 12px; }
.status-select-wrapper { display: flex; align-items: center; gap: 8px; padding: 8px 14px; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.5); border-radius: 9px; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-green { background: #3fb950; box-shadow: 0 0 8px rgba(63,185,80,0.5); }
.dot-gray { background: #484f58; }
.status-select { background: transparent; border: none; color: #c9d1d9; font-size: 13px; font-weight: 600; outline: none; cursor: pointer; font-family: inherit; }
.status-select option { background: #161b22; }

.btn-save { display: flex; align-items: center; gap: 8px; background: #238636; color: #fff; border: 1px solid #2ea043; padding: 10px 20px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-save svg { width: 15px; height: 15px; }
.btn-save:hover:not(:disabled) { background: #2ea043; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(35,134,54,0.3); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

/* Form Body */
.form-body { display: flex; flex-direction: column; gap: 0; }
.form-section { display: flex; gap: 40px; padding: 40px 0; border-bottom: 1px solid rgba(48,54,61,0.2); }
.form-section:last-child { border-bottom: none; padding-bottom: 0; }

/* Section Labels */
.section-label { width: 200px; flex-shrink: 0; padding-top: 4px; }
.section-num { font-size: 11px; font-weight: 900; color: rgba(56,139,253,0.4); font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em; margin-bottom: 8px; }
.section-title { font-size: 14px; font-weight: 800; color: #f0f6fc; margin-bottom: 4px; }
.section-desc { font-size: 11px; color: #8b949e; line-height: 1.5; }

/* Section Fields */
.section-fields { flex: 1; display: flex; flex-direction: column; gap: 20px; min-width: 0; }

/* Fields */
.field-group { display: flex; flex-direction: column; gap: 7px; }
.field-group--sm { max-width: 140px; }
.field-label { font-size: 10px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.14em; }
.required { color: #f85149; }

.field-input { background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 9px; padding: 11px 14px; color: #fff; width: 100%; outline: none; font-size: 14px; font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s; }
.field-input:focus { border-color: rgba(56,139,253,0.5); box-shadow: 0 0 0 3px rgba(56,139,253,0.08); }
.field-input::placeholder { color: #484f58; }

.field-textarea { background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 9px; padding: 14px; color: #fff; width: 100%; outline: none; font-size: 14px; font-family: inherit; line-height: 1.7; resize: vertical; transition: border-color 0.2s; }
.field-textarea:focus { border-color: rgba(56,139,253,0.5); box-shadow: 0 0 0 3px rgba(56,139,253,0.08); }
.field-textarea::placeholder { color: #484f58; }

.field-row { display: flex; gap: 16px; }
.field-row-inner { display: flex; align-items: center; gap: 10px; }
.field-input--date { flex: 1; }
.date-sep { color: #484f58; font-size: 14px; flex-shrink: 0; }
.field-hint { font-size: 11px; color: #484f58; }

.input-with-icon, .points-input-wrap { position: relative; display: flex; align-items: center; }
.input-with-icon svg, .points-input-wrap svg { position: absolute; left: 12px; width: 15px; height: 15px; color: #484f58; flex-shrink: 0; pointer-events: none; }
.field-input-icon { background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.6); border-radius: 9px; padding: 11px 14px 11px 38px; color: #fff; width: 100%; outline: none; font-size: 14px; font-family: inherit; transition: border-color 0.2s; }
.field-input-icon:focus { border-color: rgba(56,139,253,0.5); box-shadow: 0 0 0 3px rgba(56,139,253,0.08); }
.field-input-icon::placeholder { color: #484f58; }

/* Difficulty Config Cards */
.config-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.config-card { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 16px 12px; background: rgba(22,27,34,0.5); border: 1px solid rgba(48,54,61,0.4); border-radius: 12px; cursor: pointer; transition: all 0.2s; text-align: center; }
.config-card:hover { border-color: rgba(56,139,253,0.3); background: rgba(56,139,253,0.04); }
.config-card--active { border-color: rgba(56,139,253,0.5); background: rgba(56,139,253,0.08); box-shadow: 0 0 20px rgba(56,139,253,0.08); }
.config-icon { font-size: 24px; line-height: 1; }
.config-name { font-size: 12px; font-weight: 700; color: #f0f6fc; }
.config-sub { font-size: 9px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; font-family: 'JetBrains Mono', monospace; }

/* Toggle Group */
.toggle-group { display: flex; gap: 0; background: rgba(22,27,34,0.5); border: 1px solid rgba(48,54,61,0.5); border-radius: 9px; overflow: hidden; }
.toggle-btn { display: flex; align-items: center; gap: 8px; padding: 10px 18px; background: transparent; border: none; color: #8b949e; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; flex: 1; justify-content: center; }
.toggle-btn svg { width: 15px; height: 15px; }
.toggle-btn--active { background: rgba(56,139,253,0.15); color: #388bfd; }

/* List Builders */
.list-builder { display: flex; flex-direction: column; gap: 8px; padding: 16px; background: rgba(13,17,23,0.5); border: 1px solid rgba(48,54,61,0.3); border-radius: 12px; }
.list-builder-header { display: flex; justify-content: space-between; align-items: center; }
.btn-add-item { display: flex; align-items: center; gap: 5px; background: transparent; border: 1px solid rgba(56,139,253,0.3); color: #388bfd; padding: 5px 12px; border-radius: 7px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.btn-add-item svg { width: 12px; height: 12px; }
.btn-add-item:hover { background: rgba(56,139,253,0.08); }
.list-row { display: flex; align-items: center; gap: 10px; }
.list-row-num { width: 22px; height: 22px; border-radius: 6px; background: rgba(48,54,61,0.4); color: #8b949e; font-size: 10px; font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-family: 'JetBrains Mono', monospace; }
.list-row-icon { font-size: 14px; flex-shrink: 0; }
.list-input { flex: 1; background: rgba(22,27,34,0.6); border: 1px solid rgba(48,54,61,0.4); border-radius: 7px; padding: 9px 12px; color: #fff; font-size: 13px; outline: none; font-family: inherit; transition: border-color 0.15s; }
.list-input:focus { border-color: rgba(56,139,253,0.4); }
.list-input::placeholder { color: #484f58; }
.btn-remove-item { width: 28px; height: 28px; border-radius: 6px; background: transparent; border: 1px solid rgba(248,81,73,0.2); color: #f85149; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; flex-shrink: 0; }
.btn-remove-item svg { width: 12px; height: 12px; }
.btn-remove-item:hover { background: rgba(248,81,73,0.08); border-color: rgba(248,81,73,0.4); }
.list-empty { font-size: 12px; color: #484f58; font-style: italic; padding: 8px 4px; }

/* Quiz Section */
.quiz-cta { display: flex; align-items: center; gap: 20px; padding: 24px; background: rgba(56,139,253,0.04); border: 1.5px dashed rgba(56,139,253,0.25); border-radius: 14px; cursor: pointer; transition: all 0.2s; }
.quiz-cta:hover { background: rgba(56,139,253,0.08); border-color: rgba(56,139,253,0.4); }
.quiz-cta-icon { font-size: 32px; line-height: 1; }
.quiz-cta-text { flex: 1; }
.quiz-cta-title { font-size: 14px; font-weight: 800; color: #388bfd; margin-bottom: 3px; }
.quiz-cta-sub { font-size: 12px; color: #8b949e; }
.quiz-cta-arrow { font-size: 20px; color: #388bfd; opacity: 0.5; }

.questions-stack { display: flex; flex-direction: column; gap: 16px; }
.q-card { background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.5); border-radius: 14px; overflow: hidden; }
.q-card-header { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-bottom: 1px solid rgba(48,54,61,0.3); background: rgba(22,27,34,0.4); }
.q-badge { font-size: 9px; font-weight: 900; color: #388bfd; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); padding: 3px 10px; border-radius: 20px; flex-shrink: 0; font-family: 'JetBrains Mono', monospace; }
.q-title-input { flex: 1; background: transparent; border: none; color: #f0f6fc; font-size: 14px; font-weight: 600; outline: none; font-family: inherit; }
.q-title-input::placeholder { color: #484f58; }
.q-remove-btn { width: 28px; height: 28px; border-radius: 6px; background: transparent; border: 1px solid rgba(248,81,73,0.2); color: #f85149; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; flex-shrink: 0; }
.q-remove-btn svg { width: 13px; height: 13px; }
.q-remove-btn:hover { background: rgba(248,81,73,0.08); }
.q-options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 16px 20px; }
.opt-card { display: flex; align-items: center; gap: 10px; padding: 11px 14px; background: rgba(22,27,34,0.4); border: 1px solid rgba(48,54,61,0.4); border-radius: 9px; cursor: pointer; transition: all 0.15s; }
.opt-card:hover { border-color: rgba(56,139,253,0.3); background: rgba(56,139,253,0.04); }
.opt-card--correct { border-color: rgba(63,185,80,0.4) !important; background: rgba(63,185,80,0.06) !important; }
.opt-radio { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(48,54,61,0.7); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.15s; }
.opt-radio--active { border-color: #3fb950; background: rgba(63,185,80,0.15); }
.opt-radio svg { width: 10px; height: 10px; color: #3fb950; }
.opt-input { background: transparent; border: none; color: #c9d1d9; font-size: 13px; flex: 1; outline: none; font-family: inherit; min-width: 0; }
.opt-input::placeholder { color: #484f58; }
.q-hint { display: flex; align-items: center; gap: 7px; padding: 10px 20px; font-size: 11px; color: #484f58; border-top: 1px solid rgba(48,54,61,0.2); background: rgba(13,17,23,0.3); }
.q-hint svg { width: 13px; height: 13px; flex-shrink: 0; }
.btn-add-question { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; background: transparent; border: 1px dashed rgba(56,139,253,0.3); border-radius: 10px; color: #388bfd; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.btn-add-question svg { width: 14px; height: 14px; }
.btn-add-question:hover { background: rgba(56,139,253,0.06); border-color: rgba(56,139,253,0.5); }

/* Question Type Toggle */
.q-type-toggle { display: flex; gap: 4px; background: rgba(13,17,23,0.6); border: 1px solid rgba(48,54,61,0.5); border-radius: 8px; padding: 3px; flex-shrink: 0; }
.q-type-btn { display: flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 6px; background: transparent; border: none; color: #8b949e; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; white-space: nowrap; }
.q-type-btn svg { width: 12px; height: 12px; flex-shrink: 0; }
.q-type-btn:hover { color: #c9d1d9; }
.q-type-btn--active { background: rgba(56,139,253,0.15); color: #388bfd; }

/* Open-Ended Scenario */
.q-scenario-area { padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
.q-scenario-label { display: flex; align-items: center; gap: 7px; font-size: 10px; font-weight: 800; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; }
.q-scenario-label svg { width: 13px; height: 13px; color: #d29922; flex-shrink: 0; }
.q-scenario-input { background: rgba(22,27,34,0.6); border: 1px solid rgba(210,153,34,0.2); border-radius: 9px; padding: 12px 14px; color: #f0f6fc; font-size: 13px; font-family: inherit; line-height: 1.7; resize: vertical; outline: none; transition: border-color 0.2s; }
.q-scenario-input:focus { border-color: rgba(210,153,34,0.5); }
.q-scenario-input::placeholder { color: #484f58; }
.q-open-hint { display: flex; align-items: center; gap: 7px; font-size: 11px; color: #d29922; opacity: 0.7; }
.q-open-hint svg { width: 13px; height: 13px; flex-shrink: 0; }

/* Preview Column */
.preview-col { background: rgba(13,17,23,0.4); border-left: 1px solid rgba(48,54,61,0.3); }
.preview-sticky { position: sticky; top: 0; padding: 40px 28px; max-height: 100vh; overflow-y: auto; scrollbar-width: none; }
.preview-sticky::-webkit-scrollbar { display: none; }

.preview-label { display: flex; align-items: center; gap: 8px; font-size: 10px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 24px; }
.preview-dot { width: 7px; height: 7px; border-radius: 50%; background: #3fb950; box-shadow: 0 0 8px rgba(63,185,80,0.5); animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

/* Preview Card */
.preview-card { background: linear-gradient(145deg, rgba(22,27,34,0.95), rgba(13,17,23,0.98)); border: 1px solid rgba(48,54,61,0.5); border-radius: 18px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }

.preview-hero { position: relative; height: 180px; overflow: hidden; }
.preview-hero-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.preview-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(13,17,23,0.6)); }
.preview-hero-badges { position: absolute; bottom: 14px; left: 16px; right: 16px; display: flex; justify-content: space-between; }
.preview-badge { font-size: 10px; font-weight: 800; padding: 4px 12px; border-radius: 7px; }
.preview-badge--modality { background: rgba(0,0,0,0.7); color: #c9d1d9; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(8px); }
.preview-badge--status { }
.badge-published { background: rgba(63,185,80,0.2); color: #3fb950; border: 1px solid rgba(63,185,80,0.3); }
.badge-draft { background: rgba(72,79,88,0.5); color: #8b949e; border: 1px solid rgba(72,79,88,0.4); }

.preview-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; }

.preview-meta-row { display: flex; justify-content: space-between; align-items: center; }
.preview-difficulty { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; }
.diff-easy { background: rgba(63,185,80,0.1); color: #3fb950; border: 1px solid rgba(63,185,80,0.2); }
.diff-medium { background: rgba(210,153,34,0.1); color: #d29922; border: 1px solid rgba(210,153,34,0.2); }
.diff-hard { background: rgba(248,81,73,0.1); color: #f85149; border: 1px solid rgba(248,81,73,0.2); }
.preview-points { display: flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 800; color: #388bfd; font-family: 'JetBrains Mono', monospace; }
.preview-points svg { width: 14px; height: 14px; }

.preview-title { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.02em; line-height: 1.25; margin: 0; }
.preview-desc { font-size: 13px; color: #8b949e; line-height: 1.65; margin: 0; }

.preview-dates { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #8b949e; font-weight: 600; }
.preview-dates svg { width: 13px; height: 13px; flex-shrink: 0; }

.preview-section { display: flex; flex-direction: column; gap: 10px; }
.preview-section-title { font-size: 9px; font-weight: 900; color: #484f58; text-transform: uppercase; letter-spacing: 0.18em; }
.preview-context { font-size: 12px; color: #8b949e; line-height: 1.6; margin: 0; }

.preview-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 7px; }
.preview-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: #c9d1d9; line-height: 1.5; }
.list-bullet { color: #3fb950; font-weight: 900; flex-shrink: 0; margin-top: 1px; }

.preview-deliverables { display: flex; flex-wrap: wrap; gap: 7px; }
.deliverable-chip { font-size: 11px; font-weight: 600; color: #c9d1d9; background: rgba(48,54,61,0.4); border: 1px solid rgba(48,54,61,0.6); padding: 4px 10px; border-radius: 7px; }

.preview-quiz-badge { display: flex; align-items: center; gap: 12px; padding: 14px; background: rgba(63,185,80,0.06); border: 1px solid rgba(63,185,80,0.2); border-radius: 12px; }

/* Tags Input */
.tags-input-wrapper { display: flex; gap: 10px; }
.btn-add-tag { background: #388bfd; color: #fff; border: none; padding: 0 16px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.btn-add-tag:hover { background: #1f6feb; }
.tags-display { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.tag-badge { display: flex; align-items: center; gap: 6px; background: rgba(56,139,253,0.1); color: #388bfd; border: 1px solid rgba(56,139,253,0.2); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.btn-remove-tag { background: transparent; border: none; color: #388bfd; font-size: 16px; cursor: pointer; line-height: 1; padding: 0; opacity: 0.6; }
.btn-remove-tag:hover { opacity: 1; }

.preview-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; }
.preview-tag-chip { font-size: 11px; font-weight: 700; color: #388bfd; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); padding: 3px 10px; border-radius: 6px; }

.preview-quiz-badge svg { width: 20px; height: 20px; color: #3fb950; flex-shrink: 0; }
.quiz-badge-title { font-size: 12px; font-weight: 800; color: #3fb950; margin-bottom: 2px; }
.quiz-badge-sub { font-size: 11px; color: #8b949e; }

.preview-cta { padding-top: 4px; }
.preview-btn-submit { width: 100%; padding: 13px; background: linear-gradient(135deg, #388bfd, #1f6feb); color: #fff; border: none; border-radius: 11px; font-size: 13px; font-weight: 800; cursor: default; font-family: inherit; opacity: 0.8; }

/* Animations */
.animate-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
</style>


