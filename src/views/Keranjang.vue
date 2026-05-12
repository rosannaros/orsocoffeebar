<template>
  <div class="keranjang-page">
    <div class="fullwidth-section">
      <video class="section-video" autoplay muted loop playsinline>
        <source src="../assets/videos/keranjang-section.mp4" type="video/mp4" />
      </video>
    </div>

    <section v-if="cartItems.length > 0" class="keranjang-container">
      <div v-for="item in cartItems" :key="item.id" class="cart-item">
        <button class="btn-circle-delete" @click="cart.deleteItem(item.id)">
          &times;
        </button>

        <img :src="item.fullImageUrl" @error="(e) => { e.target.src = '/image/favicon.png'; }" alt="Produk"/>

        <div class="cart-info">
          <h3>{{ item.name }}</h3>
        </div>

        <div class="cart-qty">
          <button @click="decrease(item)">-</button>
          <span>{{ item.qty }}</span>
          <button @click="increase(item)">+</button>
        </div>

        <div class="cart-price">
          Rp {{ formatPrice(item.price) }}
        </div>
      </div> 
    </section>
      
    <div v-else class="orso-state-center">
      <p class="empty-desc">Keranjang Anda masih kosong.</p>
      <router-link to="/menu" class="btn-back-menu">Pesan Sekarang</router-link>
    </div>

    <section v-if="cartItems.length" class="cart-summary">
      <p class="item-count">
        Total Item: {{ totalCount }} pcs
      </p>

      <p class="total">
        Total harga: Rp {{ formatPrice(totalPrice) }}
      </p>

      <div class="cart-buttons">
        <button @click="handleCheckout" class="btn-pay-now">
          Bayar Sekarang
        </button>
      </div>
    </section>
  </div>
</template>

<script>
import { useCartStore } from "../stores/cart";
import { getMenuImage, createOrder, payWithMidtrans } from "../services/api";
import Swal from "sweetalert2";

export default {
  data() {
    return {
      isAlreadyWarned: false 
    };
  },

  computed: {
    cart() { 
      return useCartStore(); 
    },
    cartItems() { 
      return this.cart.itemList.map(item => {
        const nameForImage = item.image; 
        return {
          ...item,
          fullImageUrl: getMenuImage(nameForImage) 
        };
      });
    },
    totalPrice() { 
      return this.cart.totalPrice; 
    },
    totalCount() { 
      return this.cart.count; 
    }
  },

  methods: {
    renderImage(imageName) {
      return getMenuImage(imageName);
    },
    increase(item) { 
      this.cart.addItem(item); 
    },
    decrease(item) { 
      this.cart.removeItem(item); 
    },
    formatPrice(val) {
      if (!val) return "0";
      return new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(val);
    },
    
    async handleCheckout() {
      try {
        const userData = JSON.parse(localStorage.getItem('user'));

        if (!userData) {
          Swal.fire({
            icon: 'info',
            text: 'Silakan login terlebih dahulu untuk melakukan pembayaran.',
            confirmButtonColor: '#5C4033',
            confirmButtonText: 'Login Sekarang',
          }).then((result) => {
            if (result.isConfirmed) {
              this.$router.push('/login');
            }
          });
          return;
        }

        if (!this.isAlreadyWarned) {
          const konfirmasi = await Swal.fire({
            title: 'Cek Kembali Pesanan',
            text: "Pastikan pesanan Anda sudah benar sebelum lanjut ke pembayaran.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5C4033',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Sudah Benar',
            cancelButtonText: 'Cek Lagi',
            reverseButtons: true
          });

          if (!konfirmasi.isConfirmed) {
              this.isAlreadyWarned = true;
              return; 
          }
        }

        Swal.fire({
          title: 'Memproses Pesanan',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const payload = {
          id_user: userData.id_user,
          items: this.cartItems.map(item => ({
            id_menu: item.id,
            jumlah_pesanan: item.qty
          }))
        };

        const response = await createOrder(payload);
        Swal.close();

        if (response && response.snap_token) {
          this.cart.clearCart(); 
          this.isAlreadyWarned = false;

          payWithMidtrans(response.snap_token, {
            onSuccess: () => {
              Swal.fire("Berhasil!", "Pembayaran diterima.", "success")
                .then(() => this.$router.push('/pesanan'));
            },
            onPending: () => {
              Swal.fire("Pending", "Selesaikan pembayaran Anda segera.", "info")
                .then(() => this.$router.push('/pesanan'));
            },
            onError: () => Swal.fire("Gagal", "Pembayaran gagal.", "error"),
            onClose: () => {
              Swal.fire("Info", "Pesanan tersimpan, silakan bayar nanti.", "warning")
                .then(() => this.$router.push('/pesanan'));
            }
          });
        }
      } catch (error) {
        Swal.close();
        console.error("Gagal Checkout:", error);
        Swal.fire({
          icon: 'error',
          title: 'Pesanan Gagal',
          text: 'Maaf, terjadi kendala. Pastikan koneksi stabil atau cek ketersediaan menu.',
          confirmButtonColor: '#5C4033',
        });
      }
    }
  }
}
</script>

<style>
.keranjang-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #f9f9f9; 
}

.keranjang-container {
  max-width: 800px;     
  margin: 40px auto;
  padding: 0 20px;
  width: 100%;
}

.cart-item {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #eee;
  padding: 16px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  gap: 15px; 
}

.cart-item img {
  width: 100px;          
  height: 100px;         
  border-radius: 8px;
  object-fit: cover;
  flex-shrink: 0;      
}

.cart-info {
  flex: 2;              
}

.cart-info h3 {
  margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #000;
  }

.cart-qty {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  justify-content: center;
}

.cart-qty button {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid #ddd;
  background:  #5C4033;
  color: #ddd;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
}

.cart-price {
  flex: 1;
  text-align: right;
  font-size: 15px;
  font-weight: 700;
  min-width: 100px;
}

.cart-summary {
  max-width: 800px;
  margin: 0 auto 50px;
  padding: 0 20px;
  width: 100%;
}

.item-count {
  text-align: right;
  font-size: 16px;
  color: #666;
  margin-bottom: 5px;
}

.total {
  text-align: right;
  font-size: 22px;
  font-weight: 800;
  margin-bottom: 20px;
  border-top: 2px solid #fff;
  padding-top: 10px;
}

.cart-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

@media (max-width: 768px) {
  .keranjang-container {
    margin: 15px auto;
    padding: 0 10px;
  }

  .cart-item {
    padding: 8px;
    gap: 8px;
    margin-bottom: 10px;
  }

  .cart-item img {
    width: 60px; 
    height: 60px;
  }

  .cart-info h3 {
    font-size: 13px;
  }

  .cart-qty span {
    font-size: 13px;
  }
    
  .cart-qty button {
    width: 20px;
    height: 20px;
    font-size: 12px;
  }

  .cart-price {
    font-size: 12px;
    min-width: 70px;
  }

  .total {
    font-size: 16px; 
    margin-bottom: 12px;
    padding-top: 10px;
  }
}
</style>
