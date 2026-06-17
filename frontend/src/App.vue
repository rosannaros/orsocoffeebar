<template>
  <Navbar/>
  <router-view />
  <Footer/>
  <CartPopup />
</template>

<script setup>
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
import CartPopup from './components/CartPopup.vue';
</script>

<style>
</style>
