import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/student/DashboardView.vue';
import StudentBriefsView from '../views/student/StudentBriefsView.vue';
import BriefDetailView from '../views/student/BriefDetailView.vue';
import StudentNetworkView from '../views/student/StudentNetworkView.vue';
import StudentProfileView from '../views/student/StudentProfileView.vue';
import StudentAbsencesView from '../views/student/StudentAbsencesView.vue';
import StudentActivityView from '../views/student/StudentActivityView.vue';
import StudentChatView from '../views/student/StudentChatView.vue';
import StudentSubmissionsView from '../views/student/StudentSubmissionsView.vue';
import MyProfileView from '../views/student/MyProfileView.vue';
import StudentQuizView from '../views/student/StudentQuizView.vue';

import TeacherDashboardView from '../views/teacher/TeacherDashboardView.vue';
import TeacherStudentsView from '../views/teacher/TeacherStudentsView.vue';
import TeacherSquadsView from '../views/teacher/TeacherSquadsView.vue';
import TeacherAbsencesView from '../views/teacher/TeacherAbsencesView.vue';
import TeacherBriefsView from '../views/teacher/TeacherBriefsView.vue';
import TeacherEditBriefView from '../views/teacher/TeacherEditBriefView.vue';
import TeacherSubmissionsView from '../views/teacher/TeacherSubmissionsView.vue';
import TeacherActivityView from '../views/teacher/TeacherActivityView.vue';
import TeacherReportsView from '../views/teacher/TeacherReportsView.vue';
import TeacherChatView from '../views/teacher/TeacherChatView.vue';
import AdminDashboardView from '../views/admin/AdminDashboardView.vue';
import AdminUsersView from '../views/admin/AdminUsersView.vue';
import AdminClassroomsView from '../views/admin/AdminClassroomsView.vue';

import AdminAbsencesView from '../views/admin/AdminAbsencesView.vue';
import AdminReportsView from '../views/admin/AdminReportsView.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { guest: true }
    },
    {
        path: '/student/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/briefs',
        name: 'student.briefs',
        component: StudentBriefsView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/briefs/:id',
        name: 'student.briefs.show',
        component: BriefDetailView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/network',
        name: 'student.network',
        component: StudentNetworkView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/network/:id',
        name: 'student.network.profile',
        component: StudentProfileView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/absences',
        name: 'student.absences',
        component: StudentAbsencesView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/activity',
        name: 'student.activity',
        component: StudentActivityView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/profile',
        name: 'student.profile',
        component: MyProfileView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/chat/:id?',
        name: 'student.chat',
        component: StudentChatView,
        meta: { requiresAuth: true, role: 'student' }
    },
    {
        path: '/student/submissions',
        name: 'student.submissions',
        component: StudentSubmissionsView,
        meta: { requiresAuth: true, role: 'student' }
    },


    {
        path: '/student/quiz/:id',
        name: 'student.quiz',
        component: StudentQuizView,
        meta: { requiresAuth: true, role: 'student' }
    },
    
    // Teacher Routes
    {
        path: '/teacher/dashboard',
        name: 'teacher.dashboard',
        component: TeacherDashboardView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/students',
        name: 'teacher.students',
        component: TeacherStudentsView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/squads',
        name: 'teacher.squads',
        component: TeacherSquadsView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/absences',
        name: 'teacher.absences',
        component: TeacherAbsencesView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs',
        name: 'teacher.briefs',
        component: TeacherBriefsView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs/:id/edit',
        name: 'teacher.briefs.edit',
        component: TeacherEditBriefView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/briefs/create',
        name: 'teacher.briefs.create',
        component: TeacherEditBriefView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/submissions',
        name: 'teacher.submissions',
        component: TeacherSubmissionsView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/activity',
        name: 'teacher.activity',
        component: TeacherActivityView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/chat/:id?',
        name: 'teacher.chat',
        component: TeacherChatView,
        meta: { requiresAuth: true, role: 'formateur' }
    },
    {
        path: '/teacher/reports',
        name: 'teacher.reports',
        component: TeacherReportsView,
        meta: { requiresAuth: true, role: 'formateur' }
    },

    // Admin Routes
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: AdminDashboardView,
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/users',
        name: 'admin.users',
        component: AdminUsersView,
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/classrooms',
        name: 'admin.classrooms',
        component: AdminClassroomsView,
        meta: { requiresAuth: true, role: 'admin' }
    },

    {
        path: '/admin/absences',
        name: 'admin.absences',
        component: AdminAbsencesView,
        meta: { requiresAuth: true, role: 'admin' }
    },
    {
        path: '/admin/reports',
        name: 'admin.reports',
        component: AdminReportsView,
        meta: { requiresAuth: true, role: 'admin' }
    },

    {
        path: '/',
        redirect: (to) => {
            const user = JSON.parse(localStorage.getItem('user'));
            if (user?.role === 'formateur') return '/teacher/dashboard';
            if (user?.role === 'admin') return '/admin/dashboard';
            return '/student/dashboard';
        }
    },
    {
        path: '/chat',
        redirect: to => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.role === 'formateur') return { name: 'teacher.chat' };
            return { name: 'student.chat' };
        }
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const userData = localStorage.getItem('user');
    const user = userData ? JSON.parse(userData) : null;

    console.log('[Router] Navigating to:', to.path, 'Role:', user?.role);

    // 1. Rediriger vers login si la route nécessite une auth et que le token est absent
    if (to.meta.requiresAuth && !token) {
        console.warn('[Router] No token found, redirecting to login');
        return next('/login');
    }

    // 2. Si l'utilisateur est déjà connecté et tente d'aller sur une page "guest" (login)
    if (to.meta.guest && token) {
        if (user?.role === 'formateur') return next('/teacher/dashboard');
        if (user?.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    // 3. Vérification du rôle
    if (to.meta.role && user && user.role !== to.meta.role) {
        // Debug: Autoriser admin à voir les pages formateur pour le moment
        if (user.role === 'admin' && to.path.startsWith('/teacher/')) {
            console.log('[Router] Overriding role check: allowing Admin to view Teacher page');
            return next();
        }

        console.warn(`[Router] Access Denied: required role ${to.meta.role}. Current role: ${user.role}`);
        
        if (user.role === 'formateur') return next('/teacher/dashboard');
        if (user.role === 'admin') return next('/admin/dashboard');
        return next('/student/dashboard');
    }

    next();
});

export default router;
