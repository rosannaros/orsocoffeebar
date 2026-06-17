import { createRouter, createWebHistory } from 'vue-router'

import Beranda from '../views/Beranda.vue'
import Menu from '../views/Menu.vue'
import Testimoni from '../views/Testimoni.vue'
import Tentang from '../views/Tentang.vue'
import Pesanan from '../views/Pesanan.vue'
import Keranjang from '../views/Keranjang.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Profil from '../views/Profil.vue'
import TulisTestimoni from '../views/TulisTestimoni.vue'
import EditProfil from '../views/EditProfil.vue'
import RiwayatPesanan from '../views/RiwayatPesanan.vue'

const routes = [
  { path: '/beranda', name: 'Beranda', component: Beranda },
  { path: '/menu', name: 'Menu', component: Menu },
  { path: '/testimoni/:id?', name: 'Testimoni', component: Testimoni },
  { path: '/tentang', name: 'Tentang', component: Tentang },
  { path: '/keranjang', name: 'Keranjang', component: Keranjang },
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },

  { 
    path: '/profil', 
    name: 'Profil', 
    component: Profil, 
    meta: { requiresAuth: true } 
  },
  { 
    path: '/editprofil', 
    name: 'EditProfil', 
    component: EditProfil, 
    meta: { requiresAuth: true } 
  },
  { 
    path: '/tulistestimoni', 
    name: 'TulisTestimoni', 
    component: TulisTestimoni, 
    meta: { requiresAuth: true } 
  },
  { 
    path: '/riwayatpesanan', 
    name: 'RiwayatPesanan', 
    component: RiwayatPesanan,
    meta: { requiresAuth: true } 
  },
  { 
    path: '/pesanan/:id?',
    name: 'Pesanan', 
    component: Pesanan, 
    meta: { requiresAuth: true } 
  },

  { path: '/', redirect: '/beranda' }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to,from,savedPosition){
    return { top: 0 }
  }
})

export default router
