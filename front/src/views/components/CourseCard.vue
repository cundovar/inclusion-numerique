<template>
  <div class="course-card">
    <div class="card-header">
      <div class="card-icon">
        <i :class="iconClass"></i>
      </div>
      <h3 class="card-title">{{ title }}</h3>
    </div>
    
    <div class="card-content">
      <div class="course-list">
        <div v-for="menu in menus" :key="menu.id" class="course-item">
          <router-link :to="`/pages${menu.page.slug}`" class="course-link">
            <div class="course-info">
              <i class="fas fa-file-code course-item-icon"></i>
              <span class="course-name">{{ menu.title }}</span>
            </div>
            <i class="fas fa-arrow-right course-arrow"></i>
          </router-link>
        </div>
      </div>
    </div>
    
    <div class="card-footer">
      <div class="course-count">
        <i class="fas fa-book"></i>
        <span>{{ menus.length }} cours</span>
      </div>
    </div>
  </div>
</template>

<script setup>
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

.course-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  /* background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #f9ca24); */
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

.course-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
 
}

.course-item {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}



.course-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
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
  color:var(--color-text);
  line-height: 1.4;
}

.course-arrow {
  font-size: 14px;
  color: var(--color-text);
  transition: all 0.3s ease;
}

.course-item:hover .course-arrow {
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

.course-count {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: rgba(124, 121, 121, 0.8);
  font-weight: 500;
}

.course-count i {
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
  
  .course-name {
    font-size: 14px;
  }
}

/* Scrollbar personnalisée */
.course-list::-webkit-scrollbar {
  width: 4px;
}

.course-list::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}

.course-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
}

.course-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}
</style>