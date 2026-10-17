<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/client';

const router = useRouter();

// State
const user = ref(JSON.parse(localStorage.getItem('sikos_user') || '{}'));
const kamars = ref([]);
const isLoading = ref(false);
const statusFilter = ref('Semua');
const alertMessage = ref('');
const alertType = ref('success');

// Modal Form State (Tambah & Ubah)
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formErrors = ref({});
const activeId = ref(null);

const formData = ref({
  nomor_kamar: '',
  tipe: 'AC',
  harga_sewa: 1000000,
  status: 'Tersedia',
  fasilitas: '',
});

// Modal Hapus State
const isDeleteModalOpen = ref(false);
const kamarToDelete = ref(null);
const isDeleting = ref(false);

// Format Rupiah
const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(number);
};

// Ambil daftar kamar dari backend
const fetchKamars = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/kamars');
    kamars.value = response.data.data;
  } catch (error) {
    showAlert('Gagal memuat data kamar. Silakan coba lagi.', 'error');
  } finally {
    isLoading.value = false;
  }
};

// Notifikasi banner sementara
const showAlert = (message, type = 'success') => {
  alertMessage.value = message;
  alertType.value = type;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4000);
};

// Filter data
const filteredKamars = computed(() => {
  if (statusFilter.value === 'Semua') {
    return kamars.value;
  }
  return kamars.value.filter((k) => k.status === statusFilter.value);
});

// Hitung Statistik
const countTotal = computed(() => kamars.value.length);
const countTersedia = computed(() => kamars.value.filter((k) => k.status === 'Tersedia').length);
const countTerisi = computed(() => kamars.value.filter((k) => k.status === 'Terisi').length);
const countPerbaikan = computed(() => kamars.value.filter((k) => k.status === 'Perbaikan').length);

// Buka Modal Tambah
const openAddModal = () => {
  isEditing.value = false;
  activeId.value = null;
  formErrors.value = {};
  formData.value = {
    nomor_kamar: '',
    tipe: 'AC',
    harga_sewa: 1000000,
    status: 'Tersedia',
    fasilitas: '',
  };
  isFormModalOpen.value = true;
};

// Buka Modal Edit
const openEditModal = (kamar) => {
  isEditing.value = true;
  activeId.value = kamar.id;
  formErrors.value = {};
  formData.value = {
    nomor_kamar: kamar.nomor_kamar,
    tipe: kamar.tipe,
    harga_sewa: kamar.harga_sewa,
    status: kamar.status,
    fasilitas: kamar.fasilitas || '',
  };
  isFormModalOpen.value = true;
};

// Simpan Kamar (Tambah atau Ubah)
const handleSaveKamar = async () => {
  isSubmitting.value = true;
  formErrors.value = {};

  try {
    if (isEditing.value) {
      await api.put(`/kamars/${activeId.value}`, formData.value);
      showAlert(`Kamar ${formData.value.nomor_kamar} berhasil diperbarui.`);
    } else {
      await api.post('/kamars', formData.value);
      showAlert(`Kamar ${formData.value.nomor_kamar} berhasil ditambahkan.`);
    }
    isFormModalOpen.value = false;
    await fetchKamars();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      formErrors.value = error.response.data.errors || {};
      showAlert('Mohon periksa isian formulir kamar.', 'error');
    } else {
      showAlert('Gagal menyimpan data kamar.', 'error');
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Konfirmasi Hapus
const openDeleteModal = (kamar) => {
  kamarToDelete.value = kamar;
  isDeleteModalOpen.value = true;
};

const handleDeleteKamar = async () => {
  if (!kamarToDelete.value) return;
  isDeleting.value = true;

  try {
    await api.delete(`/kamars/${kamarToDelete.value.id}`);
    showAlert(`Kamar ${kamarToDelete.value.nomor_kamar} berhasil dihapus.`);
    isDeleteModalOpen.value = false;
    await fetchKamars();
  } catch (error) {
    showAlert('Gagal menghapus unit kamar.', 'error');
  } finally {
    isDeleting.value = false;
  }
};

// Logout
const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (e) {
    // abaikan jika token sudah tidak aktif
  } finally {
    localStorage.removeItem('sikos_token');
    localStorage.removeItem('sikos_user');
    router.push({ name: 'login' });
  }
};

onMounted(() => {
  fetchKamars();
});
</script>

<template>
  <div class="app-layout">
    <!-- Navbar -->
    <header class="navbar">
      <div class="nav-brand">
        <span class="logo-box">SiKos</span>
        <span class="brand-title">Sistem Kelola Kos</span>
      </div>

      <div class="nav-user">
        <span class="user-name">{{ user.name || 'Pengelola Kos' }}</span>
        <button @click="handleLogout" class="btn-logout">
          Keluar
        </button>
      </div>
    </header>

    <main class="main-content">
      <!-- Alert Banner -->
      <div v-if="alertMessage" :class="['alert-banner', alertType]">
        {{ alertMessage }}
      </div>

      <!-- Stats Cards -->
      <section class="stats-grid">
        <div class="stat-card">
          <span class="stat-label">Total Unit Kamar</span>
          <span class="stat-value">{{ countTotal }}</span>
        </div>
        <div class="stat-card green">
          <span class="stat-label">Kamar Tersedia</span>
          <span class="stat-value">{{ countTersedia }}</span>
        </div>
        <div class="stat-card blue">
          <span class="stat-label">Kamar Terisi</span>
          <span class="stat-value">{{ countTerisi }}</span>
        </div>
        <div class="stat-card amber">
          <span class="stat-label">Dalam Perbaikan</span>
          <span class="stat-value">{{ countPerbaikan }}</span>
        </div>
      </section>

      <!-- Control Bar -->
      <section class="control-bar">
        <div class="filter-group">
          <button
            v-for="status in ['Semua', 'Tersedia', 'Terisi', 'Perbaikan']"
            :key="status"
            :class="['filter-btn', { active: statusFilter === status }]"
            @click="statusFilter = status"
          >
            {{ status }}
          </button>
        </div>

        <button @click="openAddModal" class="btn-add">
          + Tambah Kamar Baru
        </button>
      </section>

      <!-- Data Table -->
      <section class="table-container">
        <div v-if="isLoading" class="loading-state">
          Memuat data kamar kos...
        </div>

        <div v-else-if="filteredKamars.length === 0" class="empty-state">
          Belum ada data kamar pada kategori ini.
        </div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th>No. Kamar</th>
              <th>Tipe</th>
              <th>Harga Sewa / Bln</th>
              <th>Status</th>
              <th>Fasilitas</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="kamar in filteredKamars" :key="kamar.id">
              <td class="font-bold">{{ kamar.nomor_kamar }}</td>
              <td>
                <span :class="['badge-type', kamar.tipe.toLowerCase()]">
                  {{ kamar.tipe }}
                </span>
              </td>
              <td>{{ formatRupiah(kamar.harga_sewa) }}</td>
              <td>
                <span :class="['badge-status', kamar.status.toLowerCase()]">
                  {{ kamar.status }}
                </span>
              </td>
              <td class="text-muted">{{ kamar.fasilitas || 'Tidak ada catatan fasilitas.' }}</td>
              <td class="text-right action-cells">
                <button @click="openEditModal(kamar)" class="btn-action edit">
                  Ubah
                </button>
                <button @click="openDeleteModal(kamar)" class="btn-action delete">
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="isFormModalOpen" class="modal-backdrop">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Ubah Data Kamar' : 'Tambah Unit Kamar Baru' }}</h3>
          <button @click="isFormModalOpen = false" class="btn-close">&times;</button>
        </div>

        <form @submit.prevent="handleSaveKamar" class="modal-body">
          <div class="form-group">
            <label for="f-nomor">Nomor / Kode Kamar</label>
            <input
              id="f-nomor"
              v-model="formData.nomor_kamar"
              type="text"
              placeholder="Contoh: K-105"
              required
              :class="{ 'has-error': formErrors.nomor_kamar }"
            />
            <span v-if="formErrors.nomor_kamar" class="error-text">
              {{ formErrors.nomor_kamar[0] }}
            </span>
          </div>

          <div class="grid-two">
            <div class="form-group">
              <label for="f-tipe">Tipe Kamar</label>
              <select id="f-tipe" v-model="formData.tipe">
                <option value="AC">AC</option>
                <option value="Non-AC">Non-AC</option>
              </select>
            </div>

            <div class="form-group">
              <label for="f-status">Status Hunian</label>
              <select id="f-status" v-model="formData.status">
                <option value="Tersedia">Tersedia</option>
                <option value="Terisi">Terisi</option>
                <option value="Perbaikan">Perbaikan</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="f-harga">Harga Sewa Bulanan (Rp)</label>
            <input
              id="f-harga"
              v-model.number="formData.harga_sewa"
              type="number"
              min="100000"
              step="50000"
              required
              :class="{ 'has-error': formErrors.harga_sewa }"
            />
            <span v-if="formErrors.harga_sewa" class="error-text">
              {{ formErrors.harga_sewa[0] }}
            </span>
          </div>

          <div class="form-group">
            <label for="f-fasilitas">Fasilitas & Catatan</label>
            <textarea
              id="f-fasilitas"
              v-model="formData.fasilitas"
              rows="3"
              placeholder="Contoh: AC, Lemari pakaian, Meja belajar, Wi-Fi"
            ></textarea>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              @click="isFormModalOpen = false"
              class="btn-secondary"
            >
              Batal
            </button>
            <button type="submit" class="btn-primary-action" :disabled="isSubmitting">
              <span v-if="isSubmitting">Menyimpan...</span>
              <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Tambahkan Kamar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Hapus -->
    <div v-if="isDeleteModalOpen" class="modal-backdrop">
      <div class="modal-card small">
        <div class="modal-header">
          <h3>Konfirmasi Hapus</h3>
          <button @click="isDeleteModalOpen = false" class="btn-close">&times;</button>
        </div>
        <div class="modal-body">
          <p>
            Yakin ingin menghapus kamar <strong>{{ kamarToDelete?.nomor_kamar }}</strong>? Tindakan ini tidak dapat dibatalkan.
          </p>
          <div class="modal-footer">
            <button
              type="button"
              @click="isDeleteModalOpen = false"
              class="btn-secondary"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleDeleteKamar"
              class="btn-danger-action"
              :disabled="isDeleting"
            >
              <span v-if="isDeleting">Menghapus...</span>
              <span v-else>Hapus Unit</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-layout {
  min-height: 100vh;
  background-color: #f8fafc;
  color: #0f172a;
}

.navbar {
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 0.875rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-box {
  background: #0284c7;
  color: #ffffff;
  font-weight: 700;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.9rem;
}

.brand-title {
  font-weight: 600;
  font-size: 1.1rem;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-size: 0.9rem;
  color: #475569;
  font-weight: 500;
}

.btn-logout {
  padding: 0.375rem 0.875rem;
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-logout:hover {
  background: #fecaca;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

.alert-banner {
  padding: 0.875rem 1.25rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  font-weight: 500;
}

.alert-banner.success {
  background-color: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.alert-banner.error {
  background-color: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #ffffff;
  padding: 1.25rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 0.375rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
}

.stat-card.green .stat-value { color: #059669; }
.stat-card.blue .stat-value { color: #0284c7; }
.stat-card.amber .stat-value { color: #d97706; }

.control-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-group {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  padding: 0.5rem 1rem;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  color: #475569;
  font-weight: 500;
}

.filter-btn.active {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
}

.btn-add {
  padding: 0.5rem 1.25rem;
  background: #0284c7;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
}

.btn-add:hover {
  background: #0369a1;
}

.table-container {
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.data-table th {
  background: #f8fafc;
  padding: 0.875rem 1.25rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
}

.font-bold { font-weight: 600; }
.text-muted { color: #64748b; font-size: 0.85rem; }
.text-right { text-align: right; }

.badge-type {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 700;
}
.badge-type.ac { background: #e0f2fe; color: #0369a1; }
.badge-type.non-ac { background: #f3f4f6; color: #4b5563; }

.badge-status {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge-status.tersedia { background: #dcfce7; color: #15803d; }
.badge-status.terisi { background: #dbeafe; color: #1d4ed8; }
.badge-status.perbaikan { background: #fef3c7; color: #b45309; }

.action-cells {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.btn-action {
  padding: 0.35rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
}

.btn-action.edit {
  background: #f0fdf4;
  color: #166534;
  border-color: #bbf7d0;
}

.btn-action.delete {
  background: #fef2f2;
  color: #991b1b;
  border-color: #fecaca;
}

.loading-state, .empty-state {
  padding: 3rem;
  text-align: center;
  color: #64748b;
  font-size: 0.95rem;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  z-index: 50;
}

.modal-card {
  background: #ffffff;
  border-radius: 12px;
  width: 100%;
  max-width: 520px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-card.small {
  max-width: 420px;
}

.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #94a3b8;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.grid-two {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.35rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.55rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #0f172a;
  outline: none;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.form-group input.has-error {
  border-color: #ef4444;
}

.error-text {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

.modal-footer {
  margin-top: 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-secondary {
  padding: 0.55rem 1rem;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  color: #475569;
  cursor: pointer;
}

.btn-primary-action {
  padding: 0.55rem 1.25rem;
  background: #0284c7;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
}

.btn-danger-action {
  padding: 0.55rem 1.25rem;
  background: #dc2626;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
}
</style>
