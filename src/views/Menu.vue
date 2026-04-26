<template>
  <div class="menu-page">
    <div class="fullwidth-section">
      <img src="../assets/images/sections/menu-section.jpg" alt="Our Menu" />
    </div>

    <div class="menu-search">
      <input type="text" v-model="searchQuery" placeholder="Cari menu favoritmu..."/>
    </div>

    <div class="category-filter">
      <button v-for="cat in categories" :key="cat.key" :class="{ active: selectedCategory === cat.key }" @click="selectedCategory = cat.key">
        {{ cat.label }}
      </button>
    </div>

    <div class="menu-grid">
      <div class="menu-card" v-for="item in filteredMenu" :key="item.id_menu" :class="{ 'is-habis': item.status_menu === 'habis' }">

        <div v-if="item.status_menu === 'habis'" class="label-habis">HABIS</div>
        <img :src="getMenuImage(item.image_url)" @error="handleImageError" />
        <h1>{{ item.nama_menu }}</h1>
        <div class="price">Rp {{ formatPrice(item.harga) }}</div>
        <p>{{ item.deskripsi }}</p>
        
        <div class="menu-actions">
          <div class="quantity-control">
            <button @click="decrease(item)">-</button>
            <span class="qty-number">{{ cart.getQty(item.id_menu) }}</span>
            <button @click="item.status_menu !== 'habis' && increase(item)">+</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getMenu, getMenuImage } from "../services/api"
import { useCartStore } from "../stores/cart"

export default {
  name: "Menu",
  data() {
    return {
      selectedCategory: "all",
      searchQuery: "",
      showPopup: false,
      popupText: "",
      menuItems: [],
      categories: [
        { key: "all", label: "All" },
        { key: "coffee", label: "Coffee" },
        { key: "non-coffee", label: "Non-Coffee" },
        { key: "tea", label: "Tea" },
        { key: "mojito", label: "Mojito" },
      ]
    }
  },

  computed: {
    cart() { 
      return useCartStore() 
    },

    filteredMenu() {
      if (!this.menuItems) return []; 
      return this.menuItems.filter(item => {
        const matchCategory =
          this.selectedCategory === "all" ||
          (item.kategori && item.kategori.toLowerCase() === this.selectedCategory.toLowerCase());

        const matchSearch =
          item.nama_menu.toLowerCase().includes(this.searchQuery.toLowerCase());
          
        return matchCategory && matchSearch;
      });
    }
  },
  
  watch: {
    '$route.query.kategori'(newVal) {
      this.selectedCategory = newVal || "all";
    }
  },

  async mounted() {
    const kategoriDariUrl = this.$route.query.kategori;
    if (kategoriDariUrl) {
      this.selectedCategory = kategoriDariUrl;
    }
    try {
      const data = await getMenu();
      console.log("Data diterima di Vue:", data); 
      this.menuItems = data;
    } catch (error) {
      console.error("Gagal load menu di komponen:", error);
    }
  },

  methods: { 
    getMenuImage(name) {
      return getMenuImage(name, 'menu');
    },

    increase(item) {
      this.cart.addItem({
        id: item.id_menu, 
        name: item.nama_menu,
        price: item.harga,
        image: item.image_url
      });
    },

    decrease(item) {
      if (this.cart.getQty(item.id_menu) > 0) {
        this.cart.removeItem({ id: item.id_menu }); 
      }
    },
    
    formatPrice(price) {
      return Number(price).toLocaleString("id-ID");
    },

    handleImageError(e) {
      e.target.src = '/image/placeholder-coffee.jpg';
    },
  }
}
</script>

<style>
.menu-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh; 
  font-family: 'Poppins', sans-serif;
}

.menu-search {
  width: 100%;           
  max-width: 1000px;    
  margin: 0 auto 30px;    
  padding: 0 32px;        
  box-sizing: border-box;  
}

.menu-search input {
  width: 100%;           
  padding: 15px 25px;    
  border-radius: 50px;    
  border: 1px solid #ddd;
  font-size: 16px;
  outline: none;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  font-family: inherit;
}

.menu-search input:focus {
  border-color: #664f3c;
}

.category-filter {
  text-align: center;
  margin: 40px 0;
  display: flex; 
  justify-content: center;
  flex-wrap: wrap;
  gap: 12px;
}

.category-filter button {
  padding: 12px 26px;
  font-weight: bold;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  text-align: center;
  text-decoration: none;
  transition: box-shadow 0.25s ease, transform 0.25s ease;
  background-color: #f0f0f0;
  color: #000;
  text-transform: uppercase;
  font-family: inherit;
}

.category-filter button.active,
.category-filter button:hover {
  background-color: #5C4033; 
  color: #fff;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); 
  transform: translateY(-2px); 
}

.category-filter button:active {
  transform: translateY(0);
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr); 
  gap: 22px;
  padding: 20px 32px;
  max-width: 1280px;
  margin: 0 auto 40px; 
  align-items: start; 
}

.menu-grid::-webkit-scrollbar {
  display: none;
}

.menu-card {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  padding: 15px;
  display: flex;
  flex-direction: column;
  height: 100%; 
  max-width: 360px;
  margin: 0 auto;
}

.menu-card img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 10px;
}

.menu-card h1 {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 4px;
  text-align: center;
}

.menu-card .price {
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 10px;
  text-align: center;
  color: #5C4033;
}

.menu-card p {
  font-size: 12px;
  line-height: 1.4;
  color: #777;
  margin-top: 6px;
  margin-bottom: 12px;  
}

.menu-actions {
  margin-top: auto;
}

.qty-number {
  width: 48px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  background: #fff;
  font-family: inherit;
}

.quantity-control {
  display: flex;
  justify-content: center;
  gap: 5px;
}

.quantity-control button {
  background: #5C4033;
  color: #fff;
  border: none;
  width: 34px;
  height: 34px;
  border-radius: 5px;
  cursor: pointer;
  font-family: inherit;
}

.quantity-control input {
  width: 48px;
  text-align: center;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.menu-card.is-habis {
  filter: grayscale(1);
  opacity: 0.6;
  position: relative;
  pointer-events: none; 
}

.label-habis {
  position: absolute;
  top: 10%; 
  left: 15%;
  transform: translate(-50%, -50%);
  background: black; 
  color: white;
  padding: 6px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 300;
  letter-spacing: 1px;
}

@media (max-width: 768px) {
  .menu-search {
    margin-bottom: 16px;
  }

  .menu-search input {
    font-size: 12px;
    padding: 10px 14px;
  }
  
  .menu-grid {
    grid-template-columns: repeat(3, 1fr); 
    gap: 8px;                                    
    padding: 10px;
    max-width: 100%;      
    width: 100%;          
    box-sizing: border-box; 
    margin: 0;          
    overflow-x: hidden;   
  }

  .menu-card {
    height: 100%; 
    display: flex;
    flex-direction: column;
  }

  .menu-card img {
    aspect-ratio: 1 / 1;
    height: auto;          
  }

  .menu-card h1 {
    font-size: 10px;
    font-weight: 600;
    margin-bottom: 2px;
    line-height: 1.2;
  }

  .menu-card .price {
    font-size: 10px;
    margin-bottom: 4px;
  }

  .menu-card p {
    font-size: 8px;
    line-height: 1.2;
    display: block;            
    -webkit-line-clamp: none;  
    overflow: visible;        
    margin-top: 6px;
    margin-bottom: 12px;
    flex-grow: 1;            
  }

  .menu-actions {
    margin-top: auto; 
  }

  .quantity-control button {
    width: 24px;
    height: 24px;
    font-size: 12px;
  }

  .quantity-control input {
    width: 30px;
    font-size: 11px;
  }

  .qty-number {
    width: 30px;
    height: 24px;
    font-size: 11px;
  }

  .category-filter {
    gap: 8px;
    margin: 20px 0;
  }

  .category-filter button {
    padding: 9px 16px;
    font-size: 12px;
    border-radius: 20px; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .category-filter button.active,
  .category-filter button:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .label-habis {
    top: 10%; 
    left: 15%;
    transform: translate(-50%, -50%);
    font-size: 10px; 
    padding: 4px 10px;
    font-weight: 500;
    letter-spacing: 0.5px;
    border-radius: 4px;
  }

  .menu-card.is-habis {
    opacity: 0.5;
  }
}
</style>
