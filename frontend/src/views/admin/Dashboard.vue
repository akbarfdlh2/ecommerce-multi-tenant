<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <div v-if="tenantStore.loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="i in 4" :key="i" class="card p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-2/3 mb-3"></div>
        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else-if="data" class="space-y-6">
      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="card p-5">
          <p class="text-sm text-gray-500 mb-1">{{ stat.label }}</p>
          <p class="text-2xl font-bold" :class="stat.color">{{ stat.value }}</p>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="card p-6">
          <h2 class="font-semibold mb-4">Pesanan Terbaru</h2>
          <div v-if="!data.recent_orders?.length" class="text-gray-500 text-sm">Belum ada pesanan.</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">No. Order</th>
                <th class="pb-2">Total</th>
                <th class="pb-2">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="order in data.recent_orders" :key="order._id">
                <td class="py-2 font-mono text-xs">{{ order.order_number }}</td>
                <td class="py-2">{{ formatPrice(order.total) }}</td>
                <td class="py-2">
                  <span :class="statusClass(order.status)" class="badge text-xs">{{ order.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Low Stock -->
        <div class="card p-6">
          <h2 class="font-semibold mb-4">Stok Rendah</h2>
          <div v-if="!data.low_stock?.length" class="text-gray-500 text-sm">Semua stok aman.</div>
          <div v-else class="space-y-2">
            <div v-for="p in data.low_stock" :key="p._id" class="flex justify-between items-center">
              <div>
                <p class="text-sm font-medium">{{ p.name }}</p>
                <p class="text-xs text-gray-500">{{ p.category }}</p>
              </div>
              <span :class="p.stock <= 3 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'" class="badge">
                Stok: {{ p.stock }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useTenantStore } from '@/stores/tenant'

const tenantStore = useTenantStore()
const data        = computed(() => tenantStore.dashboard)

onMounted(() => tenantStore.fetchDashboard())

const stats = computed(() => {
  if (!data.value) return []
  return [
    { label: 'Total Produk',  value: data.value.stats?.products ?? 0,                                   color: 'text-blue-600' },
    { label: 'Total Pesanan', value: data.value.stats?.orders ?? 0,                                     color: 'text-purple-600' },
    { label: 'Total Pengguna',value: data.value.stats?.users ?? 0,                                      color: 'text-green-600' },
    { label: 'Pendapatan',    value: formatPrice(data.value.stats?.revenue ?? 0),                       color: 'text-orange-600' },
  ]
})

const formatPrice = (p) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p)

const statusClass = (s) => ({
  pending: 'bg-yellow-100 text-yellow-700', processing: 'bg-blue-100 text-blue-700',
  shipped: 'bg-indigo-100 text-indigo-700', delivered: 'bg-green-100 text-green-700',
}[s] || 'bg-gray-100 text-gray-700')
</script>
