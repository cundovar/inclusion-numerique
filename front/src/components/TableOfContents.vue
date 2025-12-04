<template>
  <Teleport to="body">
    <nav
      v-if="toc.length > 0"
      ref="tocElement"
      class="table-of-contents z-[100000]"
      :class="{ 'is-dragging': isDragging }"
      :style="draggableStyle"
    >
    <!-- Hint déplaçable -->
    <transition name="fade">
      <div v-if="showHint" class="drag-hint">
        💡 Glissez-déposez pour déplacer
      </div>
    </transition>

    <!-- Zone de drag en haut -->
    <div
      class="drag-handle"
      ref="dragHandle"
      title="Cliquez et glissez pour déplacer le sommaire"
    >
      <i class="pi pi-arrows-alt drag-indicator"></i>
      <span class="drag-text">Déplacer</span>
    </div>

    <!-- Header avec titre et bouton notes -->
    <div class="toc-header">
      <h3
        class="toc-title"
        @click="toggleToc"
        :aria-label="isOpen ? 'Fermer le sommaire' : 'Ouvrir le sommaire'"
        title="Cliquez pour ouvrir/fermer le sommaire"
      >
        Sommaire
      </h3>
      <button
        class="note-button"
        @click.stop="toggleNoteEditor"
        :aria-label="showNoteEditor ? 'Fermer les notes' : 'Ouvrir les notes'"
        title="Notes"
      >
        Notes
      </button>
    </div>

    <!-- Composant Liste du sommaire -->
    <TocList
      :toc="toc"
      :activeId="activeId"
      :showProgress="showProgress"
      :isOpen="isOpen"
      :isMobile="isMobile()"
      :collapsedOnMobile="collapsedOnMobile"
      @scroll-to="handleScrollTo"
      @close-mobile="closeMobileToc"
    />

    <!-- Composant Éditeur de notes -->
    <transition name="slide">
      <NoteEditor
        v-if="showNoteEditor"
        :pageId="pageId"
        :initialContent="noteContent"
        @save="handleNoteSave"
        :bgCategorie="bgCategorie"
        :bgHover="bgHover"
      />
    </transition>
    </nav>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useDraggable } from '@vueuse/core'
import axios from 'axios'
import TocList from './table-of-contents/TocList.vue'
import NoteEditor from './table-of-contents/notes/NoteEditor.vue'

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
  },
  pageId: {
    type: Number,
    default: null
  },
  bgCategorie: {
    type: String,
    default: ''
  },
  bgHover: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['scroll-to'])

const isOpen = ref(false)
const showHint = ref(false)

// Note editor
const showNoteEditor = ref(false)
const noteContent = ref('')
axios.defaults.withCredentials = true

// Drag and drop avec VueUse
const tocElement = ref(null)
const dragHandle = ref(null)

// Calculer la position initiale en pixels selon la largeur d'écran
const getInitialPosition = () => {
  const windowWidth = window.innerWidth
  const windowHeight = window.innerHeight

  // Adapter la position selon la taille d'écran (Tailwind breakpoints)
  let xPercent, yPercent

  if (windowWidth >= 1600) {
    // 2XL screens (≥1536px) - plus à droite pour laisser place au contenu
    xPercent = 0.17
    yPercent = 0.28
  } else if (windowWidth >= 1200) {
    // XL screens (≥1280px)
    xPercent = 0.05
    yPercent = 0.25
  } else if (windowWidth >= 1024) {
    // LG screens (≥1024px)
    xPercent = 0.05
    yPercent = 0.14
  } else if (windowWidth >= 768) {
    // MD screens (≥768px) - plus à gauche pour ne pas gêner le contenu
    xPercent = 0.00
    yPercent = 0.15
  } else {
    // Mobile - position par défaut (non utilisée car drag désactivé)
    xPercent = 0.05
    yPercent = 0.10
  }

  return {
    x: windowWidth * xPercent,
    y: windowHeight * yPercent
  }
}

// Activer le drag and drop uniquement via la poignée
const { x, y, isDragging } = useDraggable(tocElement, {
  initialValue: getInitialPosition(),
  preventDefault: false,
  handle: dragHandle
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

const toggleToc = () => {
  isOpen.value = !isOpen.value
}

const closeMobileToc = () => {
  isOpen.value = false
}

const handleScrollTo = (id) => {
  emit('scroll-to', id)
}

// Note editor methods
const toggleNoteEditor = () => {
  showNoteEditor.value = !showNoteEditor.value
  if (showNoteEditor.value && props.pageId) {
    loadNote()
  }
}

const loadNote = async () => {
  if (!props.pageId) return

  try {
    const response = await axios.get(`/api/notes/page/${props.pageId}`)
    if (response.data && response.data.content) {
      noteContent.value = response.data.content
    }
  } catch (error) {
    if (error.response?.status !== 204) {
      console.error('Erreur lors du chargement de la note:', error)
    }
  }
}

const handleNoteSave = (content) => {
  noteContent.value = content
  console.log('Note sauvegardée:', content)
}

// Charger la note si le pageId change
watch(() => props.pageId, (newPageId) => {
  if (newPageId && showNoteEditor.value) {
    loadNote()
  }
})
</script>

<style scoped>
.table-of-contents {
  /* position: fixed est appliqué par VueUse via :style */
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 12px;
  width: fit-content;
  min-width: 280px;
  max-width: 90vw;
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);

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
  z-index: 100001;
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
  gap: 0.5rem;
}

/* Zone de drag en haut */
.drag-handle {
  cursor: grab;
  touch-action: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px 12px 0 0;
  margin: -1.5rem -1.5rem 1rem -1.5rem;
  border-bottom: 2px solid #dee2e6;
  transition: all 0.2s;
}

.drag-handle:hover {
  background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
}

.drag-handle:active {
  cursor: grabbing;
  background: linear-gradient(135deg, #dee2e6 0%, #ced4da 100%);
}

.drag-indicator {
  font-size: 1.1rem;
  color: #666;
  cursor: grab;
  user-select: none;
  transition: color 0.2s, transform 0.2s;
}

.drag-handle:hover .drag-indicator {
  color: #42b983;
  transform: scale(1.1);
}

.drag-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: #666;
  user-select: none;
  transition: color 0.2s;
}

.drag-handle:hover .drag-text {
  color: #42b983;
}

.toc-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  flex: 1;
  cursor: pointer;
  transition: color 0.2s;
  user-select: none;
}

.toc-title:hover {
  color: #42b983;
}

.note-button {
  background: none;
  border: none;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: all 0.2s;
  color: #42b983;
  font-weight: 500;
}

.note-button:hover {
  background-color: #f0f0f0;
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
    display: none;
  }

  .toc-header {
    margin-top: 0;
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
}
</style>
