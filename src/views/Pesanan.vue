<template>
  <div class="active-order-container">
    <div class="active-order-wrapper">

      <div v-if="!isLoggedIn" class="orso-state-center">
        <p class="empty-desc">Silakan login terlebih dahulu untuk melihat pesanan Anda.</p>
        <router-link to="/login" class="btn-check-history">
          Login Sekarang
        </router-link>
      </div>

      <div v-else-if="loading && activeOrders.length === 0" class="orso-state-center">
        <div class="orso-spinner"></div>
        <p class="loading-text">Memeriksa pesanan...</p>
      </div>

      <div v-else-if="activeOrders.length === 0 && !loading" class="orso-state-center">
        <h2 class="empty-title">Pesanan Tidak Ditemukan</h2>
        <p class="empty-desc">Tidak ada pesanan yang perlu dibayar atau sedang diproses.</p>
        <router-link to="/menu" class="btn-back-menu">
          Pesan Sekarang
        </router-link>
      </div>

      <div v-else class="order-content">
        <header class="active-header">
          <h1 class="order-title">Status Pesanan</h1>
          <p class="order-subtitle">Kamu memiliki {{ activeOrders.length }} pesanan yang sedang berjalan</p>
        </header>

        <div v-for="order in activeOrders" :key="order.id_pesanan" class="order-card-group">
          
          <div class="status-tracker-card">
            <div class="status-main">
              <span :class="['status-badge-large', getStatusClass(order.status_pesanan)]">
                {{ order.status_pesanan.toLowerCase() === 'pending' ? 'Belum Dibayar' : order.status_pesanan }}
              </span>
              <p style="font-weight: bold; margin: 5px 0;">ID Pesanan: #ORSO-{{ order.id_pesanan }}</p>
              <p class="status-time">Waktu Pesan: {{ formatDate(order.tgl_pesanan) }}</p>
            </div>
            
            <div class="status-message">
              <div v-if="order.status_pesanan.toLowerCase() === 'pending'">
                <div class="payment-deadline-info">
                  Pesanan akan dibatalkan otomatis jika tidak dibayar dalam 10 menit.
                </div>
                
                <p>Silakan selesaikan pembayaran agar barista dapat memproses kopi Anda.</p>
                <button @click="payNow(order)" class="btn-pay-now">Bayar Sekarang</button>
              </div>
              <p v-else-if="order.status_pesanan.toLowerCase() === 'diproses'">
                Kopi Anda sedang disiapkan dengan sepenuh hati oleh barista kami.
              </p>
              <p v-else>Mohon tunggu update status selanjutnya.</p>
            </div>
          </div> 

          <div class="order-items-card">
            <h3 class="section-title">Rincian Item</h3>
            <div v-for="item in itemsMap[order.id_pesanan]" :key="item.id_detail_pesanan" class="active-item-row">
              <div class="item-info">
                <div class="item-img-box">
                  <img :src="getMenuImage(item.image_url)" @error="handleImageError" />
                </div>
                <div class="item-text-row">
                  <span class="item-name">{{ item.nama_menu }}</span>
                  <span class="item-qty">{{ item.jumlah_pesanan }}x</span>
                </div>
              </div>
              <span class="item-price">Rp{{ parseInt(item.total_harga).toLocaleString('id-ID') }}</span>
            </div>

            <div class="order-summary-box">
              <div class="summary-line">
                <span>Total Item</span>
                <span class="summary-qty">{{ itemsMap[order.id_pesanan]?.reduce((t, i) => t + i.jumlah_pesanan, 0) || 0 }} Produk</span>
              </div>
              <div class="summary-line total-highlight">
                <span>Total Pembayaran</span>
                <span class="active-grand-total">Rp{{ parseInt(order.total_harga).toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div> 
        </div> 

        <router-link to="/menu" class="btn-back-menu">
          Pesan Menu Lain
        </router-link>
      </div> 
    </div>
  </div>
</template>

<script>
import { getActiveOrders, getOrderDetail, getMenuImage, payWithMidtrans } from '../services/api';
import Swal from 'sweetalert2';

export default {
  name: "ActiveOrder",
  data() {
    return {
      activeOrders: [],
      itemsMap: {},
      loading: true,
      isLoggedIn: true,
      refreshInterval: null
    };
  },
  methods: {
    async fetchActiveOrder() {
      try {
        this.loading = true;
        const savedUser = localStorage.getItem('user');
        let userId = null;

        if (savedUser) {
          userId = JSON.parse(savedUser).id_user;
        } else {
          userId = localStorage.getItem('id_user');
          if (!userId) {
            this.isLoggedIn = false;
            this.loading = false;
            return;
          }
        }
        this.isLoggedIn = true;

        const allOrders = await getActiveOrders(userId);

        if (allOrders && allOrders.length > 0) {
          const filtered = allOrders.filter(o => {
            const s = o.status_pesanan.toLowerCase();
            return s === 'pending' || s === 'diproses' || s === 'belum dibayar';
          });
          
          this.activeOrders = filtered.sort((a, b) => b.id_pesanan - a.id_pesanan);
          
          for (const order of filtered) {
            const detailData = await getOrderDetail(order.id_pesanan);
            this.itemsMap[order.id_pesanan] = detailData;
          }
        } else {
          this.activeOrders = [];
        }
      } catch (error) {
        console.error("Gagal memperbarui:", error);
      } finally {
        this.loading = false;
      }
    },

    payNow(order) {
      if (order.snap_token) {
        payWithMidtrans(order.snap_token, {
          onSuccess: () => {
            this.fetchActiveOrder();
          },
          onPending: (result) => {
            console.log('pending', result);
          },
          onError: (result) => {
            console.log('error', result);
          },
          onClose: () => {
            console.log('customer closed the popup');
          }
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Token pembayaran tidak ditemukan. Coba lagi nanti.',
          confirmButtonColor: '#5C4033'
        });
      }
    },

    formatDate(dateInput) {
      const d = new Date(dateInput);
      return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + " WITA";
    },

    getStatusClass(status) {
      if (!status) return 'pending';
      return status.toLowerCase() === 'diproses' ? 'proses' : 'pending';
    },

    getMenuImage(name) {
      return getMenuImage(name);
    },

    handleImageError(e) { 
      e.target.src = '/image/favicon.png'; 
    }
  },

  mounted() {
    this.fetchActiveOrder();
    this.refreshInterval = setInterval(this.fetchActiveOrder, 15 * 60 * 1000);
  },

  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  }
};
</script>

<style>
.active-order-container {
  font-family: 'Poppins', sans-serif;
  background-color: #fcfbf9;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  padding-bottom: 60px;
}

.active-order-wrapper {
  width: 100%;
  max-width: 650px;
  padding: 60px 20px;
  box-sizing: border-box;
}

.active-header {
  text-align: center;
  margin-bottom: 40px;
}

.order-title {
  font-family: 'Poppins', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: #5C4033;
}

.order-subtitle {
  font-family: 'Poppins', sans-serif;
  color: #888;
  font-size: 14px;
}

.order-card-group {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(92, 64, 51, 0.08);
  margin-bottom: 40px;
}

.status-tracker-card {
  padding: 30px;
  text-align: center;
  border-bottom: 1px dashed #e0dcd9;
}

.status-badge-large {
  display: inline-block;
  padding: 10px 22px;
  border-radius: 50px;
  font-size: 14px; 
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 15px;
  font-family: 'Poppins', sans-serif;
}

.status-badge-large.pending { 
  background: #fff8e1; 
  color: #ffa000; 
}

.status-badge-large.proses { 
  background: #ADD8E6; 
  color: #00008B; 
}

.status-time {
  font-size: 13px;
  color: #888;
}

.payment-deadline-info {
  background: #fff4e5;
  border-left: 4px solid #ffa000;
  padding: 12px;
  border-radius: 8px;
  font-size: 13px;
  color: #856404;
  margin: 15px auto;
  text-align: center;
  display: block;
  align-items: center;
  gap: 10px;
}

.status-message .btn-pay-now {
  margin-top: 20px;
}

.order-items-card {
  padding: 25px;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 20px;
  color: #333;
}

.active-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #fcfbf9;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.item-img-box img {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  object-fit: cover;
}

.item-text-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.item-name {
  font-size: 15px;
  font-weight: 600;
  color: #333;
}

.item-qty {
  font-size: 12px;
  font-weight: 700;
  color: #5C4033;
  background: #f1edea;
  padding: 2px 8px;
  border-radius: 8px;
}

.item-price {
  font-weight: 700;
  color: #5C4033;
}

.order-summary-box {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 2px solid #fcfbf9;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 14px;
}

.total-highlight {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #eee;
}

.active-grand-total {
  font-size: 20px;
  font-weight: 800;
  color: #5C4033;
}

.order-content {
  display: flex;
  flex-direction: column;
  align-items: center; 
  width: 100%;
}

@media (max-width: 768px) {
  .active-item-row {
    padding: 10px 0;
    gap: 8px; 
  }

  .item-info {
    flex: 1;
    min-width: 0;
  }

  .item-text-row {
    display: flex;
    flex-direction: column; 
    align-items: flex-start;
    gap: 4px;
    overflow: hidden;
  }

  .item-name {
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
    width: 100%;
  }

  .item-qty {
    font-size: 10px;
    padding: 1px 6px;
  }

  .item-price {
    font-size: 13px;
    white-space: nowrap; 
  }
}
</style>