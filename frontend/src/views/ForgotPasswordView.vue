<template>
  <div class="login-container">
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    
    <div class="login-card animate-in">
      <div class="login-header">
        <div class="back-link">
          <router-link to="/login">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
            Retour au login
          </router-link>
        </div>
        <h1>Récupération</h1>
        <p>Entrez votre email pour recevoir les instructions.</p>
      </div>

      <form v-if="!submitted" @submit.prevent="handleSubmit" class="login-form">
        <div class="input-group">
          <label>Adresse Email</label>
          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
            </svg>
            <input 
              v-model="email"
              type="email" 
              required
              placeholder="votre@email.com"
            >
          </div>
        </div>

        <Transition name="fade">
          <div v-if="error" class="error-msg">
            {{ error }}
          </div>
        </Transition>

        <button 
          type="submit"
          :disabled="loading"
          class="submit-btn"
        >
          <div v-if="loading" class="spinner"></div>
          <span v-else>Envoyer le lien</span>
        </button>
      </form>

      <div v-else class="success-area animate-in">
        <div class="success-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <h3>Email envoyé !</h3>
        <p>Vérifiez votre boîte de réception ({{ email }}) pour réinitialiser votre mot de passe.</p>
        <router-link to="/login" class="btn-secondary">Retour au login</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const email = ref('');
const loading = ref(false);
const submitted = ref(false);
const error = ref('');

const handleSubmit = async () => {
    loading.value = true;
    error.value = '';
    try {
        await api.post('/password/forgot', { email: email.value });
        submitted.value = true;
    } catch (err) {
        error.value = err.response?.data?.message || 'Une erreur est survenue.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Utiliser les mêmes styles que LoginView ou les globaliser */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.login-container {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  padding: 2rem; background: #020617; overflow: hidden; position: relative; font-family: 'Inter', sans-serif;
}

.bg-glow { position: absolute; width: 500px; height: 500px; border-radius: 50%; filter: blur(120px); z-index: 0; opacity: 0.4; }
.bg-glow-1 { background: #312e81; top: -100px; right: -100px; }
.bg-glow-2 { background: #1e1b4b; bottom: -100px; left: -100px; }

.login-card {
  width: 100%; max-width: 440px; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 3rem; position: relative; z-index: 10;
}

.login-header { text-align: center; margin-bottom: 2.5rem; }
.login-header h1 { font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem; }
.login-header p { color: #94a3b8; font-size: 0.9375rem; }

.back-link { margin-bottom: 1.5rem; }
.back-link a { display: flex; align-items: center; gap: 8px; color: #818cf8; text-decoration: none; font-size: 0.875rem; font-weight: 600; }
.back-link svg { width: 16px; height: 16px; }

.login-form { display: flex; flex-direction: column; gap: 1.5rem; }
.input-group { display: flex; flex-direction: column; gap: 0.625rem; }
.input-group label { font-size: 0.875rem; font-weight: 600; color: #cbd5e1; text-align: left; }

.input-wrapper { position: relative; display: flex; align-items: center; }
.input-wrapper svg { position: absolute; left: 1rem; width: 1.25rem; height: 1.25rem; color: #64748b; }
.input-wrapper input {
  width: 100%; background: #020617; border: 1px solid #1e293b; border-radius: 12px;
  padding: 0.875rem 1rem 0.875rem 3rem; color: white; font-size: 0.9375rem; transition: all 0.2s;
}
.input-wrapper input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

.submit-btn {
  width: 100%; background: #6366f1; color: white; border: none; border-radius: 12px;
  padding: 1rem; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.submit-btn:hover:not(:disabled) { background: #4f46e5; transform: translateY(-1px); }

.success-area { text-align: center; }
.success-icon {
  width: 64px; height: 64px; background: rgba(34, 197, 94, 0.1); border-radius: 50%;
  color: #4ade80; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;
}
.success-icon svg { width: 32px; height: 32px; }
.success-area h3 { color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
.success-area p { color: #94a3b8; font-size: 0.9375rem; margin-bottom: 2rem; }

.btn-secondary {
  display: block; width: 100%; padding: 1rem; background: #1e293b; color: white;
  text-decoration: none; border-radius: 12px; font-weight: 700;
}

.spinner { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>
