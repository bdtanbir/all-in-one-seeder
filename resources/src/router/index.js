import { createRouter, createWebHashHistory } from 'vue-router';
import HomeView      from '../views/HomeView.vue';
import FluentCrmView from '../views/FluentCrmView.vue';

const router = createRouter({
  // Hash history works cleanly inside WordPress admin page URLs
  history: createWebHashHistory(),
  routes: [
    { path: '/',           component: HomeView      },
    { path: '/fluent-crm', component: FluentCrmView },
  ],
});

export default router;
