<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Manajemen Pesanan</h1>

    <!-- Filter -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <button
        v-for="s in statuses"
        :key="s.value"
        @click="filterStatus(s.value)"
        :class="['badge cursor-pointer text-sm py-1 px-3', activeStatus === s.value ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700']"
      >
        {{ s.label }}
      </button>
    </div>

    <div class="card overflow-hidden">
      <div v-if="loading" class="p-8 text-center"><LoadingSpinner /></div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-left">No. Order</th>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-right">Total</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Pembayaran</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="order in orders" :key="order._id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono font-bold text-xs">{{ order.order_number }}</td>
            <td class="px-4 py-3 text-gray-500">{{ formatDate(order.created_at) }}</td>
            <td class="px-4 py-3 text-right font-medium">{{ formatPrice(order.total) }}</td>
            <td class="px-4 py-3 text-center">
              <select
                :value="order.status"
                @change="(e) => updateStatus(order._id, { status: e.target.value })"
                class="text-xs border rounded px-2 py-1"
              >
                <option v-for="s in orderStatuses" :key="s" :value="s">{{ s }}</option>
              </select>
            </td>
            <td class="px-4 py-3 text-center">
              <select
                :value="order.payment_status"
                @change="(e) => updateStatus(order._id, { payment_status: e.target.value })"
                class="text-xs border rounded px-2 py-1"
              >
                <option value="unpaid">unpaid</option>
                <option value="paid">paid</option>
                <option value="refunded">refunded</option>
              </select>
            </td>
            <td class="px-4 py-3 text-center text-xs text-gray-400">
              {{ order.payment_method }}
            </td>
          </tr>
          <tr v-if="!orders.length">
            <td colspan="6" class="px-4 py-12 text-center text-gray-500">Tidak ada pesanan.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ordersApi } from '@/api/orders'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const orders       = ref([])
const loading      = ref(false)
const activeStatus = ref('')

const statuses = [
  { value: '',           label: 'Semua' },
  { value: 'pending',    label: 'Pending' },
  { value: 'processing', label: 'Diproses' },
  { value: 'shipped',    label: 'Dikirim' },
  { value: 'delivered',  label: 'Terkirim' },
  { value: 'cancelled',  label: 'Dibatalkan' },
]
const orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled']

onMounted(() => fetchOrders())

async function fetchOrders() {
  loading.value = true
  try {
    const params  = activeStatus.value ? { status: activeStatus.value } : {}
    const res     = await ordersApi.adminList(params)
    orders.value  = res.data.data
  } finally {
    loading.value = false
  }
}

function filterStatus(s) {
  activeStatus.value = s
  fetchOrders()
}

async function updateStatus(id, data) {
  await ordersApi.updateStatus(id, data)
  await fetchOrders()
}

const formatPrice = (p) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p)

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
</script>
