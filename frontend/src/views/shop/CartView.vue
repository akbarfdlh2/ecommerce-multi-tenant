<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

      <div v-if="cartStore.loading" class="text-center py-16"><LoadingSpinner /></div>

      <div v-else-if="cartStore.isEmpty" class="card p-16 text-center">
        <p class="text-gray-500 text-lg mb-4">Keranjang Anda kosong.</p>
        <RouterLink to="/shop" class="btn-primary">Mulai Belanja</RouterLink>
      </div>

      <div v-else class="flex flex-col lg:flex-row gap-6">
        <!-- Items -->
        <div class="flex-1 space-y-3">
          <CartItem
            v-for="item in cartStore.cart.items"
            :key="item.product_id"
            :item="item"
            @update="(qty) => updateItem(item.product_id, qty)"
            @remove="removeItem(item.product_id)"
          />
        </div>

        <!-- Summary -->
        <div class="lg:w-72">
          <div class="card p-6 sticky top-4">
            <h2 class="font-semibold text-lg mb-4">Ringkasan</h2>
            <div class="space-y-2 text-sm mb-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal ({{ cartStore.itemCount }} item)</span>
                <span>{{ formatPrice(cartStore.cart.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Ongkos Kirim</span>
                <span>{{ formatPrice(15000) }}</span>
              </div>
            </div>
            <div class="border-t pt-3 mb-4 flex justify-between font-bold">
              <span>Total</span>
              <span class="text-primary-600">{{ formatPrice(cartStore.cart.total + 15000) }}</span>
            </div>
            <RouterLink to="/shop/checkout" class="btn-primary w-full text-center block">
              Lanjut Checkout
            </RouterLink>
            <button @click="cartStore.clearCart()" class="btn-secondary w-full mt-2 text-sm">
              Kosongkan Keranjang
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCartStore } from '@/stores/cart'
import Navbar from '@/components/Navbar.vue'
import CartItem from '@/components/CartItem.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const cartStore = useCartStore()

onMounted(() => cartStore.fetchCart())

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

async function updateItem(itemId, qty) {
  await cartStore.updateItem(itemId, qty)
}

async function removeItem(itemId) {
  await cartStore.removeItem(itemId)
}
</script>
