<template>
  <div class="teacher-chat-page">
    <SidebarTeacher :user="user" @logout="handleLogout" />

    <main class="chat-viewport-container">
      <!-- Chat Layout Container -->
      <div class="chat-container-layout">
        
        <!-- Sidebar: Conversations -->
        <aside class="chat-sidebar">
          <div class="sidebar-header">
            <div class="header-top">
              <h1 class="sidebar-title">Messages</h1>
              <button class="new-chat-btn" @click="showNewChatModal = true" title="Nouvelle discussion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            
            <div class="search-wrapper">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-6-6"/>
              </svg>
              <input v-model="searchConversations" type="text" placeholder="Rechercher..." class="sidebar-search-input" />
            </div>

            <div class="filter-tabs">
              <button v-for="cat in categories" :key="cat.id" 
                class="filter-tab" :class="{ 'filter-tab--active': selectedCategory === cat.id }"
                @click="selectedCategory = cat.id"
              >
                {{ cat.label }}
              </button>
            </div>
          </div>

          <div class="conversations-list custom-scrollbar">
            <template v-if="filteredConversations.length > 0">
              <div v-for="chat in filteredConversations" :key="chat.id" 
                class="conversation-card" :class="{ 'card--active': selectedChatId === chat.id }"
                @click="selectChat(chat)"
              >
                <div class="card-avatar-box">
                  <div class="avatar-placeholder" :class="chat.type">
                    {{ getConversationName(chat).charAt(0).toUpperCase() }}
                  </div>
                  <span v-if="chat.online" class="status-indicator online"></span>
                </div>
                
                <div class="card-content">
                  <div class="card-header-row">
                    <span class="card-name">{{ getConversationName(chat) }}</span>
                    <span class="card-time">{{ formatTime(chat.updated_at) }}</span>
                  </div>
                  <div class="card-footer-row">
                    <span class="card-preview" :class="{ 'conv-preview--active': unreadChats.has(chat.id) }">
                      {{ chat.last_message?.content || 'Aucun message' }}
                    </span>
                    <div v-if="unreadChats.has(chat.id)" class="unread-badge"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="empty-conv-list">
              <p>Aucune conversation</p>
            </div>
          </div>
        </aside>

        <!-- Chat Viewport -->
        <section class="chat-viewport">
          <template v-if="selectedChatId">
            <!-- Viewport Header -->
            <header class="viewport-header">
              <div class="active-chat-info">
                <div class="header-avatar" :class="currentChat?.type">
                  {{ getConversationName(currentChat).charAt(0).toUpperCase() }}
                </div>
                <div class="active-details">
                  <div class="active-name">{{ getConversationName(currentChat) }}</div>
                  <div class="active-meta">
                    <span class="pulse-dot"></span>
                    <span class="meta-status">En ligne</span>
                    <span class="divider">|</span>
                    <span class="meta-type">{{ currentChat?.type }}</span>
                  </div>
                </div>
              </div>
              <div class="viewport-actions">
                <button class="action-btn" title="Paramètres">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                </button>
              </div>
            </header>

            <!-- Messages List -->
            <div class="messages-viewport custom-scrollbar" ref="messagesContainer">
              <div v-if="loadingMessages" class="loading-state">
                <div class="loading-spinner"></div>
                <span>Chargement en cours...</span>
              </div>
              
              <template v-else>
                <div v-for="msg in currentMessages" :key="msg.id" 
                  class="message-group" :class="{ 'group--me': isMe(msg) }"
                >
                  <div class="msg-avatar-small" v-if="!isMe(msg)">
                    {{ msg.sender?.first_name.charAt(0) }}
                  </div>
                  <div class="msg-content-wrapper">
                    <div class="msg-balloon" :class="isMe(msg) ? 'balloon--me' : 'balloon--other'">
                      {{ msg.content }}
                    </div>
                    <span class="msg-timestamp">{{ formatTime(msg.created_at) }}</span>
                  </div>
                </div>
              </template>
            </div>

            <!-- Viewport Footer: Input -->
            <footer class="viewport-footer">
              <div class="input-container">
                <button class="input-utility" title="Joindre un fichier">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.82-2.82l8.49-8.49"/></svg>
                </button>
                <input 
                  v-model="newMessageText" 
                  @keyup.enter="handleSendMessage"
                  type="text" 
                  placeholder="Écrire un message..." 
                  class="chat-input"
                />
                <button 
                  class="send-message-btn" 
                  @click="handleSendMessage"
                  :disabled="sending || !newMessageText.trim()"
                >
                  <svg v-if="!sending" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                  <div v-else class="btn-spinner"></div>
                </button>
              </div>
            </footer>
          </template>

          <!-- Selection State -->
          <div v-else class="chat-unselected-state">
            <div class="unselected-illus">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <h2>Espace Messagerie Coach</h2>
            <p>Sélectionnez une discussion ou démarrez-en une nouvelle avec un étudiant ou un collègue.</p>
            <button class="start-chat-btn" @click="showNewChatModal = true">Nouvelle discussion</button>
          </div>
        </section>
      </div>

      <!-- New Chat Modal -->
      <Transition name="fade-scale">
        <div v-if="showNewChatModal" class="modal-backdrop" @click.self="showNewChatModal = false">
          <div class="new-chat-modal">
            <div class="modal-header">
              <h3>Nouvelle discussion</h3>
              <button @click="showNewChatModal = false" class="modal-close">×</button>
            </div>
            <div class="modal-search">
              <input v-model="userSearchQuery" placeholder="Rechercher un étudiant, formateur..." class="modal-search-input" />
            </div>
            <div class="user-results custom-scrollbar">
              <div v-for="u in userResults" :key="u.id" class="user-result-card" @click="startPrivateChat(u)">
                <div class="user-avatar-mini">{{ u.first_name.charAt(0) }}</div>
                <div class="user-info">
                  <span class="user-name">{{ u.first_name }} {{ u.last_name }}</span>
                  <span class="user-role">{{ u.role }}</span>
                </div>
                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
              </div>
              <div v-if="userResults.length === 0 && userSearchQuery" class="user-empty">Aucun utilisateur trouvé</div>
            </div>
          </div>
        </div>
      </Transition>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { ChatService } from '../../services/ApiService';
import echo from '../../services/echo';
import SidebarTeacher from '../../components/SidebarTeacher.vue';

const route               = useRoute();
const router              = useRouter();
const user                = ref({ first_name: 'Formateur' });
const conversations       = ref([]);
const selectedChatId      = ref(null);
const currentMessages     = ref([]);
const newMessageText      = ref('');
const searchConversations = ref('');
const userSearchQuery     = ref('');
const userResults         = ref([]);
const messagesContainer   = ref(null);
const selectedCategory    = ref('all');
const showNewChatModal    = ref(false);
const loadingMessages     = ref(false);
const sending             = ref(false);
const unreadChats         = ref(new Set());

const categories = [
  { id: 'all', label: 'Toutes' },
  { id: 'classroom', label: 'Classe' },
  { id: 'squad', label: 'Squad' },
  { id: 'individual', label: 'Privées' },
];

const filteredConversations = computed(() => {
  let list = conversations.value;
  if (selectedCategory.value !== 'all') {
    list = list.filter(c => c.type === selectedCategory.value);
  }
  const q = searchConversations.value.toLowerCase().trim();
  if (q) {
    list = list.filter(c => getConversationName(c).toLowerCase().includes(q));
  }
  return list;
});

const currentChat = computed(() =>
  conversations.value.find(c => c.id === selectedChatId.value)
);

watch(userSearchQuery, async (newVal) => {
  if (newVal.length >= 2) {
    const res = await ChatService.searchUsers(newVal);
    userResults.value = res.data;
  } else {
    userResults.value = [];
  }
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    const id = parseInt(newId);
    if (selectedChatId.value !== id) {
      const chat = conversations.value.find(c => c.id === id);
      if (chat) selectChat(chat, false);
      else fetchConversations().then(() => {
        const c = conversations.value.find(x => x.id === id);
        if (c) selectChat(c, false);
      });
    }
  }
});

const fetchConversations = async () => {
  try {
    const res = await ChatService.getConversations();
    conversations.value = res.data || [];
    return conversations.value;
  } catch (err) {
    console.error('Teacher: Error fetching conversations:', err);
    return [];
  }
};

const selectChat = async (chat, updateRoute = true) => {
  if (selectedChatId.value === chat.id) return;
  
  if (updateRoute) {
    router.push({ name: 'teacher.chat', params: { id: chat.id } });
  }

  if (selectedChatId.value) echo.leave(`chat.${selectedChatId.value}`);

  selectedChatId.value = chat.id;
  unreadChats.value.delete(chat.id);
  loadingMessages.value = true;
  
  const res = await ChatService.getMessages(chat.id);
  currentMessages.value = res.data;
  subscribeToChannel(chat.id);
  await scrollToBottom();
  loadingMessages.value = false;
};

const subscribeToChannel = (id) => {
  echo.private(`chat.${id}`)
    .listen('.MessageSent', (e) => {
      if (selectedChatId.value === id) {
        const exists = currentMessages.value.some(m => Number(m.id) === Number(e.message.id));
        if (!exists) {
          currentMessages.value.push(e.message);
          scrollToBottom();
        }
      } else {
        unreadChats.value.add(id);
      }
      fetchConversations();
    });
};

const handleSendMessage = async () => {
  const text = newMessageText.value.trim();
  if (!text || !selectedChatId.value || sending.value) return;

  sending.value = true;
  const res = await ChatService.sendMessage(text, selectedChatId.value);
  currentMessages.value.push(res.data);
  newMessageText.value = '';
  await scrollToBottom();
  fetchConversations();
  sending.value = false;
};

const startPrivateChat = async (u) => {
  try {
    showNewChatModal.value = false;
    userSearchQuery.value = '';

    const res = await ChatService.startConversation(u.id);
    const newConv = res.data;
    
    selectedCategory.value = 'all';
    await fetchConversations();
    
    const found = conversations.value.find(c => c.id === newConv.id);
    if (!found) {
      conversations.value.unshift(newConv);
    }

    const targetChat = conversations.value.find(c => c.id === newConv.id) || newConv;
    await selectChat(targetChat);
  } catch (err) {
    console.error('Teacher: Failed to start chat:', err);
  }
};

const getConversationName = (chat) => {
  if (!chat) return 'Discussion';
  
  if (chat.type === 'individual') {
    const users = chat.users || [];
    const meId = Number(user.value?.id);
    
    if (!isNaN(meId)) {
      const other = users.find(u => Number(u.id) !== meId);
      if (other) return `${other.first_name} ${other.last_name}`;
    }
    
    if (users.length > 0) {
      const notMe = users.find(u => u.first_name !== 'John' || u.last_name !== 'Doe');
      if (notMe) return `${notMe.first_name} ${notMe.last_name}`;
      
      return `${users[0].first_name} ${users[0].last_name}`;
    }
    
    return chat.name || 'Inconnu';
  }
  return chat.name || 'Groupe';
};

const isMe = (msg) => msg.sender_id === user.value?.id;

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const handleLogout = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(async () => {
  const cachedUser = localStorage.getItem('user');
  if (cachedUser) user.value = JSON.parse(cachedUser);
  
  const convs = await fetchConversations() || [];

  if (route.params.id) {
    const id = parseInt(route.params.id);
    const found = convs.find(c => c.id === id);
    if (found) selectChat(found, false);
  } else if (route.query.user) {
    try {
      const res = await ChatService.startConversation(route.query.user);
      await fetchConversations();
      selectChat(res.data);
    } catch (err) { console.error('Auto-start failed:', err); }
  }
});

onUnmounted(() => {
  if (selectedChatId.value) echo.leave(`chat.${selectedChatId.value}`);
});
</script>

<style scoped>
/* ─── Variables & Layout ───────────────────────────────────────────────────── */
.teacher-chat-page {
  display: flex;
  height: 100vh;
  background: #010409;
  overflow: hidden;
}

.chat-viewport-container {
  flex: 1;
  background: #010409;
  display: flex;
  flex-direction: column;
  color: #e2e8f0;
  font-family: 'Inter', system-ui, sans-serif;
  overflow: hidden;
}

.chat-container-layout {
  display: flex;
  flex: 1;
  overflow: hidden;
  background: radial-gradient(circle at 100% 0%, rgba(56, 139, 253, 0.05) 0%, transparent 50%);
}

/* ─── Sidebar: Conversations ─────────────────────────────────────────────── */
.chat-sidebar {
  width: 360px;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  background: rgba(13, 17, 23, 0.7);
  backdrop-filter: blur(12px);
}

.sidebar-header {
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.sidebar-title {
  font-size: 24px;
  font-weight: 800;
  background: linear-gradient(135deg, #fff 0%, #388bfd 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.new-chat-btn {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(56, 139, 253, 0.1);
  border: 1px solid rgba(56, 139, 253, 0.2);
  color: #388bfd;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.new-chat-btn:hover {
  background: #388bfd;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(56, 139, 253, 0.3);
}

.search-wrapper { position: relative; margin-bottom: 20px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #475569; }
.sidebar-search-input { width: 100%; background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 10px 16px 10px 42px; color: white; outline: none; }

.filter-tabs { display: flex; gap: 8px; }
.filter-tab { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #64748b; background: transparent; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; }
.filter-tab--active { background: rgba(56, 139, 253, 0.1); color: #388bfd; border-color: rgba(56, 139, 253, 0.2); }

.conversations-list { flex: 1; overflow-y: auto; padding: 12px; }
.conversation-card { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 16px; cursor: pointer; transition: all 0.2s; margin-bottom: 8px; position: relative; }
.conversation-card:hover { background: rgba(255, 255, 255, 0.03); }
.card--active { background: rgba(56, 139, 253, 0.08) !important; border: 1px solid rgba(56, 139, 253, 0.1); }

.card-avatar-box { position: relative; flex-shrink: 0; }
.avatar-placeholder { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: white; }
.avatar-placeholder.individual { background: linear-gradient(135deg, #388bfd, #1d4ed8); }
.avatar-placeholder.classroom  { background: linear-gradient(135deg, #10b981, #059669); }
.avatar-placeholder.squad      { background: linear-gradient(135deg, #f59e0b, #d97706); }

.status-indicator { position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid #0d1117; }
.status-indicator.online { background: #10b981; }

.card-content { flex: 1; min-width: 0; }
.card-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.card-name { font-weight: 700; color: #f1f5f9; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-time { font-size: 10px; color: #64748b; }
.card-preview { font-size: 12px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.unread-badge { width: 8px; height: 8px; background: #388bfd; border-radius: 50%; margin-left: 8px; }

/* ─── Chat Viewport ────────────────────────────────────────────────────────── */
.chat-viewport { flex: 1; display: flex; flex-direction: column; background: rgba(13, 17, 23, 0.4); position: relative; }
.viewport-header { height: 80px; padding: 0 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.05); background: rgba(13, 17, 23, 0.6); backdrop-filter: blur(8px); }
.active-chat-info { display: flex; align-items: center; gap: 16px; }
.header-avatar { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; }
.header-avatar.individual { background: linear-gradient(135deg, #388bfd, #1d4ed8); }
.header-avatar.classroom  { background: linear-gradient(135deg, #10b981, #059669); }

.active-name { font-size: 18px; font-weight: 800; color: white; }
.active-meta { display: flex; align-items: center; gap: 8px; margin-top: 2px; }
.pulse-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; position: relative; }
.pulse-dot::after { content: ''; position: absolute; width: 100%; height: 100%; background: inherit; border-radius: inherit; animation: pulse 2s infinite; }
@keyframes pulse { 0% { transform: scale(1); opacity: 0.8; } 100% { transform: scale(2.5); opacity: 0; } }
.meta-status { font-size: 12px; color: #10b981; font-weight: 600; }
.divider { color: #334155; }
.meta-type { font-size: 10px; padding: 2px 8px; background: rgba(51, 65, 85, 0.5); border-radius: 6px; text-transform: uppercase; font-weight: 700; color: #94a3b8; }

.action-btn { background: transparent; border: none; color: #64748b; cursor: pointer; padding: 8px; border-radius: 8px; }
.action-btn svg { width: 22px; height: 22px; }

/* ─── Messages Viewport ────────────────────────────────────────────────────── */
.messages-viewport { flex: 1; overflow-y: auto; padding: 32px; display: flex; flex-direction: column; gap: 24px; }
.message-group { display: flex; gap: 12px; max-width: 75%; }
.group--me { align-self: flex-end; flex-direction: row-reverse; }
.msg-avatar-small { width: 32px; height: 32px; border-radius: 10px; background: #334155; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: white; flex-shrink: 0; margin-top: 4px; }
.msg-content-wrapper { display: flex; flex-direction: column; gap: 6px; }
.group--me .msg-content-wrapper { align-items: flex-end; }
.msg-balloon { padding: 12px 18px; font-size: 14px; line-height: 1.6; border-radius: 18px; }
.balloon--other { background: rgba(30, 41, 59, 0.8); color: #f1f5f9; border-bottom-left-radius: 4px; border: 1px solid rgba(255, 255, 255, 0.05); }
.balloon--me { background: linear-gradient(135deg, #238636 0%, #2ea043 100%); color: white; border-bottom-right-radius: 4px; }
.msg-timestamp { font-size: 10px; color: #475569; }

/* ─── Viewport Footer ──────────────────────────────────────────────────────── */
.viewport-footer { padding: 24px 32px; background: rgba(13, 17, 23, 0.8); }
.input-container { display: flex; align-items: center; gap: 16px; background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); padding: 8px 12px; border-radius: 16px; }
.chat-input { flex: 1; background: transparent; border: none; color: white; font-size: 14px; outline: none; }
.send-message-btn { width: 42px; height: 42px; border-radius: 12px; background: #388bfd; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.send-message-btn:disabled { background: #1e293b; color: #475569; cursor: not-allowed; }

/* ─── Modal System ─────────────────────────────────────────────────────────── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(8, 10, 12, 0.8); backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.new-chat-modal { width: 100%; max-width: 480px; background: #0d1117; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px; overflow: hidden; box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5); }
.modal-header { padding: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.modal-header h3 { font-size: 20px; font-weight: 800; color: white; }
.modal-close { background: transparent; border: none; font-size: 28px; color: #64748b; cursor: pointer; }

.modal-search { padding: 20px 24px; }
.modal-search-input { width: 100%; background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 14px; padding: 12px 18px; color: white; outline: none; }

.user-results { max-height: 320px; overflow-y: auto; padding: 0 12px 12px; }
.user-result-card { display: flex; align-items: center; gap: 14px; padding: 12px; border-radius: 16px; cursor: pointer; transition: all 0.2s; }
.user-result-card:hover { background: rgba(255, 255, 255, 0.05); }
.user-avatar-mini { width: 40px; height: 40px; border-radius: 12px; background: #334155; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; }
.user-info { flex: 1; display: flex; flex-direction: column; }
.user-name { font-weight: 700; color: white; font-size: 14px; }
.user-role { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; }
.arrow-icon { width: 18px; height: 18px; color: #334155; }

/* Selection States */
.chat-unselected-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 40px; }
.unselected-illus { width: 120px; height: 120px; background: rgba(30, 41, 59, 0.4); border-radius: 40px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; color: #64748b; }
.unselected-illus svg { width: 64px; height: 64px; }
.chat-unselected-state h2 { font-size: 24px; font-weight: 800; margin-bottom: 12px; color: white; }
.chat-unselected-state p { color: #64748b; max-width: 320px; line-height: 1.6; margin-bottom: 24px; }
.start-chat-btn { padding: 12px 24px; background: #388bfd; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }

/* Utils */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }

.loading-spinner { width: 32px; height: 32px; border: 3px solid rgba(56, 139, 253, 0.2); border-top-color: #388bfd; border-radius: 50%; animation: spin 0.8s linear infinite; }
 to { transform: rotate(360deg); } 
.btn-spinner { width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }

.fade-scale-enter-active, .fade-scale-leave-active { transition: all 0.3s ease; }
.fade-scale-enter-from, .fade-scale-leave-to { opacity: 0; transform: scale(0.95); }
</style>


