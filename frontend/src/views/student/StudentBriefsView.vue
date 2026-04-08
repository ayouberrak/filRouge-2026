<template>
  <div class="layout">

    <!-- Sidebar -->
    <SidebarStudent :user="user" @logout="handleLogout" />

    <!-- Main -->
    <main class="main">

      <div class="content">

        <!-- Page Header -->
        <div class="page-header">
          <div class="page-heading">
            <h1>{{ mode === 'promo' ? 'Briefs Promotion' : 'Explorateur Projets' }}</h1>
            <p>
              {{ mode === 'promo'
                ? 'Missions obligatoires assignées à votre promotion pour valider vos compétences.'
                : 'Découvrez l\'intégralité du catalogue YouCode et auto-formez vous sur de nouveaux stacks.'
              }}
            </p>
          </div>

          <!-- Controls -->
          <div class="controls">
            <div class="tab-group">
              <button
                @click="setMode('promo')"
                class="tab-btn"
                :class="{ 'tab-btn--active': mode === 'promo' }"
              >Promotion</button>
              <button
                @click="setMode('explorer')"
                class="tab-btn"
                :class="{ 'tab-btn--active': mode === 'explorer' }"
              >Explorer</button>
            </div>

            <div class="search-wrap">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Filtrer par titre, tag..."
                class="search-input"
              />
              <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
          <span class="stats-count">
            <strong>{{ filteredBriefs.length }}</strong> brief{{ filteredBriefs.length !== 1 ? 's' : '' }} trouvés
          </span>
          <div class="modality-filters">
            <button
              v-for="f in modalityOptions"
              :key="f.value"
              @click="modalityFilter = f.value"
              class="modality-btn"
              :class="{ 'modality-btn--active': modalityFilter === f.value }"
            >{{ f.label }}</button>
          </div>
        </div>

        <!-- Skeleton Grid (Loading State) -->
        <div class="briefs-grid" v-if="isLoading">
          <div v-for="i in 8" :key="i" class="brief-card skeleton-card">
            <div class="card-thumbnail skeleton-box shimmer"></div>
            <div class="card-body">
              <div class="card-meta">
                <div class="skeleton-shimmer skeleton-tag shimmer"></div>
                <div class="skeleton-shimmer skeleton-points shimmer"></div>
              </div>
              <div class="skeleton-shimmer skeleton-title shimmer"></div>
              <div class="skeleton-shimmer skeleton-desc shimmer"></div>
              <div class="skeleton-shimmer skeleton-desc shimmer w-2/3"></div>
              <div class="card-tags">
                <div v-for="j in 3" :key="j" class="skeleton-shimmer skeleton-tag shimmer w-12"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Real Grid -->
        <div class="briefs-grid" v-else-if="filteredBriefs.length > 0">
          <div
            v-for="(brief, index) in filteredBriefs"
            :key="brief.id"
            class="brief-card"
            :style="{ animationDelay: `${index * 0.05}s` }"
            @click="router.push(`/briefs/${brief.id}`)"
          >
            <!-- Hover Glow Effect -->
            <div class="hover-glow"></div>

            <!-- Thumbnail -->
            <div class="card-thumbnail">
              <img :src="getBriefImage(brief)" :alt="brief.title" class="card-img" />
              <div class="card-img-overlay"></div>
              <span class="difficulty-badge">{{ brief.difficulty || 'Expert' }}</span>
            </div>

            <!-- Body -->
            <div class="card-body">
              <div class="card-meta">
                <span class="modality-tag" :class="brief.modality === 'COLLECTIVE' ? 'modality-tag--collective' : 'modality-tag--solo'">
                  {{ brief.modality === 'COLLECTIVE' ? 'Collectif' : 'Individuel' }}
                </span>
                <span class="card-points">{{ brief.points || 1200 }} <span class="points-unit">YC</span></span>
              </div>

              <h3 class="card-title">{{ brief.title }}</h3>
              <p class="card-desc">{{ brief.description }}</p>

              <div class="card-tags" v-if="brief.tags?.length">
                <span v-for="tag in brief.tags.slice(0, 4)" :key="tag" class="tag">{{ tag }}</span>
              </div>
            </div>

            <!-- Footer -->
            <div class="card-footer">
              <span class="card-cta">Explorer la mission</span>
              <svg class="cta-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!isLoading" class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p>Aucun projet trouvé</p>
          <span>Ajustez vos filtres de recherche.</span>
          <button @click="searchQuery = ''; modalityFilter = 'ALL'" class="btn-nadi-secondary mt-3">
            Effacer les filtres
          </button>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

// ─── State ────────────────────────────────────────────────────────────────────

const router        = useRouter();
const user          = ref(null);
const mode          = ref('promo');
const searchQuery   = ref('');
const modalityFilter = ref('ALL');
const briefs        = ref([]);
const isLoading     = ref(true);

// ─── Options ──────────────────────────────────────────────────────────────────

const modalityOptions = [
  { value: 'ALL',        label: 'Tous'       },
  { value: 'INDIVIDUAL', label: 'Individuel' },
  { value: 'COLLECTIVE', label: 'Collectif'  },
];

// ─── Computed ─────────────────────────────────────────────────────────────────

const filteredBriefs = computed(() => {
  const q = searchQuery.value.toLowerCase().trim();
  return briefs.value.filter(b => {
    const matchesSearch =
      !q ||
      b.title.toLowerCase().includes(q) ||
      (b.tags && b.tags.some(t => t.toLowerCase().includes(q)));

    const matchesModality =
      modalityFilter.value === 'ALL' ||
      b.modality === modalityFilter.value;

    return matchesSearch && matchesModality;
  });
});

// ─── Methods ──────────────────────────────────────────────────────────────────

const BRIEF_IMAGES = [
  'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1627398242454-45a1465c2479?q=80&w=600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1507721999472-8ed4421c4af2?q=80&w=600&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1534972195531-d756b9bfa9f2?q=80&w=600&auto=format&fit=crop',
];

const getBriefImage = (brief) =>
  BRIEF_IMAGES[brief.id % BRIEF_IMAGES.length];

const setMode = (newMode) => {
  mode.value = newMode;
  fetchBriefs();
};

// ─── Data Fetching ────────────────────────────────────────────────────────────

const fetchBriefs = async () => {
  isLoading.value = true;
  try {
    const params = mode.value === 'explorer' ? '?all=true' : '';
    const response = await api.get(`/briefs${params}`);
    briefs.value = response.data.data || response.data;
  } catch (err) {
    console.error('Briefs fetch error:', err);
    briefs.value = [
      {
        id: 1, title: 'Clean Architecture PHP', description: 'Maîtrisez DDD et les principes SOLID pour des applications maintenables.', modality: 'INDIVIDUAL', tags: ['PHP', 'DDD', 'SOLID'], points: 1500, difficulty: 'PRO',
      },
      {
        id: 2, title: 'Team Project : WebApp', description: 'Construisez une application collaborative complète avec Node.js et Vue.', modality: 'COLLECTIVE', tags: ['Node.js', 'Vue', 'Teamwork'], points: 2500, difficulty: 'CORE',
      },
    ];
  } finally {
    isLoading.value = false;
  }
};

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

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(() => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  fetchBriefs();
});
</script>

<style scoped>
/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #e6edf3; font-family: 'Inter', system-ui, sans-serif;
}

.main {
  flex: 1; display: flex; flex-direction: column;
  overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent;
}
.main::-webkit-scrollbar { width: 4px; }
.main::-webkit-scrollbar-thumb { background: #30363d; border-radius: 4px; }

/* ─── Skeleton Loading ────────────────────────────────────────────────────────── */
.skeleton-box { background: rgba(255,255,255,0.03); border-radius: 4px; }
.skeleton-shimmer { background: rgba(255, 255, 255, 0.04); border-radius: 4px; }
.shimmer { position: relative; overflow: hidden; }
.shimmer::after {
  content: ""; position: absolute; inset: 0; transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.04), transparent);
  animation: shimmer-anim 1.5s infinite;
}
@keyframes shimmer-anim { 100% { transform: translateX(100%); } }

.skeleton-card { pointer-events: none; border-color: rgba(255,255,255,0.05) !important; background: transparent !important;}
.skeleton-title { height: 18px; width: 80%; margin-bottom: 8px; }
.skeleton-desc { height: 12px; width: 100%; margin-bottom: 6px; }
.skeleton-tag { height: 20px; width: 56px; border-radius: 10px; }
.w-2\/3 { width: 66%; }

/* ─── Content ───────────────────────────────────────────────────────────────── */
.content {
  padding: 40px; max-width: 1400px; width: 100%; margin: 0 auto;
  display: flex; flex-direction: column; gap: 32px; padding-bottom: 80px;
}

/* ─── Page Header ───────────────────────────────────────────────────────────── */
.page-header {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 24px; flex-wrap: wrap; animation: fadeInUp 0.4s ease forwards;
}
.page-heading h1 {
  font-size: 32px; font-weight: 800; color: #fff;
  letter-spacing: -0.03em; margin-bottom: 8px;
}
.page-heading p { font-size: 14px; color: #8b949e; max-width: 500px; line-height: 1.6; }

/* ─── Controls ──────────────────────────────────────────────────────────────── */
.controls { display: flex; align-items: center; gap: 16px; flex-shrink: 0; }
.tab-group {
  display: flex; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px; padding: 4px; gap: 4px;
}
.tab-btn {
  padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;
  border: none; background: transparent; color: #8b949e; cursor: pointer; transition: all 0.2s;
}
.tab-btn:hover { color: #c9d1d9; }
.tab-btn--active {
  background: rgba(255,255,255,0.1); color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.search-wrap { position: relative; display: flex; align-items: center; }
.search-icon {
  position: absolute; left: 14px; width: 14px; height: 14px; stroke: #8b949e; pointer-events: none;
}
.search-input {
  width: 280px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; padding: 10px 36px; font-size: 13px; color: #fff;
  outline: none; transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input::placeholder { color: #484f58; }
.search-input:focus {
  background: rgba(255,255,255,0.04); border-color: #388bfd;
  box-shadow: 0 0 0 3px rgba(56, 139, 253, 0.15);
}
.search-clear {
  position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #8b949e;
}
.search-clear:hover { color: #fff; }
.search-clear svg { width: 14px; height: 14px; }

/* ─── Stats Bar ─────────────────────────────────────────────────────────────── */
.stats-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; background: linear-gradient(90deg, rgba(22,27,34,0.4) 0%, transparent 100%);
  border: 1px solid rgba(255,255,255,0.05); border-radius: 12px;
  animation: fadeInUp 0.4s ease forwards; animation-delay: 0.1s; opacity: 0;
}
.stats-count { font-size: 13px; color: #8b949e; }
.stats-count strong { color: #fff; font-weight: 700; }
.modality-filters { display: flex; gap: 6px; }
.modality-btn {
  padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
  border: 1px solid transparent; background: transparent; color: #8b949e; cursor: pointer; transition: all 0.2s;
}
.modality-btn:hover { color: #e6edf3; }
.modality-btn--active { background: rgba(56,139,253,0.15); border-color: rgba(56,139,253,0.3); color: #79c0ff; }

/* ─── Grid ──────────────────────────────────────────────────────────────────── */
.briefs-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;
}

/* ─── Brief Card (Linear/Vercel Style) ────────────────────────────────────────── */
.brief-card {
  background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;
  cursor: pointer; opacity: 0; animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
}
.hover-glow {
  position: absolute; inset: 0; pointer-events: none; z-index: 10;
  background: radial-gradient(circle at 50% 0%, rgba(56, 139, 253, 0.1), transparent 70%);
  opacity: 0; transition: opacity 0.4s ease;
}
.brief-card:hover { border-color: rgba(56, 139, 253, 0.4); transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.5), 0 0 20px rgba(56, 139, 253, 0.1); background: rgba(255, 255, 255, 0.03); }
.brief-card:hover .hover-glow { opacity: 1; }
.brief-card:active { transform: scale(0.98); }

.card-thumbnail { position: relative; height: 180px; overflow: hidden; border-bottom: 1px solid rgba(255,255,255,0.05); }
.card-img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(0.2); transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), filter 0.8s; }
.brief-card:hover .card-img { transform: scale(1.05); filter: grayscale(0); }
.card-img-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 20%, rgba(1, 4, 9, 1) 100%); }
.difficulty-badge {
  position: absolute; top: 14px; right: 14px; padding: 4px 10px;
  background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15);
  border-radius: 8px; font-size: 10px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.1em;
}

.card-body { padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 2; }
.card-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
.modality-tag { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em; }
.modality-tag--solo { background: rgba(56, 139, 253, 0.08); color: #79c0ff; border: 1px solid rgba(56, 139, 253, 0.2); }
.modality-tag--collective { background: rgba(63, 185, 80, 0.08); color: #56d364; border: 1px solid rgba(63, 185, 80, 0.2); }
.card-points { font-size: 14px; font-weight: 800; color: #fff; display: flex; align-items: baseline; gap: 4px;}
.points-unit { font-size: 9px; color: #8b949e; font-weight: 700; text-transform: uppercase;}

.card-title { font-size: 18px; font-weight: 800; color: #fff; line-height: 1.4; letter-spacing: -0.02em; transition: color 0.2s; }
.brief-card:hover .card-title { color: #79c0ff; }
.card-desc { font-size: 13px; color: #8b949e; line-height: 1.6; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; }
.card-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: auto; padding-top: 12px; }
.tag { padding: 4px 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; font-size: 11px; font-weight: 500; color: #c9d1d9; }

.card-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(0, 0, 0, 0.2); z-index: 2;
}
.card-cta { font-size: 13px; font-weight: 600; color: #8b949e; transition: color 0.2s; }
.brief-card:hover .card-cta { color: #fff; }
.cta-arrow { width: 16px; height: 16px; stroke: #8b949e; transition: all 0.2s; }
.brief-card:hover .cta-arrow { stroke: #79c0ff; transform: translateX(4px); }

/* ─── Empty State ───────────────────────────────────────────────────────────── */
.empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 14px; text-align: center; padding: 100px 20px; border: 1px dashed rgba(255,255,255,0.1);
  border-radius: 16px; opacity: 0; background: rgba(255,255,255,0.01);
}
.empty-state { animation: fadeInUp 0.5s ease forwards; }
.empty-icon {
  width: 64px; height: 64px; border-radius: 16px; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;
  margin-bottom: 10px;
}
.empty-icon svg { width: 28px; height: 28px; stroke: #8b949e; }
.empty-state p { font-size: 18px; font-weight: 700; color: #fff; }
.empty-state span { font-size: 13px; color: #8b949e; max-width: 300px; line-height: 1.6; }
.btn-nadi-secondary { margin-top: 12px; padding: 10px 20px; background: #21262d; color: #c9d1d9; border: 1px solid #30363d; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.btn-nadi-secondary:hover { background: #30363d; color: #fff; border-color: #8b949e; }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) { .page-header { flex-direction: column; align-items: flex-start; } .controls { width: 100%; flex-wrap: wrap; } .search-input { width: 100%; } }
@media (max-width: 640px) { .content { padding: 20px; } .briefs-grid { grid-template-columns: 1fr; } .stats-bar { flex-direction: column; align-items: flex-start; gap: 8px; } }
</style>