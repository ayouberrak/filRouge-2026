<template>
  <aside class="sidebar">

    <!-- Brand -->
    <div class="brand">
      <img :src="iconeSidebar" class="brand-banner" alt="Logo" />
    </div>

    <!-- Nav sections -->
    <nav class="nav">

      <div class="nav-section">
        <span class="nav-section-label">Pilotage</span>
        <router-link
          v-for="item in overviewItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.path) }"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
      </div>

      <div class="nav-section">
        <span class="nav-section-label">Structure</span>
        <router-link
          v-for="item in structureItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ 'nav-item--active': isActive(item.path) }"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
      </div>

      <div class="nav-section">
        <span class="nav-section-label">Ressources</span>
        <router-link
          v-for="item in resourceItems"
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

    <!-- Footer -->
    <div class="sidebar-footer">
      <div class="user-row">
        <img
          class="user-avatar"
          :src="user?.avatar_url || avatarFallback"
          :alt="user?.first_name"
        />
        <div class="user-info">
          <p class="user-name">{{ user?.first_name || 'Admin' }}</p>
          <p class="user-role">Super Administrateur</p>
        </div>
        <button class="logout-btn" @click="$emit('logout')" title="Se déconnecter">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </div>
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
const isActive = (path) => route.path === path || route.path.startsWith(path + '/');

const avatarFallback = computed(() => {
  const name = `${props.user?.first_name || 'A'} ${props.user?.last_name || 'U'}`;
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=21262d&color=8b949e&size=56`;
});

const overviewItems = [
  {
    path:  '/admin/dashboard',
    label: 'Dashboard',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  }
];

const structureItems = [
  {
    path:  '/admin/classrooms',
    label: 'Classes & Salles',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7M4 21v-4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4M20 7V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/></svg>',
  }
];

const resourceItems = [
  {
    path:  '/admin/users',
    label: 'Utilisateurs',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>',
  },
  {
    path:  '/admin/absences',
    label: 'Absences Globale',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  },
  {
    path:  '/admin/reports',
    label: 'Rapports Daily',
    icon:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
  }
];


</script>

<style scoped>
.sidebar {
  width: 240px;
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
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(163, 113, 247, 0.1);
  border: 1px solid rgba(163, 113, 247, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a371f7;
}

.brand-mark svg { width: 18px; height: 18px; }

.brand-text { display: flex; flex-direction: column; }
.brand-name {
  font-size: 14px;
  font-weight: 800;
  color: #e6edf3;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  line-height: 1.2;
}
.brand-role {
  font-size: 10px;
  color: #a371f7;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.nav {
  flex: 1;
  overflow-y: auto;
  padding: 20px 12px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.nav-section { display: flex; flex-direction: column; gap: 4px; }
.nav-section-label {
  font-size: 10px;
  font-weight: 700;
  color: #484f58;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 0 12px;
  margin-bottom: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #8b949e;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-item:hover {
  background: rgba(163, 113, 247, 0.05);
  color: #e6edf3;
}

.nav-item--active {
  background: rgba(163, 113, 247, 0.1);
  color: #a371f7;
  font-weight: 600;
  border: 1px solid rgba(163, 113, 247, 0.2);
}

.nav-icon {
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-icon :deep(svg) {
  width: 18px;
  height: 18px;
  stroke: currentColor;
}

.nav-badge {
  font-size: 9px;
  font-weight: 800;
  background: #a371f7;
  color: #fff;
  padding: 1px 6px;
  border-radius: 10px;
}

.sidebar-footer {
  padding: 16px;
  border-top: 1px solid #21262d;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px;
  border-radius: 10px;
  background: #161b22;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 6px;
}

.user-info { flex: 1; min-width: 0; }
.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #e6edf3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-role {
  font-size: 10px;
  color: #8b949e;
}

.logout-btn {
  background: none;
  border: none;
  color: #8b949e;
  cursor: pointer;
  transition: color 0.2s;
}

.logout-btn:hover { color: #f85149; }
</style>
