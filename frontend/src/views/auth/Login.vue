<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="card w-full max-w-md p-8">
      <div class="text-center mb-6">
        <TenantBadge />
        <h1 class="text-2xl font-bold mt-4">Masuk ke Toko</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" class="input" required autofocus />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="form.password" type="password" class="input" required />
        </div>

        <p v-if="authStore.error" class="text-red-600 text-sm">{{ authStore.error }}</p>

        <button type="submit" class="btn-primary w-full" :disabled="authStore.loading">
          {{ authStore.loading ? 'Masuk...' : 'Masuk' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-4">
        Belum punya akun?
        <RouterLink to="/register" class="text-primary-600 font-medium">Daftar</RouterLink>
      </p>
      <p class="text-center text-sm text-gray-500 mt-2">
        <RouterLink to="/" class="text-gray-400 hover:text-gray-600">← Pilih Toko Lain</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import TenantBadge from '@/components/TenantBadge.vue'

const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()

const form = ref({ email: '', password: '' })

async function submit() {
  try {
    await authStore.login(form.value)
    await cartStore.fetchCart()
    const redirect = route.query.redirect || (authStore.isAdmin ? '/admin' : '/shop')
    router.push(redirect)
  } catch { /* error shown via store */ }
}
</script>
