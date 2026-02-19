import { createRouter, createWebHashHistory } from 'vue-router';
import HomeView       from '../views/HomeView.vue';
import FluentCrmView  from '../views/FluentCrmView.vue';
import FluentCartView from '../views/FluentCartView.vue';

const router = createRouter({
  // Hash history works cleanly inside WordPress admin page URLs
  history: createWebHashHistory(),
  routes: [
    { path: '/',            component: HomeView       },
    { path: '/fluent-crm',  component: FluentCrmView  },
    { path: '/fluent-cart', component: FluentCartView },
  ],
});

export default router;
