<template>
  <nav
    v-if="toc.length > 0"
    ref="tocElement"
    class="table-of-contents"
    :class="{ 'is-dragging': isDragging }"
    :style="draggableStyle"
  >
    <!-- Hint déplaçable -->
    <transition name="fade">
      <div v-if="showHint" class="drag-hint">
        💡 Glissez-déposez pour déplacer
      </div>
    </transition>

    <!-- Header avec toggle pour mobile (poignée de drag) -->
    <div
      class="toc-header drag-handle"
      ref="dragHandle"
      title="Cliquez et glissez pour déplacer le sommaire"
    >
      <i class="pi pi-arrows-alt drag-indicator"></i>
      <h3 class="toc-title"> Sommaire</h3>
      <button
        class="toc-toggle"
        @click="toggleToc"
        :aria-label="isOpen ? 'Fermer le sommaire' : 'Ouvrir le sommaire'"
      >
        <span v-if="isOpen">fermer</span>
        <span v-else>ouvrir</span>
      </button>
    </div>

    <!-- Liste des titres -->
    <transition name="slide">
      <ul v-show="isOpen" class="toc-list">
        <li
          v-for="item in toc"
          :key="item.id"
          :class="[
            'toc-item',
            `toc-level-${item.level}`,
            { 'active': item.id === activeId }
          ]"
        >
          <a
            @click.prevent="handleClick(item.id)"
            :href="`#${item.id}`"
            class="toc-link"
            :title="item.text"
          >
            <span class="toc-bullet">•</span>
            <span class="toc-text">{{ item.text }}</span>
          </a>
        </li>
      </ul>
    </transition>

    <!-- Indicateur de progression de lecture (optionnel) -->
    <div v-if="showProgress && isOpen" class="reading-progress">
      <div class="progress-text">Progression</div>
      <div class="progress-bar-container">
        <div
          class="progress-bar"
          :style="{ width: readingProgress + '%' }"
        ></div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useDraggable } from '@vueuse/core'

const props = defineProps({
  toc: {
    type: Array,
    required: true
  },
  activeId: {
    type: String,
    default: null
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  collapsedOnMobile: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['scroll-to'])

const isOpen = ref(true)
const readingProgress = ref(0)
const showHint = ref(false)

// Drag and drop avec VueUse
const tocElement = ref(null)
const dragHandle = ref(null)

// Calculer la position initiale en pixels
const getInitialPosition = () => {
  const windowWidth = window.innerWidth
  const windowHeight = window.innerHeight
  return {
    x: windowWidth * 0.17, // 17% de la largeur
    y: windowHeight * 0.30  // 30% du haut
  }
}

// Activer le drag and drop
const { x, y, isDragging, style } = useDraggable(tocElement, {
  initialValue: getInitialPosition(),
  preventDefault: false
})

console.log('🔧 VueUse useDraggable initialized:', { x: x.value, y: y.value, isDragging: isDragging.value })

// Style dynamique pour le positionnement
const draggableStyle = computed(() => {
  // Sur mobile (< 768px), désactiver le drag
  if (window.innerWidth < 768) {
    console.log('📱 Mode mobile détecté, drag désactivé')
    return {}
  }

  const finalStyle = {
    position: 'fixed',
    left: `${x.value}px`,
    top: `${y.value}px`
  }

  console.log('🖱️ Style final appliqué:', finalStyle)
  return finalStyle
})

// Détecter si mobile
const isMobile = () => window.innerWidth < 768

// Initialiser l'état ouvert/fermé selon la taille d'écran
onMounted(() => {
  if (props.collapsedOnMobile && isMobile()) {
    isOpen.value = false
  }

  // Calculer la progression de lecture
  window.addEventListener('scroll', calculateReadingProgress)
  calculateReadingProgress()

  // Afficher le hint après 2 secondes, puis le cacher après 5 secondes
  if (!isMobile()) {
    setTimeout(() => {
      showHint.value = true
      setTimeout(() => {
        showHint.value = false
      }, 5000)
    }, 2000)
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', calculateReadingProgress)
})

const toggleToc = () => {
  isOpen.value = !isOpen.value
}

const handleClick = (id) => {
  emit('scroll-to', id)

  // Sur mobile, fermer le sommaire après un clic
  if (isMobile() && props.collapsedOnMobile) {
    setTimeout(() => {
      isOpen.value = false
    }, 300)
  }
}

const calculateReadingProgress = () => {
  const windowHeight = window.innerHeight
  const documentHeight = document.documentElement.scrollHeight - windowHeight
  const scrolled = window.scrollY

  readingProgress.value = Math.round((scrolled / documentHeight) * 100)
}
</script>

<style scoped>
.table-of-contents {
  /* position: fixed est appliqué par VueUse via :style */
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  width: 280px;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 1000;
  user-select: none;
  /* VueUse gère : position, top, left et transform automatiquement */
}

/* Curseur pendant le drag */
.table-of-contents.is-dragging {
  cursor: grabbing;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  opacity: 0.95;
}

.table-of-contents.is-dragging .drag-indicator {
  color: #42b983;
}

/* Hint déplaçable */
.drag-hint {
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg, #42b983 0%, #35a372 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(66, 185, 131, 0.3);
  white-space: nowrap;
  z-index: 1001;
  animation: bounce 2s infinite;
}

.drag-hint::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 6px solid #35a372;
}

/* Animation bounce */
@keyframes bounce {
  0%, 100% {
    transform: translateX(-50%) translateY(0);
  }
  50% {
    transform: translateX(-50%) translateY(-5px);
  }
}

/* Transition fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.table-of-contents::-webkit-scrollbar {
  width: 6px;
}

.table-of-contents::-webkit-scrollbar-track {
  background: transparent;
}

.table-of-contents::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}

.toc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #dee2e6;
  position: relative;
}

/* Header comme poignée de drag */
.drag-handle {
  cursor: grab;
  touch-action: none;
}

.drag-indicator {
  font-size: 1rem;
  color: #999;
  margin-right: 0.5rem;
  cursor: grab;
  user-select: none;
  transition: color 0.2s, transform 0.2s;
}

.drag-indicator:hover {
  color: #42b983;
  transform: scale(1.2);
}

.toc-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  flex: 1;
}

.toc-toggle {

  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
  color: #c51414;
}

.toc-toggle:hover {
  background-color: #f0f0f0;
}

.toc-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.toc-item {
  margin-bottom: 0.5rem;
  transition: all 0.2s ease;
}

/* Indentation selon le niveau */
.toc-level-2 {
  padding-left: 0;
}

.toc-level-3 {
  padding-left: 1.25rem;
}

/* Bordure gauche */
.toc-item {
  border-left: 3px solid transparent;
  padding-left: 0.75rem;
}

.toc-item.active {
  border-left-color: #42b983;
}

.toc-link {
  color: #555;
  text-decoration: none;
  font-size: 0.9rem;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.25rem 0;
  cursor: pointer;
  transition: all 0.2s;
  line-height: 1.4;
}

.toc-bullet {
  color: #999;
  font-size: 1.2rem;
  line-height: 1;
  margin-top: 2px;
  transition: all 0.2s;
}

.toc-text {
  flex: 1;
}

.toc-link:hover {
  color: #42b983;
}

.toc-link:hover .toc-bullet {
  color: #42b983;
  transform: translateX(3px);
}

.toc-item.active .toc-link {
  color: #42b983;
  font-weight: 600;
}

.toc-item.active .toc-bullet {
  color: #42b983;
}

/* Progression de lecture */
.reading-progress {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}

.progress-text {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.progress-bar-container {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #42b983 0%, #35a372 100%);
  transition: width 0.3s ease;
  border-radius: 3px;
}

/* Animations */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
/* Responsive Mobile */
@media (max-width: 768px) {
  .table-of-contents {
    position: relative !important;
    transform: none !important;
    width: 100%;
    margin-bottom: 1.5rem;
    cursor: default;
  }

  .drag-handle {
    cursor: default;
  }

  .drag-indicator {
    display: none;
  }

  .toc-toggle {
    display: block;
  }

  .toc-collapsed {
    padding: 0.75rem 1rem;
     max-width: 1rem !important;
  }

  .toc-collapsed .toc-header {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
   
  }

  .toc-list {
    margin-top: 1rem;
  }

  .reading-progress {
    margin-top: 1rem;
  }
}






</style>
