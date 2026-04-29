<template>
  <div class="login-container">
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    
    <div class="login-card animate-in">
      <div class="login-header">
        <h1>Nouveau mot de passe</h1>
        <p>Sécurisez votre compte avec un nouveau mot de passe.</p>
      </div>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="input-group">
          <label>Nouveau mot de passe</label>
          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            <input 
              v-model="password"
              type="password" 
              required
              placeholder="••••••••"
            >
          </div>
        </div>

        <div class="input-group">
          <label>Confirmer le mot de passe</label>
          <div class="input-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            <input 
              v-model="passwordConfirmation"
              type="password" 
              required
              placeholder="••••••••"
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
          <span v-else>Mettre à jour</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');

const handleSubmit = async () => {
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Les mots de passe ne correspondent pas.';
        return;
    }

    loading.value = true;
    error.value = '';
    try {
        await api.post('/password/reset', {
            token: route.query.token,
            email: route.query.email,
            password: password.value,
            password_confirmation: passwordConfirmation.value
        });
        
        alert('Mot de passe mis à jour avec succès !');
        router.push('/login');
    } catch (err) {
        error.value = err.response?.data?.message || 'Une erreur est survenue.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Utiliser les mêmes styles que LoginView */
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

.error-msg {
  background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171; padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.875rem;
}

.spinner { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>
 
