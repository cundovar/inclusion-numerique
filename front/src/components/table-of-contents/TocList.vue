<template>
  <div>
    <!-- Liste des titres -->
    <transition name="slide">
      <ul v-show="isOpen" class="toc-list">
        <li
          v-for="item in toc"
          :key="item.id"
          :class="[
            'toc-item',
            `toc-level-${item.level}`,
            { 'active': item.id === activeId }
          ]"
        >
          <a
            @click.prevent="handleClick(item.id)"
            :href="`#${item.id}`"
            class="toc-link"
            :title="item.text"
          >
            <span class="toc-bullet">•</span>
            <span class="toc-text">{{ item.text }}</span>
          </a>
        </li>
      </ul>
    </transition>

    <!-- Indicateur de progression de lecture -->
    <div v-if="showProgress && isOpen" class="reading-progress">
      <div class="progress-text">Progression</div>
      <div class="progress-bar-container">
        <div
          class="progress-bar"
          :style="{ width: readingProgress + '%' }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

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
  isOpen: {
    type: Boolean,
    default: true
  },
  isMobile: {
    type: Boolean,
    default: false
  },
  collapsedOnMobile: {
    type: Boolean,
    default: true
  },
  bgCategorie: {
    type: String,
    default: '#f97316'
  }
})

const emit = defineEmits(['scroll-to', 'close-mobile'])

const readingProgress = ref(0)

// Style dynamique pour la barre de progression
const progressBarStyle = computed(() => ({
  width: readingProgress.value + '%',
  background: props.bgCategorie || '#f97316'
}))

const handleClick = (id) => {
  emit('scroll-to', id)

  // Sur mobile, fermer le sommaire après un clic
  if (props.isMobile && props.collapsedOnMobile) {
    setTimeout(() => {
      emit('close-mobile')
    }, 300)
  }
}

const calculateReadingProgress = () => {
  const windowHeight = window.innerHeight
  const documentHeight = document.documentElement.scrollHeight - windowHeight
  const scrolled = window.scrollY

  readingProgress.value = Math.round((scrolled / documentHeight) * 100)
}

onMounted(() => {
  window.addEventListener('scroll', calculateReadingProgress)
  calculateReadingProgress()
})

onUnmounted(() => {
  window.removeEventListener('scroll', calculateReadingProgress)
})
</script>

<style scoped>
.toc-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.toc-item {
  margin-bottom: 0.5rem;
  transition: all 0.2s ease;
}

/* Indentation selon le niveau */
.toc-level-2 {
  padding-left: 0;
}

.toc-level-3 {
  padding-left: 1.25rem;
}

/* Bordure gauche */
.toc-item {
  border-left: 3px solid transparent;
  padding-left: 0.75rem;
}

.toc-item.active {
  border-left-color: #333;
}

.toc-link {
  color: #555;
  text-decoration: none;
  font-size: 0.9rem;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.25rem 0;
  cursor: pointer;
  transition: all 0.2s;
  line-height: 1.4;
}

.toc-bullet {
  color: #999;
  font-size: 1.2rem;
  line-height: 1;
  margin-top: 2px;
  transition: all 0.2s;
}

.toc-text {
  flex: 1;
}

.toc-link:hover {
  color: #333;
}

.toc-link:hover .toc-bullet {
  color: #333;
  transform: translateX(3px);
}

.toc-item.active .toc-link {
  color: #333;
  font-weight: 600;
}

.toc-item.active .toc-bullet {
  color: #333;
}

/* Progression de lecture */
.reading-progress {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}

.progress-text {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.progress-bar-container {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: #f97316;
  transition: width 0.3s ease;
  border-radius: 3px;
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
</style>
