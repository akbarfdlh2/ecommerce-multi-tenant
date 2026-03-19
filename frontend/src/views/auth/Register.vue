<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="card w-full max-w-md p-8">
      <div class="text-center mb-6">
        <TenantBadge />
        <h1 class="text-2xl font-bold mt-4">Buat Akun</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
          <input v-model="form.name" type="text" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="form.password" type="password" class="input" required minlength="8" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
          <input v-model="form.password_confirmation" type="password" class="input" required />
        </div>

        <p v-if="authStore.error" class="text-red-600 text-sm">{{ authStore.error }}</p>

        <button type="submit" class="btn-primary w-full" :disabled="authStore.loading">
          {{ authStore.loading ? 'Mendaftar...' : 'Daftar' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-4">
        Sudah punya akun?
        <RouterLink to="/login" class="text-primary-600 font-medium">Masuk</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import TenantBadge from '@/components/TenantBadge.vue'

const router    = useRouter()
const authStore = useAuthStore()

const form = ref({ name: '', email: '', password: '', password_confirmation: '' })

async function submit() {
  try {
    await authStore.register(form.value)
    router.push(authStore.isAdmin ? '/admin' : '/shop')
  } catch { /* error shown via store */ }
}
</script>
