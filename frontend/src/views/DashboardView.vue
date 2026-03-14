<template>
  <div class="min-h-screen bg-slate-950 text-slate-200">
    <nav class="bg-slate-900/50 backdrop-blur-lg border-b border-slate-800 px-6 py-4 flex items-center justify-between sticky top-0 z-50">
      <div class="flex items-center space-x-2">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-600/30">F</div>
        <span class="text-xl font-bold tracking-tight">FilRouge<span class="text-indigo-500">2026</span></span>
      </div>
      
      <div class="flex items-center space-x-6">
        <div class="hidden md:flex flex-col items-end">
          <span class="text-sm font-semibold text-slate-200">{{ user?.first_name }} {{ user?.last_name }}</span>
          <span class="text-xs text-slate-400 capitalize">{{ user?.role }}</span>
        </div>
        <button 
          @click="handleLogout"
          class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white px-4 py-2 rounded-lg text-sm transition-all duration-300 font-medium border border-slate-700"
        >
          Déconnexion
        </button>
      </div>
    </nav>

    <main class="p-8 max-w-7xl mx-auto">
      <header class="mb-10">
        <h2 class="text-3xl font-bold text-white mb-2">Tableau de bord</h2>
        <p class="text-slate-400">Bienvenue dans votre espace de gestion.</p>
      </header>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats Card -->
        <div v-for="i in 3" :key="i" class="bg-slate-900/40 border border-slate-800 p-6 rounded-2xl hover:border-indigo-500/50 transition-all duration-500 group">
          <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-500">
             <div class="w-5 h-5 bg-indigo-500 rounded-sm"></div>
          </div>
          <div class="text-slate-400 text-sm mb-1 uppercase tracking-wider font-medium">Statistique {{ i }}</div>
          <div class="text-3xl font-bold text-white font-mono tracking-tight">85%</div>
        </div>
      </div>

      <section class="mt-12 bg-slate-900/40 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-lg text-white">Activités Récentes</h3>
            <button class="text-indigo-400 text-sm font-medium hover:text-indigo-300">Voir tout</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-950/50 text-slate-400 text-sm">
                <th class="px-6 py-4 font-medium">Module</th>
                <th class="px-6 py-4 font-medium">Status</th>
                <th class="px-6 py-4 font-medium">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
              <tr v-for="i in 5" :key="i" class="hover:bg-indigo-500/5 transition-colors">
                <td class="px-6 py-4 font-medium text-slate-200">Projet Fil Rouge {{ i }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 bg-green-500/10 text-green-400 text-xs font-medium rounded-full">Actif</span>
                </td>
                <td class="px-6 py-4 text-slate-400 text-sm font-mono">2026-03-12</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const user = ref(null);

onMounted(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
        user.value = JSON.parse(userData);
    }
});

const handleLogout = async () => {
    try {
        await api.post('/logout');
    } catch (err) {
        console.error('Logout error', err);
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        router.push('/login');
    }
};
</script>
