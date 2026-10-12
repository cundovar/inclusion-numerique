<script setup>
// Niveau de preuve d'une information, repris des statuts du dossier VAE (VAE/preuves.md)
const props = defineProps({
  is: { type: String, required: true }
})

const labels = {
  code: 'Présent dans le code',
  local: 'Vérifié localement',
  ci: 'Validé par la CI',
  prod: 'En production',
  prevu: 'Prévu'
}
</script>

<template>
  <span
    class="status"
    :class="`status--${props.is}`"
  >{{ labels[props.is] ?? props.is }}</span>
</template>

<style scoped>
.status {
  display: inline-block;
  padding: 1px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  line-height: 20px;
  border: 1px solid currentColor;
  white-space: nowrap;
  vertical-align: middle;
}
.status--code { color: var(--vp-c-text-2); }
.status--local { color: var(--vp-c-tip-1); }
.status--ci { color: var(--vp-c-brand-1); }
.status--prod { color: var(--vp-c-green-1, #18794e); }
.status--prevu { color: var(--vp-c-warning-1); }
</style>
