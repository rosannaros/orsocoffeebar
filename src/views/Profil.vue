<template>
  <div class="profil-page">
    <div class="profil-card" v-if="user">

      <h2 class="welcome-text">
        Selamat Datang, {{ user.nama || 'User' }}
      </h2>
      
      <p class="username-tag"> {{ user.email || user.username || 'user' }}</p>

      <div class="action-buttons">
        <button class="btn-edit" @click="goToEdit">Edit Profil</button>
        <button class="btn-logout" @click="logout">Log Out</button>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
  name: "ProfilPage",
  data() {
    return {
      user: null
    };
  },

  mounted() {
    this.getUserData();
  },

  methods: {
    getUserData() {
      const storedUser = localStorage.getItem("user");
      if (!storedUser) {
        this.$router.push("/login");
        return;
      }
      this.user = JSON.parse(storedUser);
    },

    goToEdit() {
      this.$router.push("/EditProfil");
    },

    async logout() {
      const result = await Swal.fire({
        title: 'Konfirmasi Keluar',
        text: "Apakah kamu yakin ingin logout dari Orso Coffee?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6b4f3a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        fontFamily: 'Poppins, sans-serif'
      });

      if (result.isConfirmed) {
        localStorage.removeItem("user");
        localStorage.removeItem("token");
        window.dispatchEvent(new Event("auth-change"));
        await Swal.fire({
          title: 'Berhasil!',
          text: 'Kamu telah keluar.',
          icon: 'success',
          confirmButtonColor: '#6b4f3a',
          timer: 1500,
          showConfirmButton: false
        });
        this.$router.push("/login");
      }
    }
  }
};
</script>

<style>
.profil-page {
  background: url("../assets/images/bckgr/profilbg.png") no-repeat center center / cover;
}
</style>