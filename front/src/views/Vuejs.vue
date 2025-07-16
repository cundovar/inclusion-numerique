<template>
  <div class="md:rounded-2xl md:mt-48 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full   bg-blue-200">
    <div class="">
      <div class="header-section">
        <div class="tech-header">
          <i class="fab fa-vuejs tech-icon"></i>
          <h1 class="tech-title">Vue.js</h1>
        </div>
        <p class="tech-description">Framework JavaScript progressif pour créer des interfaces utilisateur</p>
        
        <div class="framework-presentation">
          <p>
            Vue.js est un framework JavaScript progressif et accessible qui se concentre sur l'interface utilisateur. 
            Créé par Evan You, Vue.js combine le meilleur d'Angular et de React tout en restant simple et intuitif. 
            Il offre une courbe d'apprentissage douce, une documentation exceptionnelle et une architecture flexible 
            qui peut être adoptée de manière incrémentale. Avec sa syntaxe de template familière, son système de 
            réactivité puissant et son écosystème riche incluant Vue Router, Vuex/Pinia et Vue CLI, Vue.js permet 
            de créer des applications web modernes et performantes avec un code maintenable et élégant.
          </p>
        </div>
      </div>

      <div class="flex flex-wrap justify-center items-center">
        <CourseCard
          title="Installation"
          :menus="setupCourses"
          icon-class="fas fa-download"
        />
        
        <CourseCard
          title="Composants"
          :menus="componentCourses"
          icon-class="fas fa-puzzle-piece"
        />
        
        <CourseCard
          title="Réactivité"
          :menus="reactivityCourses"
          icon-class="fas fa-sync-alt"
        />
        
        <CourseCard
          title="Directives"
          :menus="directivesCourses"
          icon-class="fas fa-code"
        />
        
        <CourseCard
          title="Routing"
          :menus="routingCourses"
          icon-class="fas fa-route"
        />
        
        <CourseCard
          title="État Global"
          :menus="stateCourses"
          icon-class="fas fa-database"
        />
        
        <CourseCard
          title="API & HTTP"
          :menus="apiCourses"
          icon-class="fas fa-cloud"
        />
        
        <CourseCard
          title="Formulaires"
          :menus="formsCourses"
          icon-class="fas fa-wpforms"
        />
        
        <CourseCard
          title="Tests"
          :menus="testingCourses"
          icon-class="fas fa-vial"
        />
        
        <CourseCard
          title="Performances"
          :menus="performanceCourses"
          icon-class="fas fa-tachometer-alt"
        />
        
        <CourseCard
          title="Déploiement"
          :menus="deploymentCourses"
          icon-class="fas fa-rocket"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useData } from "../utlis/fetchDataPwa";
import { computed, watch } from "vue";
import CourseCard from "./components/CourseCard.vue";

const { menus } = useData();

watch(menus, (newVal) => {
  console.log("Menus mis à jour dans la page Vue.js :", newVal);
});

const filterMenus = computed(() =>
  menus.value.filter((menu) => menu.menu.label?.toUpperCase() === "VUE")
);

const setupCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("INSTALL") ||
    menu.title?.toUpperCase().includes("SETUP") ||
    menu.title?.toUpperCase().includes("CONFIGURATION")
  )
);

const componentCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("COMPONENT") ||
    menu.title?.toUpperCase().includes("COMPOSANT")
  )
);

const reactivityCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("REACTIVITY") ||
    menu.title?.toUpperCase().includes("RÉACTIVITÉ") ||
    menu.title?.toUpperCase().includes("REACTIVE") ||
    menu.title?.toUpperCase().includes("REF")
  )
);

const directivesCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("DIRECTIVE") ||
    menu.title?.toUpperCase().includes("V-IF") ||
    menu.title?.toUpperCase().includes("V-FOR") ||
    menu.title?.toUpperCase().includes("V-MODEL")
  )
);

const routingCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("ROUTING") ||
    menu.title?.toUpperCase().includes("ROUTER") ||
    menu.title?.toUpperCase().includes("NAVIGATION")
  )
);

const stateCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("STATE") ||
    menu.title?.toUpperCase().includes("VUEX") ||
    menu.title?.toUpperCase().includes("PINIA") ||
    menu.title?.toUpperCase().includes("STORE") ||
    menu.title?.toUpperCase().includes("ÉTAT")
  )
);

const apiCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("API") ||
    menu.title?.toUpperCase().includes("HTTP") ||
    menu.title?.toUpperCase().includes("FETCH") ||
    menu.title?.toUpperCase().includes("AXIOS")
  )
);

const formsCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("FORM") ||
    menu.title?.toUpperCase().includes("INPUT") ||
    menu.title?.toUpperCase().includes("FORMULAIRE")
  )
);

const testingCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("TEST") ||
    menu.title?.toUpperCase().includes("VITEST") ||
    menu.title?.toUpperCase().includes("JEST")
  )
);

const performanceCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("PERFORMANCE") ||
    menu.title?.toUpperCase().includes("LAZY") ||
    menu.title?.toUpperCase().includes("OPTIMISATION")
  )
);

const deploymentCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("DEPLOY") ||
    menu.title?.toUpperCase().includes("BUILD") ||
    menu.title?.toUpperCase().includes("PRODUCTION")
  )
);
</script>

<style scoped>
.vue-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #4fc08d 0%, #3a7f62 100%);
  padding: 20px;
  padding-bottom: 120px;
}

.header-section {
  text-align: center;
  padding: 40px 0;
  margin-bottom: 40px;
}

.tech-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  margin-bottom: 16px;
}

.tech-icon {
  font-size: 60px;
  color: #4fc08d;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
  transition: transform 0.3s ease;
}

.tech-icon:hover {
  transform: scale(1.1);
}

.tech-title {
  font-size: 48px;
  font-weight: 500;
  color: #2c3e50;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  margin: 0;
}

.tech-description {
  font-size: 18px;
  color: rgba(44, 62, 80, 0.8);
  margin: 0;
  font-weight: 500;
}

.framework-presentation {
  max-width: 800px;
  margin: 30px auto 0;
  padding: 25px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #4fc08d;
}

.framework-presentation p {
  font-size: 16px;
  line-height: 1.7;
  color: #444;
  margin: 0;
  text-align: justify;
  font-weight: 400;
}

.cards-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

@media screen and (max-width: 768px) {
  .tech-header {
    flex-direction: column;
    gap: 10px;
  }
  
  .tech-icon {
    font-size: 40px;
  }
  
  .tech-title {
    font-size: 32px;
  }
  
  .tech-description {
    font-size: 16px;
  }
  
  .framework-presentation {
    margin: 20px 10px 0;
    padding: 20px;
  }
  
  .framework-presentation p {
    font-size: 14px;
    line-height: 1.6;
    text-align: left;
  }
  
  .vue-container {
    padding: 16px;
    padding-bottom: 120px;
  }
}
</style>