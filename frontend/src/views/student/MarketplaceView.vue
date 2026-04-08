<template>
  <div class="flex h-screen overflow-hidden font-body bg-[#0A0F27] text-white">
    
    <!-- Sidebar -->
    <SidebarStudent :user="user" @logout="handleLogout" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-y-auto custom-scrollbar">
      
      <!-- Minimalist Header -->
      <header class="h-16 flex items-center justify-between px-10 bg-[#0A0F27]/80 backdrop-blur-xl border-b border-white/5 sticky top-0 z-30">
          <div class="flex items-center gap-6">
              <h1 class="text-xl font-bold tracking-tight text-white uppercase font-display">Marketplace</h1>
              <div class="h-4 w-px bg-white/10"></div>
              <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase">Boutique YouCode</p>
          </div>
          <div class="flex items-center gap-4 bg-blue-600/10 px-4 py-1.5 rounded-lg border border-blue-600/20">
              <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">Mon Solde :</span>
              <span class="text-sm font-black text-white italic tracking-tight">{{ user?.points || 0 }} Pts</span>
          </div>
      </header>

      <div class="p-10 max-w-[1400px] mx-auto w-full animate-in fade-in duration-700 pb-32">
        
        <div class="mb-12 space-y-2">
            <h2 class="text-4xl font-bold tracking-tight text-white">Récompenses & Équipements</h2>
            <p class="text-slate-400 font-medium text-base opacity-70">Utilisez vos points accumulés lors de vos missions pour débloquer des avantages ou du matériel.</p>
        </div>

        <!-- Professional Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <div v-for="product in products" :key="product.id" 
                 class="bg-[#111827] rounded-2xl overflow-hidden border border-white/5 group hover:border-blue-600/30 transition-all duration-300 flex flex-col shadow-xl">
                
                <div class="aspect-square w-full relative overflow-hidden bg-[#0A0F27]">
                    <img :src="product.image" class="w-full h-full object-cover grayscale opacity-40 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500 group-hover:scale-105">
                </div>

                <div class="p-8 flex-1 flex flex-col space-y-4">
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold tracking-tight text-white leading-none">{{ product.name }}</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ product.quantity > 0 ? 'En stock' : 'Rupture' }}</p>
                    </div>
                    <p class="text-sm text-slate-400 font-medium line-clamp-2 leading-relaxed italic opacity-80 flex-1">"{{ product.description }}"</p>
                    
                    <div class="pt-6 border-t border-white/5 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1">Prix</span>
                            <span class="text-xl font-black text-blue-500 leading-none">{{ product.price }} <span class="text-[11px] font-bold uppercase tracking-widest">Pts</span></span>
                        </div>
                        <button @click="purchase(product)" 
                                :disabled="product.quantity <= 0 || (user?.points < product.price)"
                                class="bg-white text-[#0A0F27] hover:bg-blue-600 hover:text-white font-bold px-6 py-2.5 rounded-xl text-[10px] uppercase tracking-widest transition-all disabled:opacity-20 disabled:cursor-not-allowed active:scale-95 shadow-xl">
                            Acheter
                        </button>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const products = ref([
    { id: 1, name: 'YouCode Tech Hoodie', price: 1500, quantity: 10, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=2070&auto=format&fit=crop', description: 'Sweat à capuche premium aux couleurs de YouCode. Confort et style.' },
    { id: 2, name: 'License Coursera Pro', price: 800, quantity: 5, image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070&auto=format&fit=crop', description: 'Accès illimité aux certifications professionnelles pendant 3 mois.' },
    { id: 3, name: 'Pass VIP Event YouCode', price: 500, quantity: 20, image: 'https://images.unsplash.com/photo-1475721027187-402ad2989a38?q=80&w=2070&auto=format&fit=crop', description: 'Accès aux premières loges pour le prochain grand événement YouCode.' },
    { id: 4, name: 'Pack Stickers Dev', price: 100, quantity: 50, image: 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?q=80&w=2068&auto=format&fit=crop', description: 'Lot de 20 stickers exclusifs pour personnaliser votre laptop.' }
]);

onMounted(async () => {
    const userData = localStorage.getItem('user');
    if (userData) {
        user.value = JSON.parse(userData);
    }
    // Fetch real products if API is ready
    try {
        const response = await api.get('/marketplace/products');
        if (response.data && response.data.length > 0) {
            products.value = response.data;
        }
    } catch (err) {
        console.warn('API Marketplace non disponible, utilisation du mock.');
    }
});

const purchase = async (product) => {
    try {
        await api.post(`/marketplace/purchase/${product.id}`);
        // Refresh profile to update points
        const userRes = await api.get('/user');
        user.value = userRes.data;
        localStorage.setItem('user', JSON.stringify(userRes.data));
    } catch (err) {
        console.error('Achat échoué', err);
    }
};

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

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-in {
  animation: fade-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.05); 
  border-radius: 10px;
}
</style>
