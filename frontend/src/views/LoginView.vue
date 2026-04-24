<template>
  <div class="login-container">
    <!-- Animated background circles -->
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    
    <div class="login-card animate-in">
      <div class="login-header">
        <div class="logo-area">
          <div class="logo-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <h1>Nadi Dashboard</h1>
        </div>
        <p>FilRouge 2026 · Excellence Académique</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
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

        <div class="input-group">
          <div class="label-row">
            <label>Mot de passe</label>
            <router-link to="/forgot-password" class="forgot-link">Oublié ?</router-link>
          </div>
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

        <div class="options-row">
          <label class="remember-me">
            <input type="checkbox" v-model="rememberMe">
            <span>Se souvenir de moi</span>
          </label>
        </div>

        <Transition name="fade">
          <div v-if="error" class="error-msg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ error }}
          </div>
        </Transition>

        <button 
          type="submit"
          :disabled="loading"
          class="submit-btn"
        >
          <div v-if="loading" class="spinner"></div>
          <span v-else>Se Connecter</span>
          <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>

      <div class="login-footer">
        <p>© 2026 YouCode FilRouge. Propulsé par Nadi OS.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const email = ref('admin@yc.com');
const password = ref('password');
const rememberMe = ref(false);
const error = ref('');
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await api.post('/login', {
            email: email.value,
            password: password.value,
            remember: rememberMe.value
        });
        
        localStorage.setItem('auth_token', response.data.access_token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        
        // Redirection intelligente selon le rôle
        const role = response.data.user.role;
        if (role === 'formateur') {
            router.push('/teacher/dashboard');
        } else if (role === 'admin') {
            router.push('/admin/dashboard');
        } else {
            router.push('/student/dashboard');
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Email ou mot de passe incorrect.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: #020617;
  overflow: hidden;
  position: relative;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Background effects */
.bg-glow {
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  filter: blur(120px);
  z-index: 0;
  opacity: 0.4;
}
.bg-glow-1 {
  background: #312e81;
  top: -100px;
  right: -100px;
}
.bg-glow-2 {
  background: #1e1b4b;
  bottom: -100px;
  left: -100px;
}

.login-card {
  width: 100%;
  max-width: 440px;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 3rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  position: relative;
  z-index: 10;
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.logo-box {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
}
.logo-box svg { width: 32px; height: 32px; }

.login-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: white;
  letter-spacing: -0.025em;
}

.login-header p {
  color: #94a3b8;
  font-size: 0.9375rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.input-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #cbd5e1;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.forgot-link {
  font-size: 0.8125rem;
  color: #818cf8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.forgot-link:hover { color: #a5b4fc; }

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper svg {
  position: absolute;
  left: 1rem;
  width: 1.25rem;
  height: 1.25rem;
  color: #64748b;
  transition: color 0.2s;
}

.input-wrapper input {
  width: 100%;
  background: #020617;
  border: 1px solid #1e293b;
  border-radius: 12px;
  padding: 0.875rem 1rem 0.875rem 3rem;
  color: white;
  font-size: 0.9375rem;
  transition: all 0.2s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.input-wrapper input:focus + svg,
.input-wrapper input:not(:placeholder-shown) + svg {
  color: #6366f1;
}

.options-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
}

.remember-me input {
  width: 1rem;
  height: 1rem;
  border-radius: 4px;
  border: 1px solid #1e293b;
  background: #020617;
  accent-color: #6366f1;
}

.remember-me span {
  font-size: 0.875rem;
  color: #94a3b8;
}

.submit-btn {
  width: 100%;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 1rem;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.submit-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.submit-btn svg {
  width: 1.25rem;
  height: 1.25rem;
}

.error-msg {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.error-msg svg { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }

.login-footer {
  margin-top: 2.5rem;
  text-align: center;
}

.login-footer p {
  font-size: 0.8125rem;
  color: #64748b;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-in {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

