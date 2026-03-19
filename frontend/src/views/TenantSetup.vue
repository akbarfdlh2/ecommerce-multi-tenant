<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="card w-full max-w-md p-8">
      <RouterLink to="/" class="text-primary-600 text-sm flex items-center gap-1 mb-6">
        ← Kembali
      </RouterLink>
      <h1 class="text-2xl font-bold mb-6">Daftarkan Toko Baru</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
          <input v-model="form.name" type="text" class="input" placeholder="Toko Saya" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email Pemilik</label>
          <input v-model="form.owner_email" type="email" class="input" placeholder="admin@toko.com" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Domain (opsional)</label>
          <input v-model="form.domain" type="text" class="input" placeholder="toko-saya.localhost" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
          <select v-model="form.plan" class="input">
            <option value="free">Free</option>
            <option value="pro">Pro</option>
          </select>
        </div>

        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>

        <button type="submit" class="btn-primary w-full" :disabled="loading">
          {{ loading ? 'Mendaftarkan...' : 'Daftarkan Toko' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTenantStore } from '@/stores/tenant'
import { useAuthStore } from '@/stores/auth'

const router      = useRouter()
const tenantStore = useTenantStore()
const authStore   = useAuthStore()

const form    = ref({ name: '', owner_email: '', domain: '', plan: 'free' })
const loading = ref(false)
const error   = ref('')
const success = ref('')

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    const tenant = await tenantStore.registerTenant(form.value)
    success.value = `Toko "${tenant.name}" berhasil didaftarkan! Mengalihkan...`
    authStore.setTenant(tenant.domain)
    setTimeout(() => router.push({ name: 'register' }), 1500)
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mendaftarkan toko'
  } finally {
    loading.value = false
  }
}
</script>
