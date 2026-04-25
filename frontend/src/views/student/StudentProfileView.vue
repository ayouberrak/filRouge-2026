<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Loading State -->
      <div v-if="loading" class="loading-overlay">
        
        <p>Chargement en cours...</p>
      </div>

      <template v-else-if="profile">
        <!-- Topbar / Breadcrumbs -->
        <header class="topbar">
          <div class="breadcrumbs animate-in">
            <router-link to="/network" class="breadcrumb-item">Communauté</router-link>
            <svg class="separator" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            <span class="breadcrumb-current">{{ profile.first_name }} {{ profile.last_name }}</span>
          </div>
          <div class="topbar-actions animate-in">
            <button class="btn-msg" @click="startChat(profile.id)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
              <span>Message</span>
            </button>
          </div>
        </header>

        <div class="profile-container animate-in">
          <!-- Profile Header Card -->
          <div class="profile-hero">
            <div class="hero-bg"></div>
            <div class="hero-content">
              <div class="hero-avatar-wrap">
                <img :src="profile.avatar_url || `https://ui-avatars.com/api/?name=${profile.first_name}+${profile.last_name}&size=200&background=0d1117&color=388bfd`" class="hero-avatar" />
                <div class="status-ring"></div>
              </div>
              
              <div class="hero-info">
                <div class="name-row">
                  <h1 class="hero-name">{{ profile.first_name }} {{ profile.last_name }}</h1>
                </div>
                <div class="hero-links">
                  <a v-if="profile.github_url" :href="profile.github_url" target="_blank" class="social-link">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    GitHub
                  </a>
                  <a v-if="profile.linkedin_url" :href="profile.linkedin_url" target="_blank" class="social-link">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    LinkedIn
                  </a>
                </div>
              </div>

              <div class="hero-stats">
                <div class="stat-item pulse-blue">
                  <span class="stat-value">{{ stats.validated_briefs }}</span>
                  <span class="stat-label">BRIEFS VALIDÉS</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">#{{ stats.rank }}</span>
                  <span class="stat-label">RANG PROGRÈS</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Grid -->
          <div class="profile-cols">
            <div class="col-main">

              <!-- Achievements Card -->
              <div class="card glass-card">
                <div class="card-header">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3v4M19 3v4M5 11h14M5 15h14M5 19h14M10 3h4"/></svg>
                  <h3>Derniers livrables archivés</h3>
                </div>
                <div class="card-body">
                  <div class="achievements-list">
                    <div v-if="stats.validated_briefs > 0" class="achievement-item">
                       <div class="ach-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></div>
                       <div class="ach-info">
                         <h4>{{ stats.validated_briefs }} Briefs Validés</h4>
                         <p>Complété avec succès dans le cursus YouCode.</p>
                       </div>
                    </div>
                    <div v-else class="empty-ach">
                      Aucun livrable archivé publiquement.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-side">
              <!-- Squad Card -->
              <div class="card glass-card squad-card">
                <div class="card-header">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                  <h3>Sa Squad</h3>
                </div>
                <div class="card-body">
                  <template v-if="profile.squad">
                    <div class="squad-info">
                      <span class="squad-name">{{ profile.squad.name }}</span>
                    </div>
                    <div class="squad-members">
                      <div v-for="member in profile.squad.members" :key="member.id" class="member-row" @click="viewOtherProfile(member.id)">
                        <img :src="member.avatar_url || `https://ui-avatars.com/api/?name=${member.first_name}+${member.last_name}&background=0d1117&color=388bfd`" class="member-avatar" />
                        <div class="member-meta">
                          <span class="member-name">{{ member.first_name }}</span>
                          <span class="member-pts">Apprenant</span>
                        </div>
                      </div>
                    </div>
                  </template>
                  <div v-else class="empty-side">Aucune squad assignée.</div>
                </div>
              </div>

               <!-- Activity Status -->
               <div class="card glass-card presence-card">
                <div class="card-header">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <h3>Présence</h3>
                </div>
                <div class="card-body">
                  <div class="presence-stat">
                    <span class="p-val">{{ stats.absences_count }}</span>
                    <span class="p-label">Heures d'absence</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div v-else class="error-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <h3>Étudiant introuvable</h3>
        <router-link to="/network" class="btn-back">Retour à l'annuaire</router-link>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const route = useRoute();
const router = useRouter();
const user = ref(null);
const profile = ref(null);
const stats = ref(null);
const loading = ref(true);

const fetchProfile = async () => {
  loading.value = true;
  const res = await api.get(`/students/${route.params.id}`);
  profile.value = res.data.user;
  stats.value = res.data.stats;
  loading.value = false;
};

const startChat = (id) => {
  router.push({ name: 'student.chat', params: { id } });
};

const viewOtherProfile = (id) => {
  router.push(`/network/${id}`);
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  const cached = localStorage.getItem('user');
  if (cached) user.value = JSON.parse(cached);
  fetchProfile();
});

watch(() => route.params.id, fetchProfile);
</script>

<style scoped>
.layout {
  display: flex;
  height: 100vh;
  background: #010409;
  color: #e6edf3;
  font-family: 'Inter', sans-serif;
  overflow: hidden;
}

.main {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar {
  height: 72px;
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(1, 4, 9, 0.8);
  backdrop-filter: blur(12px);
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid #21262d;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 12px;
}

.breadcrumb-item {
  color: #8b949e;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.2s;
}

.breadcrumb-item:hover { color: #58a6ff; }

.separator { width: 14px; height: 14px; color: #484f58; }

.breadcrumb-current {
  color: #f0f6fc;
  font-size: 14px;
  font-weight: 700;
}

.btn-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #238636;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-msg:hover { background: #2ea043; }
.btn-msg svg { width: 16px; height: 16px; }

/* ─── Profile Layout ────────────────────────────────────────────────────────── */
.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px;
}

.profile-hero {
  background: #0d1117;
  border: 1px solid #30363d;
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  margin-bottom: 32px;
}

.hero-bg {
  height: 120px;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  position: relative;
}

.hero-bg::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 80% 20%, rgba(56, 139, 253, 0.15) 0%, transparent 50%);
}

.hero-content {
  padding: 0 40px 40px;
  display: flex;
  align-items: flex-end;
  gap: 32px;
  margin-top: -60px;
}

.hero-avatar-wrap {
  position: relative;
  z-index: 10;
}

.hero-avatar {
  width: 160px;
  height: 160px;
  border-radius: 40px;
  object-fit: cover;
  border: 6px solid #0d1117;
  background: #0d1117;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.status-ring {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  background: #3fb950;
  border: 4px solid #0d1117;
  border-radius: 50%;
}

.hero-info {
  flex: 1;
  padding-bottom: 12px;
}

.name-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.hero-name {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #f0f6fc;
}

.role-badge {
  padding: 4px 12px;
  background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  color: #58a6ff;
  letter-spacing: 0.02em;
}

.hero-location {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #8b949e;
  margin-bottom: 16px;
}

.hero-location svg { width: 14px; height: 14px; }

.hero-links {
  display: flex;
  gap: 16px;
}

.social-link {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #8b949e;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: color 0.2s;
}

.social-link:hover { color: #f0f6fc; }
.social-link svg { width: 16px; height: 16px; }

.hero-stats {
  display: flex;
  gap: 16px;
}

.stat-item {
  background: #161b22;
  border: 1px solid #30363d;
  padding: 16px 24px;
  border-radius: 20px;
  min-width: 140px;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 800;
  color: #f0f6fc;
}

.stat-label {
  font-size: 10px;
  font-weight: 700;
  color: #8b949e;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.pulse-blue {
  border-color: rgba(56, 139, 253, 0.3);
  background: radial-gradient(circle at center, rgba(56, 139, 253, 0.05) 0%, transparent 100%);
}
.pulse-blue .stat-value { color: #58a6ff; }

/* ─── Cards Grid ────────────────────────────────────────────────────────────── */
.profile-cols {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 32px;
}

.col-main, .col-side {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.card {
  background: #0d1117;
  border: 1px solid #30363d;
  border-radius: 20px;
  overflow: hidden;
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #21262d;
  display: flex;
  align-items: center;
  gap: 12px;
}

.card-header svg { width: 18px; height: 18px; color: #58a6ff; }
.card-header h3 { font-size: 15px; font-weight: 700; color: #f0f6fc; }

.card-body { padding: 24px; }

.bio-text {
  font-size: 15px;
  line-height: 1.7;
  color: #8b949e;
  white-space: pre-wrap;
}

.skills-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.skill-pill {
  padding: 6px 14px;
  background: #161b22;
  border: 1px solid #30363d;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #c9d1d9;
}

/* ─── Squad & Meta ──────────────────────────────────────────────────────────── */
.squad-members {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.member-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.2s;
}

.member-row:hover { background: #161b22; }

.member-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: cover;
}

.member-meta { display: flex; flex-direction: column; }
.member-name { font-size: 13px; font-weight: 700; color: #f0f6fc; }
.member-pts { font-size: 11px; color: #8b949e; }

.presence-stat {
  text-align: center;
}

.p-val { font-size: 32px; font-weight: 800; color: #f85149; display: block; }
.p-label { font-size: 11px; color: #8b949e; text-transform: uppercase; font-weight: 600; }

/* ─── Achievements ──────────────────────────────────────────────────────────── */
.achievement-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: rgba(56, 139, 253, 0.05);
  border: 1px solid rgba(56, 139, 253, 0.1);
  border-radius: 16px;
}

.ach-icon {
  width: 40px;
  height: 40px;
  background: #238636;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.ach-icon svg { width: 20px; height: 20px; }
.ach-info h4 { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
.ach-info p { font-size: 12px; color: #8b949e; }

/* ─── States ────────────────────────────────────────────────────────────────── */
.loading-overlay {
  position: absolute;
  inset: 0;
  background: #010409;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
}



.error-state {
  text-align: center;
  padding: 100px 40px;
}

.error-state svg { width: 64px; height: 64px; color: #f85149; margin-bottom: 24px; }
.btn-back {
  display: inline-block;
  margin-top: 24px;
  padding: 10px 24px;
  background: #21262d;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
}

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-in {
  animation: fadeInUp 0.5s ease forwards;
}

@media (max-width: 1024px) {
  .profile-cols { grid-template-columns: 1fr; }
  .profile-container { padding: 24px; }
  .hero-content { flex-direction: column; align-items: center; text-align: center; }
  .hero-avatar { width: 120px; height: 120px; }
  .name-row { justify-content: center; flex-direction: column; }
  .hero-links { justify-content: center; }
}
</style>


