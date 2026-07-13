<template>
  <div class="note-editor-section"  >
  

    <!-- Barre d'outils Tiptap -->
    <div :class="bgCategorie" v-if="editor" class="flex flex-wrap max-w-64 m-auto p-5 gap-1 ">
      <template v-for="(button, index) in tableButton" :key="button.key">
        <AppButton
          @click="toggleFormat(button.key)"
          :disabled="!canToggleFormat(button.key)"
          :class="{ 'is-active': editor.isActive(button.key) }"
          variant="neutral"
          :textContent="button.label"
          size="xs"
          :hoverBgColor="hoverStyle"
        />
        <div v-if="index < tableButton.length - 1" class="toolbar-divider"></div>
      </template>
    </div>

    <!-- Éditeur Tiptap -->
    <div class="note-editor">
      <editor-content :editor="editor" class="tiptap-editor" />
    </div>

    <!-- Message de confirmation -->
    <transition name="fade">
      <div v-if="noteSaved" class="note-saved-message">
        ✓ Note enregistrée !
      </div>
    </transition>
  </div>
    <div class="r">


      <AppButton
        v-if="editor"
        @click="saveNote"
       variant="ghost"
       textContent="Enregistrer"
        :disabled="isSavingNote || !hasContent"
      >
        
      </AppButton>
    </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import AppButton from '../../ui/AppButton.vue'
import { noteService } from '../../../services/noteService'

const tableButton = [
  { key: "bold", label: "Gras" },
  { key: "italic", label: "Italique" },
  { key: "strike", label: "Barré" },
  { key: "heading", label: "Titre" },
  { key: "bulletList", label: "Liste" },
  { key: "orderedList", label: "Numéro" },
  { key: "codeBlock", label: "Code" },
  { key: "blockquote", label: "Citation" },
  { key: "undo", label: "Annuler" },
  { key: "redo", label: "Refaire" }
]

const props = defineProps({
  pageId: {
    type: Number,
    default: null
  },
  initialContent: {
    type: String,
    default: ''
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

const emit = defineEmits(['save'])

// Référence pour l'élément caché
const colorExtractor = ref(null)

// Fonction pour extraire la couleur CSS d'une classe Tailwind
const extractTailwindColor = (tailwindClass) => {
  if (!tailwindClass || !colorExtractor.value) return ''

  // Créer un élément temporaire avec la classe Tailwind
  const tempEl = document.createElement('div')
  tempEl.className = tailwindClass
  tempEl.style.display = 'none'
  document.body.appendChild(tempEl)

  // Extraire la couleur computed
  const computedColor = window.getComputedStyle(tempEl).backgroundColor

  // Nettoyer
  document.body.removeChild(tempEl)

  console.log('🎨 Couleur extraite de', tailwindClass, '→', computedColor)
  return computedColor
}

// Computed pour le style hover
const hoverStyle = computed(() => {
  if (!props.bgHover) return ''
  return extractTailwindColor(props.bgHover)
})

// État
const isSavingNote = ref(false)
const noteSaved = ref(false)

// Configuration de l'éditeur Tiptap
const editor = useEditor({
  content: props.initialContent,
  extensions: [
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3]
      }
    })
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm max-w-none focus:outline-none min-h-[200px] p-3'
    }
  }
})

// Vérifier si l'éditeur a du contenu
const hasContent = computed(() => {
  if (!editor.value) return false
  const text = editor.value.getText().trim()
  return text.length > 0
})

// Fonction pour basculer le formatage selon le type de bouton
const toggleFormat = (key) => {
  if (!editor.value) return

  const formatActions = {
    bold: () => editor.value.chain().focus().toggleBold().run(),
    italic: () => editor.value.chain().focus().toggleItalic().run(),
    strike: () => editor.value.chain().focus().toggleStrike().run(),
    heading: () => editor.value.chain().focus().toggleHeading({ level: 2 }).run(),
    bulletList: () => editor.value.chain().focus().toggleBulletList().run(),
    orderedList: () => editor.value.chain().focus().toggleOrderedList().run(),
    codeBlock: () => editor.value.chain().focus().toggleCodeBlock().run(),
    blockquote: () => editor.value.chain().focus().toggleBlockquote().run(),
    undo: () => editor.value.chain().focus().undo().run(),
    redo: () => editor.value.chain().focus().redo().run()
  }

  if (formatActions[key]) {
    formatActions[key]()
  }
}

// Fonction pour vérifier si le formatage peut être appliqué
const canToggleFormat = (key) => {
  if (!editor.value) return false

  const canActions = {
    bold: () => editor.value.can().chain().focus().toggleBold().run(),
    italic: () => editor.value.can().chain().focus().toggleItalic().run(),
    strike: () => editor.value.can().chain().focus().toggleStrike().run(),
    heading: () => editor.value.can().chain().focus().toggleHeading({ level: 2 }).run(),
    bulletList: () => editor.value.can().chain().focus().toggleBulletList().run(),
    orderedList: () => editor.value.can().chain().focus().toggleOrderedList().run(),
    codeBlock: () => editor.value.can().chain().focus().toggleCodeBlock().run(),
    blockquote: () => editor.value.can().chain().focus().toggleBlockquote().run(),
    undo: () => editor.value.can().chain().focus().undo().run(),
    redo: () => editor.value.can().chain().focus().redo().run()
  }

  if (canActions[key]) {
    return canActions[key]()
  }

  return true
}

// Charger le contenu initial
watch(() => props.initialContent, (newContent) => {
  if (editor.value && newContent !== editor.value.getHTML()) {
    editor.value.commands.setContent(newContent)
  }
})

// Sauvegarder la note
const saveNote = async () => {
  if (!props.pageId || !hasContent.value) return

  isSavingNote.value = true
  noteSaved.value = false

  const content = editor.value.getHTML()

  try {
    await noteService.createOrUpdate(props.pageId, content)

    noteSaved.value = true
    emit('save', content)

    setTimeout(() => {
      noteSaved.value = false
    }, 3000)
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement de la note:', error)
  } finally {
    isSavingNote.value = false
  }
}

// Nettoyer l'éditeur
onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy()
  }
})
</script>

<style scoped>
/* Note Editor Section */
.note-editor-section {


  width: fit-content;
  /* min-width: 100%; */
}

.note-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  width: fit-content;
  /* min-width: 100%; */
width: 100%;
}

.note-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.save-note-btn {
  background: #333;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.save-note-btn:hover:not(:disabled) {
  background: #555;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.save-note-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Toolbar */
.editor-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  padding: 0.5rem;
  background: #f8f9fa;
 

  width: fit-content;

  max-width: 23rem;
  min-width: 15rem;
  margin: 0 auto;

}

.toolbar-btn {
  background: white;
  border: 1px solid #dee2e6;
  padding: 0.5rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
  color: #555;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
}

.toolbar-btn:hover:not(:disabled) {
  background: #e9ecef;
  border-color: #adb5bd;
}

.toolbar-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.toolbar-btn.is-active {
  background: #333;
  color: white;
  border-color: #333;
}

.toolbar-divider {
  width: 1px;
  background: #dee2e6;
  margin: 0 0.25rem;
}

/* Note Editor */
.note-editor {
  margin-bottom: 0.75rem;
  width: fit-content;
  min-width: 100%;
}

.tiptap-editor {
  border: 2px solid #dee2e6;
  border-radius: 0 0 8px 8px;
  background: white;
  min-height: 200px;
  min-width: 250px;
  resize: both;
  overflow: auto;
}

.note-saved-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #d4edda;
  color: #155724;
  border-radius: 6px;
  font-size: 0.85rem;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Styles pour le contenu Tiptap */
:deep(.tiptap-editor .ProseMirror) {
  outline: none;
  min-height: 200px;
  height: 100%;
}

:deep(.tiptap-editor h1) {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  color: #333;
}

:deep(.tiptap-editor h2) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 0.75rem;
  margin-bottom: 0.5rem;
  color: #333;
}

:deep(.tiptap-editor h3) {
  font-size: 1.1rem;
  font-weight: 600;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  color: #333;
}

:deep(.tiptap-editor p) {
  margin-bottom: 0.5rem;
  line-height: 1.6;
}

:deep(.tiptap-editor ul),
:deep(.tiptap-editor ol) {
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}

:deep(.tiptap-editor li) {
  margin-bottom: 0.25rem;
}

:deep(.tiptap-editor code) {
  background: #f1f3f5;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  font-size: 0.9em;
  font-family: 'Courier New', monospace;
}

:deep(.tiptap-editor pre) {
  background: #2d2d2d;
  color: #f8f8f2;
  padding: 1rem;
  border-radius: 6px;
  overflow-x: auto;
  margin-bottom: 0.5rem;
}

:deep(.tiptap-editor pre code) {
  background: none;
  padding: 0;
  color: inherit;
}

:deep(.tiptap-editor blockquote) {
  border-left: 4px solid #999;
  padding-left: 1rem;
  margin-left: 0;
  color: #666;
  font-style: italic;
}

:deep(.tiptap-editor strong) {
  font-weight: 700;
}

:deep(.tiptap-editor em) {
  font-style: italic;
}

:deep(.tiptap-editor s) {
  text-decoration: line-through;
}
</style>
