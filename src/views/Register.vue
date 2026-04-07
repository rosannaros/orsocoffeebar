<template>
  <section class="register-page">
    <div class="register-card">
      <img src="../assets/images/logo/logo-orso.png" alt="Logo" />
      <h1>Create Your Orso Moment</h1>
      <p> Register to get started </p>

      <div v-if="error" class="error-message">{{ error }}</div>
      
      <form @submit.prevent="handleRegister" autocomplete="off">
        <input type="text" name="prevent_autofill" style="display:none" aria-hidden="true">
        <input type="password" name="password_fake" style="display:none" aria-hidden="true">

        <input type="text" v-model="nama" placeholder="Username" autocomplete="new-password" required/>
        <input type="email" v-model="email" placeholder="Email" autocomplete="new-password" required/>
        
        <div class="password-wrapper">
          <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="Password" autocomplete="new-password" required/>
          <span class="material-icons toggle-password-inside" @click="showPassword = !showPassword">
            {{ showPassword ? 'visibility' : 'visibility_off' }}
          </span>
        </div>
      
        <button type="submit" :disabled="loading">
          {{ loading ? 'Mendaftar...' : 'REGISTER' }}
        </button>
      </form>

      <div class="auth-footer">
        Already have an account? <router-link to="/login">Login</router-link>
      </div>
    </div>
  </section>
</template>

<script>
import { registerUser } from '../services/api'; 
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      nama: "",
      email: "",
      password: "",
      error: "",
      loading: false,
      showPassword: false
    };
  },
  methods: {
    async handleRegister() {
      this.error = "";

      if (this.password.length < 6) {
        this.error = "Password minimal 6 karakter.";
        return;
      }

      this.loading = true;
      try {
        const payload = {
          nama: this.nama,
          email: this.email,
          password: this.password
        };

        const response = await registerUser(payload);

        Swal.fire({
          icon: 'success',
          title: 'Registrasi Berhasil!',
          text: response.message,
          confirmButtonColor: '#6b4f3a'
        }).then(() => {
          this.$router.push("/login");
        });

      } catch (err) {
        this.error = err.error || "Email sudah digunakan, silakan gunakan email lain.";
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style>
.register-page {
  background: url("../assets/images/bckgr/registerbg.jpg") no-repeat center center;
}
</style>
