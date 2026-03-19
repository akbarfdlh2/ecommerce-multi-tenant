<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold mb-6">Checkout</h1>

      <div v-if="orderSuccess" class="card p-12 text-center">
        <div class="text-5xl mb-4">✅</div>
        <h2 class="text-2xl font-bold text-green-600 mb-2">Pesanan Berhasil!</h2>
        <p class="text-gray-600 mb-2">No. Order: <span class="font-mono font-bold">{{ orderNumber }}</span></p>
        <RouterLink to="/shop/orders" class="btn-primary mt-4 inline-block">Lihat Pesanan Saya</RouterLink>
      </div>

      <form v-else @submit.prevent="submit" class="flex flex-col lg:flex-row gap-6">
        <!-- Shipping Form -->
        <div class="flex-1 space-y-4">
          <div class="card p-6">
            <h2 class="font-semibold text-lg mb-4">Alamat Pengiriman</h2>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                <input v-model="form.shipping_address.name" class="input" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input v-model="form.shipping_address.phone" class="input" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea v-model="form.shipping_address.address" class="input" rows="2" required></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                <input v-model="form.shipping_address.city" class="input" required />
              </div>
            </div>
          </div>

          <div class="card p-6">
            <h2 class="font-semibold text-lg mb-4">Metode Pembayaran</h2>
            <div class="space-y-2">
              <label v-for="m in paymentMethods" :key="m.value" class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" v-model="form.payment_method" :value="m.value" class="text-primary-600" />
                <span>{{ m.label }}</span>
              </label>
            </div>
          </div>

          <div class="card p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
            <textarea v-model="form.notes" class="input" rows="2" placeholder="Instruksi khusus..."></textarea>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:w-72">
          <div class="card p-6 sticky top-4">
            <h2 class="font-semibold text-lg mb-4">Ringkasan Pesanan</h2>
            <div class="space-y-2 text-sm mb-4 max-h-48 overflow-y-auto">
              <div v-for="item in cartStore.cart.items" :key="item.product_id" class="flex justify-between">
                <span class="text-gray-600 truncate max-w-32">{{ item.name }} x{{ item.quantity }}</span>
                <span>{{ formatPrice(item.subtotal) }}</span>
              </div>
            </div>
            <div class="border-t pt-3 space-y-1 text-sm mb-4">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span><span>{{ formatPrice(cartStore.cart.total) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Ongkir</span><span>{{ formatPrice(15000) }}</span>
              </div>
              <div class="flex justify-between font-bold text-base">
                <span>Total</span>
                <span class="text-primary-600">{{ formatPrice(cartStore.cart.total + 15000) }}</span>
              </div>
            </div>
            <p v-if="error" class="text-red-600 text-sm mb-3">{{ error }}</p>
            <button type="submit" class="btn-primary w-full" :disabled="loading">
              {{ loading ? 'Memproses...' : 'Pesan Sekarang' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCartStore } from '@/stores/cart'
import { ordersApi } from '@/api/orders'
import Navbar from '@/components/Navbar.vue'

const cartStore = useCartStore()
onMounted(() => cartStore.fetchCart())

const form = ref({
  shipping_address: { name: '', phone: '', address: '', city: '' },
  payment_method: 'cod',
  notes: '',
})
const paymentMethods = [
  { value: 'cod', label: 'Bayar di Tempat (COD)' },
  { value: 'bank_transfer', label: 'Transfer Bank' },
  { value: 'ewallet', label: 'E-Wallet' },
]
const loading      = ref(false)
const error        = ref('')
const orderSuccess = ref(false)
const orderNumber  = ref('')

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    const res       = await ordersApi.checkout(form.value)
    orderNumber.value = res.data.data.order_number
    orderSuccess.value = true
    cartStore.reset()
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal membuat pesanan'
  } finally {
    loading.value = false
  }
}
</script>
