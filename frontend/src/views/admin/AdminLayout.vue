<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-sm flex-shrink-0">
      <div class="p-5 border-b">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Admin Panel</p>
        <TenantBadge class="mt-1" />
      </div>
      <nav class="p-4 space-y-1">
        <RouterLink
          v-for="item in menu"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="$route.path.startsWith(item.to) ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-50'"
        >
          <span>{{ item.icon }}</span>
          {{ item.label }}
        </RouterLink>
      </nav>
      <div class="p-4 border-t mt-auto">
        <div class="text-sm text-gray-600 mb-2">{{ authStore.user?.name }}</div>
        <button @click="handleLogout" class="btn-secondary w-full text-sm">Logout</button>
        <RouterLink to="/shop" class="btn-secondary w-full text-sm mt-2 text-center block">
          Lihat Toko
        </RouterLink>
      </div>
    </aside>

    <!-- Content -->
    <main class="flex-1 overflow-auto">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useRouter } from 'vue-router'
import TenantBadge from '@/components/TenantBadge.vue'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router    = useRouter()

const menu = [
  { to: '/admin',          label: 'Dashboard',  icon: '📊' },
  { to: '/admin/products', label: 'Produk',      icon: '📦' },
  { to: '/admin/orders',   label: 'Pesanan',     icon: '🛍️' },
]

async function handleLogout() {
  await authStore.logout()
  cartStore.reset()
  router.push({ name: 'login' })
}
</script>
