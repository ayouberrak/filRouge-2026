<template>
  <aside class="sidebar">

    
    <div class="brand">
      <img :src="iconeSidebar" class="brand-banner" alt="Logo" />
    </div>

    
    <nav class="nav">
      <div class="nav-section">
        <span class="nav-section-label">Parcours</span>
        <router-link
          v-for="item in parcoursItems"
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
        <span class="nav-section-label">Communauté</span>
        <router-link
          v-for="item in communityItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.path) }"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
      </div>

    </nav>

    
    <div class="sidebar-footer">
      <router-link to="/profile" class="user-row" :class="{ 'user-row--active': isActive('/profile') }">
        <img
          class="user-avatar"
          :src="user?.avatar_url || avatarFallback"
          :alt="user?.first_name"
        />
        <div class="user-info">
          <p class="user-name">{{ user?.first_name || 'Étudiant' }} {{ user?.last_name?.[0] || '' }}.</p>
          <p class="user-role">Apprenant</p>
        </div>
      </router-link>
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


const avatarFallback = computed(() => {
  const name = `${props.user?.first_name || 'S'} ${props.user?.last_name || 'A'}`;
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=21262d&color=8b949e&size=56`;
});


const parcoursItems = [
  {
    path:  '/student/dashboard',
    label: 'Dashboard',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
  },
  {
    path:  '/student/briefs',
    label: 'Briefs Actifs',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
  },
  {
    path:  '/student/submissions',
    label: 'Mes Rendus',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>',
  },
  {
    path:  '/student/absences',
    label: 'Assiduité',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  },
  {
    path:  '/student/activity',
    label: 'Mon Activité',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
  },
  {
    path:  '/student/quizzes',
    label: 'Quiz',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4m-2 6v10a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2h10"/></svg>',
  },
];

const communityItems = [
  {
    path:  '/student/chat',
    label: 'Messagerie',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
  },

  {
    path:  '/student/network',
    label: 'Communauté',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
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
  color: #388bfd;
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
  color: #388bfd;
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
  background: #388bfd;
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
.nav-item:hover   .nav-icon { opacity: 1; color: #388bfd; }

.nav-icon :deep(svg) {
  width: 15px;
  height: 15px;
  stroke: currentColor;
}

.nav-label { flex: 1; }

.nav-badge {
  font-size: 9px;
  font-weight: 700;
  background: rgba(56, 139, 253, 0.15);
  color: #388bfd;
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
  flex: 1;
  text-decoration: none;
  min-width: 0;
}
.user-row:hover, .user-row--active { background: #161b22; }
.user-row--active { border: 1px solid #21262d; }

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
  color: #388bfd;
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

footer {
  padding: 12px 10px;
  border-top: 1px solid #21262d;
  flex-shrink: 0;
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
.user-points {
  font-size: 10px;
  color: #388bfd;
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
