<template>
  <div class="card p-4 flex items-center gap-4">
    <!-- Image -->
    <div class="w-16 h-16 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
      <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
      <div v-else class="w-full h-full flex items-center justify-center text-2xl">📦</div>
    </div>

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <p class="font-medium text-gray-900 truncate">{{ item.name }}</p>
      <p class="text-primary-600 font-semibold">{{ formatPrice(item.price) }}</p>
    </div>

    <!-- Qty Controls -->
    <div class="flex items-center border rounded-lg">
      <button @click="$emit('update', item.quantity - 1)" class="px-2 py-1 text-gray-500 hover:text-gray-700 text-sm">−</button>
      <span class="px-3 py-1 text-sm font-medium">{{ item.quantity }}</span>
      <button @click="$emit('update', item.quantity + 1)" class="px-2 py-1 text-gray-500 hover:text-gray-700 text-sm">+</button>
    </div>

    <!-- Subtotal -->
    <p class="w-24 text-right font-semibold text-sm">{{ formatPrice(item.subtotal) }}</p>

    <!-- Remove -->
    <button @click="$emit('remove')" class="text-red-500 hover:text-red-700 text-sm ml-2">✕</button>
  </div>
</template>

<script setup>
defineProps({ item: Object })
defineEmits(['update', 'remove'])

const formatPrice = (p) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p)
</script>
