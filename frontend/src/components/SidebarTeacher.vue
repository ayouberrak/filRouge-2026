<template>
  <aside class="sidebar">

    
    <div class="brand">
      <img :src="iconeSidebar" class="brand-banner" alt="Logo" />
    </div>

    
    <nav class="nav">

      <div class="nav-section">
        <span class="nav-section-label">Gestion</span>
        <router-link
          v-for="item in managementItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.path) }"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
          <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
        </router-link>
      </div>

      <div class="nav-section">
        <span class="nav-section-label">Pédagogie</span>
        <router-link
          v-for="item in pedagogyItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.path) }"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
          <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
        </router-link>
      </div>



    </nav>

    
    <div class="sidebar-footer">
      <div class="user-row">
        <img
          class="user-avatar"
          :src="user?.avatar_url || avatarFallback"
          :alt="user?.first_name"
        />
        <div class="user-info">
          <p class="user-name">{{ user?.first_name || 'Formateur' }} {{ user?.last_name?.[0] || '' }}.</p>
          <p class="user-role">Coach · YouCode</p>
        </div>
      </div>
      <button class="logout-btn" @click="$emit('logout')" title="Se déconnecter">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
      </button>
    </div>

  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import iconeSidebar from '../assets/iconeSidebar.jpg';


const props = defineProps(['user']);
defineEmits(['logout']);

const route = useRoute();

const isActive = (path) =>
  route.path === path || route.path.startsWith(path + '/');

// ─── Avatar fallback ──────────────────────────────────────────────────────────

const avatarFallback = computed(() => {
  const name = `${props.user?.first_name || 'J'} ${props.user?.last_name || 'D'}`;
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=21262d&color=8b949e&size=56`;
});

// ─── Navigation ───────────────────────────────────────────────────────────────

const managementItems = [
  {
    path:  '/teacher/dashboard',
    label: 'Dashboard',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
  },
  {
    path:  '/teacher/students',
    label: 'Étudiants',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>',
  },
  {
    path:  '/teacher/squads',
    label: 'Squads',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
  },
  {
    path:  '/teacher/absences',
    label: 'Absences',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  },
  {
    path:  '/teacher/chat',
    label: 'Messagerie',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
  },
];

const pedagogyItems = [
  {
    path:  '/teacher/briefs',
    label: 'Briefs',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
  },
  {
    path:  '/teacher/submissions',
    label: 'Rendus',
    badge: '3',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  },
  {
    path:  '/teacher/activity',
    label: 'Activités',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
  },
  {
    path:  '/teacher/quizzes',
    label: 'Quiz',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/></svg>',
  },
];


</script>

<style scoped>
/* ─── Reset ─────────────────────────────────────────────────────────────────── */
* { box-sizing: border-box; }

/* ─── Sidebar ───────────────────────────────────────────────────────────────── */
.sidebar {
  width: 220px;
  flex-shrink: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #0d1117;
  border-right: 1px solid #21262d;
  position: relative;
  z-index: 40;
  font-family: 'Inter', system-ui, sans-serif;
}

/* ─── Brand ─────────────────────────────────────────────────────────────────── */
.brand {
  display: flex;
  align-items: center;
  height: 140px;
  border-bottom: 1px solid #21262d;
  flex-shrink: 0;
  overflow: hidden;
  position: relative;
}
.brand::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.7));
  pointer-events: none;
}
.brand-banner {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.brand-mark {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: #161b22;
  border: 1px solid #21262d;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6e40c9;
  flex-shrink: 0;
}
.brand-mark svg { width: 14px; height: 14px; }
.brand-text { display: flex; flex-direction: column; gap: 1px; }
.brand-name {
  font-size: 13px;
  font-weight: 700;
  color: #e6edf3;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  line-height: 1;
}
.brand-role {
  font-size: 9px;
  color: #6e40c9;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

/* ─── Nav ───────────────────────────────────────────────────────────────────── */
.nav {
  flex: 1;
  overflow-y: auto;
  padding: 10px 8px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}
.nav::-webkit-scrollbar       { width: 3px; }
.nav::-webkit-scrollbar-thumb { background: #21262d; border-radius: 3px; }

.nav-section { display: flex; flex-direction: column; gap: 1px; }
.nav-section-label {
  font-size: 9px;
  font-weight: 700;
  color: #30363d;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  padding: 0 10px;
  margin-bottom: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 7px 10px;
  border-radius: 7px;
  font-size: 13px;
  font-weight: 400;
  color: #8b949e;
  text-decoration: none;
  border: 1px solid transparent;
  transition: background 0.12s, color 0.12s, border-color 0.12s;
  position: relative;
}
.nav-item:hover {
  background: #161b22;
  color: #e6edf3;
}
.nav-item--active {
  background: #161b22;
  color: #e6edf3;
  font-weight: 500;
  border-color: #21262d;
}
.nav-item--active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 2px;
  height: 16px;
  background: #6e40c9;
  border-radius: 0 2px 2px 0;
}

.nav-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
  opacity: 0.65;
  transition: opacity 0.12s;
}
.nav-item--active .nav-icon,
.nav-item:hover   .nav-icon { opacity: 1; color: #6e40c9; }

.nav-icon :deep(svg) {
  width: 15px;
  height: 15px;
  stroke: currentColor;
}

.nav-label { flex: 1; }

.nav-badge {
  font-size: 9px;
  font-weight: 700;
  background: rgba(110, 64, 201, 0.15);
  color: #9f6eff;
  border-radius: 8px;
  padding: 1px 6px;
  letter-spacing: 0.04em;
}

/* ─── Footer ────────────────────────────────────────────────────────────────── */
.sidebar-footer {
  padding: 12px 10px;
  border-top: 1px solid #21262d;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.user-row {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 7px 8px;
  border-radius: 8px;
  transition: background 0.12s;
}
.user-row:hover { background: #161b22; }

.user-avatar {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  object-fit: cover;
  border: 1px solid #21262d;
  flex-shrink: 0;
}
.user-info { flex: 1; min-width: 0; }
.user-name {
  font-size: 12px;
  font-weight: 500;
  color: #e6edf3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-role {
  font-size: 10px;
  color: #6e40c9;
  font-weight: 500;
  margin-top: 1px;
}

.logout-btn {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  background: transparent;
  border: none;
  color: #484f58;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: color 0.12s, background 0.12s;
}
.logout-btn:hover {
  color: #f85149;
  background: rgba(248, 81, 73, 0.08);
}
.logout-btn svg { width: 14px; height: 14px; }
</style>
