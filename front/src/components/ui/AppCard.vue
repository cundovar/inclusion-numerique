<template>
  <div :class="cardClasses">
    <!-- Border decoratif en haut (optionnel) -->
    <div v-if="showTopBorder" class="card-top-border"></div>

    <!-- Header -->
    <div class="card-header">
      <slot name="header">
        <div class="card-icon">
          <i :class="icon"></i>
        </div>
        <h3 class="card-title">{{ title }}</h3>
      </slot>
    </div>

    <!-- Contenu principal -->
    <div class="card-content">
      <slot>
        <div v-if="items && items.length > 0" class="card-items-list">
          <div
            v-for="(item, index) in items"
            :key="item.id || index"
            class="card-item"
          >
            <slot name="item" :item="item" :index="index">
              <!-- Contenu par défaut de l'item -->
              <div class="item-content">
                <div class="item-info">
                  <i v-if="item.icon" :class="[item.icon, 'item-icon']"></i>
                  <span class="item-label">{{ item.label || item.title || item.name }}</span>
                </div>
                <i v-if="clickable" class="fas fa-arrow-right item-arrow"></i>
              </div>
            </slot>
          </div>
        </div>
      </slot>
    </div>

    <!-- Footer -->
    <div v-if="showFooter" class="card-footer">
      <slot name="footer">
        <div class="footer-count">
          <i :class="footerIcon"></i>
          <span>{{ items.length }} {{ footerText }}</span>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Titre de la carte
  title: {
    type: String,
    required: true
  },

  // Icône dans le header
  icon: {
    type: String,
    default: 'fas fa-code'
  },

  // Liste d'items à afficher
  items: {
    type: Array,
    default: () => []
  },

  // Items cliquables
  clickable: {
    type: Boolean,
    default: false
  },

  // Afficher le footer
  showFooter: {
    type: Boolean,
    default: false
  },

  // Afficher la bordure décorative en haut
  showTopBorder: {
    type: Boolean,
    default: false
  },

  // Texte du footer
  footerText: {
    type: String,
    default: 'items'
  },

  // Icône du footer
  footerIcon: {
    type: String,
    default: 'fas fa-list'
  },

  // Classe CSS additionnelle pour la bordure gauche
  borderClass: {
    type: String,
    default: 'border-l-pink-500'
  }
})

const emit = defineEmits(['item-click'])

// Classes de la carte
const cardClasses = computed(() => {
  return [
    'course-card',
    'border',
    props.borderClass
  ]
})
</script>

<style scoped>
.course-card {
  color: black;
  border-radius: 20px;
  padding: 24px;
  margin: 16px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  min-height: 350px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.card-top-border {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #f9ca24);
  border-radius: 20px 20px 0 0;
}

.course-card:hover {
  transform: translateY(-1px);
  background-color: var(--primary-color);
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  border-bottom: 1px solid rgba(97, 95, 95, 0.5);
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 15px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  backdrop-filter: blur(10px);
  background-color: var(--primary-color);
}

.card-icon i {
  font-size: 24px;
  color: var(--color-text);
}

.card-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--color-text);
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.card-content {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 20px;
}

.card-items-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.card-item {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.item-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 18px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.item-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.item-icon {
  font-size: 16px;
  color: var(--color-text);
  margin-right: 12px;
  width: 20px;
  text-align: center;
}

.item-label {
  font-size: 16px;
  font-weight: 500;
  color: var(--color-text);
  line-height: 1.4;
}

.item-arrow {
  font-size: 14px;
  color: var(--color-text);
  transition: all 0.3s ease;
}

.card-item:hover .item-arrow {
  color: var(--color-text);
  transform: translateX(5px);
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-count {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: rgba(124, 121, 121, 0.8);
  font-weight: 500;
}

.footer-count i {
  margin-right: 8px;
  color: #4ecdc4;
}

/* Responsive */
@media screen and (min-width: 769px) {
  .course-card {
    width: calc(50% - 32px);
    max-width: 400px;
  }
}

@media screen and (max-width: 768px) {
  .course-card {
    width: calc(100% - 32px);
    margin: 8px 16px;
    padding: 20px;
  }

  .card-title {
    font-size: 20px;
  }

  .item-label {
    font-size: 14px;
  }
}

/* Scrollbar personnalisée */
.card-items-list::-webkit-scrollbar {
  width: 4px;
}

.card-items-list::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}

.card-items-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
}

.card-items-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>
