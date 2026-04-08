<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left animate-in">
          <div class="topbar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
          </div>
          <div>
            <h1 class="topbar-title">Marketplace</h1>
            <p class="topbar-sub">Échangez vos points contre des récompenses</p>
          </div>
        </div>
        <div class="topbar-right animate-in">
          <!-- Points Balance -->
          <div class="balance-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span class="bal-pts">{{ userPoints }}</span>
            <span class="bal-label">points</span>
          </div>
          <!-- Orders drawer toggle -->
          <button class="orders-toggle-btn" @click="showOrdersDrawer = !showOrdersDrawer" :class="{ active: showOrdersDrawer }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Mes Achats</span>
            <span v-if="myOrders.length > 0" class="orders-count">{{ myOrders.length }}</span>
          </button>
        </div>
      </header>

      <div class="page-body">

        <!-- Main content -->
        <div class="content" :class="{ 'content--narrow': showOrdersDrawer }">

          <!-- Filter bar -->
          <div class="filter-bar animate-in">
            <div class="filters">
              <button v-for="cat in categories" :key="cat" class="filter-btn" :class="{ 'filter-btn--active': activeCategory === cat }" @click="activeCategory = cat">
                {{ cat }}
              </button>
            </div>
            <div class="search-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input v-model="search" type="text" placeholder="Rechercher un article..." />
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="loading-state animate-in">
            <div class="spinner"></div>
            <p>Chargement de la boutique...</p>
          </div>

          <!-- Empty -->
          <div v-else-if="filteredProducts.length === 0" class="empty-state animate-in">
            <div class="empty-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3>Boutique vide</h3>
            <p>Aucun article ne correspond à votre recherche.</p>
          </div>

          <!-- Products Grid -->
          <div v-else class="products-grid">
            <div
              v-for="(product, idx) in filteredProducts"
              :key="product.id"
              class="product-card animate-in"
              :style="{ animationDelay: `${idx * 0.06}s` }"
            >
              <div class="product-img-wrap">
                <img :src="product.image || 'https://images.unsplash.com/photo-1618517351616-38fb9c5210c6?w=400&q=80'" :alt="product.name" class="product-img" />
                <div class="img-overlay"></div>
                <div v-if="product.quantity > 0 && product.quantity < 5" class="stock-badge">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4m0 4h.01"/></svg>
                  {{ product.quantity }} restant{{ product.quantity > 1 ? 's' : '' }}
                </div>
                <div v-if="product.quantity === 0" class="rupture-badge">Rupture</div>
              </div>

              <div class="product-info">
                <h3 class="product-name">{{ product.name }}</h3>
                <p class="product-desc">{{ product.description }}</p>

                <div class="product-footer">
                  <div class="price-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>{{ product.price }} pts</span>
                  </div>
                  <button
                    class="buy-btn"
                    :class="{
                      'buy-btn--ok': userPoints >= product.price && product.quantity > 0,
                      'buy-btn--disabled': userPoints < product.price || product.quantity === 0
                    }"
                    :disabled="userPoints < product.price || product.quantity === 0 || purchasing === product.id"
                    @click="confirmPurchase(product)"
                  >
                    <div v-if="purchasing === product.id" class="spinner-sm"></div>
                    <span v-else>{{ product.quantity === 0 ? 'Rupture' : (userPoints < product.price ? 'Insuffisant' : 'Acheter') }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Drawer -->
        <Transition name="slide-right">
          <aside v-if="showOrdersDrawer" class="orders-drawer">
            <div class="drawer-header">
              <h2>Mes Achats</h2>
              <button class="drawer-close" @click="showOrdersDrawer = false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
              </button>
            </div>

            <div v-if="loadingOrders" class="drawer-loading">
              <div class="spinner"></div>
            </div>
            <div v-else-if="myOrders.length === 0" class="drawer-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              <p>Aucun achat pour le moment.</p>
            </div>
            <div v-else class="orders-list">
              <div v-for="order in myOrders" :key="order.id" class="order-row animate-in">
                <div class="order-img-box">
                  <img :src="order.product_image || 'https://images.unsplash.com/photo-1618517351616-38fb9c5210c6?w=80'" :alt="order.product_name" />
                </div>
                <div class="order-details">
                  <p class="order-name">{{ order.product_name || 'Article #' + order.product_id }}</p>
                  <p class="order-price">{{ order.price_at_purchase }} pts</p>
                  <span class="order-status" :class="`status--${order.status?.toLowerCase()}`">{{ order.status }}</span>
                </div>
                <div class="order-date">{{ formatDate(order.created_at) }}</div>
              </div>
            </div>
          </aside>
        </Transition>
      </div>
    </main>

    <!-- Purchase Confirm Modal -->
    <Transition name="modal">
      <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
        <div class="nadi-modal">
          <div class="modal-head">
            <h3>Confirmer l'achat</h3>
            <button class="nadi-close-btn" @click="showModal = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="purchase-preview">
              <img :src="selectedProduct?.image" :alt="selectedProduct?.name" class="preview-img"/>
              <div class="preview-info">
                <p class="preview-name">{{ selectedProduct?.name }}</p>
                <div class="preview-price-chip">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  {{ selectedProduct?.price }} points
                </div>
              </div>
            </div>

            <div class="balance-breakdown">
              <div class="bb-row">
                <span>Solde actuel</span>
                <span class="val-green">{{ userPoints }} pts</span>
              </div>
              <div class="bb-row">
                <span>Coût de l'article</span>
                <span class="val-red">- {{ selectedProduct?.price }} pts</span>
              </div>
              <div class="bb-sep"></div>
              <div class="bb-row bb-final">
                <span>Nouveau solde</span>
                <span>{{ userPoints - (selectedProduct?.price || 0) }} pts</span>
              </div>
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn-nadi-cancel" @click="showModal = false">Annuler</button>
            <button class="btn-nadi-confirm" @click="handlePurchase" :disabled="purchasing !== null">
              <div v-if="purchasing" class="spinner-sm"></div>
              <span v-else>Confirmer</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Success Toast -->
    <Transition name="toast">
      <div v-if="successToast" class="success-toast">
        <div class="toast-glow"></div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <div>
          <p class="toast-title">Achat validé !</p>
          <p class="toast-sub">L'article a été ajouté à votre inventaire.</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router = useRouter();
const user = ref(null);
const userPoints = ref(0);
const products = ref([]);
const loading = ref(true);
const purchasing = ref(null);
const search = ref('');
const activeCategory = ref('Tout');
const categories = ['Tout', 'Vêtements', 'Accessoires', 'Tech', 'Papeterie'];

const showModal = ref(false);
const selectedProduct = ref(null);
const successToast = ref(false);

const showOrdersDrawer = ref(false);
const myOrders = ref([]);
const loadingOrders = ref(false);

// ─── Computed ─────────────────────────────────────────────────────────────────
const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchesSearch = !search.value ||
      p.name.toLowerCase().includes(search.value.toLowerCase()) ||
      p.description.toLowerCase().includes(search.value.toLowerCase());
    const matchesCategory = activeCategory.value === 'Tout';
    return matchesSearch && matchesCategory;
  });
});

// ─── Methods ──────────────────────────────────────────────────────────────────
const fetchProducts = async () => {
  try {
    const res = await api.get('/marketplace/products');
    products.value = res.data.data || [];
  } catch (err) {
    console.error('Fetch products error:', err);
  } finally {
    loading.value = false;
  }
};

const fetchMyOrders = async () => {
  loadingOrders.value = true;
  try {
    const res = await api.get('/marketplace/my-orders');
    myOrders.value = res.data.data || [];
  } catch (err) {
    console.error('Fetch orders error:', err);
  } finally {
    loadingOrders.value = false;
  }
};

const confirmPurchase = (product) => {
  selectedProduct.value = product;
  showModal.value = true;
};

const handlePurchase = async () => {
  if (!selectedProduct.value) return;
  purchasing.value = selectedProduct.value.id;
  showModal.value = false;

  try {
    await api.post(`/marketplace/purchase/${selectedProduct.value.id}`);

    // Update local points
    userPoints.value -= selectedProduct.value.price;
    if (user.value) {
      user.value.total_points = userPoints.value;
      localStorage.setItem('user', JSON.stringify(user.value));
    }

    await Promise.all([fetchProducts(), fetchMyOrders()]);

    successToast.value = true;
    setTimeout(() => { successToast.value = false; }, 4000);
  } catch (err) {
    alert(err.response?.data?.error || "Erreur lors de l'achat.");
  } finally {
    purchasing.value = null;
    selectedProduct.value = null;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
};

const handleLogout = async () => {
  try { await api.post('/logout'); } catch {}
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

// Auto-load orders when drawer opens
watch(showOrdersDrawer, (val) => {
  if (val && myOrders.value.length === 0) fetchMyOrders();
});

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  const cached = localStorage.getItem('user');
  if (cached) {
    user.value = JSON.parse(cached);
    // Lire les points depuis la clé disponible (points ou total_points selon la session)
    userPoints.value = user.value.points ?? user.value.total_points ?? 0;
  }

  // Récupérer les points frais depuis l'API (dashboard endpoint)
  try {
    const res = await api.get('/student/dashboard');
    if (res.data?.stats?.total_points !== undefined) {
      userPoints.value = res.data.stats.total_points;
      // Mettre à jour le localStorage pour les prochaines pages
      if (user.value) {
        user.value.points = userPoints.value;
        localStorage.setItem('user', JSON.stringify(user.value));
      }
    }
  } catch {
    // Silently fail - l'affichage depuis localStorage est suffisant
  }

  fetchProducts();
});
</script>

<style scoped>
* { box-sizing: border-box; }

/* ─── Layout ────────────────────────────────────────────────────────────────── */
.layout { display: flex; height: 100vh; overflow: hidden; background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif; }
.main { flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
.page-body { flex: 1; display: flex; overflow: hidden; }

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar { height: 64px; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; background: #010409; z-index: 20; }
.topbar-left { display: flex; align-items: center; gap: 14px; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(56,139,253,0.08); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; color: #79c0ff; }
.topbar-icon svg { width: 18px; height: 18px; }
.topbar-title { font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
.topbar-sub { font-size: 11px; color: #484f58; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; margin-top: 1px; }

.balance-chip { display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(210,153,34,0.06); border: 1px solid rgba(210,153,34,0.2); border-radius: 10px; }
.balance-chip svg { width: 16px; height: 16px; color: #e3b341; }
.bal-pts { font-size: 18px; font-weight: 900; color: #e3b341; font-family: 'JetBrains Mono', monospace; }
.bal-label { font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.08em; }

.orders-toggle-btn { display: flex; align-items: center; gap: 8px; padding: 10px 18px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; color: #c9d1d9; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.orders-toggle-btn svg { width: 18px; height: 18px; }
.orders-toggle-btn:hover, .orders-toggle-btn.active { background: rgba(56,139,253,0.08); border-color: rgba(56,139,253,0.3); color: #79c0ff; }
.orders-count { min-width: 20px; height: 20px; background: #388bfd; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; padding: 0 6px; box-shadow: 0 0 10px rgba(56,139,253,0.4); }

/* ─── Content ───────────────────────────────────────────────────────────────── */
.content { flex: 1; padding: 28px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; scrollbar-width: thin; scrollbar-color: #21262d transparent; transition: all 0.3s ease; }
.content::-webkit-scrollbar { width: 4px; }
.content::-webkit-scrollbar-thumb { background: #21262d; border-radius: 4px; }
.content--narrow { flex: none; width: calc(100% - 380px); }

/* ─── Filter Bar ────────────────────────────────────────────────────────────── */
.filter-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.filters { display: flex; gap: 8px; flex-wrap: wrap; }
.filter-btn { padding: 7px 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; font-size: 12px; font-weight: 700; color: #8b949e; cursor: pointer; transition: all 0.15s; text-transform: uppercase; letter-spacing: 0.05em; }
.filter-btn:hover { border-color: rgba(255,255,255,0.12); color: #c9d1d9; }
.filter-btn--active { background: rgba(56,139,253,0.08); border-color: rgba(56,139,253,0.3); color: #79c0ff; }

.search-wrap { position: relative; min-width: 240px; }
.search-wrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #8b949e; }
.search-wrap input { width: 100%; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 10px 16px 10px 40px; font-size: 13px; color: #fff; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none; }
.search-wrap input::placeholder { color: #484f58; font-weight: 500; }
.search-wrap input:focus { border-color: #388bfd; background: rgba(56,139,253,0.03); }

/* ─── Products Grid ─────────────────────────────────────────────────────────── */
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }

.product-card { background: #0d1117; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; transition: all 0.25s cubic-bezier(0.16,1,0.3,1); }
.product-card:hover { transform: translateY(-6px); border-color: rgba(255,255,255,0.12); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }

.product-img-wrap { aspect-ratio: 4/3; overflow: hidden; position: relative; background: #161b22; }
.product-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.img-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 60%, rgba(1,4,9,0.7)); }
.product-card:hover .product-img { transform: scale(1.08); }

.stock-badge { position: absolute; top: 12px; right: 12px; background: rgba(248,81,73,0.85); backdrop-filter: blur(4px); color: white; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; display: flex; align-items: center; gap: 4px; }
.stock-badge svg { width: 12px; height: 12px; }
.rupture-badge { position: absolute; top: 12px; right: 12px; background: rgba(22,27,34,0.9); border: 1px solid #30363d; color: #8b949e; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; }

.product-info { padding: 20px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
.product-name { font-size: 16px; font-weight: 800; color: #e6edf3; letter-spacing: -0.01em; line-height: 1.3; }
.product-desc { font-size: 13px; color: #8b949e; line-height: 1.6; flex: 1; }

.product-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 4px; }
.price-chip { display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: rgba(210,153,34,0.06); border: 1px solid rgba(210,153,34,0.2); border-radius: 8px; font-size: 13px; font-weight: 800; color: #e3b341; }
.price-chip svg { width: 14px; height: 14px; }

.buy-btn { padding: 9px 20px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s; border: none; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
.buy-btn--ok { background: #238636; color: white; border: 1px solid #2ea043; }
.buy-btn--ok:hover { background: #2ea043; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(35,134,54,0.3); }
.buy-btn--disabled { background: rgba(255,255,255,0.03); color: #484f58; border: 1px solid rgba(255,255,255,0.06); cursor: not-allowed; }

/* ─── Orders Drawer ──────────────────────────────────────────────────────────── */
.orders-drawer { width: 370px; flex-shrink: 0; border-left: 1px solid rgba(255,255,255,0.06); background: #0d1117; display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.drawer-header { padding: 24px 24px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center; }
.drawer-header h2 { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
.drawer-close { background: transparent; border: none; color: #8b949e; cursor: pointer; padding: 6px; border-radius: 8px; transition: all 0.2s; }
.drawer-close:hover { background: rgba(255,255,255,0.05); color: #fff; }
.drawer-close svg { width: 20px; height: 20px; }

.drawer-loading { flex: 1; display: flex; align-items: center; justify-content: center; }
.drawer-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; color: #484f58; }
.drawer-empty svg { width: 40px; height: 40px; }
.drawer-empty p { font-size: 13px; font-weight: 500; }

.orders-list { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; scrollbar-width: none; }
.orders-list::-webkit-scrollbar { display: none; }

.order-row { display: flex; gap: 14px; align-items: flex-start; padding: 14px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; transition: border-color 0.2s; }
.order-row:hover { border-color: rgba(255,255,255,0.1); }
.order-img-box { width: 52px; height: 52px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: #161b22; }
.order-img-box img { width: 100%; height: 100%; object-fit: cover; }
.order-details { flex: 1; display: flex; flex-direction: column; gap: 4px; }
.order-name { font-size: 14px; font-weight: 700; color: #e6edf3; line-height: 1.3; }
.order-price { font-size: 12px; font-weight: 700; color: #e3b341; }
.order-status { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 2px 8px; border-radius: 6px; width: fit-content; }
.status--pending { background: rgba(210,153,34,0.1); color: #e3b341; border: 1px solid rgba(210,153,34,0.3); }
.status--completed, .status--approved { background: rgba(35,134,54,0.1); color: #56d364; border: 1px solid rgba(35,134,54,0.3); }
.order-date { font-size: 10px; font-weight: 600; color: #484f58; white-space: nowrap; padding-top: 2px; }

/* ─── Modal ─────────────────────────────────────────────────────────────────── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(1,4,9,0.8); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.nadi-modal { width: 100%; max-width: 420px; background: #0d1117; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.5); }

.modal-head { display: flex; justify-content: space-between; align-items: center; padding: 24px 28px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.modal-head h3 { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
.nadi-close-btn { background: transparent; border: none; color: #8b949e; cursor: pointer; padding: 4px; border-radius: 6px; }
.nadi-close-btn:hover { color: #ff7b72; }
.nadi-close-btn svg { width: 22px; height: 22px; }

.modal-body { padding: 24px 28px; display: flex; flex-direction: column; gap: 20px; }
.purchase-preview { display: flex; gap: 16px; align-items: center; padding: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; }
.preview-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; }
.preview-name { font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 8px; }
.preview-price-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.3); border-radius: 8px; font-size: 13px; font-weight: 800; color: #e3b341; }
.preview-price-chip svg { width: 14px; height: 14px; }

.balance-breakdown { display: flex; flex-direction: column; gap: 10px; }
.bb-row { display: flex; justify-content: space-between; font-size: 14px; color: #8b949e; font-weight: 500; }
.val-green { color: #56d364; font-weight: 700; }
.val-red { color: #ff7b72; font-weight: 700; }
.bb-sep { height: 1px; background: rgba(255,255,255,0.06); margin: 4px 0; }
.bb-final { color: #fff; font-weight: 800; font-size: 15px; }

.modal-foot { padding: 16px 28px 24px; display: flex; gap: 12px; }
.btn-nadi-cancel { flex: 1; padding: 12px; background: transparent; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #8b949e; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.btn-nadi-cancel:hover { border-color: rgba(255,255,255,0.2); color: #fff; }
.btn-nadi-confirm { flex: 1.5; padding: 12px; background: #238636; border: 1px solid #2ea043; border-radius: 10px; color: white; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-nadi-confirm:hover:not(:disabled) { background: #2ea043; box-shadow: 0 4px 15px rgba(35,134,54,0.3); }
.btn-nadi-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── Toast ─────────────────────────────────────────────────────────────────── */
.success-toast { position: fixed; bottom: 32px; right: 32px; background: #0d1117; border: 1px solid #238636; border-radius: 14px; padding: 18px 22px; display: flex; align-items: center; gap: 14px; box-shadow: 0 8px 30px rgba(0,0,0,0.4), 0 0 20px rgba(35,134,54,0.1); z-index: 2000; }
.toast-glow { position: absolute; inset: -1px; border-radius: 14px; background: radial-gradient(circle at 50% 0%, rgba(35,134,54,0.15), transparent 70%); pointer-events: none; }
.success-toast svg { width: 24px; height: 24px; color: #56d364; flex-shrink: 0; }
.toast-title { font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 2px; }
.toast-sub { font-size: 12px; color: #8b949e; }

/* ─── States ────────────────────────────────────────────────────────────────── */
.loading-state, .empty-state { padding: 80px; text-align: center; color: #484f58; display: flex; flex-direction: column; align-items: center; gap: 16px; }
.empty-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; display: flex; align-items: center; justify-content: center; }
.empty-icon svg { width: 28px; height: 28px; }
.empty-state h3 { font-size: 18px; font-weight: 800; color: #e6edf3; letter-spacing: -0.01em; }
.empty-state p { font-size: 13px; }

/* ─── Spinners ──────────────────────────────────────────────────────────────── */
.spinner { width: 28px; height: 28px; border: 3px solid rgba(56,139,253,0.2); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }
.spinner-sm { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.2); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Animations ────────────────────────────────────────────────────────────── */
@keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.4s cubic-bezier(0.16,1,0.3,1) forwards; }

.slide-right-enter-active, .slide-right-leave-active { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); opacity: 0; }

.modal-enter-active, .modal-leave-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .nadi-modal, .modal-leave-active .nadi-modal { transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.modal-enter-from .nadi-modal { transform: scale(0.92); }
.modal-leave-to .nadi-modal { transform: scale(0.95); opacity: 0; }

.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
.toast-enter-from { transform: translateX(100%) scale(0.9); opacity: 0; }
.toast-leave-to { transform: translateY(20px); opacity: 0; }

/* ─── Responsive ────────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .content--narrow { width: 100%; }
  .orders-drawer { position: fixed; top: 64px; right: 0; bottom: 0; width: 320px; z-index: 100; }
}
</style>
