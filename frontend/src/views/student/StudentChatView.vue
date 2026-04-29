<template>
  <div class="layout">
    <SidebarStudent :user="user" @logout="handleLogout" />

    <main class="main">
      <div class="chat-wrapper">
        
        <!-- Sidebar: Conversations -->
        <aside class="chat-sidebar">
          <div class="sidebar-header">
            <div class="header-top">
              <h1 class="sidebar-title">Messagerie</h1>
              <button class="new-chat-btn" @click="showNewChatModal = true" title="Nouvelle discussion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            
            <div class="search-wrapper">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
              </svg>
              <input v-model="searchConversations" type="text" placeholder="Rechercher un contact..." class="nadi-search" />
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

          <div class="conversations-list">
            <template v-if="filteredConversations.length > 0">
              <div v-for="chat in filteredConversations" :key="chat.id" 
                class="conv-card" :class="{ 'conv-card--active': selectedChatId === chat.id }"
                @click="selectChat(chat)"
              >
                <div class="avatar-box">
                  <div class="avatar" :class="chat.type">
                    {{ getConversationName(chat).charAt(0).toUpperCase() }}
                  </div>
                  <span v-if="chat.online" class="status-dot"></span>
                </div>
                
                <div class="conv-content">
                  <div class="conv-head">
                    <span class="conv-name">{{ getConversationName(chat) }}</span>
                    <span class="conv-time">{{ formatTime(chat.updated_at) }}</span>
                  </div>
                  <div class="conv-foot">
                    <p class="conv-preview">{{ chat.last_message?.content || 'Nouvelle conversation' }}</p>
                    <div v-if="unreadChats.has(chat.id)" class="unread-pulse"></div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="empty-list">
              Aucune discussion disponible
            </div>
          </div>
        </aside>

        <!-- Main Chat Area -->
        <section class="chat-main">
          <template v-if="currentChat">
            <!-- Active Chat Header -->
            <header class="chat-header">
              <div class="active-info animate-in">
                <div class="avatar" :class="currentChat.type">
                  {{ getConversationName(currentChat).charAt(0).toUpperCase() }}
                </div>
                <div class="active-text">
                  <h2>{{ getConversationName(currentChat) }}</h2>
                  <div class="meta-row">
                    <span class="online-indicator">En ligne</span>
                    <span class="meta-dot">•</span>
                    <span class="meta-pill">{{ currentChat.type === 'classroom' ? 'Classe' : (currentChat.type === 'squad' ? 'Squad' : 'Connexion Privée') }}</span>
                  </div>
                </div>
              </div>
              
              <div class="header-actions animate-in">
                <button class="nadi-action-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                <button class="nadi-action-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg></button>
              </div>
            </header>

            <!-- Messages Window -->
            <div class="messages-area custom-scrollbar" ref="messagesContainer">
              <div v-if="loadingMessages" class="loading-state">
                
                <p>Chargement en cours...</p>
              </div>

              <template v-else>
                <div v-for="(msg, idx) in currentMessages" :key="msg.id || idx" 
                  class="msg-row animate-in" :class="{ 'msg-row--me': isMe(msg) }"
                  :style="{ animationDelay: `${Math.min(idx * 0.05, 0.5)}s` }"
                >
                  <div v-if="!isMe(msg)" class="msg-avatar">{{ msg.sender?.first_name?.charAt(0) }}</div>
                  <div class="msg-body">
                    <div class="msg-bubble" :class="isMe(msg) ? 'bubble--me' : 'bubble--other'">
                      {{ msg.content }}
                    </div>
                    <span class="msg-time">{{ formatTime(msg.created_at) }}</span>
                  </div>
                </div>
                <div v-if="currentMessages.length === 0" class="empty-chat-state animate-in">
                  <div class="empty-icon-large">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                  </div>
                  <h3>Canal Initialisé</h3>
                  <p>Démarrez la conversation en envoyant un message ci-dessous.</p>
                </div>
              </template>
            </div>

            <!-- Input Bar -->
            <footer class="chat-footer animate-in">
              <div class="input-bar">

                <input 
                  v-model="newMessageText" 
                  @keyup.enter="handleSendMessage"
                  type="text" 
                  placeholder="Rédigez votre message..." 
                  class="nadi-chat-input" 
                  :disabled="sending"
                />
                <button 
                  class="btn-send-primary" 
                  :disabled="!newMessageText.trim() || sending" 
                  @click="handleSendMessage"
                >
                  <svg v-if="!sending" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                  <div v-else class="spinner-sm"></div>
                </button>
              </div>
            </footer>
          </template>

          <!-- Select Placeholder -->
          <div v-else class="unselected-state animate-in">
            <div class="unselected-illus">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h2>Flux de Communication</h2>
            <p>Sélectionnez une discussion dans le panneau latéral pour commencer l'échange.</p>
            <button class="btn-nadi-glow" @click="showNewChatModal = true">Initier un contact</button>
          </div>
        </section>
      </div>

      <!-- New Chat Modal -->
      <Transition name="fade-scale">
        <div v-if="showNewChatModal" class="modal-backdrop" @click.self="showNewChatModal = false">
          <div class="nadi-modal">
            <div class="modal-header-nadi">
              <h3>Démarrer un échange</h3>
              <button @click="showNewChatModal = false" class="nadi-close-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
            </div>
            <div class="modal-search-box">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input v-model="userSearchQuery" placeholder="Rechercher par nom..." class="nadi-search" />
            </div>
            <div class="user-list custom-scrollbar">
              <div v-for="u in userResults" :key="u.id" class="user-row" @click="startPrivateChat(u)">
                <div class="user-avatar-small">{{ u.first_name.charAt(0) }}</div>
                <div class="user-details">
                  <span class="u-name">{{ u.first_name }} {{ u.last_name }}</span>
                  <span class="u-role">{{ u.role }}</span>
                </div>
                <svg class="arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
              </div>
              <div v-if="userResults.length === 0 && userSearchQuery" class="empty-users">Aucune correspondance dans la base.</div>
            </div>
          </div>
        </div>
      </Transition>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { ChatService } from '../../services/ApiService';
import echo from '../../services/echo';
import SidebarStudent from '../../components/SidebarStudent.vue';

const router              = useRouter();
const user                = ref(null);
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
  let list = conversations.value || [];
  if (!Array.isArray(list)) return [];
  
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

const fetchConversations = async () => {
  try {
    const res = await ChatService.getConversations();
    conversations.value = res.data || [];
    console.log('Conversations fetched:', conversations.value.length);
    return conversations.value;
  } catch (err) {
    console.error('Error fetching conversations:', err);
    return [];
  }
};

const selectChat = async (chat) => {
  if (selectedChatId.value === chat.id) return;
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
    
    console.log('Starting chat with user:', u.id);
    const res = await ChatService.startConversation(u.id);
    const newConv = res.data;
    
    selectedCategory.value = 'all';
    await fetchConversations();
    
    const found = conversations.value.find(c => c.id === newConv.id);
    if (!found) {
      console.log('New conversation missing from fetch, adding manually');
      conversations.value.unshift(newConv);
    }

    const targetChat = conversations.value.find(c => c.id === newConv.id) || newConv;
    await selectChat(targetChat);
  } catch (err) {
    console.error('Failed to start chat:', err);
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
  
  const convs = await fetchConversations();

  if (router.currentRoute.value.params.id) {
    const id = parseInt(router.currentRoute.value.params.id);
    const found = convs.find(c => c.id === id);
    if (found) selectChat(found);
  } else if (router.currentRoute.value.query.user) {
    try {
      const res = await ChatService.startConversation(router.currentRoute.value.query.user);
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
/* ─── Variables & Root Layout ──────────────────────────────────────────────── */
* { box-sizing: border-box; }

.layout {
  display: flex; height: 100vh; overflow: hidden;
  background: #010409; color: #c9d1d9; font-family: 'Inter', system-ui, sans-serif;
}
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

.chat-wrapper {
  display: flex; flex: 1; overflow: hidden; width: 100%; height: 100%;
}

/* ─── Sidebar ───────────────────────────────────────────────────────────────── */
.chat-sidebar {
  width: 340px; border-right: 1px solid rgba(255,255,255,0.06); display: flex; flex-direction: column;
  background: #010409; flex-shrink: 0; z-index: 10;
}

.sidebar-header { padding: 24px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); }
.header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.sidebar-title { font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.01em; }

.new-chat-btn {
  width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);
  color: #79c0ff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
}
.new-chat-btn:hover { background: rgba(56,139,253,0.1); border-color: rgba(56,139,253,0.3); box-shadow: 0 0 15px rgba(56,139,253,0.15); transform: translateY(-1px); }

.search-wrapper { position: relative; margin-bottom: 16px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: #8b949e; }
.nadi-search {
  width: 100%; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px;
  padding: 10px 16px 10px 40px; color: #fff; font-size: 13px; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
}
.nadi-search::placeholder { color: #484f58; font-weight: 500; }
.nadi-search:focus { border-color: #388bfd; background: rgba(56,139,253,0.03); }

.filter-tabs { display: flex; gap: 6px; }
.filter-tab {
  padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; color: #8b949e;
  background: transparent; border: 1px solid transparent; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.05em;
}
.filter-tab--active { background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.1); }
.filter-tab:hover:not(.filter-tab--active) { color: #c9d1d9; }

/* Conv List */
.conversations-list { flex: 1; overflow-y: auto; padding: 12px; scrollbar-width: none; }
.conversations-list::-webkit-scrollbar { display: none; }

.conv-card {
  display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 12px; cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); margin-bottom: 6px; border: 1px solid transparent;
}
.conv-card:hover { background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.04); }
.conv-card--active { background: rgba(56,139,253,0.05) !important; border-color: rgba(56,139,253,0.2) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

.avatar-box { position: relative; flex-shrink: 0; }
.avatar {
  width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
  font-size: 16px; font-weight: 800; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.avatar.individual { background: #010409; border: 1px solid rgba(255,255,255,0.1); color: #c9d1d9;}
.avatar.classroom { background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.3); color: #79c0ff; }
.avatar.squad { background: rgba(210,153,34,0.1); border: 1px solid rgba(210,153,34,0.3); color: #e3b341; }
.conv-card--active .avatar.individual { background: rgba(56,139,253,0.1); color: #79c0ff; border-color: rgba(56,139,253,0.3); }

.status-dot { position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px; border-radius: 50%; background: #56d364; border: 2px solid #010409; }

.conv-content { flex: 1; min-width: 0; }
.conv-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.conv-name { font-weight: 700; color: #e6edf3; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.2s;}
.conv-card--active .conv-name { color: #fff; }
.conv-time { font-size: 10px; font-weight: 600; color: #8b949e; }

.conv-foot { display: flex; justify-content: space-between; align-items: center; }
.conv-preview { font-size: 13px; color: #484f58; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; font-weight: 500;}
.conv-card--active .conv-preview { color: #79c0ff; }

.unread-pulse { width: 8px; height: 8px; background: #388bfd; border-radius: 50%; margin-left: 8px; box-shadow: 0 0 10px #388bfd; animation: pulse-blue 2s infinite; }

.empty-list { padding: 40px 20px; text-align: center; font-size: 13px; color: #484f58; font-weight: 500; }

/* ─── Main Chat ─────────────────────────────────────────────────────────────── */
.chat-main { flex: 1; display: flex; flex-direction: column; background: #010409; position: relative; }

.chat-header {
  height: 80px; padding: 0 32px; display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(1, 4, 9, 0.8); backdrop-filter: blur(12px); z-index: 10;
}
.active-info { display: flex; align-items: center; gap: 16px; }
.active-text h2 { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em; margin-bottom: 2px;}
.meta-row { display: flex; align-items: center; gap: 8px; }
.online-indicator { font-size: 11px; font-weight: 700; color: #56d364; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 6px;}
.online-indicator::before { content: ''; width: 6px; height: 6px; background: #56d364; border-radius: 50%; box-shadow: 0 0 8px #56d364;}
.meta-dot { color: #484f58; }
.meta-pill { font-size: 10px; font-weight: 800; color: #8b949e; background: rgba(255,255,255,0.05); padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.1em; border: 1px solid rgba(255,255,255,0.1);}

.nadi-action-btn { background: transparent; border: none; color: #8b949e; cursor: pointer; padding: 8px; border-radius: 8px; transition: all 0.2s; }
.nadi-action-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
.nadi-action-btn svg { width: 20px; height: 20px; }

/* Messages */
.messages-area { flex: 1; overflow-y: auto; padding: 40px; display: flex; flex-direction: column; gap: 24px; scrollbar-width: thin; scrollbar-color: #21262d transparent;}
.messages-area::-webkit-scrollbar { width: 5px; }
.messages-area::-webkit-scrollbar-thumb { background: #30363d; border-radius: 10px; }

.msg-row { display: flex; gap: 16px; max-width: 80%; }
.msg-row--me { align-self: flex-end; flex-direction: row-reverse; }

.msg-avatar { width: 36px; height: 36px; border-radius: 10px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; color: #79c0ff; flex-shrink: 0; align-self: flex-end;}

.msg-body { display: flex; flex-direction: column; gap: 6px; }
.msg-row--me .msg-body { align-items: flex-end; }

.msg-bubble { padding: 14px 18px; font-size: 14px; line-height: 1.6; border-radius: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-weight: 400;}
.bubble--other { background: rgba(255,255,255,0.03); color: #c9d1d9; border: 1px solid rgba(255,255,255,0.08); border-bottom-left-radius: 4px; }
.bubble--me { background: #238636; color: white; border: 1px solid #2ea043; border-bottom-right-radius: 4px; box-shadow: 0 4px 15px rgba(35,134,54,0.15);}

.msg-time { font-size: 10px; font-weight: 600; color: #484f58; letter-spacing: 0.05em;}

.loading-state, .empty-chat-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #8b949e; gap: 16px;}
.empty-icon-large { width: 64px; height: 64px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #484f58; }
.empty-icon-large svg { width: 32px; height: 32px; }
.empty-chat-state h3 { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em;}
.empty-chat-state p { font-size: 13px; max-width: 300px; line-height: 1.6;}

/* Footer Input */
.chat-footer { padding: 24px 40px; background: rgba(1, 4, 9, 0.9); backdrop-filter: blur(12px); border-top: 1px solid rgba(255,255,255,0.04); }
.input-bar { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 8px 8px 8px 16px; transition: all 0.2s; }
.input-bar:focus-within { border-color: #388bfd; box-shadow: 0 0 0 4px rgba(56,139,253,0.1); background: rgba(56,139,253,0.02); }

.nadi-chat-input { flex: 1; background: transparent; border: none; color: #fff; font-size: 14px; font-family: 'Inter', sans-serif; outline: none; padding: 10px 0; }
.nadi-chat-input::placeholder { color: #484f58; font-weight: 500; }

.btn-send-primary { width: 44px; height: 44px; border-radius: 12px; background: #388bfd; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(56,139,253,0.3); }
.btn-send-primary:hover:not(:disabled) { background: #58a6ff; transform: translateY(-1px); box-shadow: 0 8px 25px rgba(56,139,253,0.4); }
.btn-send-primary:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; background: rgba(255,255,255,0.1); color: #8b949e;}
.btn-send-primary svg { width: 20px; height: 20px; position: relative; left: -1px;}

.spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }

/* ─── Unselected State ──────────────────────────────────────────────────────── */
.unselected-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 16px; }
.unselected-illus { width: 80px; height: 80px; border-radius: 20px; background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: #484f58;}
.unselected-illus svg { width: 40px; height: 40px; stroke-width: 1.5;}
.unselected-state h2 { font-size: 22px; font-weight: 800; color: #e6edf3; letter-spacing: -0.01em;}
.unselected-state p { font-size: 14px; color: #8b949e; max-width: 350px; line-height: 1.6; }
.btn-nadi-glow { margin-top: 16px; padding: 12px 24px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.3); color: #79c0ff; font-weight: 700; border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(56,139,253,0.1); }
.btn-nadi-glow:hover { background: #388bfd; color: white; box-shadow: 0 8px 25px rgba(56,139,253,0.3); transform: translateY(-1px); }

/* ─── Modals ────────────────────────────────────────────────────────────────── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(1,4,9,0.8); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.nadi-modal { width: 100%; max-width: 480px; background: #0d1117; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.5); display: flex; flex-direction: column; max-height: 80vh;}

.modal-header-nadi { display: flex; justify-content: space-between; align-items: center; padding: 24px 32px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.modal-header-nadi h3 { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.01em;}
.nadi-close-btn { background: transparent; border: none; color: #8b949e; cursor: pointer; transition: all 0.2s; padding: 4px; border-radius: 6px;}
.nadi-close-btn:hover { color: #ff7b72; background: rgba(248,81,73,0.1); }
.nadi-close-btn svg { width: 24px; height: 24px; }

.modal-search-box { padding: 20px 32px 10px; position: relative; }
.modal-search-box .search-icon { position: absolute; left: 46px; top: 32px; width: 18px; height: 18px; color: #8b949e;}
.modal-search-box .nadi-search { padding: 14px 16px 14px 44px; font-size: 14px; background: rgba(255,255,255,0.02); }

.user-list { padding: 10px 24px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;}
.user-row { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; }
.user-row:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.05); }
.user-avatar-small { width: 40px; height: 40px; border-radius: 10px; background: rgba(56,139,253,0.1); border: 1px solid rgba(56,139,253,0.2); display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; color: #79c0ff; flex-shrink: 0;}
.user-details { flex: 1; display: flex; flex-direction: column; gap: 2px;}
.u-name { font-weight: 700; color: #e6edf3; font-size: 15px;}
.u-role { font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.1em;}
.arrow-right { width: 20px; height: 20px; color: #484f58; transition: transform 0.2s; }
.user-row:hover .arrow-right { transform: translateX(4px); color: #79c0ff;}
.empty-users { text-align: center; padding: 40px 20px; font-size: 13px; color: #484f58; font-weight: 500;}

/* ─── Animations ────────────────────────────────────────────────────────────── */
 
@keyframes pulse-blue { 0%, 100% { opacity: 1; box-shadow: 0 0 10px #388bfd; } 50% { opacity: 0.4; box-shadow: 0 0 2px #388bfd; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-in { opacity: 0; animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

.fade-scale-enter-active, .fade-scale-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.fade-scale-enter-from, .fade-scale-leave-to { opacity: 0; transform: scale(0.95); }

/* Responsive */
@media (max-width: 900px) {
  .chat-sidebar { width: 280px; }
  .chat-header, .chat-footer { padding: 0 20px; }
  .messages-area { padding: 20px; }
  .msg-row { max-width: 90%; }
}
@media (max-width: 760px) {
  .chat-wrapper { flex-direction: column; }
  .chat-sidebar { width: 100%; height: 300px; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
}
</style>

