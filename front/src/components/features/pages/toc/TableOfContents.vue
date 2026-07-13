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
      :class="bgCategorie"
      ref="dragHandle"
      title="Cliquez et glissez pour déplacer le sommaire"
    >
      <AppButton
      variant="danger"
      :textContent="closed ? '+ ' : 'X'"
      @click="toggleClosed"
      class="absolute top-1 left-1"
      size="sm"
    />
      <i class="pi pi-arrows-alt "></i>
      <span class="drag-text"></span>
    </div>
<div class="transition-all duration-300 ease-in-out" :class="closed ? 'hidden' : 'transition-all duration-300 ease-in-out'">
    <!-- Header avec onglets Sommaire et Notes -->
    <div class="toc-tabs">
      <button
        class="toc-tab"
        :class="{ active: activeTab === 'sommaire' }"
        @click="switchTab('sommaire')"
      >
        Sommaire
      </button>
      <button
        v-if="isUserAuthenticated"
        class="toc-tab"
        :class="{ active: activeTab === 'notes' }"
        @click="switchTab('notes')"
      >
        Notes
      </button>
    </div>

    <!-- Contenu des onglets -->
    <div class="tab-content">
      <!-- Onglet Sommaire -->
      <TocList
        v-if="activeTab === 'sommaire'"
        :toc="toc"
        :activeId="activeId"
        :showProgress="showProgress"
        :isOpen="true"
        :isMobile="isMobile()"
        :collapsedOnMobile="collapsedOnMobile"
        :bgCategorie="bgCategorie"
        @scroll-to="handleScrollTo"
        @close-mobile="toggleClosed"
      />

      <!-- Onglet Notes -->
      <NoteEditor
        v-if="activeTab === 'notes' && isUserAuthenticated"
        :pageId="pageId"
        :initialContent="noteContent"
        @save="handleNoteSave"
        :bgCategorie="bgCategorie"
        :bgHover="bgHover"
      />
    </div>
</div>


    </nav>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useDraggable } from '@vueuse/core'
import TocList from './TocList.vue'
import NoteEditor from '../../notes/NoteEditor.vue'
import AppButton from '../../../ui/AppButton.vue'
import { useData } from '../../../../utils/fetchDataPwa'
import { noteService } from '../../../../services/noteService'

// Récupérer l'état de l'utilisateur
const { user } = useData()

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

// Onglet actif : 'sommaire' ou 'notes'
const activeTab = ref('sommaire')
const showHint = ref(false)

const closed = ref(false)
const toggleClosed = () => {
  closed.value = !closed.value
}

// Vérifier si l'utilisateur est authentifié
const isUserAuthenticated = computed(() => {
  return user.value && user.value.username && user.value.username.trim() !== ''
})

// Observer les changements d'authentification
watch(isUserAuthenticated, (isAuth) => {
  // Si l'utilisateur se déconnecte alors qu'il est sur l'onglet Notes,
  // le ramener à l'onglet Sommaire
  if (!isAuth && activeTab.value === 'notes') {
    activeTab.value = 'sommaire'
  }
})

// Note editor
const noteContent = ref('')
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
    xPercent = 0.85
    yPercent = 0.28
  } else if (windowWidth >= 1200) {
    // XL screens (≥1280px)
    xPercent = 0.40
    yPercent = 0.25
  } else if (windowWidth >= 1024) {
    // LG screens (≥1024px)
    xPercent = 0.40
    yPercent = 0.25
  } else if (windowWidth >= 768) {
    // MD screens (≥768px) - plus à gauche pour ne pas gêner le contenu
    xPercent = 0.30
    yPercent = 0.25
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

// Afficher le hint après 2 secondes sur desktop
onMounted(() => {
  if (!isMobile()) {
    setTimeout(() => {
      showHint.value = true
      setTimeout(() => {
        showHint.value = false
      }, 5000)
    }, 2000)
  }
})

// Changer d'onglet
const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'notes' && props.pageId) {
    loadNote()
  }
}

const closeMobileToc = () => {
  // Fermer sur mobile
}

const handleScrollTo = (id) => {
  emit('scroll-to', id)
}

const loadNote = async () => {
  if (!props.pageId) return

  try {
    const data = await noteService.getNoteByPage(props.pageId)
    noteContent.value = data?.content || ''
  } catch (error) {
    console.error('Erreur lors du chargement de la note:', error)
  }
}

const handleNoteSave = (content) => {
  noteContent.value = content
  console.log('Note sauvegardée:', content)
}

// Charger la note si le pageId change
watch(() => props.pageId, (newPageId) => {
  if (newPageId && activeTab.value === 'notes') {
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
  color: #555;
}

/* Hint déplaçable */
.drag-hint {
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg, #555 0%, #333 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
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
  border-top: 6px solid #333;
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

.toc-tabs {
  display: flex;
  margin-bottom: 1rem;
  border-bottom: 2px solid #dee2e6;
  position: relative;
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
  color: #333;
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
  color: #333;
}

.toc-tab {
  flex: 1;
  background: transparent;
  border: none;
  font-size: 0.9rem;
  font-weight: 600;
  color: #999;
  cursor: pointer;
  padding: 0.75rem 1rem;
  transition: all 0.2s;
  user-select: none;
  position: relative;
}

.toc-tab::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: transparent;
  transition: background 0.2s;
}

.toc-tab:hover {
  color: #555;
}

.toc-tab.active {
  color: #333;
}

.toc-tab.active::after {
  background: #333;
}

.tab-content {
  min-height: 100px;
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
