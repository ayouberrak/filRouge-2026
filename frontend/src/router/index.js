import { createRouter, createWebHistory } from 'vue-router';

/**
 * --------------------------------------------------------------------------
 * 1. CONFIGURATION DES ROUTES
 * --------------------------------------------------------------------------
 * Nous utilisons le "Lazy Loading" (() => import) pour que chaque page 
 * ne se charge que lorsqu'on clique dessus. C'est plus propre et plus rapide.
 */
const routes = [
    // --- AUTHENTIFICATION ---
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/LoginView.vue'),
        meta: { guest: true }
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../views/ForgotPasswordView.vue'),
        meta: { guest: true }
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../views/ResetPasswordView.vue'),
        meta: { guest: true }
    },

    // --- ESPACE APPRENANT (Student) ---
    {
        path: '/student/dashboard',
        name: 'dashboard',
        component: () => import('../views/student/DashboardView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/briefs',
        name: 'student.briefs',
        component: () => import('../views/student/StudentBriefsView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/briefs/:id',
        name: 'student.briefs.show',
        component: () => import('../views/student/BriefDetailView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/network',
        name: 'student.network',
        component: () => import('../views/student/StudentNetworkView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/network/:id',
        name: 'student.network.profile',
        component: () => import('../views/student/StudentProfileView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/absences',
        name: 'student.absences',
        component: () => import('../views/student/StudentAbsencesView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/activity',
        name: 'student.activity',
        component: () => import('../views/student/StudentActivityView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/profile',
        name: 'student.profile',
        component: () => import('../views/student/MyProfileView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/chat/:id?',
        name: 'student.chat',
        component: () => import('../views/student/StudentChatView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/submissions',
        name: 'student.submissions',
        component: () => import('../views/student/StudentSubmissionsView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/quizzes',
        name: 'student.quizzes',
        component: () => import('../views/student/StudentQuizzesView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/quiz/:id',
        name: 'student.quiz',
        component: () => import('../views/student/StudentQuizView.vue'),
        meta: { requiresAuth: true, role: 'student' }
    },

    // --- ESPACE FORMATEUR (Teacher) ---
    {
        path: '/teacher/dashboard',
        name: 'teacher.dashboard',
        component: () => import('../views/teacher/TeacherDashboardView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/students',
        name: 'teacher.students',
        component: () => import('../views/teacher/TeacherStudentsView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/squads',
        name: 'teacher.squads',
        component: () => import('../views/teacher/TeacherSquadsView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/absences',
        name: 'teacher.absences',
        component: () => import('../views/teacher/TeacherAbsencesView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs',
        name: 'teacher.briefs',
        component: () => import('../views/teacher/TeacherBriefsView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs/:id/edit',
        name: 'teacher.briefs.edit',
        component: () => import('../views/teacher/TeacherEditBriefView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs/create',
        name: 'teacher.briefs.create',
        component: () => import('../views/teacher/TeacherEditBriefView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/submissions',
        name: 'teacher.submissions',
        component: () => import('../views/teacher/TeacherSubmissionsView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/activity',
        name: 'teacher.activity',
        component: () => import('../views/teacher/TeacherActivityView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/quizzes',
        name: 'teacher.quizzes',
        component: () => import('../views/teacher/TeacherQuizzesView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/quizzes/create',
        name: 'teacher.quizzes.create',
        component: () => import('../views/teacher/TeacherEditQuizView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/quizzes/:id/edit',
        name: 'teacher.quizzes.edit',
        component: () => import('../views/teacher/TeacherEditQuizView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/chat/:id?',
        name: 'teacher.chat',
        component: () => import('../views/teacher/TeacherChatView.vue'),
        meta: { requiresAuth: true, role: 'formateur' }
    },

    // --- ESPACE ADMIN ---
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: () => import('../views/admin/AdminDashboardView.vue'),
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/users',
        name: 'admin.users',
        component: () => import('../views/admin/AdminUsersView.vue'),
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/classrooms',
        name: 'admin.classrooms',
        component: () => import('../views/admin/AdminClassroomsView.vue'),
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/absences',
        name: 'admin.absences',
        component: () => import('../views/admin/AdminAbsencesView.vue'),
        meta: { requiresAuth: true, role: 'admin' }
    },

    // --- REDIRECTIONS ET CHAT ---
    {
        path: '/',
        redirect: () => {
            const user = JSON.parse(localStorage.getItem('user'));
            if (user?.role === 'formateur') return '/teacher/dashboard';
            if (user?.role === 'admin') return '/admin/dashboard';
            return '/student/dashboard';
        }
    },
    {
        path: '/chat',
        redirect: () => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.role === 'formateur') return { name: 'teacher.chat' };
            return { name: 'student.chat' };
        }
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

/**
 * --------------------------------------------------------------------------
 * 2. GARDIEN DE NAVIGATION (beforeEach)
 * --------------------------------------------------------------------------
 * Ce code vérifie les permissions avant d'afficher la page.
 */
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    // 1. Rediriger vers login si la page nécessite une auth et que le token est absent
    if (to.meta.requiresAuth && !token) {
        return next('/login');
    }

    // 2. Si déjà connecté et tente d'aller sur Login -> Rediriger vers son Dashboard
    if (to.meta.guest && token) {
        if (user?.role === 'formateur') return next('/teacher/dashboard');
        if (user?.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    // 3. Vérification du rôle autorisé
    if (to.meta.role && user && user.role !== to.meta.role) {
        // Optionnel : On autorise l'admin à voir les pages prof pour le debug
        if (user.role === 'admin' && to.path.startsWith('/teacher/')) {
            return next();
        }
        // Sinon, retour au dashboard correspondant au rôle de l'utilisateur
        if (user.role === 'formateur') return next('/teacher/dashboard');
        if (user.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    next(); // Tout est OK, on affiche la page
});

export default router;
