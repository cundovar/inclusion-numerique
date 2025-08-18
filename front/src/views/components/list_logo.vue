<template>
  <div class="h-auto  max-xl:w-full xl:max-w-[15rem]">
    <div class="flex flex-wrap  xl:justify-center xl:items-center shadow-xxl  p-0 gap-3 w-full justify-end items-end ">
      <!-- GitHub -->
      <div class="tech-item">
        <a href="https://github.com/poleS-dev" target="_blank" class="tech-link">
          <div class="tech-icon-container github-hover">
            <i class="pi pi-github tech-icon"></i>
          </div>
        </a>
        <p class="tech-name">{{ capitalize("github") }}</p>
      </div>
      
      <!-- Logos des technologies -->
      <div 
        class="tech-item" 
        v-for="logo in logos" 
        :key="logo.alt"
      >
        <a :href="logo.src" target="_blank" class="tech-link">
          <div 
            class="tech-icon-container" 
            :class="`${logo.name}-hover`"
            @mouseenter="setHoverColor(logo.color)"
            @mouseleave="resetHoverColor"
          >
            <i :class="logo.icon" class="tech-icon"></i>
          </div>
        </a>
        <p class="tech-name">{{ capitalize(logo.name) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { capitalize } from "../../utlis/stringsUtlis";
import { logos } from "../../tab-object/list-logo";

// Fonctions pour gérer les couleurs de hover dynamiques
const setHoverColor = (color) => {
  // Optionnel : logique pour changer la couleur dynamiquement
  console.log(`Hover color: ${color}`);
};

const resetHoverColor = () => {
  // Optionnel : logique pour reset la couleur
  console.log('Reset hover color');
};
</script>

<style scoped>
.tech-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.tech-link {
  display: inline-block;
  text-decoration: none;
  transition: transform 0.3s ease;
}

.tech-link:hover {
  transform: scale(1.1);
}

.tech-icon-container {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  cursor: pointer;
}

.tech-icon {
 
  font-size: 2.3rem;

 
  transition: all 0.3s ease;
}
.tech-icon:hover {
  color: #ffffff;
}

.tech-name {
  text-align: center;
 
  font-weight: bold;
  font-size: 0.85rem;
  margin: 0;
  transition: color 0.3s ease;
}

/* Effets de hover spécifiques pour chaque technologie */
.html-hover:hover {
  background: #E34F26;
  box-shadow: 0 8px 25px rgba(227, 79, 38, 0.4);
}

.css-hover:hover {
  background: #1572B6;
  box-shadow: 0 8px 25px rgba(21, 114, 182, 0.4);
}

.js-hover:hover {
  background: #F7DF1E;
  box-shadow: 0 8px 25px rgba(247, 223, 30, 0.4);
}

.js-hover:hover .tech-icon {
  color: #000000;
}

.php-hover:hover {
  background: #777BB4;
  box-shadow: 0 8px 25px rgba(119, 123, 180, 0.4);
}

.symfony-hover:hover {
  background: #000000;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}

.bootstrap-hover:hover {
  background: #7952B3;
  box-shadow: 0 8px 25px rgba(121, 82, 179, 0.4);
}

.tailwind-hover:hover {
  background: #06B6D4;
  box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
}

.vue-hover:hover {
  background: #4FC08D;
  box-shadow: 0 8px 25px rgba(79, 192, 141, 0.4);
}

.react-hover:hover {
  background: #61DAFB;
  box-shadow: 0 8px 25px rgba(97, 218, 251, 0.4);
}

.react-hover:hover .tech-icon {
  color: #000000;
}

.node-hover:hover {
  background: #339933;
  box-shadow: 0 8px 25px rgba(51, 153, 51, 0.4);
}

.github-hover:hover {
  background: #333333;
  box-shadow: 0 8px 25px rgba(51, 51, 51, 0.4);
}

/* Animation des icônes spécifiques */
.react-hover:hover .tech-icon {
  animation: spin 2s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Hover sur le nom */
.tech-item:hover .tech-name {
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .tech-icon-container {
    width: 40px;
    height: 40px;
  }
  
  .tech-icon {
    font-size: 1.5rem;
  }
  
  .tech-name {
    font-size: 0.75rem;
  }
}
</style>
