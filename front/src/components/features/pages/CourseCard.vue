<template>
  <AppCard
    :title="title"
    :icon="iconClass"
    :items="menus"
    :showFooter="true"
    :showTopBorder="false"
    footerText="cours"
    footerIcon="fas fa-book"
    borderClass="border-l-pink-500"
  >
    <template #item="{ item }">
      <router-link :to="`/pages${item.page.slug}`" class="course-link">
        <div class="course-info">
          <i class="fas fa-file-code course-item-icon"></i>
          <span class="course-name"> {{ item.title }}</span>
        </div>
        <i class="fas fa-arrow-right course-arrow"></i>
      </router-link>
    </template>
  </AppCard>
</template>

<script setup>
import AppCard from '@/components/ui/AppCard.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  menus: {
    type: Array,
    required: true
  },
  iconClass: {
    type: String,
    default: 'fas fa-code'
  }
})
</script>

<style scoped>
/* Styles spécifiques pour CourseCard */
.course-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 15px 18px;
  text-decoration: none;
  color: inherit;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.course-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.course-item-icon {
  font-size: 16px;
  color: var(--color-text);
  margin-right: 12px;
  width: 20px;
  text-align: center;
}

.course-name {
  font-size: 16px;
  font-weight: 500;
  color: var(--color-text);
  line-height: 1.4;
}

.course-arrow {
  font-size: 14px;
  color: var(--color-text);
  transition: all 0.3s ease;
}

.course-link:hover .course-arrow {
  color: var(--color-text);
  transform: translateX(5px);
}
</style>
