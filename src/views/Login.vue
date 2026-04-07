<template>
  <section class="login-page">
    <div class="login-card">
      <img src="../assets/images/logo/logo-orso.png" alt="Orso Coffee Logo" />
      <h1> Welcome Back, Orsoers! </h1>
      <p> Login to your account </p>

      <div v-if="error" class="error-message">{{ error }}</div>
      
      <form @submit.prevent="handleLogin">
        <input type="email" v-model="email" placeholder="Email" required/>
        
        <div class="password-wrapper">
          <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="Password" required/>
          <span class="material-icons google-eye-inside" @click="showPassword = !showPassword">
            {{ showPassword ? 'visibility' : 'visibility_off' }}
          </span>
        </div>
        
        <button type="submit" :disabled="loading">
          {{ loading ? 'Mohon Tunggu...' : 'LOGIN' }}
        </button>
      </form>
      
      <div class="auth-footer">
        Doesn’t have an account? 
        <router-link to="/register">Register</router-link>
      </div>
    </div>
  </section>
</template>

<script>
import { loginUser } from '../services/api.js'; 
import Swal from 'sweetalert2';

export default {
  name: "Login",
  data() {
    return {
      email: "",
      password: "",
      error: "",
      loading: false,
      showPassword: false
    };
  },
  methods: {
    async handleLogin() {
      this.error = "";
      this.loading = true;

      try {
        const payload = {
          email: this.email,
          password: this.password
        };

        const response = await loginUser(payload);
        localStorage.setItem("user", JSON.stringify(response.user));

        window.dispatchEvent(new Event("auth-change"));

        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Selamat datang kembali, ' + response.user.nama,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          this.$router.push("/beranda");
        });

      } catch (err) {
        this.error = err.error || "Email atau password salah.";
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style>
.login-page {
  background: url("../assets/images/bckgr/loginbg.jpg") no-repeat center center;
}
</style>



