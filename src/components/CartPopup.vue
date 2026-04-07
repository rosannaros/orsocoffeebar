<template>
  <div id="cartPopup" :class="{ show: cartStore.showPopup }">
    {{ cartStore.popupText }}
  </div>
</template>

<script setup>
import { useCartStore } from '../stores/cart';
const cartStore = useCartStore();
</script>

<style>
#cartPopup {
  position: fixed;
  top: 90px;
  right: 40px;
  background: #c49a6c;
  color: #fff;
  padding: 10px 18px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.4;
  white-space: nowrap;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease, top 0.3s ease;
  z-index: 99999;
}

#cartPopup.show {
  opacity: 1;
  pointer-events: auto;
  top: 70px; 
}
</style>