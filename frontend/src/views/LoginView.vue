<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/client';

const router = useRouter();

const email = ref('ibukos@sikos.id');
const password = ref('password123');
const isLoading = ref(false);
const errorMessage = ref('');
const validationErrors = ref({});

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  validationErrors.value = {};

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    });

    const data = response.data;
    localStorage.setItem('sikos_token', data.access_token);
    localStorage.setItem('sikos_user', JSON.stringify(data.user));

    router.push({ name: 'kamars' });
  } catch (error) {
    if (error.response) {
      if (error.response.status === 422) {
        validationErrors.value = error.response.data.errors || {};
        errorMessage.value = error.response.data.message || 'Validasi gagal.';
      } else if (error.response.status === 401) {
        errorMessage.value = error.response.data.message || 'Kredensial tidak cocok.';
      } else {
        errorMessage.value = 'Terjadi kesalahan sistem, silakan coba lagi.';
      }
    } else {
      errorMessage.value = 'Gagal menghubungi server API. Periksa koneksi backend.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="brand-header">
        <div class="brand-badge">SiKos</div>
        <h2>Portal Pemilik Kos</h2>
        <p>Sistem Pengelolaan Kamar Kos Terpadu</p>
      </div>

      <div v-if="errorMessage" class="alert-box error">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="form-container">
        <div class="form-group">
          <label for="email">Alamat Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="nama@domain.com"
            required
            :class="{ 'has-error': validationErrors.email }"
          />
          <span v-if="validationErrors.email" class="error-text">
            {{ validationErrors.email[0] }}
          </span>
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            :class="{ 'has-error': validationErrors.password }"
          />
          <span v-if="validationErrors.password" class="error-text">
            {{ validationErrors.password[0] }}
          </span>
        </div>

        <button type="submit" class="btn-primary" :disabled="isLoading">
          <span v-if="isLoading">Memproses...</span>
          <span v-else>Masuk ke Dashboard</span>
        </button>

        <div class="demo-box">
          <small>Akun Demo Default:</small>
          <code>ibukos@sikos.id / password123</code>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f1f5f9;
  padding: 1.5rem;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
  padding: 2.25rem;
  border: 1px solid #e2e8f0;
}

.brand-header {
  text-align: center;
  margin-bottom: 2rem;
}

.brand-badge {
  display: inline-block;
  background: #0284c7;
  color: #ffffff;
  font-weight: 700;
  font-size: 0.875rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  margin-bottom: 0.75rem;
  letter-spacing: 0.05em;
}

.brand-header h2 {
  font-size: 1.5rem;
  color: #0f172a;
  margin: 0 0 0.25rem 0;
  font-weight: 700;
}

.brand-header p {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.alert-box {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-size: 0.875rem;
}

.alert-box.error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.form-group {
  margin-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.375rem;
}

.form-group input {
  padding: 0.625rem 0.875rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.form-group input.has-error {
  border-color: #ef4444;
}

.error-text {
  font-size: 0.8rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

.btn-primary {
  width: 100%;
  padding: 0.75rem;
  background-color: #0284c7;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0369a1;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.demo-box {
  margin-top: 1.5rem;
  padding: 0.75rem;
  background-color: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  text-align: center;
  font-size: 0.8rem;
  color: #64748b;
}

.demo-box code {
  display: block;
  font-family: monospace;
  font-weight: 600;
  color: #0f172a;
  margin-top: 0.25rem;
}
</style>
