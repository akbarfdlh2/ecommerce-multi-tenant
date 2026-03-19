<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Produk</h1>
        <div class="flex gap-3">
          <input
            v-model="searchQuery"
            @input="onSearch"
            type="text"
            class="input w-64"
            placeholder="Cari produk..."
          />
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          @click="filterCategory('')"
          :class="['badge cursor-pointer', !activeCategory ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700']"
        >
          Semua
        </button>
        <button
          v-for="cat in productsStore.categories"
          :key="cat"
          @click="filterCategory(cat)"
          :class="['badge cursor-pointer', activeCategory === cat ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700']"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="productsStore.loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="card p-4 animate-pulse">
          <div class="bg-gray-200 rounded-lg h-48 mb-3"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-2/3"></div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else-if="productsStore.items.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <ProductCard
          v-for="product in productsStore.items"
          :key="product._id"
          :product="product"
          @add-to-cart="addToCart"
        />
      </div>

      <div v-else class="text-center py-16 text-gray-500">
        <p class="text-lg">Produk tidak ditemukan.</p>
      </div>

      <!-- Pagination -->
      <div v-if="productsStore.pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in productsStore.pagination.last_page"
          :key="page"
          @click="changePage(page)"
          :class="[
            'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
            page === productsStore.pagination.current_page
              ? 'bg-primary-600 text-white'
              : 'bg-white text-gray-700 border hover:bg-gray-50'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Navbar from '@/components/Navbar.vue'
import ProductCard from '@/components/ProductCard.vue'

const productsStore = useProductsStore()
const cartStore     = useCartStore()
const authStore     = useAuthStore()
const router        = useRouter()

const searchQuery   = ref('')
const activeCategory = ref('')
let searchTimeout   = null

onMounted(() => productsStore.fetchProducts())

function filterCategory(cat) {
  activeCategory.value = cat
  productsStore.fetchProducts({ category: cat || undefined })
}

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    if (searchQuery.value.length >= 2) {
      productsStore.searchProducts(searchQuery.value)
    } else if (!searchQuery.value) {
      productsStore.fetchProducts()
    }
  }, 400)
}

function changePage(page) {
  productsStore.fetchProducts({ page, category: activeCategory.value || undefined })
}

async function addToCart(product) {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' })
    return
  }
  try {
    await cartStore.addItem(product._id, 1)
  } catch { /* error handled in store */ }
}
</script>
