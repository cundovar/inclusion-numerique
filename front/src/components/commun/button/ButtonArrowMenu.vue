<template>


    <button
    :class="ajoutClass"
    class="flex items-center max-xl:hidden justify-center z-[100000] cursor-pointer shadow-2xl p-2 rounded-md bg-red-300 hover:bg-red-400 transition-colors"
    @click="fonction">
        <i :class="isOpen ? 'pi pi-chevron-right' : 'pi pi-chevron-left'" class="text-white "></i>
    </button>
</template>


<script setup>

const { isOpen, fonction, ajoutClass } = defineProps({
  isOpen: { type: Boolean, required: true },
  fonction: { type: Function, required: true },
  ajoutClass: { type: [String, Array, Object], default: '' }, // accepte "absolute right-0 top-6" ou un objet/array
})

</script>



        