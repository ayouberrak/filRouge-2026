<template>
  <div class="layout">

    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">

      <!-- Hero Section -->
      <div class="hero">
        <img
          :src="brief.image_url || 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=1400&auto=format&fit=crop'"
          class="hero-img"
          :alt="brief.title"
        />
        <div class="hero-overlay"></div>

        <!-- Safe Area Wrapper for alignment -->
        <div class="hero-wrapper">
          <!-- Top nav -->
          <div class="hero-nav">
            <div class="hero-nav-left animate-in" style="animation-delay: 0.05s">
              <router-link to="/briefs" class="back-btn" title="Retour aux briefs">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M15 19l-7-7 7-7"/>
                </svg>
              </router-link>
              <span class="hero-nav-label">Détail du brief</span>
            </div>
            <span class="session-badge animate-in" style="animation-delay: 0.1s" v-if="brief.status === 'IN_PROGRESS'">
              <span class="live-dot"></span> Mission en cours
            </span>
          </div>

          <!-- Hero Content -->
          <div class="hero-content animate-in" style="animation-delay: 0.15s">
            <div class="hero-badges">
              <span class="badge badge--neutral">{{ brief.difficulty || 'Expert' }}</span>
              <span class="badge badge--amber">{{ brief.modality || 'Individuel' }}</span>
            </div>
            <h1 class="hero-title">{{ brief.title }}</h1>
            
            <div class="hero-meta">
              <div class="meta-item">
                <img :src="brief.formateur_avatar || 'https://i.pravatar.cc/100?img=12'" class="meta-avatar" alt="coach" />
                <div class="meta-text">
                  <span class="meta-label">Encadrant</span>
                  <span class="meta-value">{{ brief.formateur_name || 'Coach Formateur' }}</span>
                </div>
              </div>
              <div class="meta-divider"></div>
              <div class="meta-item">
                <div class="meta-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="meta-text">
                  <span class="meta-label">Échéance</span>
                  <span class="meta-value meta-value--amber">{{ brief.date_end_f || 'Non définie' }}</span>
                </div>
              </div>
              <div class="meta-divider"></div>
              <div class="meta-item">
                <div class="meta-icon-box meta-icon-box--blue">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                  </svg>
                </div>
                <div class="meta-text">
                  <span class="meta-label">Valeur</span>
                  <span class="meta-value meta-value--blue">{{ brief.points || 1500 }} YC</span>
                </div>
              </div>
              
              <template v-if="brief.status === 'VALIDATED'">
                <div class="meta-divider"></div>
                <div class="meta-item">
                  <div class="meta-icon-box meta-icon-box--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4L12 14.01l-3-3"/>
                    </svg>
                  </div>
                  <div class="meta-text">
                    <span class="meta-label">Expertise</span>
                    <span class="meta-value meta-value--green">Validée</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Body -->
      <div class="body-grid">

        <!-- Left column -->
        <div class="col-left">

          <!-- Contexte -->
          <section class="section animate-in" style="animation-delay: 0.2s">
            <div class="section-header">
              <span class="section-number">01</span>
              <h2 class="section-title">Contexte du projet</h2>
            </div>
            <div class="prose-card">
              <p class="prose-text">{{ brief.context || 'Aucun contexte spécifique n\'a été fourni pour ce projet.' }}</p>
            </div>
          </section>

          <!-- Pédagogie & Évaluation -->
          <section class="section animate-in" style="animation-delay: 0.25s">
            <div class="two-col-sm">
              <div class="info-block">
                <div class="section-header">
                  <span class="section-number">02</span>
                  <h2 class="section-title">Pédagogie</h2>
                </div>
                <div class="tinted-card tinted-card--green">
                  <p class="tinted-text">{{ brief.pedagogical_modalities || 'Standard institutionnel' }}</p>
                </div>
              </div>

              <div class="info-block">
                <div class="section-header">
                  <span class="section-number">03</span>
                  <h2 class="section-title">Modalités d'évaluation</h2>
                </div>
                <div class="tinted-card tinted-card--amber">
                  <p class="tinted-text">{{ brief.evaluation_modalities || 'Revue finale avec présentation' }}</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Livrables & Critères -->
          <section class="section animate-in" style="animation-delay: 0.3s">
            <div class="two-col-sm">
              <div class="info-block">
                <div class="section-header">
                  <span class="section-number">04</span>
                  <h2 class="section-title">Livrables requis</h2>
                </div>
                <div class="list-card list-card--soft">
                  <div v-for="(del, idx) in brief.deliverables" :key="idx" class="list-item">
                    <span class="list-num">{{ idx + 1 }}</span>
                    <p class="list-text">{{ del }}</p>
                  </div>
                  <p v-if="!brief.deliverables?.length" class="empty-list">Aucun livrable défini.</p>
                </div>
              </div>

              <div class="info-block">
                <div class="section-header">
                  <span class="section-number">05</span>
                  <h2 class="section-title section-title--blue">Critères de performance</h2>
                </div>
                <div class="tinted-card tinted-card--blue list-card">
                  <div
                    v-for="(crit, idx) in brief.performance_criteria"
                    :key="idx"
                    class="crit-item"
                    :class="{ 'crit-item--last': idx === brief.performance_criteria?.length - 1 }"
                  >
                    <span class="crit-dot"></span>
                    <p class="list-text list-text--glow">{{ crit }}</p>
                  </div>
                  <p v-if="!brief.performance_criteria?.length" class="empty-list">Aucun critère défini.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Compétences -->
          <section class="section animate-in" style="animation-delay: 0.35s">
            <div class="section-header">
              <span class="section-number">06</span>
              <h2 class="section-title section-title--green">Compétences ciblées</h2>
            </div>
            <div class="competencies-wrap">
              <span
                v-for="comp in brief.target_competencies"
                :key="comp"
                class="competency-tag"
              >{{ comp }}</span>
              <p v-if="!brief.target_competencies?.length" class="empty-list">Aucune compétence liée à ce brief.</p>
            </div>
          </section>

        </div>

        <!-- Right sidebar -->
        <aside class="col-right animate-in" style="animation-delay: 0.4s">

          <div class="sidebar-card">
            <!-- Actions -->
            <div class="actions">
              <button class="btn-nadi-primary" @click="router.push('/submissions')" v-if="brief.status === 'IN_PROGRESS'">
                Soumettre mon travail
              </button>
              <button class="btn-nadi-primary" v-else>
                S'engager sur le brief
              </button>
              <button class="btn-nadi-secondary">Télécharger la fiche PDF</button>
            </div>

            <div class="sidebar-divider"></div>

            <!-- Resources -->
            <div>
              <span class="sidebar-label">Ressources & Annexes</span>
              <div class="resources-list">
                <a
                  v-for="res in brief.resources"
                  :key="res"
                  href="#"
                  class="resource-item"
                >
                  <span class="resource-name">{{ res }}</span>
                  <svg class="resource-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4v16m8-8H4"/>
                  </svg>
                </a>
                <p v-if="!brief.resources?.length" class="empty-list">Aucune ressource fournie.</p>
              </div>
            </div>
          </div>

        </aside>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

// ─── State ────────────────────────────────────────────────────────────────────
const route  = useRoute();
const router = useRouter();
const user   = ref(null);

const brief = ref({
  title:                   'Chargement du brief...',
  description:             '',
  points:                  0,
  tags:                    [],
  date_end_f:              '',
  modality:                '',
  difficulty:              '',
  status:                  '',
  context:                 '',
  objectives:              [],
  pedagogical_modalities:  '',
  evaluation_modalities:   '',
  deliverables:            [],
  performance_criteria:    [],
  target_competencies:     [],
  resources:               [],
});

// ─── Data Fetching ────────────────────────────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric',
  });
};

onMounted(async () => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);

  try {
    const res  = await api.get(`/briefs/${route.params.id}`);
    const data = res.data.data || res.data;
    brief.value = {
      ...data,
      date_end_f:         formatDate(data.date_end),
      objectives:         data.objectives         || [],
      tags:               data.tags               || [],
      deliverables:       data.deliverables       || [],
      performance_criteria:  data.performance_criteria  || [],
      target_competencies:   data.target_competencies   || [],
      resources:             data.resources             || [],
    };
  } catch (err) {
    console.warn('Brief fetch error:', err);
  }
});

// ─── Logout ───────────────────────────────────────────────────────────────────
const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (err) {
    console.error('Logout error:', err);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};
</script>

<style scoped>
/* ─── Reset ─────────────────────────────────────────────────────────────────── */
* { box-sizing: border-box; }

/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #e6edf3; font-family: 'Inter', system-ui, sans-serif;
}

.main {
  flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent;
}
.main::-webkit-scrollbar { width: 5px; }
.main::-webkit-scrollbar-thumb { background: #21262d; border-radius: 10px; }

/* ─── Hero ──────────────────────────────────────────────────────────────────── */
.hero {
  position: relative; width: 100%; height: 460px; flex-shrink: 0;
}
.hero-img {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
  filter: grayscale(0.5); opacity: 0.25; transition: opacity 0.8s ease, filter 0.8s ease, transform 2s ease;
}
.hero:hover .hero-img { opacity: 0.4; filter: grayscale(0.1); transform: scale(1.03); }
.hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, #010409 0%, rgba(1,4,9,0.5) 40%, #010409 100%);
}

.hero-wrapper {
  position: relative; max-width: 1300px; padding: 0 40px; margin: 0 auto; height: 100%;
}

/* Hero nav */
.hero-nav {
  position: absolute; top: 0; left: 40px; right: 40px; height: 80px;
  display: flex; align-items: center; justify-content: space-between; z-index: 20;
}
.hero-nav-left { display: flex; align-items: center; gap: 14px; }
.back-btn {
  width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;
  color: #8b949e; backdrop-filter: blur(8px); transition: all 0.2s; text-decoration: none;
}
.back-btn:hover { background: rgba(56, 139, 253, 0.15); color: #fff; border-color: rgba(56, 139, 253, 0.4); box-shadow: 0 0 15px rgba(56, 139, 253, 0.2); }
.back-btn:active { transform: scale(0.95); }
.back-btn svg { width: 18px; height: 18px; stroke-width: 2px;}

.hero-nav-label { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700; }
.session-badge {
  display: flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(35, 134, 54, 0.08);
  border: 1px solid rgba(35, 134, 54, 0.2); border-radius: 20px; font-size: 11px; color: #56d364; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 0 20px rgba(35, 134, 54, 0.05);
}
.live-dot { width: 6px; height: 6px; background: #56d364; border-radius: 50%; animation: pulse 2s infinite; box-shadow: 0 0 8px #56d364; }

/* Hero content */
.hero-content { position: absolute; bottom: 40px; left: 40px; right: 40px; z-index: 20; }
.hero-badges { display: flex; gap: 10px; margin-bottom: 20px; }
.badge { padding: 4px 12px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; }
.badge--neutral { background: rgba(255,255,255,0.05); color: #c9d1d9; border: 1px solid rgba(255,255,255,0.1); }
.badge--amber { background: rgba(210, 153, 34, 0.1); color: #e3b341; border: 1px solid rgba(210, 153, 34, 0.3); }

.hero-title { font-size: clamp(32px, 5vw, 56px); font-weight: 800; color: #fff; letter-spacing: -0.03em; line-height: 1.1; margin-bottom: 32px; }

.hero-meta { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.meta-item { display: flex; align-items: center; gap: 12px; }
.meta-avatar { width: 36px; height: 36px; border-radius: 10px; object-fit: cover; border: 1px solid rgba(255,255,255,0.15); }
.meta-icon-box {
  width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
}
.meta-icon-box svg { width: 16px; height: 16px; stroke: #8b949e; }
.meta-icon-box--blue { background: rgba(56, 139, 253, 0.1); border-color: rgba(56, 139, 253, 0.2); }
.meta-icon-box--blue svg { stroke: #79c0ff; }
.meta-icon-box--green { background: rgba(35, 134, 54, 0.1); border-color: rgba(35, 134, 54, 0.2); }
.meta-icon-box--green svg { stroke: #56d364; }

.meta-text { display: flex; flex-direction: column; gap: 2px; }
.meta-label { font-size: 10px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 700; }
.meta-value { font-size: 14px; font-weight: 700; color: #fff; }
.meta-value--amber { color: #e3b341; }
.meta-value--blue { color: #79c0ff; }
.meta-value--green { color: #56d364; }
.meta-divider { width: 1px; height: 32px; background: rgba(255,255,255,0.1); flex-shrink: 0; }

/* ─── Body ──────────────────────────────────────────────────────────────────── */
.body-grid {
  display: grid; grid-template-columns: 1fr 340px; gap: 40px; padding: 40px;
  max-width: 1300px; margin: 0 auto; padding-bottom: 80px; position: relative; z-index: 20;
}

/* ─── Left column ───────────────────────────────────────────────────────────── */
.col-left { display: flex; flex-direction: column; gap: 48px; }

.section { display: flex; flex-direction: column; gap: 18px; }
.section-header { display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.section-number { font-size: 11px; font-weight: 700; color: #484f58; letter-spacing: 0.1em; font-family: 'JetBrains Mono', monospace; }
.section-title { font-size: 13px; font-weight: 800; color: #c9d1d9; text-transform: uppercase; letter-spacing: 0.15em; }
.section-title--blue { color: #79c0ff; text-shadow: 0 0 10px rgba(56, 139, 253, 0.2); }
.section-title--green { color: #56d364; text-shadow: 0 0 10px rgba(35, 134, 54, 0.2); }

.prose-card {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 16px; padding: 32px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.prose-card::before {
  content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 3px;
  background: #30363d; border-radius: 8px 0 0 8px;
}
.prose-text { font-size: 15px; color: #c9d1d9; line-height: 1.8; font-weight: 400; }

.info-block { display: flex; flex-direction: column; gap: 14px; }
.tinted-card { border-radius: 16px; padding: 24px; border: 1px solid; min-height: 120px; display: flex; align-items: flex-start; }
.tinted-card--green { background: rgba(35, 134, 54, 0.05); border-color: rgba(35, 134, 54, 0.15); }
.tinted-card--amber { background: rgba(210, 153, 34, 0.05); border-color: rgba(210, 153, 34, 0.15); }
.tinted-card--blue { background: rgba(56, 139, 253, 0.05); border-color: rgba(56, 139, 253, 0.15); }
.tinted-text { font-size: 14px; color: #e6edf3; line-height: 1.8; }

.list-card { background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.list-item { display: flex; align-items: flex-start; gap: 14px; }
.list-num {
  width: 26px; height: 26px; border-radius: 8px; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; color: #8b949e; flex-shrink: 0; font-family: 'JetBrains Mono', monospace;
  transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.list-item:hover .list-num { background: rgba(56, 139, 253, 0.15); color: #79c0ff; border-color: rgba(56, 139, 253, 0.3); }
.list-text { font-size: 14px; color: #c9d1d9; line-height: 1.7; margin-top: 3px; }
.list-text--glow { color: #e6edf3; }

.crit-item { display: flex; align-items: flex-start; gap: 14px; padding-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.crit-item--last { border-bottom: none; padding-bottom: 0; }
.crit-dot { width: 6px; height: 6px; border-radius: 50%; background: #79c0ff; flex-shrink: 0; margin-top: 10px; box-shadow: 0 0 10px rgba(121, 192, 255, 0.5); }
.empty-list { font-size: 13px; color: #484f58; font-style: italic; padding: 8px 0; }

.competencies-wrap { display: flex; flex-wrap: wrap; gap: 12px; }
.competency-tag {
  padding: 10px 18px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
  border-radius: 10px; font-size: 13px; font-weight: 600; color: #8b949e;
  transition: all 0.2s; cursor: default;
}
.competency-tag:hover { background: rgba(35, 134, 54, 0.05); border-color: rgba(35, 134, 54, 0.3); color: #56d364; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

.two-col-sm { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

/* ─── Right sidebar ─────────────────────────────────────────────────────────── */
.col-right { position: sticky; top: 32px; height: fit-content; }
.sidebar-card {
  background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05);
  border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 28px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3); backdrop-filter: blur(10px);
}
.sidebar-divider { height: 1px; background: rgba(255,255,255,0.05); }

.actions { display: flex; flex-direction: column; gap: 14px; }
.btn-nadi-primary {
  width: 100%; padding: 14px; background: #238636; color: white;
  border: 1px solid #2ea043; border-radius: 10px; font-size: 14px; font-weight: 700;
  cursor: pointer; box-shadow: 0 4px 15px rgba(35, 134, 54, 0.2); transition: all 0.2s;
}
.btn-nadi-primary:hover { background: #2ea043; border-color: #3fb950; box-shadow: 0 8px 20px rgba(35, 134, 54, 0.3); transform: translateY(-1px); }
.btn-nadi-primary:active { transform: scale(0.98); }

.btn-nadi-secondary {
  width: 100%; padding: 14px; background: rgba(255,255,255,0.03); color: #c9d1d9;
  border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; font-size: 13px; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
}
.btn-nadi-secondary:hover { background: rgba(255,255,255,0.08); color: #fff; border-color: rgba(255,255,255,0.2); }

.sidebar-label { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.12em; font-weight: 800; display: block; margin-bottom: 14px; }
.resources-list { display: flex; flex-direction: column; gap: 8px; }
.resource-item {
  display: flex; align-items: center; justify-content: space-between; padding: 12px 16px;
  border-radius: 10px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
  text-decoration: none; transition: all 0.2s;
}
.resource-item:hover { background: rgba(255,255,255,0.03); border-color: rgba(56, 139, 253, 0.3); transform: translateX(2px); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.resource-name { font-size: 13px; font-weight: 600; color: #8b949e; transition: color 0.15s; }
.resource-item:hover .resource-name { color: #79c0ff; }
.resource-icon { width: 14px; height: 14px; stroke: #484f58; transition: stroke 0.15s; }
.resource-item:hover .resource-icon { stroke: #79c0ff; }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes pulse { 0%, 100% { opacity: 1; box-shadow: 0 0 8px #56d364; } 50% { opacity: 0.5; box-shadow: 0 0 2px #56d364; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) { .body-grid { grid-template-columns: 1fr; } .col-right { position: static; } }
@media (max-width: 760px) {
  .body-grid { padding: 20px; } .two-col-sm { grid-template-columns: 1fr; }
  .hero { height: auto; padding-bottom: 40px; } .hero-wrapper { padding: 0 20px; }
  .hero-nav { left: 20px; right: 20px; position: relative; margin-bottom: 40px; }
  .hero-content { position: relative; left: 0; right: 0; bottom: 0; padding-top: 20px; }
  .hero-title { font-size: 28px; }
}
</style>