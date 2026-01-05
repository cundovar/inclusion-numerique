<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-4xl font-bold mb-8 text-gray-800">
        <i class="pi pi-palette mr-3"></i>
        Design System - Composants
      </h1>

      <!-- Navigation rapide -->
      <div class="bg-white rounded-lg shadow p-4 mb-8 sticky top-4 z-10">
        <div class="flex flex-wrap gap-2">
          <a href="#buttons" class="px-4 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">Boutons</a>
          <a href="#inputs" class="px-4 py-2 bg-green-100 text-green-700 rounded hover:bg-green-200">Inputs</a>
          <a href="#selects" class="px-4 py-2 bg-purple-100 text-purple-700 rounded hover:bg-purple-200">Selects</a>
          <a href="#alerts" class="px-4 py-2 bg-orange-100 text-orange-700 rounded hover:bg-orange-200">Alerts</a>
        </div>
      </div>

      <!-- AppButton -->
      <section id="buttons" class="mb-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
          <i class="pi pi-stop-circle mr-2 text-blue-500"></i>
          AppButton
        </h2>

        <!-- Variantes -->
        <div class="mb-8">
          <h3 class="text-xl font-semibold mb-4 text-gray-700">Variantes</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="primary" text-content="Primary" />
              <code class="text-xs text-gray-500">primary</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="secondary" text-content="Secondary" />
              <code class="text-xs text-gray-500">secondary</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="danger" text-content="Danger" />
              <code class="text-xs text-gray-500">danger</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="success" text-content="Success" />
              <code class="text-xs text-gray-500">success</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="warning" text-content="Warning" />
              <code class="text-xs text-gray-500">warning</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="ghost" text-content="Ghost" />
              <code class="text-xs text-gray-500">ghost</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="outline" text-content="Outline" />
              <code class="text-xs text-gray-500">outline</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="profile" icon="pi pi-user" text-content="Profile" />
              <code class="text-xs text-gray-500">profile</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="auth" icon="pi pi-sign-in" text-content="Auth" />
              <code class="text-xs text-gray-500">auth</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="favorite" icon="pi pi-heart-fill" text-content="Favoris" />
              <code class="text-xs text-gray-500">favorite</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton variant="unfavorite" icon="pi pi-heart" text-content="Ajouter" />
              <code class="text-xs text-gray-500">unfavorite</code>
            </div>
          </div>
        </div>

        <!-- Tailles -->
        <div class="mb-8">
          <h3 class="text-xl font-semibold mb-4 text-gray-700">Tailles</h3>
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex flex-col items-center gap-2">
              <AppButton size="xs" text-content="XS" />
              <code class="text-xs text-gray-500">xs</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton size="sm" text-content="Small" />
              <code class="text-xs text-gray-500">sm</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton size="md" text-content="Medium" />
              <code class="text-xs text-gray-500">md</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton size="lg" text-content="Large" />
              <code class="text-xs text-gray-500">lg</code>
            </div>
            <div class="flex flex-col items-center gap-2">
              <AppButton size="xl" text-content="XL" />
              <code class="text-xs text-gray-500">xl</code>
            </div>
          </div>
        </div>

        <!-- Avec icônes -->
        <div class="mb-8">
          <h3 class="text-xl font-semibold mb-4 text-gray-700">Avec icônes</h3>
          <div class="flex flex-wrap gap-4">
            <AppButton icon="pi pi-check" text-content="Valider" />
            <AppButton icon="pi pi-times" text-content="Annuler" variant="danger" />
            <AppButton icon="pi pi-arrow-right" icon-position="right" text-content="Suivant" />
            <AppButton icon="pi pi-save" text-content="Enregistrer" variant="success" />
          </div>
        </div>

        <!-- États -->
        <div>
          <h3 class="text-xl font-semibold mb-4 text-gray-700">États</h3>
          <div class="flex flex-wrap gap-4">
            <AppButton text-content="Normal" />
            <AppButton text-content="Disabled" disabled />
            <AppButton text-content="Loading" loading />
            <AppButton text-content="Pleine largeur" full-width class="max-w-md" />
          </div>
        </div>
      </section>

      <!-- AppInput -->
      <section id="inputs" class="mb-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
          <i class="pi pi-pencil mr-2 text-green-500"></i>
          AppInput
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Text -->
          <div>
            <h4 class="font-semibold mb-2">Text Input</h4>
            <AppInput
              v-model="demoInputs.text"
              type="text"
              label="Nom"
              placeholder="Entrez votre nom"
            />
          </div>

          <!-- Email -->
          <div>
            <h4 class="font-semibold mb-2">Email Input</h4>
            <AppInput
              v-model="demoInputs.email"
              type="email"
              label="Email"
              placeholder="exemple@email.com"
              prefix-icon="Message"
              clearable
            />
          </div>

          <!-- Password -->
          <div>
            <h4 class="font-semibold mb-2">Password Input</h4>
            <AppInput
              v-model="demoInputs.password"
              type="password"
              label="Mot de passe"
              placeholder="Votre mot de passe"
            />
          </div>

          <!-- Number -->
          <div>
            <h4 class="font-semibold mb-2">Number Input</h4>
            <AppInput
              v-model="demoInputs.number"
              type="number"
              label="Âge"
              placeholder="Votre âge"
            />
          </div>

          <!-- With error -->
          <div>
            <h4 class="font-semibold mb-2">Avec erreur</h4>
            <AppInput
              v-model="demoInputs.error"
              type="text"
              label="Champ avec erreur"
              error="Ce champ est requis"
              required
            />
          </div>

          <!-- With help text -->
          <div>
            <h4 class="font-semibold mb-2">Avec aide</h4>
            <AppInput
              v-model="demoInputs.help"
              type="text"
              label="Nom d'utilisateur"
              help-text="Entre 3 et 20 caractères"
              :maxlength="20"
            />
          </div>

          <!-- Textarea -->
          <div class="md:col-span-2">
            <h4 class="font-semibold mb-2">Textarea</h4>
            <AppInput
              v-model="demoInputs.textarea"
              type="textarea"
              label="Description"
              placeholder="Décrivez-vous"
              :rows="4"
              :maxlength="200"
              show-word-limit
            />
          </div>
        </div>
      </section>

      <!-- AppSelect -->
      <section id="selects" class="mb-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
          <i class="pi pi-list mr-2 text-purple-500"></i>
          AppSelect
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Select simple -->
          <div>
            <h4 class="font-semibold mb-2">Select simple</h4>
            <AppSelect
              v-model="demoSelects.simple"
              label="Pays"
              :options="countries"
              clearable
            />
          </div>

          <!-- Select filterable -->
          <div>
            <h4 class="font-semibold mb-2">Select avec recherche</h4>
            <AppSelect
              v-model="demoSelects.filterable"
              label="Framework"
              :options="frameworks"
              filterable
              clearable
            />
          </div>

          <!-- Select multiple -->
          <div>
            <h4 class="font-semibold mb-2">Select multiple</h4>
            <AppSelect
              v-model="demoSelects.multiple"
              label="Compétences"
              :options="skills"
              multiple
              collapse-tags
              clearable
            />
          </div>

          <!-- Cascader -->
          <div>
            <h4 class="font-semibold mb-2">Cascader (hiérarchique)</h4>
            <AppSelect
              v-model="demoSelects.cascader"
              cascader
              label="Localisation"
              :options="locations"
              clearable
            />
          </div>
        </div>
      </section>

      <!-- AppAlert -->
      <section id="alerts" class="mb-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
          <i class="pi pi-info-circle mr-2 text-orange-500"></i>
          AppAlert
        </h2>

        <div class="space-y-4">
          <!-- Success -->
          <div>
            <h4 class="font-semibold mb-2">Success</h4>
            <AppAlert
              type="success"
              title="Succès"
              message="Votre opération a été effectuée avec succès"
              :model-value="true"
              variant="solid"
            />
          </div>

          <!-- Error -->
          <div>
            <h4 class="font-semibold mb-2">Error</h4>
            <AppAlert
              type="error"
              title="Erreur"
              message="Une erreur s'est produite lors du traitement"
              :model-value="true"
              variant="light"
            />
          </div>

          <!-- Warning -->
          <div>
            <h4 class="font-semibold mb-2">Warning</h4>
            <AppAlert
              type="warning"
              title="Attention"
              message="Cette action nécessite votre attention"
              :model-value="true"
              variant="outline"
            />
          </div>

          <!-- Info -->
          <div>
            <h4 class="font-semibold mb-2">Info</h4>
            <AppAlert
              type="info"
              message="Information importante à prendre en compte"
              :model-value="true"
            />
          </div>
        </div>
      </section>

      <!-- Guide des couleurs -->
      <section class="mb-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-3">
          <i class="pi pi-palette mr-2 text-pink-500"></i>
          Palette de couleurs
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <div class="text-center">
            <div class="w-full h-20 bg-blue-500 rounded mb-2"></div>
            <p class="text-sm">Primary</p>
            <code class="text-xs text-gray-500">blue-500</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-gray-500 rounded mb-2"></div>
            <p class="text-sm">Secondary</p>
            <code class="text-xs text-gray-500">gray-500</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-red-500 rounded mb-2"></div>
            <p class="text-sm">Danger</p>
            <code class="text-xs text-gray-500">red-500</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-green-500 rounded mb-2"></div>
            <p class="text-sm">Success</p>
            <code class="text-xs text-gray-500">green-500</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-yellow-500 rounded mb-2"></div>
            <p class="text-sm">Warning</p>
            <code class="text-xs text-gray-500">yellow-500</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-amber-200 rounded mb-2"></div>
            <p class="text-sm">Profile</p>
            <code class="text-xs text-gray-500">amber-200</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-pink-400 rounded mb-2"></div>
            <p class="text-sm">Auth</p>
            <code class="text-xs text-gray-500">pink-400</code>
          </div>
          <div class="text-center">
            <div class="w-full h-20 bg-purple-500 rounded mb-2"></div>
            <p class="text-sm">Accent</p>
            <code class="text-xs text-gray-500">purple-500</code>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppButton from '../components/ui/AppButton.vue'
import AppInput from '../components/ui/AppInput.vue'
import AppSelect from '../components/ui/AppSelect.vue'
import AppAlert from '../components/ui/AppAlert.vue'

// Demo data
const demoInputs = ref({
  text: '',
  email: '',
  password: '',
  number: '',
  error: '',
  help: '',
  textarea: ''
})

const demoSelects = ref({
  simple: '',
  filterable: '',
  multiple: [],
  cascader: []
})

const countries = [
  { label: 'France', value: 'fr' },
  { label: 'Espagne', value: 'es' },
  { label: 'Italie', value: 'it' },
  { label: 'Allemagne', value: 'de' }
]

const frameworks = [
  { label: 'Vue.js', value: 'vue' },
  { label: 'React', value: 'react' },
  { label: 'Angular', value: 'angular' },
  { label: 'Svelte', value: 'svelte' }
]

const skills = [
  { label: 'JavaScript', value: 'js' },
  { label: 'TypeScript', value: 'ts' },
  { label: 'PHP', value: 'php' },
  { label: 'Python', value: 'py' }
]

const locations = [
  {
    label: 'Europe',
    value: 'eu',
    children: [
      {
        label: 'France',
        value: 'fr',
        children: [
          { label: 'Paris', value: 'paris' },
          { label: 'Lyon', value: 'lyon' }
        ]
      },
      {
        label: 'Espagne',
        value: 'es',
        children: [
          { label: 'Madrid', value: 'madrid' },
          { label: 'Barcelone', value: 'barcelona' }
        ]
      }
    ]
  }
]
</script>
