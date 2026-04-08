<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Paramètres Profil</h1>
            <p class="topbar-sub">Gérez votre identité numérique</p>
          </div>
        </div>

        <div class="topbar-right animate-in">
          <button class="btn-save shadow-blue" :disabled="isSaving" @click="saveProfile">
            <div v-if="isSaving" class="spinner-sm"></div>
            {{ isSaving ? 'SYNCHRONISATION...' : 'ENREGISTRER LES MODIFICATIONS' }}
          </button>
        </div>
      </header>

      <div class="content">
        <!-- Profile Hero -->
        <div class="profile-hero animate-in">
          <div class="avatar-container">
            <div class="avatar-glow"></div>
            <img :src="user?.avatar_url || avatarFallback" class="avatar-img" />
            <button class="avatar-edit-overlay" title="Changer l'avatar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
              </svg>
            </button>
          </div>
          
          <div class="hero-details">
            <div class="hero-top">
              <h2 class="hero-name">{{ user?.first_name }} {{ user?.last_name }}</h2>
              <span class="p-badge platinum">ÉTUDIANT CERTIFIÉ</span>
            </div>
            <p class="hero-email">{{ user?.email }}</p>
            <div class="hero-stats">
              <div class="h-stat">
                <span class="h-stat-v">{{ (user?.total_points || 0).toLocaleString() }}</span>
                <span class="h-stat-l">XP TOTAL</span>
              </div>
              <div class="h-stat">
                <span class="h-stat-v">{{ form.skills?.length || 0 }}</span>
                <span class="h-stat-l">COMPÉTENCES</span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid-container">
          <!-- Left Column: Primary Info -->
          <div class="grid-main animate-in" style="animation-delay: 0.1s">
            
            <!-- Basic Info Card -->
            <div class="nadi-card">
              <div class="card-head">
                <svg class="head-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <h3 class="card-title">Informations Personnelles</h3>
              </div>
              <div class="card-content">
                <div class="form-row">
                  <div class="input-wrap">
                    <label>Bio Professionnelle</label>
                    <textarea v-model="form.bio" placeholder="Parlez-nous de vous..." rows="4"></textarea>
                  </div>
                </div>
                <div class="form-row dual">
                  <div class="input-wrap">
                    <label>Numéro de Téléphone</label>
                    <input v-model="form.phone" type="tel" placeholder="+212 600..." />
                  </div>
                  <div class="input-wrap">
                    <label>Spécialité</label>
                    <input :value="user?.speciality || 'Fullstack Developer'" disabled class="input-disabled" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Skills Management -->
            <div class="nadi-card">
              <div class="card-head">
                <svg class="head-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                <h3 class="card-title">Expertises & Skills</h3>
              </div>
              <div class="card-content">
                <div class="skills-input-area">
                  <input 
                    v-model="newSkill" 
                    @keyup.enter="addSkill" 
                    placeholder="Ajouter une compétence (ex: Vue.js, Laravel...)"
                    class="skill-field"
                  />
                  <button @click="addSkill" class="btn-add-skill">AJOUTER</button>
                </div>
                <div class="skills-list">
                  <TransitionGroup name="list">
                    <div v-for="skill in form.skills" :key="skill" class="skill-tag shadow-blue">
                      {{ skill }}
                      <button @click="removeSkill(skill)" class="skill-remove">×</button>
                    </div>
                  </TransitionGroup>
                  <div v-if="!form.skills?.length" class="empty-msg">Aucune compétence ajoutée</div>
                </div>
              </div>
            </div>

          </div>

          <!-- Right Column: Networks & Security -->
          <div class="grid-side animate-in" style="animation-delay: 0.2s">
            
            <!-- Social Networks -->
            <div class="nadi-card">
              <div class="card-head">
                <svg class="head-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                <h3 class="card-title">Présence Digitale</h3>
              </div>
              <div class="card-content social-grid">
                <div class="social-box">
                  <div class="s-icon github"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg></div>
                  <input v-model="form.github_url" placeholder="URL GitHub..." />
                </div>
                <div class="social-box">
                  <div class="s-icon linkedin"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg></div>
                  <input v-model="form.linkedin_url" placeholder="URL LinkedIn..." />
                </div>
              </div>
            </div>

            <!-- Security Shortcut -->
            <div class="nadi-card">
              <div class="card-head">
                <svg class="head-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <h3 class="card-title">Sécurité du Compte</h3>
              </div>
              <div class="card-content">
                <button class="btn-security">
                  <span>Changer le mot de passe</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="security-info">Dernière connexion: Aujourd'hui, 22:30</div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Success Notification -->
      <Transition name="fade">
        <div v-if="saveSuccess" class="success-notification">
           <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
           <span>PROFIL SYNCHRONISÉ AVEC SUCCÈS</span>
        </div>
      </Transition>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const isSaving = ref(false);
const saveSuccess = ref(false);
const newSkill = ref('');

const form = ref({
  phone: '',
  bio: '',
  skills: [],
  github_url: '',
  linkedin_url: '',
});

const avatarFallback = computed(() => {
  const name = `${user.value?.first_name || 'U'} ${user.value?.last_name || 'S'}`;
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=21262d&color=8b949e&size=100`;
});

const fetchProfileData = async () => {
  try {
    const res = await api.get('/user');
    user.value = res.data;
    form.value = {
      phone: user.value.phone || '',
      bio: user.value.bio || '',
      skills: Array.isArray(user.value.skills) ? user.value.skills : [],
      github_url: user.value.github_url || '',
      linkedin_url: user.value.linkedin_url || '',
    };
  } catch (err) {
    console.error('Error fetching profile:', err);
  }
};

const addSkill = () => {
  const skill = newSkill.value.trim();
  if (skill && !form.value.skills.includes(skill)) {
    form.value.skills.push(skill);
    newSkill.value = '';
  }
};

const removeSkill = (skill) => {
  form.value.skills = form.value.skills.filter(s => s !== skill);
};

const saveProfile = async () => {
  isSaving.value = true;
  try {
    await api.put('/user/profile', form.value);
    
    // Update local user data
    const updatedUser = { ...user.value, ...form.value };
    user.value = updatedUser;
    localStorage.setItem('user', JSON.stringify(updatedUser));
    
    saveSuccess.value = true;
    setTimeout(() => { saveSuccess.value = false; }, 3000);
  } catch (err) {
    console.error('Error saving profile:', err);
    alert('Erreur lors de la sauvegarde. Vérifiez les URLs.');
  } finally {
    isSaving.value = false;
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  fetchProfileData();
});
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
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #21262d transparent;
}

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar {
  height: 72px; padding: 0 40px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid #21262d; background: rgba(1, 4, 9, 0.8);
  backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 100;
}

.topbar-icon {
  width: 40px; height: 40px;
  background: rgba(56, 139, 253, 0.1); border: 1px solid rgba(56, 139, 253, 0.2);
  border-radius: 10px; display: flex; align-items: center; justify-content: center;
  color: #58a6ff;
}
.topbar-icon svg { width: 18px; height: 18px; }

.topbar-title { font-size: 18px; font-weight: 700; }
.topbar-sub { font-size: 11px; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em; }

.btn-save {
  background: #238636; color: white; border: none; padding: 10px 20px;
  border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer;
  transition: all 0.2s; display: flex; align-items: center; gap: 8px;
}
.btn-save:hover:not(:disabled) { transform: translateY(-2px); background: #2ea043; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

.shadow-blue { box-shadow: 0 4px 14px rgba(56, 139, 253, 0.15); }

/* ─── Content ───────────────────────────────────────────────────────────────── */
.content { padding: 40px; max-width: 1200px; width: 100%; margin: 0 auto; }

/* ─── Profile Hero ───────────────────────────────────────────────────────────── */
.profile-hero {
  background: #0d1117; border: 1px solid #30363d; border-radius: 20px;
  padding: 32px; display: flex; align-items: center; gap: 32px; margin-bottom: 32px;
  position: relative; overflow: hidden;
}

.avatar-container { position: relative; width: 100px; height: 100px; flex-shrink: 0; }
.avatar-glow {
  position: absolute; inset: -10px;
  background: radial-gradient(circle, rgba(56, 139, 253, 0.2) 0%, transparent 70%);
  animation: pulse-avatar 3s infinite;
}
@keyframes pulse-avatar { 0% { opacity: 0.3; } 50% { opacity: 0.7; } 100% { opacity: 0.3; } }

.avatar-img { width: 100%; height: 100%; border-radius: 24px; object-fit: cover; border: 2px solid #30363d; position: relative; }

.avatar-edit-overlay {
  position: absolute; inset: 0; background: rgba(0,0,0,0.5);
  border-radius: 24px; opacity: 0; transition: opacity 0.2s;
  display: flex; align-items: center; justify-content: center;
  color: white; border: none; cursor: pointer;
}
.avatar-container:hover .avatar-edit-overlay { opacity: 1; }
.avatar-edit-overlay svg { width: 24px; height: 24px; }

.hero-details { flex: 1; }
.hero-top { display: flex; align-items: center; gap: 16px; margin-bottom: 4px; }
.hero-name { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
.p-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(56, 139, 253, 0.3); }
.platinum { background: linear-gradient(135deg, rgba(230, 237, 243, 0.1), rgba(139, 148, 158, 0.1)); color: #c9d1d9; border-color: #30363d; }

.hero-email { color: #8b949e; font-size: 14px; margin-bottom: 16px; }

.hero-stats { display: flex; gap: 24px; }
.h-stat { display: flex; flex-direction: column; }
.h-stat-v { font-size: 20px; font-weight: 800; color: #58a6ff; }
.h-stat-l { font-size: 10px; font-weight: 700; color: #484f58; text-transform: uppercase; }

/* ─── Grid ──────────────────────────────────────────────────────────────────── */
.grid-container { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }

.nadi-card {
  background: #0d1117; border: 1px solid #30363d; border-radius: 18px; margin-bottom: 24px;
}
.card-head {
  padding: 16px 20px; border-bottom: 1px solid #21262d; display: flex; align-items: center; gap: 12px;
}
.head-icon { width: 16px; height: 16px; color: #58a6ff; }
.card-title { font-size: 13px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; }

.card-content { padding: 24px; }

/* ─── Forms ─────────────────────────────────────────────────────────────────── */
.form-row { margin-bottom: 20px; }
.form-row.dual { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.input-wrap { display: flex; flex-direction: column; gap: 8px; }
.input-wrap label { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; }

input, textarea {
  background: #161b22; border: 1px solid #30363d; border-radius: 10px;
  padding: 12px 16px; color: #e6edf3; font-size: 14px; outline: none; transition: border-color 0.2s;
}
input:focus, textarea:focus { border-color: #58a6ff; }
.input-disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── Skills ────────────────────────────────────────────────────────────────── */
.skills-input-area { display: flex; gap: 10px; margin-bottom: 20px; }
.skill-field { flex: 1; }
.btn-add-skill { 
  background: #161b22; border: 1px solid #30363d; color: #58a6ff;
  padding: 0 16px; border-radius: 10px; font-size: 11px; font-weight: 800; cursor: pointer;
}
.btn-add-skill:hover { border-color: #58a6ff; }

.skills-list { display: flex; flex-wrap: wrap; gap: 10px; }
.skill-tag {
  background: rgba(56, 139, 253, 0.1); border: 1px solid rgba(56, 139, 253, 0.2);
  color: #58a6ff; padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;
  display: flex; align-items: center; gap: 8px;
}
.skill-remove { background: none; border: none; color: #f85149; font-size: 16px; cursor: pointer; line-height: 1; }

.empty-msg { font-size: 12px; color: #484f58; text-align: center; width: 100%; padding: 20px 0; border: 1px dashed #21262d; border-radius: 12px; }

/* ─── Social ────────────────────────────────────────────────────────────────── */
.social-grid { display: flex; flex-direction: column; gap: 16px; }
.social-box { display: flex; align-items: center; gap: 12px; background: #161b22; border-radius: 12px; padding: 4px 12px; border: 1px solid #30363d; }
.social-box input { border: none; background: none; padding: 8px 0; font-size: 13px; flex: 1; font-family: monospace; }
.s-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
.github { background: #24292e; color: white; }
.linkedin { background: #0077b5; color: white; }
.s-icon svg { width: 16px; height: 16px; }

/* ─── Security ──────────────────────────────────────────────────────────────── */
.btn-security {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; background: #161b22; border: 1px solid #30363d; border-radius: 12px;
  color: #e6edf3; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-security:hover { border-color: #f85149; color: #f85149; }
.btn-security svg { width: 16px; height: 16px; opacity: 0.5; }

.security-info { font-size: 10px; color: #484f58; margin-top: 12px; text-align: center; }

/* ─── Notifications ──────────────────────────────────────────────────────────── */
.success-notification {
  position: fixed; bottom: 40px; right: 40px; background: #238636; color: white;
  padding: 16px 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px;
  font-size: 12px; font-weight: 800; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 1000;
}
.success-notification svg { width: 18px; height: 18px; }

/* ─── Transitions ───────────────────────────────────────────────────────────── */
.animate-in { animation: fadeInUp 0.5s ease forwards; opacity: 0; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.list-enter-active, .list-leave-active { transition: all 0.3s; }
.list-enter-from, .list-leave-to { opacity: 0; transform: scale(0.8); }

.fade-enter-active, .fade-leave-active { transition: all 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(20px); }

.spinner-sm {
  width: 12px; height: 12px; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white; border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 1000px) {
  .grid-container { grid-template-columns: 1fr; }
  .profile-hero { flex-direction: column; text-align: center; }
  .hero-top { justify-content: center; }
  .hero-stats { justify-content: center; }
}
</style>