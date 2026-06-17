<template>
  <div class="orso-history-container">
    <div class="orso-history-wrapper">
      <div v-if="!selectedOrder">
        <header class="orso-header">
          <h1 class="orso-title">Riwayat Pesanan</h1>
          <p class="orso-subtitle">Daftar pesanan Anda yang selesai dan dibatalkan.</p>
        </header>

        <div v-if="loading" class="orso-state-center">
          <div class="orso-spinner"></div>
          <p class="empty-desc">Memuat riwayat...</p>
        </div>
        <div v-else-if="orders.length === 0" class="orso-state-center">
          <p class="empty-desc">Belum ada riwayat pesanan. Silakan membuat pesanan terlebih dahulu.</p>
          <router-link to="/menu" class="btn-back-menu">
            Pesan Sekarang
          </router-link>
        </div>
        
        <div v-else class="orso-list">
          <div v-for="order in orders" :key="order.id_pesanan" class="orso-card">
            <div class="orso-card-header">
              <div class="orso-meta">
                <span class="orso-id">#ORSO-{{ order.id_pesanan }}</span>
                <span>&nbsp;&nbsp;</span> <span class="orso-date">{{ formatDate(order.tgl_pesanan) }}</span>
              </div>
              <span :class="['orso-status-badge', getStatusClass(order.status_pesanan)]">
                {{ order.status_pesanan }}
              </span>
            </div>

            <div class="orso-card-body">
              <div class="orso-prod-info">
                <img :src="getMenuImage(order.image_url)" class="orso-img-thumb" @error="handleImageError"/>
                <div class="orso-text-group">
                  <h3 class="orso-item-name">{{ order.menu_name }}</h3>
                  <p class="orso-item-qty">
                    {{ order.total_items > 1 ? `+${order.total_items - 1} produk lainnya` : '1 Produk' }}
                  </p>
                </div>
              </div>

              <div class="orso-actions-outer">
                <div class="orso-price-stack">
                  <span class="orso-total-label">Total Bayar</span>
                  <span class="orso-total-amount">Rp{{ parseInt(order.total_harga).toLocaleString('id-ID') }}</span>
                </div>
                
                <button @click="openDetail(order)" class="btn-detail">
                  Detail
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="orso-detail-view">
        <header class="orso-detail-header">
          <button @click="closeDetail" class="btn-back-history">←</button>
          <div class="orso-header-text">
            <h1 class="orso-title">Detail Riwayat Pesanan</h1>
            <p class="orso-subtitle">#ORSO-{{ selectedOrder.id_pesanan }}</p>
          </div>
        </header>

        <div class="orso-detail-card">
          <div class="orso-info-grid">
            <div class="orso-info-box">
              <span class="orso-info-label">Status Pesanan</span>
              <span :class="['orso-status-badge', getStatusClass(selectedOrder.status_pesanan)]">
                {{ selectedOrder.status_pesanan }}
              </span>
            </div>
            <div class="orso-info-box">
              <span class="orso-info-label">Waktu Transaksi</span>
              <span class="orso-info-value">{{ formatDate(selectedOrder.tgl_pesanan) }}</span>
            </div>
          </div>

          <div class="orso-section">
            <h3 class="orso-section-title">Rincian Produk</h3>
            <div v-for="item in detailItems" :key="item.id_detail_pesanan" class="orso-item-row">
              <div class="orso-item-left">
                <img :src="getMenuImage(item.image_url)" @error="handleImageError" class="orso-img-small" />
                <div class="orso-item-text">
                  <span class="orso-name-text">{{ item.nama_menu }}</span>
                  <span class="orso-qty-text">{{ item.jumlah_pesanan }}x @ Rp{{ parseInt(item.harga).toLocaleString('id-ID') }}</span>
                </div>
              </div>
              <span class="orso-subtotal-text">Rp{{ (item.harga * item.jumlah_pesanan).toLocaleString('id-ID') }}</span>
            </div>
          </div>

          <div class="orso-payment-box">
            <h3 class="orso-section-title">Ringkasan Pembayaran</h3>
            <div class="orso-pay-row">
              <span>Total Item</span>
              <span>{{ totalQuantity }} Produk</span>
            </div>
            <div class="orso-pay-row">
              <span>Subtotal Produk</span>
              <span>Rp{{ parseInt(selectedOrder.total_harga).toLocaleString('id-ID') }}</span>
            </div>
            <div class="orso-pay-row orso-grand-total">
              <span>Total Pembayaran</span>
              <span class="orso-text-brown">Rp{{ parseInt(selectedOrder.total_harga).toLocaleString('id-ID') }}</span>
            </div>
          </div>
          
          <div class="orso-detail-actions">
            <button @click="handlePesanLagi(selectedOrder.id_pesanan)" class="btn-reorder">
              Pesan Lagi Sekarang
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api, { getMenuImage, getActiveOrders, getOrderDetail, } from '../services/api';
import { useCartStore } from '../stores/cart';
import Swal from 'sweetalert2';

export default {
  name: 'RiwayatPesanan',
  data() {
    return {
      orders: [],
      loading: true,
      selectedOrder: null,
      detailItems: []
    };
  },
  computed: {
    totalQuantity() {
      return this.detailItems.reduce((acc, item) => acc + parseInt(item.jumlah_pesanan), 0);
    }
  },
  mounted() {
    const savedUser = localStorage.getItem('user');
    if (!savedUser) {
      this.$router.push('/login');
    }
    this.fetchOrders();
  },
  methods: {
    getMenuImage(name) {
      return getMenuImage(name, 'menu');
    },

    async fetchOrders() {
      this.loading = true;
      try {
        const savedUser = localStorage.getItem('user');
        const user = JSON.parse(savedUser);
        const responseOrders = await getActiveOrders(user.id_user);

        const filtered = responseOrders.filter(o => 
          o.status_pesanan.toLowerCase().includes('selesai') || o.status_pesanan.toLowerCase().includes('batal')
        );

        const detailed = await Promise.all(filtered.map(async (order) => {
          const res = await getOrderDetail(order.id_pesanan);
          return { 
            ...order, 
            menu_name: res[0]?.nama_menu || 'Menu', 
            image_url: res[0]?.image_url || null, 
            total_items: res.length 
          };
        }));

        this.orders = detailed.sort((a, b) => b.id_pesanan - a.id_pesanan);
      } catch (e) {
        this.orders = [];
      } finally {
        this.loading = false;
      }
    },

    async openDetail(order) {
      this.selectedOrder = order;
      try {
        const res = await getOrderDetail(order.id_pesanan);
        this.detailItems = res;
      } catch (e) {
        console.error("Open Detail Error:", e);
      }
    },

    closeDetail() { 
      this.selectedOrder = null; 
      this.detailItems = [];
    },

    async handlePesanLagi(orderId) {
      const cartStore = useCartStore(); 
      try {
        const items = await getOrderDetail(orderId);
        if (items && items.length > 0) {
          items.forEach(item => {
            cartStore.addItem({ 
              id: item.id_menu, 
              name: item.nama_menu, 
              price: item.harga, 
              image: item.image_url, 
              jumlah: item.jumlah_pesanan 
            });
          });
          this.$router.push('/keranjang'); 
        }
      } catch (e) { 
        console.error("Error Reorder:", e);
      }
    },

    formatDate(s) {
      if (!s) return "";
      const d = new Date(s);
      return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) + 
             " - " + 
             d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    },

    getStatusClass(s) {
      return s?.toLowerCase().includes('selesai') ? 'selesai' : 'batal';
    },

    handleImageError(e) { 
      e.target.src = '/image/favicon.png'; 
    }
  }
};
</script>

<style>
.orso-history-container {
  font-family: 'Poppins', sans-serif;
  background: #f8f9fa;
  min-height: 100vh;
  padding: 30px 15px;
  color: #333;
}

.orso-history-wrapper {
  max-width: 700px;
  margin: 0 auto;
}

.orso-header {
  margin-bottom: 25px;
}

.orso-title {
  font-size: 22px;
  font-weight: 800;
  color: #5C4033;
  margin: 0;
}

.orso-subtitle {
  font-size: 13px;
  color: #888;
  margin-top: 4px;
}

.orso-card {
  background: white;
  border-radius: 12px;
  margin-bottom: 15px;
  border: 1px solid #eee;
  overflow: hidden;
}

.orso-card-header {
  display: flex;
  justify-content: space-between;
  padding: 12px 15px;
  background: #fafafa;
  border-bottom: 1px solid #eee;
  align-items: center;
}

.orso-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.orso-id {
  font-weight: 700;
  font-size: 13px;
  color: #5C4033;
}

.orso-date {
  font-size: 11px;
  color: #999;
}

.orso-status-badge {
  font-size: 10px;
  padding: 4px 10px;
  border-radius: 5px;
  font-weight: 700;
  text-transform: uppercase;
}

.orso-status-badge.selesai {
  background: #E8F5E9;
  color: #2E7D32;
}

.orso-status-badge.batal {
  background: #FFEBEE;
  color: #C62828;
}

.orso-card-body {
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.orso-prod-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.orso-img-thumb {
  width: 55px;
  height: 55px;
  border-radius: 8px;
  object-fit: cover;
}

.orso-item-name {
  font-size: 14px;
  font-weight: 700;
  margin: 0;
}

.orso-item-qty {
  font-size: 12px;
  color: #999;
  margin: 0;
}

.orso-actions-outer {
  display: flex;
  align-items: center;
  gap: 15px;
  text-align: right;
}

.orso-price-stack {
  text-align: right;
}

.orso-total-label {
  font-size: 10px;
  color: #999;
  text-transform: uppercase;
  display: block;
}

.orso-total-amount {
  font-size: 15px;
  font-weight: 800;
  color: #5C4033;
}

.btn-detail {
  padding: 8px 16px;
  background: #f1f3f5;
  border: 1.5px solid #dee2e6;
  color: #495057;
  border-radius: 8px;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
}

.orso-detail-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 25px;
}

.orso-detail-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.orso-detail-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  border: 1px solid #eee;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.orso-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  background: #fdfaf8;
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 25px;
  border: 1px solid #f3ece7;
}

.orso-info-label {
  font-size: 10px;
  color: #999;
  text-transform: uppercase;
  font-weight: 700;
  display: block;
  margin-bottom: 5px;
}

.orso-info-value {
  font-size: 13px;
  font-weight: 600;
  color: #333;
}

.orso-section-title {
  font-size: 14px;
  font-weight: 800;
  color: #5C4033;
  border-bottom: 1.5px solid #f8f9fa;
  padding-bottom: 8px;
  margin-bottom: 15px;
}

.orso-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.orso-item-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.orso-img-small {
  width: 45px;
  height: 45px;
  border-radius: 6px;
  object-fit: cover;
  border: 1px solid #eee;
}

.orso-name-text {
  font-size: 13px;
  font-weight: 700;
  display: block;
}

.orso-qty-text {
  font-size: 11px;
  color: #888;
}

.orso-subtotal-text {
  font-weight: 700;
  font-size: 13px;
  color: #333;
}

.orso-payment-box {
  margin-top: 25px;
  background: #fafafa;
  padding: 15px;
  border-radius: 12px;
}

.orso-pay-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 8px;
  color: #666;
}

.orso-grand-total {
  border-top: 1px solid #eee;
  padding-top: 10px;
  margin-top: 10px;
  font-weight: 800;
  font-size: 16px;
  color: #333;
}

.orso-text-brown {
  color: #5C4033;
  font-size: 20px;
}

@media (max-width: 600px) {
  .orso-meta {
    gap: 2px;
  }
  .orso-card-body {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .orso-actions-outer {
    width: 100%;
    justify-content: space-between;
    border-top: 1px dashed #eee;
    padding-top: 12px;
  }
  .orso-info-grid {
    grid-template-columns: 1fr;
  }
}
</style>