import AppBadge from "../components/commun/badge/AppBadge.vue"

export default {
  title: "Components/AppBadge",
  component: AppBadge,
  tags: ["autodocs"],
  argTypes: {
    variant: {
      control: { type: "select" },
      options: ["default", "primary", "secondary", "success", "warning", "danger", "info", "dark"]
    },
    size: {
      control: { type: "select" },
      options: ["xs", "sm", "md", "lg", "xl"]
    },
    rounded: {
      control: { type: "select" },
      options: ["none", "sm", "md", "lg", "full"]
    },
    icon: {
      control: "text"
    },
    dot: {
      control: "boolean"
    },
    removable: {
      control: "boolean"
    },
    clickable: {
      control: "boolean"
    },
    outline: {
      control: "boolean"
    },
    onClick: { action: "clicked" },
    onRemove: { action: "removed" }
  }
}

export const Default = {
  args: {
    default: "Badge"
  },
  render: (args) => ({
    components: { AppBadge },
    setup() {
      return { args }
    },
    template: "<AppBadge v-bind=\"args\">{{ args.default }}</AppBadge>"
  })
}

export const AllVariants = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="default">Default</AppBadge>
        <AppBadge variant="primary">Primary</AppBadge>
        <AppBadge variant="secondary">Secondary</AppBadge>
        <AppBadge variant="success">Success</AppBadge>
        <AppBadge variant="warning">Warning</AppBadge>
        <AppBadge variant="danger">Danger</AppBadge>
        <AppBadge variant="info">Info</AppBadge>
        <AppBadge variant="dark">Dark</AppBadge>
      </div>
    `
  })
}

export const AllSizes = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <AppBadge variant="primary" size="xs">Extra Small</AppBadge>
        <AppBadge variant="primary" size="sm">Small</AppBadge>
        <AppBadge variant="primary" size="md">Medium</AppBadge>
        <AppBadge variant="primary" size="lg">Large</AppBadge>
        <AppBadge variant="primary" size="xl">Extra Large</AppBadge>
      </div>
    `
  })
}

export const WithIcons = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="primary" icon="fas fa-star">Featured</AppBadge>
        <AppBadge variant="success" icon="fas fa-check">Completed</AppBadge>
        <AppBadge variant="danger" icon="fas fa-times">Error</AppBadge>
        <AppBadge variant="warning" icon="fas fa-exclamation">Warning</AppBadge>
        <AppBadge variant="info" icon="fas fa-info-circle">Info</AppBadge>
      </div>
    `
  })
}

export const WithDots = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="success" dot>En ligne</AppBadge>
        <AppBadge variant="danger" dot>Hors ligne</AppBadge>
        <AppBadge variant="warning" dot>Occupé</AppBadge>
        <AppBadge variant="default" dot>Inactif</AppBadge>
      </div>
    `
  })
}

export const Outline = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="default" outline>Default</AppBadge>
        <AppBadge variant="primary" outline>Primary</AppBadge>
        <AppBadge variant="success" outline>Success</AppBadge>
        <AppBadge variant="warning" outline>Warning</AppBadge>
        <AppBadge variant="danger" outline>Danger</AppBadge>
        <AppBadge variant="info" outline>Info</AppBadge>
      </div>
    `
  })
}

export const Rounded = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <AppBadge variant="primary" rounded="none">None</AppBadge>
        <AppBadge variant="primary" rounded="sm">Small</AppBadge>
        <AppBadge variant="primary" rounded="md">Medium</AppBadge>
        <AppBadge variant="primary" rounded="lg">Large</AppBadge>
        <AppBadge variant="primary" rounded="full">Full</AppBadge>
      </div>
    `
  })
}

export const Removable = {
  render: () => ({
    components: { AppBadge },
    setup() {
      return {
        handleRemove: (event) => {
          console.log("Badge removed", event)
          alert("Badge supprimé!")
        }
      }
    },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="primary" removable @remove="handleRemove">Vue.js</AppBadge>
        <AppBadge variant="success" removable @remove="handleRemove">JavaScript</AppBadge>
        <AppBadge variant="info" removable @remove="handleRemove">CSS</AppBadge>
        <AppBadge variant="warning" removable @remove="handleRemove">HTML</AppBadge>
      </div>
    `
  })
}

export const Clickable = {
  render: () => ({
    components: { AppBadge },
    setup() {
      return {
        handleClick: (event) => {
          console.log("Badge clicked", event)
          alert("Badge cliqué!")
        }
      }
    },
    template: `
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <AppBadge variant="primary" clickable @click="handleClick">Cliquable</AppBadge>
        <AppBadge variant="success" clickable @click="handleClick">Clique-moi</AppBadge>
        <AppBadge variant="info" clickable icon="fas fa-hand-pointer" @click="handleClick">Action</AppBadge>
      </div>
    `
  })
}

export const UseCaseCategories = {
  render: () => ({
    components: { AppBadge },
    setup() {
      const categories = [
        { id: 1, name: "JavaScript", variant: "warning" },
        { id: 2, name: "Vue.js", variant: "success" },
        { id: 3, name: "PHP", variant: "primary" },
        { id: 4, name: "Symfony", variant: "dark" },
        { id: 5, name: "CSS", variant: "info" }
      ]
      return { categories }
    },
    template: `
      <div>
        <h3 style="margin-bottom: 12px;">Catégories de cours</h3>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <AppBadge
            v-for="cat in categories"
            :key="cat.id"
            :variant="cat.variant"
            size="sm"
            rounded="full"
          >
            {{ cat.name }}
          </AppBadge>
        </div>
      </div>
    `
  })
}

export const UseCaseUserStatus = {
  render: () => ({
    components: { AppBadge },
    setup() {
      const users = [
        { id: 1, name: "Alice", online: true },
        { id: 2, name: "Bob", online: false },
        { id: 3, name: "Charlie", online: true },
        { id: 4, name: "Diana", online: false }
      ]
      return { users }
    },
    template: `
      <div>
        <h3 style="margin-bottom: 12px;">Statut des utilisateurs</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <div v-for="user in users" :key="user.id" style="display: flex; align-items: center; gap: 12px;">
            <span style="min-width: 80px;">{{ user.name }}</span>
            <AppBadge
              :variant="user.online ? 'success' : 'default'"
              dot
              size="sm"
            >
              {{ user.online ? "En ligne" : "Hors ligne" }}
            </AppBadge>
          </div>
        </div>
      </div>
    `
  })
}

export const UseCaseCodeTags = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div>
        <h3 style="margin-bottom: 12px;">Langages de programmation</h3>
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
          <AppBadge variant="danger" size="xs" rounded="sm">HTML</AppBadge>
          <AppBadge variant="info" size="xs" rounded="sm">CSS</AppBadge>
          <AppBadge variant="warning" size="xs" rounded="sm">JavaScript</AppBadge>
          <AppBadge variant="success" size="xs" rounded="sm">Vue.js</AppBadge>
          <AppBadge variant="primary" size="xs" rounded="sm">PHP</AppBadge>
          <AppBadge variant="dark" size="xs" rounded="sm">Symfony</AppBadge>
        </div>
      </div>
    `
  })
}

export const UseCaseNotifications = {
  render: () => ({
    components: { AppBadge },
    template: `
      <div style="display: flex; gap: 16px; align-items: center;">
        <div style="position: relative; display: inline-block;">
          <i class="fas fa-bell" style="font-size: 24px;"></i>
          <AppBadge
            variant="danger"
            size="xs"
            rounded="full"
            style="position: absolute; top: -4px; right: -8px;"
          >
            3
          </AppBadge>
        </div>
        <div style="position: relative; display: inline-block;">
          <i class="fas fa-envelope" style="font-size: 24px;"></i>
          <AppBadge
            variant="primary"
            size="xs"
            rounded="full"
            style="position: absolute; top: -4px; right: -8px;"
          >
            12
          </AppBadge>
        </div>
        <div style="position: relative; display: inline-block;">
          <i class="fas fa-comment" style="font-size: 24px;"></i>
          <AppBadge
            variant="success"
            size="xs"
            rounded="full"
            style="position: absolute; top: -4px; right: -8px;"
          >
            5
          </AppBadge>
        </div>
      </div>
    `
  })
}
