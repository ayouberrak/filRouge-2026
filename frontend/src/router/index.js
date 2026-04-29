import { createRouter, createWebHistory } from 'vue-router';

const routes = [
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
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    if (to.meta.requiresAuth && !token) {
        return next('/login');
    }

    if (to.meta.guest && token) {
        if (user?.role === 'formateur') return next('/teacher/dashboard');
        if (user?.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    if (to.meta.role && user && user.role !== to.meta.role) {
        if (user.role === 'admin' && to.path.startsWith('/teacher/')) {
            return next();
        }
        if (user.role === 'formateur') return next('/teacher/dashboard');
        if (user.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    next();
});

export default router;
