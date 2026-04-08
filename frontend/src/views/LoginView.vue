<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="w-full max-w-md">
      <!-- Glow effect -->
      <div class="absolute -z-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
      
      <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-10">
          <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400 mb-2">
            FilRouge 2026
          </h1>
          <p class="text-slate-400">Connectez-vous à votre espace personnel</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Email</label>
            <input 
              v-model="email"
              type="email" 
              required
              class="w-full bg-slate-950/50 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-300"
              placeholder="admin@admin.com"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Mot de passe</label>
            <input 
              v-model="password"
              type="password" 
              required
              class="w-full bg-slate-950/50 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-300"
              placeholder="••••••••"
            >
          </div>

          <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-3 rounded-lg text-center">
            {{ error }}
          </div>

          <button 
            type="submit"
            :disabled="loading"
            class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-lg transition-all duration-300 transform active:scale-[0.98] shadow-lg shadow-indigo-600/20 flex items-center justify-center space-x-2 disabled:opacity-50"
          >
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ loading ? 'Connexion...' : 'Se connecter' }}</span>
          </button>
        </form>

        <div class="mt-8 text-center text-sm text-slate-500">
          <p>© 2026 YouCode FilRouge. Tous droits réservés.</p>
        </div>
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
const error = ref('');
const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await api.post('/login', {
            email: email.value,
            password: password.value
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
        error.value = err.response?.data?.message || 'Une erreur est survenue lors de la connexion. Vérifiez que le serveur backend est démarré.';
    } finally {
        loading.value = false;
    }
};
</script>
