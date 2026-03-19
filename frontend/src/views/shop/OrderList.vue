<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold mb-6">Pesanan Saya</h1>

      <div v-if="loading" class="text-center py-16"><LoadingSpinner /></div>

      <div v-else-if="!orders.length" class="card p-16 text-center">
        <p class="text-gray-500 mb-4">Belum ada pesanan.</p>
        <RouterLink to="/shop" class="btn-primary">Mulai Belanja</RouterLink>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order._id" class="card p-5">
          <div class="flex items-start justify-between">
            <div>
              <p class="font-mono font-bold text-gray-800">{{ order.order_number }}</p>
              <p class="text-sm text-gray-500 mt-0.5">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="text-right">
              <span :class="statusClass(order.status)" class="badge">{{ order.status }}</span>
              <p class="text-lg font-bold text-primary-600 mt-1">{{ formatPrice(order.total) }}</p>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t flex gap-4 text-sm text-gray-600">
            <span>{{ order.items?.length }} item</span>
            <span>·</span>
            <span>Pembayaran: {{ order.payment_method }}</span>
            <span>·</span>
            <span :class="order.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'">
              {{ order.payment_status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ordersApi } from '@/api/orders'
import Navbar from '@/components/Navbar.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const orders  = ref([])
const loading = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    const res = await ordersApi.myOrders()
    orders.value = res.data.data
  } finally {
    loading.value = false
  }
})

const formatPrice = (price) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })

const statusClass = (s) => ({
  pending:    'bg-yellow-100 text-yellow-700',
  processing: 'bg-blue-100 text-blue-700',
  shipped:    'bg-indigo-100 text-indigo-700',
  delivered:  'bg-green-100 text-green-700',
  cancelled:  'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700')
</script>
