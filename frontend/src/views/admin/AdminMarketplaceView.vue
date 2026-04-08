<template>
  <div class="layout">
    <SidebarAdmin :user="currentUser" @logout="handleLogout" />

    <main class="main">
      <div class="content">

        <!-- ===== TOPBAR ===== -->
        <header class="topbar animate-in">
          <div class="topbar-left">
            <h1 class="topbar-title">Marketplace Admin</h1>
            <p class="topbar-sub">Gestion des récompenses & suivi des commandes</p>
          </div>
          <div class="topbar-right">
             <button class="btn-primary" @click="showProductModal = true">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
               Nouveau Produit
             </button>
          </div>
        </header>

        <!-- ===== TABS ===== -->
        <div class="tab-row animate-in" style="animation-delay: 0.1s">
          <button class="tab" :class="{ active: activeTab === 'orders' }" @click="activeTab = 'orders'">
            Commandes ({{ orders.length }})
          </button>
          <button class="tab" :class="{ active: activeTab === 'products' }" @click="activeTab = 'products'">
            Catalogue
          </button>
        </div>

        <!-- ===== ORDERS VIEW ===== -->
        <div v-if="activeTab === 'orders'" class="panel animate-in" style="animation-delay: 0.2s">
          <div class="table-container">
            <table class="nadi-table">
              <thead>
                <tr>
                  <th>Client</th>
                  <th>Produit</th>
                  <th>Points</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th class="actions-col">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td>
                    <div class="user-cell">
                      <img :src="getAvatar(order.user?.first_name)" class="u-avatar" />
                      <div class="u-info">
                        <span class="u-name">{{ order.user?.first_name || 'Inconnu' }} {{ order.user?.last_name || '' }}</span>
                        <span class="u-meta">ID: #{{ order.user_id }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="prod-name">{{ order.product?.name || 'Produit supprimé' }}</span>
                  </td>
                  <td>
                    <span class="xp-val">{{ order.price_at_purchase }} XP</span>
                  </td>
                  <td>
                    <span class="date-val">{{ formatDate(order.created_at) }}</span>
                  </td>
                  <td>
                    <span class="status-badge" :class="order.status.toLowerCase()">
                      {{ 
                        order.status === 'PENDING' ? 'En attente' : 
                        order.status === 'DELIVERED' ? 'Terminé' : 'Annulé'
                      }}
                    </span>
                  </td>
                  <td class="actions-col">
                    <div v-if="order.status === 'PENDING'" class="action-buttons">
                      <button class="btn-check" @click="completeOrder(order.id)" title="Valider">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                      <button class="btn-reject" @click="cancelOrder(order.id)" title="Refuser">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!isLoading && !orders.length">
                  <td colspan="6" class="empty-state">Aucune commande en attente</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== PRODUCTS VIEW ===== -->
        <div v-if="activeTab === 'products'" class="grid animate-in" style="animation-delay: 0.2s">
          <div v-for="prod in products" :key="prod.id" class="prod-card">
            <img :src="prod.image || 'https://via.placeholder.com/300x200?text=Product'" class="prod-img" />
            <div class="prod-body">
              <div class="prod-top">
                <h3 class="p-name">{{ prod.name }}</h3>
                <span class="p-price">{{ prod.price }} XP</span>
              </div>
              <p class="p-desc">{{ prod.description }}</p>
              <div class="p-footer">
                <span class="p-stock">Stock: {{ prod.quantity }}</span>
                <button class="btn-delete" @click="deleteProduct(prod.id)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- Product Modal -->
    <Transition name="fade">
      <div v-if="showProductModal" class="modal-overlay" @click.self="showProductModal = false">
        <div class="modal-content">
          <h2 class="modal-title">Ajouter un produit</h2>
          <div class="form-group">
            <label>Nom du produit</label>
            <input v-model="newProduct.name" type="text" placeholder="..." />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Prix (XP)</label>
              <input v-model="newProduct.price" type="number" />
            </div>
            <div class="form-group">
              <label>Quantité</label>
              <input v-model="newProduct.quantity" type="number" />
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="newProduct.description"></textarea>
          </div>
          <div class="form-group">
            <label>URL Image</label>
            <input v-model="newProduct.image" type="text" />
          </div>
          <div class="modal-actions">
            <button class="btn-secondary" @click="showProductModal = false">Annuler</button>
            <button class="btn-primary" @click="createProduct">Créer le produit</button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SidebarAdmin from '../../components/SidebarAdmin.vue';
import api from '../../services/api';

const router = useRouter();
const currentUser = ref(JSON.parse(localStorage.getItem('user')) || {});
const products = ref([]);
const orders = ref([]);
const activeTab = ref('orders');
const isLoading = ref(true);
const showProductModal = ref(false);

const newProduct = ref({
  name: '',
  description: '',
  price: 100,
  quantity: 10,
  image: ''
});

const getAvatar = (name) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'U')}&background=21262d&color=a371f7&bold=true`;
const formatDate = (date) => new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });

const fetchData = async () => {
  try {
    isLoading.value = true;
    const [pRes, oRes] = await Promise.all([
      api.get('/marketplace/products'),
      api.get('/admin/marketplace/orders')
    ]);
    products.value = pRes.data.data;
    orders.value = oRes.data.data;
  } catch (err) {
    // Error logged silently in production
  } finally {
    isLoading.value = false;
  }
};

const createProduct = async () => {
  try {
    // Ensure numbers are numbers and empty image is null
    const payload = {
      ...newProduct.value,
      price: parseInt(newProduct.value.price),
      quantity: parseInt(newProduct.value.quantity),
      image: newProduct.value.image.trim() || null
    };

    await api.post('/admin/marketplace/products', payload);
    showProductModal.value = false;
    newProduct.value = { name: '', description: '', price: 100, quantity: 10, image: '' };
    fetchData();
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.error || 'Erreur lors de la création';
    alert(msg);
    console.error('Create error:', err.response?.data);
  }
};

const completeOrder = async (id) => {
  if (!confirm('Voulez-vous marquer cette commande comme complétée ?')) return;
  try {
    await api.patch(`/admin/marketplace/orders/${id}/complete`);
    fetchData();
  } catch (err) {
    const msg = err.response?.data?.error || err.response?.data?.message || 'Erreur lors de la validation';
    alert(msg);
  }
};

const cancelOrder = async (id) => {
  if (!confirm('Voulez-vous refuser cette commande ? Les points seront remboursés à l\'étudiant.')) return;
  try {
    await api.patch(`/admin/marketplace/orders/${id}/cancel`);
    fetchData();
  } catch (err) {
    const msg = err.response?.data?.error || err.response?.data?.message || 'Erreur lors de l\'annulation';
    alert(msg);
  }
};

const deleteProduct = async (id) => {
  if (!confirm('Voulez-vous supprimer ce produit ? Cela annulera les commandes en attente.')) return;
  try {
    await api.delete(`/admin/marketplace/products/${id}`);
    fetchData();
  } catch (err) {
    alert('Erreur lors de la suppression');
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(fetchData);
</script>

<style scoped>
.layout { display: flex; height: 100vh; background: #010409; color: #e6edf3; overflow: hidden; }
.main { flex: 1; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #21262d transparent; }
.content { padding: 40px; max-width: 1400px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: center; }
.topbar-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
.topbar-sub { color: #8b949e; font-size: 14px; margin-top: 4px; }

/* Tabs */
.tab-row { display: flex; gap: 24px; border-bottom: 1px solid #21262d; }
.tab { background: none; border: none; padding: 12px 0; font-size: 14px; font-weight: 700; color: #484f58; cursor: pointer; position: relative; }
.tab.active { color: #a371f7; }
.tab.active::after { content: ""; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px; background: #a371f7; }

/* Table */
.panel { background: #0d1117; border: 1px solid #21262d; border-radius: 16px; overflow: hidden; }
.table-container { width: 100%; border-collapse: collapse; }
.nadi-table { width: 100%; border-collapse: collapse; text-align: left; }
.nadi-table th { background: rgba(22, 27, 34, 0.5); padding: 16px 24px; font-size: 11px; font-weight: 800; color: #484f58; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #21262d; }
.nadi-table td { padding: 16px 24px; border-bottom: 1px solid rgba(48, 54, 61, 0.4); }

.user-cell { display: flex; align-items: center; gap: 12px; }
.u-avatar { width: 32px; height: 32px; border-radius: 8px; }
.u-name { display: block; font-weight: 700; font-size: 13px; }
.u-meta { display: block; font-size: 10px; color: #484f58; }

.xp-val { font-weight: 800; color: #d29922; }
.status-badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.action-buttons { display: flex; gap: 8px; }
.btn-check { width: 32px; height: 32px; border-radius: 8px; background: #238636; border: none; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-check:hover { background: #2ea043; transform: scale(1.1); }
.btn-check svg, .btn-reject svg { width: 16px; height: 16px; }

.btn-reject { width: 32px; height: 32px; border-radius: 8px; background: #f85149; border: none; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-reject:hover { background: #ff7b72; transform: scale(1.1); }

.status-badge.pending { background: rgba(210, 153, 34, 0.1); color: #d29922; }
.status-badge.delivered { background: rgba(35, 134, 54, 0.1); color: #3fb950; }
.status-badge.cancelled { background: rgba(248, 81, 73, 0.1); color: #f85149; }

/* Product Grid */
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
.prod-card { background: #0d1117; border: 1px solid #21262d; border-radius: 20px; overflow: hidden; transition: all 0.3s; }
.prod-card:hover { border-color: #a371f7; transform: translateY(-4px); }
.prod-img { width: 100%; height: 160px; object-fit: cover; }
.prod-body { padding: 20px; }
.prod-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.p-name { font-size: 16px; font-weight: 700; }
.p-price { font-weight: 800; color: #d29922; }
.p-desc { font-size: 12px; color: #8b949e; line-height: 1.5; height: 36px; overflow: hidden; margin-bottom: 20px; }
.p-footer { display: flex; justify-content: space-between; align-items: center; }
.p-stock { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; }
.btn-delete { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #30363d; background: transparent; color: #484f58; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-delete:hover { border-color: #f85149; color: #f85149; background: rgba(248, 81, 73, 0.05); }
.btn-delete svg { width: 16px; height: 16px; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: #0d1117; border: 1px solid #30363d; border-radius: 20px; padding: 32px; width: 500px; display: flex; flex-direction: column; gap: 20px; }
.modal-title { font-size: 20px; font-weight: 800; }

.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 12px; font-weight: 700; color: #8b949e; }
.form-group input, .form-group textarea { background: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 12px; color: white; font-family: inherit; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.modal-actions { display: flex; gap: 12px; margin-top: 12px; }

.btn-primary { background: #a371f7; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
.btn-primary:hover { background: #b085f9; transform: translateY(-2px); }
.btn-secondary { flex: 1; background: none; border: 1px solid #30363d; color: #8b949e; border-radius: 12px; padding: 12px; cursor: pointer; }

/* Animations */
.animate-in { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
