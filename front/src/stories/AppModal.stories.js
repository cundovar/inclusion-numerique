import { ref } from "vue"
import AppModal from "../components/ui/AppModal.vue"
import AppButton from "../components/ui/AppButton.vue"
import AppInput from "../components/ui/AppInput.vue"

export default {
  title: "Components/AppModal",
  component: AppModal,
  tags: ["autodocs"],
  argTypes: {
    modelValue: { control: "boolean" },
    title: { control: "text" },
    icon: { control: "text" },
    size: {
      control: { type: "select" },
      options: ["xs", "sm", "md", "lg", "xl", "full"]
    },
    position: {
      control: { type: "select" },
      options: ["center", "top", "bottom"]
    },
    closable: { control: "boolean" },
    closeOnBackdrop: { control: "boolean" },
    closeOnEscape: { control: "boolean" },
    persistent: { control: "boolean" },
    showHeader: { control: "boolean" },
    showFooter: { control: "boolean" },
    bodyPadding: {
      control: { type: "select" },
      options: ["none", "sm", "md", "lg", "xl"]
    },
    backdropBlur: { control: "boolean" },
    showCancelButton: { control: "boolean" },
    showConfirmButton: { control: "boolean" },
    cancelText: { control: "text" },
    confirmText: { control: "text" },
    confirmVariant: { control: "text" },
    loading: { control: "boolean" },
    onClose: { action: "close" },
    onOpen: { action: "open" },
    onConfirm: { action: "confirm" },
    onCancel: { action: "cancel" }
  }
}

export const Default = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Ouvrir la modal" />
        <AppModal
          v-model="isOpen"
          title="Ma Modal"
          closable
        >
          <p>Ceci est le contenu de la modal. Vous pouvez mettre ici du texte, des formulaires, des images, etc.</p>
          <p class="mt-4">La modal se ferme en cliquant sur le backdrop, le bouton X ou la touche Escape.</p>
        </AppModal>
      </div>
    `
  })
}

export const AllSizes = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const openXs = ref(false)
      const openSm = ref(false)
      const openMd = ref(false)
      const openLg = ref(false)
      const openXl = ref(false)
      const openFull = ref(false)
      return { openXs, openSm, openMd, openLg, openXl, openFull }
    },
    template: `
      <div class="flex flex-wrap gap-2">
        <AppButton @click="openXs = true" text-content="Extra Small (xs)" size="sm" />
        <AppButton @click="openSm = true" text-content="Small (sm)" size="sm" />
        <AppButton @click="openMd = true" text-content="Medium (md)" size="sm" />
        <AppButton @click="openLg = true" text-content="Large (lg)" size="sm" />
        <AppButton @click="openXl = true" text-content="Extra Large (xl)" size="sm" />
        <AppButton @click="openFull = true" text-content="Full Screen" size="sm" />

        <AppModal v-model="openXs" title="Extra Small" size="xs">
          <p>Modal extra petite (320px)</p>
        </AppModal>

        <AppModal v-model="openSm" title="Small" size="sm">
          <p>Modal petite (400px)</p>
        </AppModal>

        <AppModal v-model="openMd" title="Medium" size="md">
          <p>Modal moyenne (500px) - Taille par défaut</p>
        </AppModal>

        <AppModal v-model="openLg" title="Large" size="lg">
          <p>Modal large (700px)</p>
        </AppModal>

        <AppModal v-model="openXl" title="Extra Large" size="xl">
          <p>Modal extra large (900px)</p>
        </AppModal>

        <AppModal v-model="openFull" title="Full Screen" size="full">
          <p>Modal plein écran</p>
        </AppModal>
      </div>
    `
  })
}

export const Positions = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const openCenter = ref(false)
      const openTop = ref(false)
      const openBottom = ref(false)
      return { openCenter, openTop, openBottom }
    },
    template: `
      <div class="flex gap-2">
        <AppButton @click="openCenter = true" text-content="Center" />
        <AppButton @click="openTop = true" text-content="Top" />
        <AppButton @click="openBottom = true" text-content="Bottom" />

        <AppModal v-model="openCenter" title="Position Center" position="center">
          <p>Modal au centre de l'écran (animation scale)</p>
        </AppModal>

        <AppModal v-model="openTop" title="Position Top" position="top">
          <p>Modal en haut de l'écran (animation slide down)</p>
        </AppModal>

        <AppModal v-model="openBottom" title="Position Bottom" position="bottom">
          <p>Modal en bas de l'écran (animation slide up) - Bottom Sheet</p>
        </AppModal>
      </div>
    `
  })
}

export const WithIcon = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Ouvrir modal avec icône" />
        <AppModal
          v-model="isOpen"
          title="Information"
          icon="pi pi-info-circle"
        >
          <p>Modal avec une icône dans le header.</p>
        </AppModal>
      </div>
    `
  })
}

export const ConfirmDialog = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      const handleConfirm = () => {
        alert("Confirmation !")
        isOpen.value = false
      }
      return { isOpen, handleConfirm }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Supprimer" variant="danger" />
        <AppModal
          v-model="isOpen"
          title="Confirmer la suppression"
          icon="pi pi-exclamation-triangle"
          size="sm"
          show-cancel-button
          show-confirm-button
          confirm-text="Supprimer"
          confirm-variant="danger"
          cancel-text="Annuler"
          @confirm="handleConfirm"
        >
          <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
          <p class="text-sm text-gray-500 mt-2">Cette action est irréversible.</p>
        </AppModal>
      </div>
    `
  })
}

export const WithForm = {
  render: () => ({
    components: { AppModal, AppButton, AppInput },
    setup() {
      const isOpen = ref(false)
      const saving = ref(false)
      const name = ref("")
      const email = ref("")

      const handleSubmit = async () => {
        saving.value = true
        await new Promise(resolve => setTimeout(resolve, 1500))
        console.log("Form submitted:", { name: name.value, email: email.value })
        saving.value = false
        isOpen.value = false
        name.value = ""
        email.value = ""
      }

      return { isOpen, saving, name, email, handleSubmit }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Ajouter un utilisateur" icon="pi-user-plus" />
        <AppModal
          v-model="isOpen"
          title="Nouvel utilisateur"
          icon="pi pi-user-plus"
          size="md"
          show-cancel-button
          show-confirm-button
          confirm-text="Enregistrer"
          :loading="saving"
          @confirm="handleSubmit"
        >
          <div class="space-y-4">
            <AppInput
              v-model="name"
              label="Nom complet"
              placeholder="Entrez le nom"
            />
            <AppInput
              v-model="email"
              type="email"
              label="Email"
              placeholder="exemple@email.com"
            />
          </div>
        </AppModal>
      </div>
    `
  })
}

export const Persistent = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      const handleConfirm = () => {
        alert("Confirmé !")
        isOpen.value = false
      }
      return { isOpen, handleConfirm }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Ouvrir modal persistante" />
        <AppModal
          v-model="isOpen"
          title="Action importante"
          persistent
          show-cancel-button
          show-confirm-button
          @confirm="handleConfirm"
        >
          <p>Cette modal ne peut pas être fermée en cliquant en dehors ou avec Escape.</p>
          <p class="mt-2">Vous devez cliquer sur "Confirmer" ou "Annuler".</p>
        </AppModal>
      </div>
    `
  })
}

export const CustomHeaderFooter = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Header/Footer personnalisés" />
        <AppModal v-model="isOpen" size="md">
          <template #header>
            <div class="flex items-center justify-between w-full">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <i class="pi pi-user text-blue-600"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-lg">Profil utilisateur</h3>
                  <p class="text-sm text-gray-500">Modifier les informations</p>
                </div>
              </div>
              <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600">
                <i class="pi pi-times"></i>
              </button>
            </div>
          </template>

          <p>Contenu de la modal avec header personnalisé...</p>

          <template #footer>
            <div class="flex justify-between w-full">
              <AppButton variant="danger" text-content="Supprimer" size="sm" />
              <div class="flex gap-2">
                <AppButton variant="secondary" text-content="Annuler" size="sm" @click="isOpen = false" />
                <AppButton variant="primary" text-content="Sauvegarder" size="sm" />
              </div>
            </div>
          </template>
        </AppModal>
      </div>
    `
  })
}

export const NoHeaderNoFooter = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Message de succès" variant="success" />
        <AppModal
          v-model="isOpen"
          :show-header="false"
          :show-footer="false"
          size="sm"
          body-padding="xl"
        >
          <div class="text-center">
            <i class="pi pi-check-circle text-6xl text-green-500 mb-4"></i>
            <h2 class="text-2xl font-bold mb-2">Succès !</h2>
            <p class="text-gray-600 mb-6">Votre action a été effectuée avec succès.</p>
            <AppButton text-content="Fermer" variant="success" @click="isOpen = false" />
          </div>
        </AppModal>
      </div>
    `
  })
}

export const ScrollableContent = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Contenu scrollable" />
        <AppModal
          v-model="isOpen"
          title="Conditions d'utilisation"
          size="lg"
          show-footer
          show-confirm-button
          confirm-text="J'accepte"
        >
          <div class="space-y-4">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
          </div>
        </AppModal>
      </div>
    `
  })
}

export const BottomSheet = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Bottom Sheet (mobile)" />
        <AppModal
          v-model="isOpen"
          title="Options"
          position="bottom"
          size="sm"
          body-padding="lg"
        >
          <div class="space-y-2">
            <button class="w-full text-left p-3 hover:bg-gray-100 rounded flex items-center">
              <i class="pi pi-share-alt mr-3"></i> Partager
            </button>
            <button class="w-full text-left p-3 hover:bg-gray-100 rounded flex items-center">
              <i class="pi pi-download mr-3"></i> Télécharger
            </button>
            <button class="w-full text-left p-3 hover:bg-gray-100 rounded flex items-center">
              <i class="pi pi-pencil mr-3"></i> Modifier
            </button>
            <button class="w-full text-left p-3 hover:bg-gray-100 rounded text-red-600 flex items-center">
              <i class="pi pi-trash mr-3"></i> Supprimer
            </button>
          </div>
        </AppModal>
      </div>
    `
  })
}

export const NoPadding = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Image sans padding" />
        <AppModal
          v-model="isOpen"
          title="Galerie"
          body-padding="none"
          size="lg"
        >
          <div class="bg-gray-200 h-96 flex items-center justify-center">
            <p class="text-gray-500">Image placeholder</p>
          </div>
        </AppModal>
      </div>
    `
  })
}

export const NoBackdropBlur = {
  render: () => ({
    components: { AppModal, AppButton },
    setup() {
      const isOpen = ref(false)
      return { isOpen }
    },
    template: `
      <div>
        <AppButton @click="isOpen = true" text-content="Sans backdrop blur" />
        <AppModal
          v-model="isOpen"
          title="Sans flou"
          :backdrop-blur="false"
        >
          <p>Cette modal n'a pas d'effet de flou sur le backdrop.</p>
        </AppModal>
      </div>
    `
  })
}
