<template>

  <div class="md:rounded-2xl md:mt-10  xl:mt-0 - pb-96 text-blue-500 min-h-screen w-ful bg-purple-200">
    <div class="">
    <div class="header-section">
      <div class="tech-header">
        <i class="fab fa-symfony tech-icon"></i>
        <h1 class="tech-title">Symfony</h1>
      </div>
      <p class="tech-description">Framework PHP moderne et puissant</p>
      
      <div class="framework-presentation">
        <p>
          Symfony est un framework PHP de haute performance conçu pour développer des applications web robustes et évolutives. 
          Basé sur une architecture MVC (Model-View-Controller), il offre une structure modulaire avec des composants réutilisables 
          qui accélèrent le développement. Symfony privilégie les bonnes pratiques de programmation, la sécurité et la maintenabilité 
          du code. Avec son écosystème riche incluant Doctrine pour l'ORM, Twig pour les templates et un système de routing flexible, 
          Symfony est devenu la référence pour les développeurs PHP professionnels souhaitant créer des applications.
        </p>
      </div>
    </div>

 <div class="  flex flex-wrap  justify-center items-center " >
      <CourseCard
        title="Bases de Symfony"
        :menus="baseMenus"
        icon-class="fas fa-graduation-cap"
      />
      <CourseCard
        title="Models"
        :menus="modelMenus"
        icon-class="fas fa-database"
      />
      
      <CourseCard
        title="Views"
        :menus="VueMenus"
        icon-class="fas fa-eye"
      />
      
      <CourseCard
        title="Controllers"
        :menus="controllerMenus"
        icon-class="fas fa-cogs"
      />
      
      <CourseCard
        title="API"
        :menus="apiMenus"
        icon-class="fas fa-plug"
      />
      
      <CourseCard
        title="Forms"
        :menus="formMenus"
        icon-class="fas fa-wpforms"
      />
      
      <CourseCard
        title="Security"
        :menus="securityMenus"
        icon-class="fas fa-shield-alt"
      />
      
      <CourseCard
        title="ORM Doctrine"
        :menus="doctrinMenus"
        icon-class="fas fa-layer-group"
      />
      
      <CourseCard
        title="Commands"
        :menus="commandMenus"
        icon-class="fas fa-terminal"
      />
      
      <CourseCard
        title="Repository"
        :menus="repositoryMenus"
        icon-class="fas fa-folder-open"
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
  console.log("Menus mis à jour dans la page symfony :", newVal);
});

const filterMenus = computed(() =>
  menus.value.filter((menu) => menu.menu.label?.toUpperCase() === "SYMFONY")
);

const baseMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("INTRO")
  )
);

const modelMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("MODEL")
  )
);

const VueMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("VIEW")
  )
);

const controllerMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("CONTROLLER")
  )
);

const apiMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("API")
  )
);

const formMenus = computed(() =>
  filterMenus.value.filter((menu) => {
    const title = menu.title?.toUpperCase() || "";
    return title.includes("FORM") && !title.includes("API");
  })
);

const securityMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("SECURITY")
  )
);

const doctrinMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("DOCTRINE") 
  
  )
);

const commandMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("COMMAND")
  )
);

const repositoryMenus = computed(() =>
  filterMenus.value.filter((menu) =>
    menu.title?.toUpperCase().includes("REPOSITORY")
  )
);
</script>

<style scoped>
.symfony-container {
  min-height: 100vh;
background-color: #f5f5f5;
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
  color: #ffffff;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
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
  border-left: 4px solid #326ce5;
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
  
  .symfony-container {
    padding: 16px;
    padding-bottom: 120px;
  }
}
</style>