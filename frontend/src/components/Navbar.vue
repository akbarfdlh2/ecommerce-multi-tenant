<template>
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
      <!-- Brand + Tenant -->
      <div class="flex items-center gap-3">
        <RouterLink to="/shop" class="text-xl font-bold text-primary-600">🛒 eCommerce</RouterLink>
        <TenantBadge />
      </div>

      <!-- Nav links -->
      <nav class="hidden sm:flex items-center gap-4 text-sm">
        <RouterLink to="/shop" class="text-gray-600 hover:text-primary-600 font-medium">Produk</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/shop/orders" class="text-gray-600 hover:text-primary-600">Pesanan</RouterLink>
        <RouterLink v-if="authStore.isAdmin" to="/admin" class="text-gray-600 hover:text-primary-600">Admin</RouterLink>
      </nav>

      <!-- Auth + Cart -->
      <div class="flex items-center gap-3">
        <RouterLink v-if="authStore.isAuthenticated" to="/shop/cart" class="relative">
          <button class="btn-secondary text-sm flex items-center gap-1.5">
            🛍️ Keranjang
            <span v-if="cartStore.itemCount > 0" class="bg-primary-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
              {{ cartStore.itemCount }}
            </span>
          </button>
        </RouterLink>

        <template v-if="authStore.isAuthenticated">
          <div class="relative" ref="dropdownRef">
            <button @click="showDropdown = !showDropdown" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
              <div class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold">
                {{ authStore.user?.name?.[0]?.toUpperCase() }}
              </div>
            </button>
            <div v-if="showDropdown" class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border py-1 text-sm z-50">
              <p class="px-4 py-2 text-gray-500 border-b truncate">{{ authStore.user?.name }}</p>
              <RouterLink to="/shop/orders" @click="showDropdown = false" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Pesanan Saya</RouterLink>
              <button @click="handleLogout" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Logout</button>
            </div>
          </div>
        </template>

        <template v-else>
          <RouterLink to="/login" class="btn-secondary text-sm">Masuk</RouterLink>
          <RouterLink to="/register" class="btn-primary text-sm">Daftar</RouterLink>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import TenantBadge from './TenantBadge.vue'

const authStore   = useAuthStore()
const cartStore   = useCartStore()
const router      = useRouter()
const showDropdown = ref(false)
const dropdownRef  = ref(null)

function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    showDropdown.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

async function handleLogout() {
  showDropdown.value = false
  await authStore.logout()
  cartStore.reset()
  router.push({ name: 'login' })
}
</script>
