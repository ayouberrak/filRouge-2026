<template>
  <div ref="quizContainer" class="fixed inset-0 z-[1000] bg-[#0A0F27] flex flex-col font-sans overflow-hidden animate-in zoom-in duration-500">
    
    <!-- Linear Timer Bar -->
    <div class="fixed top-0 left-0 h-1.5 bg-slate-800 w-full z-[1100]">
        <div class="h-full bg-blue-500 shadow-[0_0_20px_rgba(59,130,246,0.6)] transition-all duration-1000 ease-linear"
             :style="{ width: timerWidth + '%' }"></div>
    </div>

    <!-- Interface Header -->
    <header class="h-24 flex items-center justify-between px-10 relative z-40 shrink-0">
        <div class="flex items-center gap-6">
            <button @click="$emit('close')" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-slate-500 hover:text-white hover:bg-white/10 transition-all border border-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="px-5 py-2 bg-white/5 rounded-2xl border border-white/5 flex items-center gap-3">
                 <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                 <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Live Mission : {{ briefTitle }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <div class="text-right">
                <p class="text-[9px] font-black text-slate-600 uppercase tracking-widest mb-1 italic">Score Analysis</p>
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-black text-white tabular-nums tracking-tighter">{{ points }}</span>
                    <div class="px-2 py-0.5 bg-blue-500/10 rounded text-[10px] font-black text-blue-400">STREAK x2</div>
                </div>
            </div>
            <div class="w-px h-10 bg-white/10 hidden md:block"></div>
            <div class="text-right hidden md:block">
                <p class="text-[9px] font-black text-slate-600 uppercase tracking-widest mb-1 italic">Progress</p>
                <p class="text-2xl font-black text-slate-400 tracking-tighter tabular-nums">{{ currentQuestionIndex + 1 }} / {{ questions.length }}</p>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col items-center justify-center p-8 lg:p-20 relative min-h-0">
        
        <div v-if="!showResult" class="w-full max-w-6xl relative z-30">
            
            <!-- Question Content -->
            <div class="text-center space-y-8 mb-12 animate-in slide-in-from-bottom duration-700">
                <div class="inline-flex items-center gap-3 px-6 py-2 rounded-full bg-white/5 border border-white/10">
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] italic">{{ questions[currentQuestionIndex].category }}</span>
                </div>
                <h2 class="text-2xl lg:text-4xl font-black tracking-tight text-white leading-tight max-w-4xl mx-auto font-display">
                     {{ questions[currentQuestionIndex].text }}
                </h2>
            </div>
            
            <!-- Type MULTIPLE: Choice Grid -->
            <div v-if="questions[currentQuestionIndex].type === 'multiple'" 
                 class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-5xl mx-auto"
                 :class="isScanning ? 'opacity-20 blur-md pointer-events-none' : 'opacity-100 transition-all'">
                <div v-for="(opt, idx) in questions[currentQuestionIndex].options" :key="idx" 
                     @click="selectOption(idx)"
                     class="group relative h-28 lg:h-36 rounded-[2rem] cursor-pointer transition-all duration-300 flex items-center overflow-hidden border-b-[6px] active:translate-y-2 active:border-b-0"
                     :class="[
                        optionStyles[idx].bg,
                        optionStyles[idx].border,
                        selectedOpt === idx ? 'scale-[1.05] ring-8 ring-white/10 z-50' : 'hover:scale-[1.02]'
                     ]">
                    <div class="px-8 lg:px-10 flex items-center gap-6 relative z-10 w-full">
                        <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl bg-black/20 flex items-center justify-center text-white" v-html="optionStyles[idx].icon"></div>
                        <span class="text-lg lg:text-xl font-black text-white truncate drop-shadow-sm">{{ opt }}</span>
                        <div v-if="selectedOpt === idx" class="ml-auto w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-lg animate-bounce">
                             <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="4" stroke-linecap="round"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Type CODE: Mini Editor UI -->
            <div v-else-if="questions[currentQuestionIndex].type === 'code'" 
                 class="max-w-4xl mx-auto relative animate-in zoom-in duration-500"
                 :class="isScanning ? 'opacity-20 blur-md' : 'opacity-100 transition-all'">
                
                <div class="bg-[#050816]/80 backdrop-blur-xl border border-white/[0.08] rounded-[2.5rem] overflow-hidden shadow-3xl">
                    <div class="px-8 py-4 border-b border-white/[0.05] flex items-center justify-between bg-white/[0.02]">
                         <div class="flex gap-2">
                             <div class="w-3 h-3 rounded-full bg-rose-500/50"></div>
                             <div class="w-3 h-3 rounded-full bg-amber-500/50"></div>
                             <div class="w-3 h-3 rounded-full bg-emerald-500/50"></div>
                         </div>
                         <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">MasterEditor v1.0.4 - PHP Mode</span>
                    </div>
                    <div class="flex relative min-h-[300px]">
                        <!-- Line Numbers -->
                        <div class="w-14 bg-white/[0.01] border-r border-white-5 p-6 text-right select-none">
                            <div v-for="n in 8" :key="n" class="text-xs font-mono text-slate-800 leading-relaxed">{{ n }}</div>
                        </div>
                        <!-- Textarea Editor -->
                        <textarea v-model="userCode" 
                                  placeholder="<?php\n\n// Write your solution here..." 
                                  class="flex-1 bg-transparent p-6 text-blue-400 font-mono text-base leading-relaxed outline-none resize-none placeholder-slate-800"
                                  spellcheck="false"></textarea>
                    </div>
                </div>

                <div class="flex justify-center mt-10">
                    <button @click="handleNext" 
                            :disabled="!userCode || isScanning"
                            class="px-16 py-6 bg-white text-[#0A0F27] hover:bg-blue-600 hover:text-white disabled:opacity-20 font-black rounded-[2rem] shadow-2xl transition-all duration-500 uppercase tracking-[0.3em] flex items-center gap-4 group">
                        Analyser mon code
                        <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="4" stroke-linecap="round"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Global Scanning Bar -->
            <div v-if="isScanning" class="absolute top-1/2 left-0 w-full h-[3px] bg-gradient-to-r from-transparent via-blue-500 to-transparent shadow-[0_0_20px_rgba(59,130,246,1)] z-50 animate-scan"></div>

        </div>

    </div>

    <!-- Fullscreen Toggle Button -->
    <button @click="toggleFullscreen" class="fixed bottom-8 right-8 w-14 h-14 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 flex items-center justify-center text-slate-500 hover:text-white transition-all z-[1200] group">
        <svg v-if="!isFullscreen" class="w-6 h-6 group-active:scale-95 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 4v4m0 0H4m4 0L3 3m13-2v4m0 0h4m-4 0l5-5M8 20v-4m0 0H4m4 0l-5 5m13-5v4m0 0h4m-4 0l5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
    </button>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps(['briefTitle']);
const emit = defineEmits(['close', 'finish']);

const quizContainer = ref(null);
const isFullscreen = ref(false);
const selectedOpt = ref(null);
const userCode = ref('');
const currentQuestionIndex = ref(0);
const isScanning = ref(false);
const showResult = ref(false);
const points = ref(1250);
const timerWidth = ref(100);
let timerInterval = null;

const optionStyles = [
    { bg: 'bg-rose-500', border: 'border-rose-900', icon: '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3l9 17H3L12 3z"/></svg>' },
    { bg: 'bg-blue-500', border: 'border-blue-900', icon: '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l10 10-10 10-10-10L12 2z"/></svg>' },
    { bg: 'bg-amber-500', border: 'border-amber-900', icon: '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>' },
    { bg: 'bg-emerald-500', border: 'border-emerald-900', icon: '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>' }
];

const questions = [
    { 
        type: 'multiple',
        text: "Quel pattern permet de séparer la logique de stockage du domaine ?", 
        options: ['Factory', 'Repository', 'Observer', 'Singleton'], 
        category: 'Architecture' 
    },
    { 
        type: 'code',
        text: "Écrivez le code PHP pour instancier la classe 'ProductRepository' et appeler sa méthode 'findAll()'.",
        category: 'Coding Challenge',
        points: 500
    },
    { 
        type: 'multiple',
        text: "En architecture hexagonale, l'interface du Repository se situe dans :", 
        options: ['La couche Infrastructure', 'La couche Domain', 'La couche Application', 'La Database'], 
        category: 'Architecture' 
    }
];

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().then(() => isFullscreen.value = true);
    } else {
        document.exitFullscreen().then(() => isFullscreen.value = false);
    }
};

const startTimer = () => {
    timerWidth.value = 100;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (timerWidth.value > 0) {
            timerWidth.value -= 0.5;
        } else {
            handleNext();
        }
    }, 100);
};

const selectOption = (idx) => {
    selectedOpt.value = idx;
    setTimeout(() => handleNext(), 600);
};

const handleNext = () => {
    isScanning.value = true;
    setTimeout(() => {
        isScanning.value = false;
        if (currentQuestionIndex.value < questions.length - 1) {
            currentQuestionIndex.value++;
            selectedOpt.value = null;
            userCode.value = '';
            startTimer();
        } else {
            showResult.value = true;
            clearInterval(timerInterval);
        }
    }, 1200);
};

onMounted(() => startTimer());
onUnmounted(() => { if (timerInterval) clearInterval(timerInterval); });
</script>

<style scoped>
@keyframes scan { 0% { top: 10%; opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { top: 90%; opacity: 0; } }
.animate-scan { animation: scan 1.2s cubic-bezier(0.4, 0, 0.2, 1) infinite; }
@keyframes fade-in { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes zoom-in { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
.animate-in { animation: fade-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.zoom-in { animation: zoom-in 1s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
</style>
