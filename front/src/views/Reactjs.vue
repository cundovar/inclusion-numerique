<template>
  <div class="md:rounded-2xl md:mt-48 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full bg-blue-200">
    <div class="">
      <div class="header-section">
        <div class="tech-header">
          <i class="fab fa-react tech-icon"></i>
          <h1 class="tech-title">React</h1>
        </div>
        <p class="tech-description">Bibliothèque JavaScript pour créer des interfaces utilisateur</p>
        
        <div class="framework-presentation">
          <p>
            React est une bibliothèque JavaScript développée par Facebook pour créer des interfaces utilisateur interactives et performantes. 
            Basé sur un paradigme de composants réutilisables, React utilise un DOM virtuel pour optimiser les performances et offre une 
            approche déclarative pour construire des applications web modernes. Avec son écosystème riche incluant React Router pour la navigation, 
            Redux pour la gestion d'état et une multitude d'outils de développement, React est devenu l'un des frameworks front-end les plus 
            populaires au monde pour créer des applications web scalables et maintenables.
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
          title="Hooks"
          :menus="hooksCourses"
          icon-class="fas fa-link"
        />
        
        <CourseCard
          title="Formulaires"
          :menus="formsCourses"
          icon-class="fas fa-wpforms"
        />
        
        <CourseCard
          title="Routing"
          :menus="routingCourses"
          icon-class="fas fa-route"
        />
        
        <CourseCard
          title="API & HTTP"
          :menus="apiCourses"
          icon-class="fas fa-cloud"
        />
        
        <CourseCard
          title="État Global"
          :menus="stateCourses"
          icon-class="fas fa-database"
        />
        
        <CourseCard
          title="Tests"
          :menus="testingCourses"
          icon-class="fas fa-vial"
        />
        
        <CourseCard
          title="Styling"
          :menus="stylingCourses"
          icon-class="fas fa-paint-brush"
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
  console.log("Menus mis à jour dans la page React :", newVal);
});

const filterMenus = computed(() =>
  menus.value.filter((menu) => menu.menu.label?.toUpperCase() === "REACT")
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

const hooksCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("HOOK") ||
    menu.title?.toUpperCase().includes("USESTATE") ||
    menu.title?.toUpperCase().includes("USEEFFECT")
  )
);

const formsCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("FORM") ||
    menu.title?.toUpperCase().includes("INPUT") ||
    menu.title?.toUpperCase().includes("FORMULAIRE")
  )
);

const routingCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("ROUTING") ||
    menu.title?.toUpperCase().includes("ROUTER") ||
    menu.title?.toUpperCase().includes("NAVIGATION")
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

const stateCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("STATE") ||
    menu.title?.toUpperCase().includes("REDUX") ||
    menu.title?.toUpperCase().includes("CONTEXT") ||
    menu.title?.toUpperCase().includes("ÉTAT")
  )
);

const testingCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("TEST") ||
    menu.title?.toUpperCase().includes("JEST") ||
    menu.title?.toUpperCase().includes("ENZYME")
  )
);

const stylingCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("STYLE") ||
    menu.title?.toUpperCase().includes("CSS") ||
    menu.title?.toUpperCase().includes("STYLED")
  )
);
</script>

<style scoped>
.react-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #61dafb 0%, #21232a 100%);
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
  color: #61dafb;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
  animation: spin 10s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.tech-title {
  font-size: 48px;
  font-weight: 500;
  color: #d4c1c1;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  margin: 0;
}

.tech-description {
  font-size: 18px;
  color: rgba(102, 102, 102, 0.9);
  margin: 0;
  font-weight: 500;
}

.framework-presentation {
  max-width: 800px;
  margin: 30px auto 0;
  padding: 25px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #61dafb;
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
  
  .react-container {
    padding: 16px;
    padding-bottom: 120px;
  }
}
</style>