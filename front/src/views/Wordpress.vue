<template>
  <div class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full bg-blue-200">
    <div class="">
      <div class="header-section">
        <div class="tech-header">
          <i class="fab fa-wordpress tech-icon"></i>
          <h1 class="tech-title">WordPress</h1>
        </div>
        <p class="tech-description">CMS le plus populaire au monde pour créer des sites web</p>
        
        <div class="framework-presentation">
          <p>
            WordPress est un système de gestion de contenu (CMS) open source qui propulse plus de 40% des sites web dans le monde. 
            Créé en 2003, WordPress offre une plateforme flexible et extensible pour créer des sites web de toutes tailles, 
            des blogs personnels aux sites d'entreprise complexes. Avec son interface intuitive, son écosystème riche de thèmes 
            et plugins, et sa communauté active, WordPress permet aux développeurs et aux utilisateurs non techniques de créer 
            des sites web performants et professionnels. Son architecture modulaire et ses API REST modernes en font également 
            un choix populaire pour les solutions headless et les applications web modernes.
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
          title="Thèmes"
          :menus="themesCourses"
          icon-class="fas fa-palette"
        />
        
        <CourseCard
          title="Plugins"
          :menus="pluginsCourses"
          icon-class="fas fa-plug"
        />
        
        <CourseCard
          title="Types de contenu"
          :menus="customTypesCourses"
          icon-class="fas fa-th-large"
        />
        
        <CourseCard
          title="API REST"
          :menus="apiCourses"
          icon-class="fas fa-code"
        />
        
        <CourseCard
          title="Sécurité"
          :menus="securityCourses"
          icon-class="fas fa-shield-alt"
        />
        
        <CourseCard
          title="Performances"
          :menus="performanceCourses"
          icon-class="fas fa-tachometer-alt"
        />
        
        <CourseCard
          title="Gutenberg"
          :menus="gutenbergCourses"
          icon-class="fas fa-blocks"
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
  console.log("Menus mis à jour dans la page WordPress :", newVal);
});

const filterMenus = computed(() =>
  menus.value.filter((menu) => menu.menu.label?.toUpperCase() === "WORDPRESS")
);

const setupCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("INSTALL") ||
    menu.title?.toUpperCase().includes("SETUP") ||
    menu.title?.toUpperCase().includes("CONFIGURATION")
  )
);

const themesCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("THEME") ||
    menu.title?.toUpperCase().includes("THÈME")
  )
);

const pluginsCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("PLUGIN") ||
    menu.title?.toUpperCase().includes("EXTENSION")
  )
);

const customTypesCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("CUSTOM") ||
    menu.title?.toUpperCase().includes("POST TYPE") ||
    menu.title?.toUpperCase().includes("PERSONNALISÉ")
  )
);

const apiCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("API") ||
    menu.title?.toUpperCase().includes("REST") ||
    menu.title?.toUpperCase().includes("ENDPOINT")
  )
);

const securityCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("SECURITY") ||
    menu.title?.toUpperCase().includes("SÉCURITÉ") ||
    menu.title?.toUpperCase().includes("SECURITE")
  )
);

const performanceCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("PERFORMANCE") ||
    menu.title?.toUpperCase().includes("CACHE") ||
    menu.title?.toUpperCase().includes("OPTIMISATION")
  )
);

const gutenbergCourses = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("GUTENBERG") ||
    menu.title?.toUpperCase().includes("BLOCK") ||
    menu.title?.toUpperCase().includes("EDITOR")
  )
);
</script>

<style scoped>
.wordpress-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #21759b 0%, #1e6b8c 100%);
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
  color: #21759b;
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
  border-left: 4px solid #21759b;
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
  
  .wordpress-container {
    padding: 16px;
    padding-bottom: 120px;
  }
}
</style>