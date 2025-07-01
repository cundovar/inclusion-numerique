<template>
  <div class="flex gap-2 items-center justify-start max-lg:flex-col">
    <input
      type="text"
      class="w-full rounded-2xl p-1 focus:bg-gray-200 shadow-2xs focus:border-blue-300 focus:outline-none border border-blue-300"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @keyup.enter="$emit('search')"
    >
    <button @click="$emit('search')" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
      Rechercher
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: 'Rechercher'
  }
});
</script>
