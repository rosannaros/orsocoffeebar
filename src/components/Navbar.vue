<template>
  <header class="navbar">
    <div class="logo">
      <img src="../assets/images/logo/logo-orso.png" alt="Logo Orso" />
      <span>Orso Coffee Bar</span>
    </div>

    <nav class="desktop-menu">
      <RouterLink to="/beranda">Beranda</RouterLink>
      <RouterLink to="/menu">Menu</RouterLink>
      <RouterLink to="/testimoni">Testimoni</RouterLink>
      <RouterLink to="/tentang">Tentang</RouterLink>
      <RouterLink to="/pesanan">Pesanan</RouterLink>
    </nav>

    <div class="nav-util">
      <RouterLink 
        v-if="isLoggedIn" to="/RiwayatPesanan" class="history-link" title="Riwayat Pesanan">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </RouterLink>
      <RouterLink to="/keranjang" class="cart-link" :class="{ active: isCartActive }">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-badge">{{ cart.count }}</span>
      </RouterLink>
      <RouterLink
        v-if="!isLoggedIn" to="/login" class="login-btn hide-mobile">Login
      </RouterLink>
      <RouterLink
        v-else to="/profil" class="login-btn hide-mobile">Profil
      </RouterLink>
      <button class="hamburger" aria-label="Toggle menu" @click="menuOpen = !menuOpen">
        <span v-if="!menuOpen">☰</span>
        <span v-else>✕</span>
      </button>
    </div>
  </header>

  <nav class="mobile-menu" :class="{ open: menuOpen }">
    <RouterLink to="/beranda" @click="closeMenu">Beranda</RouterLink>
    <RouterLink to="/menu" @click="closeMenu">Menu</RouterLink>
    <RouterLink to="/testimoni" @click="closeMenu">Testimoni</RouterLink>
    <RouterLink to="/tentang" @click="closeMenu">Tentang</RouterLink>
    <RouterLink to="/pesanan" @click="closeMenu">Pesanan</RouterLink>
    <RouterLink
      v-if="!isLoggedIn" to="/login" @click="closeMenu" class="mobile-login-link">Login
    </RouterLink>
    <RouterLink
      v-else to="/profil" @click="closeMenu" class="mobile-login-link">Profil
    </RouterLink>
  </nav>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useCartStore } from '../stores/cart'

const menuOpen = ref(false)
const isLoggedIn = ref(false)
const cart = useCartStore()
const route = useRoute()

const isCartActive = computed(() => {
  return route.path === '/keranjang' || route.path === '/payment'
})

const checkLogin = () => {
  isLoggedIn.value = !!localStorage.getItem('user')
}

const closeMenu = () => {
  menuOpen.value = false
}

onMounted(() => {
  checkLogin()
  window.addEventListener('auth-change', checkLogin)
})

onBeforeUnmount(() => {
  window.removeEventListener('auth-change', checkLogin)
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  font-family: 'Poppins', sans-serif;
}

body {
  padding-top: 70px; 
  background-color: #fff;
  overflow-x: hidden;
}

.navbar {
  background: #000;
  padding: 0 40px;
  height: 70px;
  display: flex;
  justify-content: space-between; 
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1100;
  font-weight: 500;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo img { height: 35px; }
.logo span {
  color: #fff;
  font-weight: 600; 
  font-size: 15px;
}

.desktop-menu {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 30px;
}

.desktop-menu a, 
.cart-link, 
.history-link {
  color: #fff;
  text-decoration: none;
  transition: color 0.3s ease;
}

.desktop-menu a {
  font-size: 14px;
  font-weight: 400;
  padding: 8px 0;
}

.desktop-menu a:hover,
.desktop-menu a.router-link-active,
.cart-link.active,
.cart-link.active i,
.history-link:hover,
.history-link.router-link-active {
  color: #c49a6c;
}

.nav-util {
  display: flex;
  align-items: center;
  gap: 24px;
}

.cart-link {
  position: relative;
  font-size: 16px;
  display: flex;
  align-items: center;
}

.cart-badge {
  position: absolute;
  top: -8px;
  right: -12px;
  background: #c49a6c;
  color: #000;
  font-size: 11px;
  font-weight: 500;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.history-link { 
  font-size: 18px; 
}

.login-btn {
  background: #c49a6c;
  padding: 8px 22px;
  border-radius: 20px;
  color: #fff;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.3s ease;
}

.login-btn:hover, 
.login-btn.router-link-active {
  background: #d9b387;
  color: #000;
}

.login-btn:hover { 
  transform: translateY(-2px); 
}

.login-btn.router-link-active { 
  font-weight: 600; 
}

.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 20px;
  color: #fff;
  cursor: pointer;
}

.mobile-menu {
  position: fixed;
  top: 70px;
  left: 0;
  right: 0;
  background: #111;
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  transform: translateY(-150%); 
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1000;
}

.mobile-menu.open { 
  transform: translateY(0); 
}

.mobile-menu a {
  color: #fff;
  text-decoration: none;
  font-size: 14px;
  font-weight: 400;
  padding: 15px 40px;
}

.mobile-menu a.router-link-active {
  color: #c49a6c;        
  font-weight: 600;
  background: rgba(196, 154, 108, 0.12);
}

.mobile-menu a i { margin-right: 10px; }

.desktop-menu a.router-link-active,
.mobile-menu a.router-link-active {
  color: #c49a6c;
}

@media (max-width: 992px) {
  .navbar {
    padding: 0 20px;
  }
  .desktop-menu, 
  .login-btn {
    display: none;
  }
  .hamburger {
    display: block;
  }
}
</style>
