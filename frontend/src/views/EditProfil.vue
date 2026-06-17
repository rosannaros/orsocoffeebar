<template>
  <section class="editprofil-page">
    <div class="editprofil-card card-master">
      <h2>Edit Profil</h2>

      <form @submit.prevent="saveProfile">
        <div class="form-group">
          <label>Username Baru</label>
          <input type="text" v-model="form.nama" placeholder="Masukkan username" required />
        </div>

        <div class="form-group">
          <label>Email Baru</label>
          <input type="email" v-model="form.email" name="email" id="email" autocomplete="email" placeholder="Masukkan email" required />
        </div>

        <div class="form-group password-wrapper">
          <label>Password Baru</label>
          <div class="input-with-icon">
            <input :type="showPassword ? 'text' : 'password'" v-model="form.password" autocomplete="new-password" placeholder="Isi jika ingin ubah sandi" />
            <span class="material-icons google-eye-inside" @click="showPassword = !showPassword">
              {{ showPassword ? 'visibility' : 'visibility_off' }}
            </span>
          </div>
        </div>
        <div class="button-container">
          <router-link to="/profil" class="back-link">Kembali</router-link>
          <button type="submit" class="save-btn" :disabled="isSubmitting">
            {{ isSubmitting ? 'Mengirim...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import Swal from 'sweetalert2'
import { updateUser } from "../services/api.js";

export default {
  name: "EditProfil",
  data() {
    return {
      isSubmitting: false,
      showPassword: false,
      form: {
        nama: "", 
        email: "", 
        password: ""
      }
    };
  },

  mounted() {
    const storedUser = JSON.parse(localStorage.getItem("user"));
    if (storedUser) {
      this.form.nama = storedUser.nama || ""; 
      this.form.email = storedUser.email || "";
  } else {
    this.$router.push('/login');
  }
},

  methods: {
    async saveProfile() {
      this.isSubmitting = true;
      try {
        const storedUser = JSON.parse(localStorage.getItem("user"));
        if (!storedUser) throw new Error("Sesi berakhir, silakan login kembali.");

        const isNamaSama = this.form.nama === storedUser.nama;
        const isEmailSama = this.form.email === storedUser.email;
        const isPasswordKosong = !this.form.password;

        if (isNamaSama && isEmailSama && isPasswordKosong) {
          this.isSubmitting = false;
          return Swal.fire({
            icon: 'info',
            title: 'Tidak ada perubahan',
            text: 'Data profil kamu masih sama dengan yang sebelumnya.',
            confirmButtonColor: '#6b4f3a',
          });
        }

        const rawId = storedUser.id || storedUser.id_user; 
        if (!rawId) {
          throw new Error("ID User tidak ditemukan. Silakan login ulang.");
        }

        const payload = {
          nama: this.form.nama,
          email: this.form.email,
        }

        if (this.form.password) {
          payload.password = this.form.password;
        }

        await updateUser(rawId, payload);
    
        const updatedUserData = { 
          ...storedUser, 
          nama: this.form.nama, 
          email: this.form.email 
        };
        localStorage.setItem("user", JSON.stringify(updatedUserData));

        await Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Profil kamu sudah diperbarui.',
          confirmButtonColor: '#6b4f3a', 
        })

        this.$router.push("/profil");
  
      } catch (error) {
        console.error("Update Error:", error);
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: error.message || 'Terjadi kesalahan saat menyimpan data.',
        });
      } finally {
        this.isSubmitting = false; 
      }
    }
  }
};
</script>

<style>
.editprofil-page {
  background: url("../assets/images/bckgr/editprofilbg.jpg") no-repeat center center / cover;
}
</style>