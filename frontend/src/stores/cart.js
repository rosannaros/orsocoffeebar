import { defineStore } from "pinia"

export const useCartStore = defineStore("cart", {
  state: () => ({
    items: JSON.parse(localStorage.getItem("cart")) || {},
    showPopup: false,
    popupText: ""
  }),

  getters: {
    itemList(state) {
      return Object.values(state.items)
    },
    count(state) {
      return Object.values(state.items).reduce((total, item) => total + item.qty, 0)
    },
    totalPrice(state) {
      return Object.values(state.items).reduce((total, item) => total + item.qty * item.price, 0)
    },
    getQty: (state) => (id) => {
      return state.items[id]?.qty || 0
    }
  },

  actions: {
    save() {
      localStorage.setItem("cart", JSON.stringify(this.items))
    },

    triggerPopup(text) {
      this.popupText = text;
      this.showPopup = true;
      setTimeout(() => { this.showPopup = false }, 1500);
    },

    addItem(item) {
      const id = item.id || item.id_menu;
      if (!this.items[id]) {
        this.items[id] = {
          id: id,
          name: item.name || item.nama_menu,
          price: Number(item.price || item.harga),
          image: item.image || item.image_url,
          qty: Number(item.jumlah || 1)
        }
      } else {
        this.items[id].qty += Number(item.jumlah || 1);
      }
      this.save();
      this.triggerPopup("✅ Ditambahkan ke keranjang");
    },

    removeItem(item) {
      const id = item.id || item.id_menu;
      if (!this.items[id]) return

      if (this.items[id].qty > 1) {
        this.items[id].qty--
      } else {
        delete this.items[id]
      }
      this.save();
      this.triggerPopup("❌ Dihapus dari keranjang");
    },

    deleteItem(id) {
      if (this.items[id]) {
        const itemName = this.items[id].name;
        delete this.items[id];
        this.save();
        this.triggerPopup(` ${itemName} dihapus`);
      }
    },

    clearCart() {
      this.items = {}; 
      localStorage.setItem("cart", JSON.stringify({})); 
    },
  }
})