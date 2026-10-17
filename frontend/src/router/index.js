import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import KamarView from '../views/KamarView.vue';

const routes = [
  {
    path: '/',
    redirect: '/kamars',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/kamars',
    name: 'kamars',
    component: KamarView,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('sikos_token');

  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' });
  } else if (to.meta.guestOnly && token) {
    next({ name: 'kamars' });
  } else {
    next();
  }
});

export default router;
