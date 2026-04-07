<template>
  <div class="testimoni-page">
    <div class="fullwidth-section">
      <img src="../assets/images/sections/testimoni-section.jpg" alt="Testimoni Orso Coffee Bar" />
    </div>

    <section class="testimoni-text">
      <h1>~ Setiap cangkir kopi punya cerita ~</h1>
      <p>Yuk, cari tahu apa kata mereka tentang Orso Coffee Bar.</p>
    </section>

    <section v-if="testimoni.length > 0" class="testimoni-container">
      <div v-for="t in testimoni" :key="t.id_testimoni" class="testimoni-card">
        <img :src="t.foto_testimoni ? getMenuImage(t.foto_testimoni, 'testimoni') : '/images/placeholder-user.png'" @error="handleImageError" alt="Foto Testimoni"/>
        <h3>{{ t.nama || 'Pelanggan Orso' }}</h3>
        <p>“{{ t.isi_testimoni }}”</p>
      </div>
    </section>

    <div v-else class="orso-state-center">
      <p class="empty-desc">Belum Ada Testimoni</p>
    </div>

    <button @click="handleTambahTestimoni" class="btn-add"> + </button>
  </div>
</template>

<script>
import { getTestimoni, getMenuImage } from '../services/api';
import Swal from 'sweetalert2';

export default {
  name: 'TestimoniPage',
  data() {
    return {
      testimoni: []
    };
  },
  mounted() {
    this.ambilData();
  },
  methods: {
    getMenuImage,

    handleTambahTestimoni() {
      const userData = JSON.parse(localStorage.getItem('user'));
      if (!userData) {
        Swal.fire({
          icon: 'info',
          text: 'Silakan login terlebih dahulu untuk menulis testimoni.',
          confirmButtonColor: '#5C4033',
          confirmButtonText: 'Login Sekarang',
        }).then((result) => {
          if (result.isConfirmed) {
            this.$router.push('/login');
          }
        });
      } else {
        this.$router.push('/tulistestimoni');
      }
    },

    async ambilData() {
      try {
        const data = await getTestimoni();
        this.testimoni = data;
        console.log("Data testimoni diterima:", data);
      } catch (error) {
        console.error("Gagal memuat testimoni:", error);
      }
    },

    handleImageError(e) {
      const placeholder = '/images/placeholder-user.png';
      if (e.target.src !== window.location.origin + placeholder) {
        e.target.src = placeholder;
      }
    }
  }
};
</script>

<style>
.testimoni-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh; 
  width: 100%;
  background: #fff;
}

.testimoni-text {
  max-width: 860px;
  margin: 0 auto 50px;
  text-align: center;
  padding: 40px 24px 0;
}

.testimoni-text h1 {
  font-size: 26px;
  font-weight: 800;
  line-height: 1.4;
  color: #42281e;
  margin-bottom: 10px;
}

.testimoni-text p {
  font-size: 15px;
  line-height: 1.6;
  color: #666;
  max-width: 680px;
  margin: 0 auto;
}

.testimoni-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, 240px); 
  gap: 60px 30px; 
  max-width: 1100px;
  margin: 0 auto; 
  padding: 40px 24px;
  justify-content: center;
}

.testimoni-card {
  position: relative;
  width: 240px;
  min-height: auto; 
  height: fit-content; 
  background: #e7d6c4;
  border-radius: 18px;
  box-shadow: 0 4px 10px rgba(0,0,0,.1);
  padding: 50px 15px 25px; 
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start; 
}

.testimoni-card img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  position: absolute;
  top: -50px;
  left: 50%;
  transform: translateX(-50%);
  border: 4px solid #fff;
}

.testimoni-card h3 {
  margin: 4px 0 12px; 
  font-size: 16px;
  font-weight: 700;
  color: #42281e;
}

.testimoni-card p {
  font-size: 13px; 
  line-height: 1.5;
  font-style: italic;
  margin: 0; 
  word-wrap: break-word;
  overflow-wrap: break-word;
  width: 100%; 
}

.btn-add {
  position: fixed;
  right: 30px;
  bottom: 20px;
  width: 56px;
  height: 56px;
  background: #5C4033;
  color: #fff;
  font-size: 30px;
  border-radius: 50%;
  z-index: 999;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;      
  outline: none;         
  padding: 0;            
  line-height: 0;       
}

.btn-add:focus {
  outline: none;
  border: none;
}

@media (max-width: 1024px) {
  .testimoni-container {
    grid-template-columns: repeat(3, 220px);
    gap: 70px 25px; 
    margin: 60px auto;
    justify-content: center;
  }

  .testimoni-card { 
    width: 220px;
    height: auto;
    padding: 60px 15px 25px; 
    border-radius: 18px;
  }

  .testimoni-card img {
    width: 85px;
    height: 85px;
    top: -42px;
    border-width: 4px;
  }

  .testimoni-card h3 {
    font-size: 15px;
    margin: 0 0 10px 0; 
  }

  .testimoni-card p {
    font-size: 12px;
    line-height: 1.4;
  }
}

@media (max-width: 640px) {
  .testimoni-container {
    grid-template-columns: repeat(2, 160px);
    gap: 60px 15px; 
    padding: 20px 10px;
    margin: 40px auto 100px;
  }
  
  .testimoni-card {
    width: 160px;
    min-height: 100px; 
    padding: 35px 10px 15px; 
  }

  .testimoni-card img {
    width: 60px;
    height: 60px;
    top: -30px;
    border-width: 3px;
  }

  .testimoni-card h3 { 
    font-size: 12px;
    margin-bottom: 8px;
  }

  .testimoni-card p { 
    font-size: 10px; 
    line-height: 1.2; 
    margin: 2px 0;
  }
}
</style>