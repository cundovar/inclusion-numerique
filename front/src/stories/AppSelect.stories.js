import { ref } from "vue"
import AppSelect from "../components/ui/AppSelect.vue"

export default {
  title: "Components/AppSelect",
  component: AppSelect,
  tags: ["autodocs"],
  argTypes: {
    modelValue: { control: "text" },
    label: { control: "text" },
    placeholder: { control: "text" },
    size: {
      control: { type: "select" },
      options: ["large", "default", "small"]
    },
    multiple: { control: "boolean" },
    filterable: { control: "boolean" },
    clearable: { control: "boolean" },
    disabled: { control: "boolean" },
    required: { control: "boolean" },
    cascader: { control: "boolean" },
    collapseTags: { control: "boolean" },
    error: { control: "text" },
    helpText: { control: "text" },
    "onUpdate:modelValue": { action: "update:modelValue" },
    onChange: { action: "change" },
    onBlur: { action: "blur" },
    onFocus: { action: "focus" }
  }
}

// Select par défaut
export const Default = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("")
      const options = [
        { label: "Option 1", value: "1" },
        { label: "Option 2", value: "2" },
        { label: "Option 3", value: "3" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Sélectionnez une option"
          :options="options"
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// Tailles
export const Sizes = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("")
      const options = [
        { label: "Option 1", value: "1" },
        { label: "Option 2", value: "2" },
        { label: "Option 3", value: "3" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppSelect v-model="value" label="Small" size="small" :options="options" />
        <AppSelect v-model="value" label="Default" size="default" :options="options" />
        <AppSelect v-model="value" label="Large" size="large" :options="options" />
      </div>
    `
  })
}

// Clearable
export const Clearable = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("2")
      const options = [
        { label: "Vue.js", value: "1" },
        { label: "React", value: "2" },
        { label: "Angular", value: "3" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Framework"
          placeholder="Sélectionnez un framework"
          :options="options"
          clearable
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value }}</p>
      </div>
    `
  })
}

// Filterable
export const Filterable = {
  render: () => ({
    components: { AppSelect },
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
        { label: "Java", value: "java" },
        { label: "C++", value: "cpp" },
        { label: "C#", value: "cs" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Langage de programmation"
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

// Multiple
export const Multiple = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref([])
      const options = [
        { label: "HTML", value: "html" },
        { label: "CSS", value: "css" },
        { label: "JavaScript", value: "js" },
        { label: "Vue.js", value: "vue" },
        { label: "React", value: "react" },
        { label: "Angular", value: "angular" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
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

// Multiple avec collapse tags
export const MultipleCollapseTags = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref(["html", "css", "js", "vue", "react"])
      const options = [
        { label: "HTML", value: "html" },
        { label: "CSS", value: "css" },
        { label: "JavaScript", value: "js" },
        { label: "Vue.js", value: "vue" },
        { label: "React", value: "react" },
        { label: "Angular", value: "angular" },
        { label: "Svelte", value: "svelte" },
        { label: "Next.js", value: "next" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Technologies (avec collapse)"
          placeholder="Sélectionnez plusieurs technologies"
          :options="options"
          multiple
          clearable
          collapse-tags
          collapse-tags-tooltip
        />
        <p class="mt-4 text-sm text-gray-600">{{ value.length }} technologie(s) sélectionnée(s)</p>
      </div>
    `
  })
}

// États
export const States = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const normal = ref("")
      const disabled = ref("2")
      const options = [
        { label: "Option 1", value: "1" },
        { label: "Option 2", value: "2" },
        { label: "Option 3", value: "3" }
      ]
      return { normal, disabled, options }
    },
    template: `
      <div style="max-width: 400px;" class="space-y-4">
        <AppSelect v-model="normal" label="Normal" :options="options" />
        <AppSelect v-model="disabled" label="Désactivé" :options="options" disabled />
      </div>
    `
  })
}

// Avec erreur
export const WithError = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("")
      const options = [
        { label: "France", value: "fr" },
        { label: "Belgique", value: "be" },
        { label: "Suisse", value: "ch" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Pays"
          placeholder="Sélectionnez votre pays"
          :options="options"
          required
          error="Veuillez sélectionner un pays"
        />
      </div>
    `
  })
}

// Avec texte d'aide
export const WithHelpText = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("")
      const options = [
        { label: "Public", value: "public" },
        { label: "Privé", value: "private" },
        { label: "Amis uniquement", value: "friends" }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Visibilité"
          placeholder="Choisir la visibilité"
          :options="options"
          help-text="Contrôlez qui peut voir votre profil"
        />
      </div>
    `
  })
}

// Options groupées (avec cascader)
export const Cascader = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref([])
      const options = [
        {
          value: "frontend",
          label: "Frontend",
          children: [
            { value: "vue", label: "Vue.js" },
            { value: "react", label: "React" },
            { value: "angular", label: "Angular" }
          ]
        },
        {
          value: "backend",
          label: "Backend",
          children: [
            { value: "node", label: "Node.js" },
            { value: "php", label: "PHP" },
            { value: "python", label: "Python" }
          ]
        },
        {
          value: "database",
          label: "Database",
          children: [
            { value: "mysql", label: "MySQL" },
            { value: "mongodb", label: "MongoDB" },
            { value: "postgresql", label: "PostgreSQL" }
          ]
        }
      ]
      return { value, options }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Catégorie et technologie"
          placeholder="Sélectionnez une technologie"
          :options="options"
          cascader
          clearable
        />
        <p class="mt-4 text-sm text-gray-600">Valeur: {{ value.join(" > ") }}</p>
      </div>
    `
  })
}

// Cas d'usage: Sélection de pays
export const UseCaseCountry = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const value = ref("")
      const countries = [
        { label: "🇫🇷 France", value: "FR" },
        { label: "🇧🇪 Belgique", value: "BE" },
        { label: "🇨🇭 Suisse", value: "CH" },
        { label: "🇨🇦 Canada", value: "CA" },
        { label: "🇺🇸 États-Unis", value: "US" },
        { label: "🇬🇧 Royaume-Uni", value: "GB" },
        { label: "🇩🇪 Allemagne", value: "DE" },
        { label: "🇪🇸 Espagne", value: "ES" },
        { label: "🇮🇹 Italie", value: "IT" },
        { label: "🇵🇹 Portugal", value: "PT" }
      ]
      return { value, countries }
    },
    template: `
      <div style="max-width: 400px;">
        <AppSelect
          v-model="value"
          label="Pays"
          placeholder="Sélectionnez votre pays"
          :options="countries"
          filterable
          clearable
        />
      </div>
    `
  })
}

// Cas d'usage: Formulaire complet
export const CompleteForm = {
  render: () => ({
    components: { AppSelect },
    setup() {
      const form = ref({
        framework: "",
        languages: [],
        level: "",
        timezone: []
      })

      const frameworks = [
        { label: "Vue.js", value: "vue" },
        { label: "React", value: "react" },
        { label: "Angular", value: "angular" },
        { label: "Svelte", value: "svelte" }
      ]

      const languages = [
        { label: "JavaScript", value: "js" },
        { label: "TypeScript", value: "ts" },
        { label: "Python", value: "py" },
        { label: "PHP", value: "php" },
        { label: "Ruby", value: "rb" },
        { label: "Go", value: "go" }
      ]

      const levels = [
        { label: "Débutant", value: "beginner" },
        { label: "Intermédiaire", value: "intermediate" },
        { label: "Avancé", value: "advanced" },
        { label: "Expert", value: "expert" }
      ]

      const timezones = [
        {
          value: "europe",
          label: "Europe",
          children: [
            { value: "paris", label: "Paris (GMT+1)" },
            { value: "london", label: "London (GMT+0)" },
            { value: "berlin", label: "Berlin (GMT+1)" }
          ]
        },
        {
          value: "america",
          label: "America",
          children: [
            { value: "newyork", label: "New York (GMT-5)" },
            { value: "losangeles", label: "Los Angeles (GMT-8)" },
            { value: "toronto", label: "Toronto (GMT-5)" }
          ]
        }
      ]

      return { form, frameworks, languages, levels, timezones }
    },
    template: `
      <div style="max-width: 500px;">
        <h3 class="text-lg font-bold mb-4">Profil développeur</h3>
        <div class="space-y-4">
          <AppSelect
            v-model="form.framework"
            label="Framework préféré"
            placeholder="Choisissez un framework"
            :options="frameworks"
            required
          />

          <AppSelect
            v-model="form.languages"
            label="Langages de programmation"
            placeholder="Sélectionnez vos langages"
            :options="languages"
            multiple
            filterable
            clearable
            collapse-tags
          />

          <AppSelect
            v-model="form.level"
            label="Niveau d'expérience"
            placeholder="Sélectionnez votre niveau"
            :options="levels"
            help-text="Évaluez honnêtement votre niveau"
          />

          <AppSelect
            v-model="form.timezone"
            label="Fuseau horaire"
            placeholder="Sélectionnez votre zone"
            :options="timezones"
            cascader
            clearable
          />

          <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Enregistrer
          </button>
        </div>
      </div>
    `
  })
}
