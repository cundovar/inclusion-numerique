<template>
  <aside
    v-if="toc.length > 0"
    class="sidebar-toc"
  >
    <!-- Header avec bouton toggle -->
    <div class="toc-header" :class="bgCategorie">
      <button
        @click="toggleClosed"
        class="toggle-btn"
      >
        {{ closed ? '+' : 'X' }}
      </button>
      <span class="header-title">Sommaire</span>
    </div>

    <!-- Contenu (caché si closed) -->
    <div v-show="!closed" class="toc-body">
      <!-- Onglets Sommaire et Notes -->
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
          :isMobile="false"
          :bgCategorie="bgCategorie"
          @scroll-to="handleScrollTo"
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
  </aside>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import TocList from './TocList.vue'
import NoteEditor from '../../notes/NoteEditor.vue'
import { useData } from '../../../../utils/fetchDataPwa'
import { noteService } from '../../../../services/noteService'

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

const activeTab = ref('sommaire')
const closed = ref(false)
const noteContent = ref('')

const toggleClosed = () => {
  closed.value = !closed.value
}

const isUserAuthenticated = computed(() => {
  return user.value && user.value.username && user.value.username.trim() !== ''
})

watch(isUserAuthenticated, (isAuth) => {
  if (!isAuth && activeTab.value === 'notes') {
    activeTab.value = 'sommaire'
  }
})

const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'notes' && props.pageId) {
    loadNote()
  }
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
}

watch(() => props.pageId, (newPageId) => {
  if (newPageId && activeTab.value === 'notes') {
    loadNote()
  }
})
</script>

<style scoped>
.sidebar-toc {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 120px;
  right: 20px;
  width: 250px;
  max-height: calc(100vh - 140px);
  overflow-y: auto;
  z-index: 50;
}

.toc-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #f3f4f6;
  border-bottom: 1px solid #e5e7eb;
}

.toggle-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: #ef4444;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn:hover {
  background: #dc2626;
}

.header-title {
  font-weight: 600;
  color: #374151;
}

.toc-body {
  padding: 1rem;
}

.toc-tabs {
  display: flex;
  margin-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.toc-tab {
  flex: 1;
  background: transparent;
  border: none;
  font-size: 0.875rem;
  font-weight: 600;
  color: #9ca3af;
  cursor: pointer;
  padding: 0.5rem;
  transition: all 0.2s;
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
  color: #6b7280;
}

.toc-tab.active {
  color: #111827;
}

.toc-tab.active::after {
  background: #111827;
}

.tab-content {
  min-height: 100px;
}

/* Scrollbar */
.sidebar-toc::-webkit-scrollbar {
  width: 6px;
}

.sidebar-toc::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-toc::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.sidebar-toc::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
