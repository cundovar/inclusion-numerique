import { ref } from "vue"
import AppInput from "../components/commun/input/AppInput.vue"

export default {
  title: "Components/AppInput",
  component: AppInput,
  tags: ["autodocs"],
  argTypes: {
    modelValue: { control: "text" },
    type: {
      control: { type: "select" },
      options: ["text", "password", "email", "number", "textarea", "select"]
    },
    label: { control: "text" },
    placeholder: { control: "text" },
    size: {
      control: { type: "select" },
      options: ["large", "default", "small"]
    },
    disabled: { control: "boolean" },
    readonly: { control: "boolean" },
    required: { control: "boolean" },
    clearable: { control: "boolean" },
    maxlength: { control: "number" },
    prefixIcon: { control: "text" },
    suffixIcon: { control: "text" },
    rows: { control: "number" },
    showWordLimit: { control: "boolean" },
    error: { control: "text" },
    helpText: { control: "text" },
    "onUpdate:modelValue": { action: "update:modelValue" },
    onChange: { action: "change" },
    onBlur: { action: "blur" },
    onFocus: { action: "focus" }
  }
}

// Input par défaut
export const Default = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Nom complet"
          placeholder="Entrez votre nom"
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// Tous les types
export const AllTypes = {
  render: () => ({
    components: { AppInput },
    setup() {
      const text = ref("")
      const email = ref("")
      const password = ref("")
      const number = ref("")
      const textarea = ref("")
      return { text, email, password, number, textarea }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppInput v-model="text" type="text" label="Texte" placeholder="Texte simple" />
        <AppInput v-model="email" type="email" label="Email" placeholder="exemple@email.com" />
        <AppInput v-model="password" type="password" label="Mot de passe" placeholder="••••••••" />
        <AppInput v-model="number" type="number" label="Nombre" placeholder="123" />
        <AppInput v-model="textarea" type="textarea" label="Textarea" placeholder="Plusieurs lignes..." />
      </div>
    `
  })
}

// Tailles
export const Sizes = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppInput v-model="value" size="small" label="Small" placeholder="Petite taille" />
        <AppInput v-model="value" size="default" label="Default" placeholder="Taille par défaut" />
        <AppInput v-model="value" size="large" label="Large" placeholder="Grande taille" />
      </div>
    `
  })
}

// Avec icônes
export const WithIcons = {
  render: () => ({
    components: { AppInput },
    setup() {
      const search = ref("")
      const email = ref("")
      const location = ref("")
      return { search, email, location }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppInput
          v-model="search"
          label="Rechercher"
          placeholder="Rechercher..."
          prefix-icon="Search"
        />
        <AppInput
          v-model="email"
          label="Email"
          placeholder="Email"
          prefix-icon="Message"
        />
        <AppInput
          v-model="location"
          label="Localisation"
          placeholder="Ville"
          prefix-icon="Location"
          suffix-icon="ArrowRight"
        />
      </div>
    `
  })
}

// Clearable
export const Clearable = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("Texte à effacer")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Input effaçable"
          placeholder="Entrez du texte"
          clearable
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// États
export const States = {
  render: () => ({
    components: { AppInput },
    setup() {
      const normal = ref("")
      const disabled = ref("Input désactivé")
      const readonly = ref("Input en lecture seule")
      return { normal, disabled, readonly }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppInput v-model="normal" label="Normal" placeholder="Input normal" />
        <AppInput v-model="disabled" label="Désactivé" disabled />
        <AppInput v-model="readonly" label="Lecture seule" readonly />
      </div>
    `
  })
}

// Requis
export const Required = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Champ requis"
          placeholder="Ce champ est obligatoire"
          required
        />
      </div>
    `
  })
}

// Avec erreur
export const WithError = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Email"
          placeholder="exemple@email.com"
          error="Format d'email invalide"
        />
      </div>
    `
  })
}

// Avec texte d'aide
export const WithHelpText = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Mot de passe"
          type="password"
          placeholder="••••••••"
          help-text="Le mot de passe doit contenir au moins 8 caractères"
        />
      </div>
    `
  })
}

// Avec limite de caractères
export const WithMaxLength = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Bio"
          type="textarea"
          placeholder="Parlez-nous de vous..."
          :maxlength="200"
          show-word-limit
          :rows="4"
        />
      </div>
    `
  })
}

// Textarea avec autosize
export const TextareaAutosize = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      return { value }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          label="Message"
          type="textarea"
          placeholder="Écrivez votre message..."
          :autosize="{ minRows: 2, maxRows: 6 }"
        />
      </div>
    `
  })
}

// Select
export const Select = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      const options = [
        { label: "Vue.js", value: "vue" },
        { label: "React", value: "react" },
        { label: "Angular", value: "angular" },
        { label: "Svelte", value: "svelte" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          type="select"
          label="Framework"
          placeholder="Sélectionnez un framework"
          :options="options"
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// Select filtrable
export const SelectFilterable = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref("")
      const options = [
        { label: "JavaScript", value: "js" },
        { label: "TypeScript", value: "ts" },
        { label: "Python", value: "py" },
        { label: "PHP", value: "php" },
        { label: "Ruby", value: "rb" },
        { label: "Go", value: "go" },
        { label: "Rust", value: "rust" },
        { label: "Java", value: "java" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          type="select"
          label="Langage"
          placeholder="Rechercher un langage..."
          :options="options"
          filterable
          clearable
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// Select multiple
export const SelectMultiple = {
  render: () => ({
    components: { AppInput },
    setup() {
      const value = ref([])
      const options = [
        { label: "HTML", value: "html" },
        { label: "CSS", value: "css" },
        { label: "JavaScript", value: "js" },
        { label: "Vue.js", value: "vue" },
        { label: "React", value: "react" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppInput
          v-model="value"
          type="select"
          label="Technologies"
          placeholder="Sélectionnez plusieurs technologies"
          :options="options"
          multiple
          clearable
        />
        <p class="mt-4 text-sm text-gray-600">Valeurs: {{ value.join(", ") }}</p>
      </div>
    `
  })
}

// Formulaire complet
export const CompleteForm = {
  render: () => ({
    components: { AppInput },
    setup() {
      const form = ref({
        firstName: "",
        lastName: "",
        email: "",
        password: "",
        country: "",
        bio: ""
      })

      const countries = [
        { label: "France", value: "fr" },
        { label: "Belgique", value: "be" },
        { label: "Suisse", value: "ch" },
        { label: "Canada", value: "ca" }
      ]

      return { form, countries }
    },
    template: `
      <div style="max-width: 500px;">
        <h3 class="text-lg font-bold mb-4">Formulaire d'inscription</h3>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <AppInput
              v-model="form.firstName"
              label="Prénom"
              placeholder="John"
              required
            />
            <AppInput
              v-model="form.lastName"
              label="Nom"
              placeholder="Doe"
              required
            />
          </div>

          <AppInput
            v-model="form.email"
            type="email"
            label="Email"
            placeholder="john.doe@example.com"
            prefix-icon="Message"
            required
            help-text="Nous ne partagerons jamais votre email"
          />

          <AppInput
            v-model="form.password"
            type="password"
            label="Mot de passe"
            placeholder="••••••••"
            required
            help-text="Au moins 8 caractères"
          />

          <AppInput
            v-model="form.country"
            type="select"
            label="Pays"
            placeholder="Sélectionnez votre pays"
            :options="countries"
            filterable
          />

          <AppInput
            v-model="form.bio"
            type="textarea"
            label="Biographie"
            placeholder="Parlez-nous de vous..."
            :maxlength="300"
            show-word-limit
            :autosize="{ minRows: 3, maxRows: 6 }"
          />

          <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            S'inscrire
          </button>
        </div>
      </div>
    `
  })
}
