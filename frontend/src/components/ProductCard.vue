<template>
  <div class="card overflow-hidden hover:shadow-md transition-shadow duration-200 flex flex-col">
    <!-- Image -->
    <RouterLink :to="`/shop/product/${product._id}`">
      <div class="bg-gray-100 aspect-square overflow-hidden">
        <img
          v-if="product.images?.[0]"
          :src="product.images[0]"
          :alt="product.name"
          class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-4xl">📦</div>
      </div>
    </RouterLink>

    <!-- Info -->
    <div class="p-4 flex flex-col flex-1">
      <span class="badge bg-blue-50 text-blue-600 text-xs mb-2 self-start">{{ product.category }}</span>
      <RouterLink :to="`/shop/product/${product._id}`" class="font-semibold text-gray-900 hover:text-primary-600 line-clamp-2 mb-1">
        {{ product.name }}
      </RouterLink>
      <p class="text-primary-600 font-bold text-lg mt-auto mb-3">{{ formatPrice(product.price) }}</p>

      <div class="flex items-center justify-between gap-2">
        <span class="text-xs" :class="product.stock > 0 ? 'text-gray-500' : 'text-red-500'">
          {{ product.stock > 0 ? `Stok: ${product.stock}` : 'Habis' }}
        </span>
        <button
          @click.prevent="$emit('add-to-cart', product)"
          :disabled="!product.stock"
          class="btn-primary text-xs py-1.5 px-3"
        >
          + Keranjang
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ product: Object })
defineEmits(['add-to-cart'])

const formatPrice = (p) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p)
</script>
