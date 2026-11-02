<template>
  <div
    v-if="APP_CONFIG.COMPTES_VISIBLES"
    class="flex items-center gap-2"
  >
    <p
      v-if="logoutError"
      class="text-sm text-red-700"
      role="alert"
    >
      {{ logoutError }}
    </p>

    <template v-if="isLoggedIn">
      <router-link
        to="/mon-espace"
        data-testid="lien-mon-espace"
      >
        <AppButton
          variant="profile"
          :size="compact ? 'xs' : 'sm'"
          icon="pi pi-user"
          :rounded="compact ? 'full' : 'md'"
          :text-content="compact ? '' : 'Mon espace'"
          :aria-label="compact ? 'Mon espace' : undefined"
        />
      </router-link>
      <AppButton
        variant="ghost"
        :size="compact ? 'xs' : 'sm'"
        icon="pi pi-sign-out"
        :rounded="compact ? 'full' : 'md'"
        :text-content="compact ? '' : 'Se déconnecter'"
        :aria-label="compact ? 'Se déconnecter' : undefined"
        @click="handleLogout"
      />
    </template>

    <a
      v-else
      href="/login"
      data-testid="lien-connexion"
    >
      <AppButton
        variant="outline"
        :size="compact ? 'xs' : 'sm'"
        icon="pi pi-sign-in"
        :rounded="compact ? 'full' : 'md'"
        :text-content="compact ? '' : 'Se connecter'"
        :aria-label="compact ? 'Se connecter ou créer un compte' : undefined"
      />
    </a>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import AppButton from '../../ui/AppButton.vue'
import { APP_CONFIG } from '../../../config/app.js'
import { useData } from '../../../utils/fetchDataPwa'
import { logout } from '../../../services/auth.js'

defineProps({
  // Header mobile : icônes seules
  compact: {
    type: Boolean,
    default: false
  }
})

const { user, clearUser } = useData()
const logoutError = ref('')
const isLoggedIn = computed(() => user.value?.roles?.includes('ROLE_USER') ?? false)

const handleLogout = async () => {
  logoutError.value = ''
  try {
    await logout()
    await clearUser()
    window.location.assign('/spa')
  } catch (error) {
    console.error('Erreur lors de la déconnexion', error)
    logoutError.value = 'La déconnexion a échoué. Vous êtes toujours connecté.'
  }
}
</script>
