<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-5xl mx-auto px-4 py-8">
      <RouterLink to="/shop" class="text-primary-600 text-sm flex items-center gap-1 mb-6">
        ← Kembali ke Produk
      </RouterLink>

      <div v-if="productsStore.loading" class="card p-8 animate-pulse">
        <div class="flex gap-8">
          <div class="bg-gray-200 rounded-xl w-80 h-80 flex-shrink-0"></div>
          <div class="flex-1 space-y-4">
            <div class="h-8 bg-gray-200 rounded w-2/3"></div>
            <div class="h-6 bg-gray-200 rounded w-1/3"></div>
            <div class="h-4 bg-gray-200 rounded"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>
      </div>

      <div v-else-if="product" class="card p-8">
        <div class="flex flex-col md:flex-row gap-8">
          <!-- Images -->
          <div class="flex-shrink-0">
            <img
              :src="selectedImage || '/placeholder.png'"
              :alt="product.name"
              class="w-80 h-80 object-cover rounded-xl"
            />
            <div v-if="product.images?.length > 1" class="flex gap-2 mt-3">
              <img
                v-for="(img, i) in product.images"
                :key="i"
                :src="img"
                @click="selectedImage = img"
                class="w-16 h-16 object-cover rounded-lg cursor-pointer border-2 transition-colors"
                :class="selectedImage === img ? 'border-primary-500' : 'border-transparent'"
              />
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1">
            <span class="badge bg-blue-100 text-blue-700 mb-3">{{ product.category }}</span>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
            <p class="text-3xl font-bold text-primary-600 mb-4">{{ formatPrice(product.price) }}</p>

            <p class="text-gray-600 mb-6">{{ product.description || 'Tidak ada deskripsi.' }}</p>

            <div class="flex items-center gap-3 mb-6">
              <span class="text-sm text-gray-500">Stok:</span>
              <span :class="product.stock > 0 ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                {{ product.stock > 0 ? `${product.stock} tersedia` : 'Habis' }}
              </span>
            </div>

            <div class="flex items-center gap-4">
              <div class="flex items-center border rounded-lg">
                <button @click="qty > 1 && qty--" class="px-3 py-2 text-gray-500 hover:text-gray-700">−</button>
                <span class="px-4 py-2 font-medium">{{ qty }}</span>
                <button @click="qty < product.stock && qty++" class="px-3 py-2 text-gray-500 hover:text-gray-700">+</button>
              </div>
              <button
                @click="addToCart"
                :disabled="!product.stock || cartStore.loading"
                class="btn-primary flex-1"
              >
                {{ cartStore.loading ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
              </button>
            </div>

            <p v-if="addedMessage" class="mt-3 text-green-600 text-sm">{{ addedMessage }}</p>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-16 text-gray-500">Produk tidak ditemukan.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'

const route         = useRoute()
const router        = useRouter()
const productsStore = useProductsStore()
const cartStore     = useCartStore()
const authStore     = useAuthStore()

const qty           = ref(1)
const selectedImage = ref(null)
const addedMessage  = ref('')

const product = computed(() => productsStore.current)

onMounted(async () => {
  await productsStore.fetchProduct(route.params.id)
  if (product.value?.images?.[0]) {
    selectedImage.value = product.value.images[0]
  }
})

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

async function addToCart() {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }
  try {
    await cartStore.addItem(product.value._id, qty.value)
    addedMessage.value = 'Produk berhasil ditambahkan ke keranjang!'
    setTimeout(() => (addedMessage.value = ''), 3000)
  } catch (e) {
    addedMessage.value = e.response?.data?.message || 'Gagal menambahkan produk'
  }
}
</script>
