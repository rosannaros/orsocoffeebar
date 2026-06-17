<template>
  <section class="tulistestimoni-page">
    <div class="tulistestimoni-card card-master">
      <h2>Tulis Testimoni Kamu Yuk!</h2>
      
      <p class="user-display">Mengirim sebagai: <strong>{{ userName }}</strong></p>
      
      <form @submit.prevent="submitTestimoni">
        <div class="textarea-wrapper">
        <textarea 
          v-model="testimoni" 
          placeholder="Upload foto pesanan dan Pastikan kamu menyebutkan produk yang sudah kamu beli ya!" 
          maxlength="100" 
          required>
        </textarea>
        <div class="char-counter" :class="{ 'limit-reached': testimoni.length >= 100 }">
          {{ testimoni.length }} / 100 karakter
        </div>
      </div>
        <div class="file-input-wrapper">
          <label for="fileInput" class="file-btn">Pilih File</label>
          <span class="file-info">{{ fileName || 'Tidak ada file' }}</span>
          <input type="file" id="fileInput" accept="image/*" @change="handleFile" />
        </div>

        <div class="preview-container" v-if="preview">
          <img :src="preview" alt="Preview" />
          <button type="button" class="btn-circle-delete preview-remove-pos" @click="removeFile">×</button>
        </div>

        <div class="button-container">
          <router-link to="/testimoni">Kembali</router-link>
          <button type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Mengirim...' : 'Kirim Testimoni' }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import { postTestimoni } from '../services/api.js'; 
import Swal from 'sweetalert2';

export default {
  name: 'TulisTestimoni',
  data() {
    return {
      userName: '',
      testimoni: '',
      foto: null,
      preview: null,
      fileName: '',
      isSubmitting: false
    }
  },
  mounted() {
    const userData = JSON.parse(localStorage.getItem('user'));
    if (userData) {
      this.userName = userData.nama_user || userData.nama;
    } else {
      this.$router.push('/login');
    }
  },
  methods: {
    handleFile(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      this.foto = file;
      this.fileName = file.name;
      const reader = new FileReader();
      reader.onload = e => { this.preview = e.target.result; };
      reader.readAsDataURL(file);
    },

    removeFile() {
      this.foto = null;
      this.preview = null;
      this.fileName = '';
      const fileInput = document.getElementById('fileInput');
      if (fileInput) fileInput.value = '';
    },

    async submitTestimoni() {
      const userData = JSON.parse(localStorage.getItem('user'));
      if (!userData?.id_user) {
        this.$router.push('/login');
        return;
      }

      this.isSubmitting = true;

      const formData = new FormData();
      formData.append('id_user', userData.id_user);
      formData.append('isi_testimoni', this.testimoni);

      if (this.foto) {
        formData.append('foto_testimoni', this.foto);
      }

      try {
        await postTestimoni(formData);
        Swal.fire({
          icon: 'info',
          title: 'Pending!',
          text: 'Testimoni kamu menunggu persetujuan owner.',
        }).then(() => {
          this.$router.push('/testimoni');
        });
      } catch (error) {
        Swal.fire('Gagal', error.error || 'Terjadi kesalahan sistem', 'error');
      } finally {
        this.isSubmitting = false;
      }
    }
  } 
};
</script>

<style>
.tulistestimoni-page {
  background: url("../assets/images/bckgr/tulistestimonibg.jpg") no-repeat center center;
}

.textarea-wrapper {
  position: relative;
  width: 100%;
}

.tulistestimoni-card textarea {
  border-radius: 20px;
  height: 120px;
  resize: none;
  width: 100%;
  padding: 15px; 
  box-sizing: border-box;
  margin-bottom: 0px;
}

.char-counter {
  position: absolute;
  right: 15px;    
  bottom: 20px;    
  font-size: 11px;
  color: #999;
  pointer-events: none;
}

.char-counter.limit-reached {
  color: #ff4d4d;
  font-weight: bold;
}

.user-display {
  font-size: 14px;
  color: #5C4033;
  margin-bottom: 10px;
  text-align: left;
}

.file-input-wrapper {
  margin-top: 10px; 
  display: flex;
  align-items: center;
  gap: 15px;
}

.file-btn {
  background-color: #d9d9d9;
  border-radius: 6px;
  padding: 8px 15px;
  cursor: pointer;
  font-weight: bold;
  color: #000;
}

#fileInput {
  display: none;
}

.preview-container {
  position: relative;
  display: inline-block;
  margin-top: 15px;
}

.preview-container img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #ddd;
  display: block;
}

.preview-remove-pos {
  position: absolute;
  top: -8px;
  right: -8px;
}
</style>