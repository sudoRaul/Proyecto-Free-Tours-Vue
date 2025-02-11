import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginViews.vue'
import AdminView from '@/views/AdminView.vue'
import CrearRuta from '@/views/CrearRuta.vue'
import VerRutas from '@/views/VerRutas.vue'
import GuiaView from '@/views/GuiaView.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/admin',
      name: 'admin',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: AdminView
    },
    {
      path: '/crear-ruta',
      name: 'crear-ruta',
      component: CrearRuta
    },
    {
      path: '/ver-rutas',
      name: 'ver-rutas',
      component: VerRutas
    },
    {
      path: '/visitas-pendientes',
      name: 'visitas-pendientes',
      component: GuiaView
    },
  ],
})

export default router
