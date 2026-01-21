<template>
  <!-- Composant SEO pour gérer les balises meta (seulement après le chargement des données) -->
  <SeoHead v-if="cats.length > 0" :seo-data="seoData" />

  <div class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full" :class="categoryBgClass">
    <div class="">
      <div class="header-section">
        <div class="tech-header">
          <i :class="categoryIconClass + ' tech-icon'"></i>
          <h1 class="tech-title">{{ categoryDisplayName }}</h1>
        </div>

        <div >

     


        </div>


        <div class="framework-presentation" v-html="categoryPresentation">

        </div>
      </div>

      <div class="flex flex-wrap justify-center items-start">
        <CourseCard
          v-for="group in categoryMenus" 
          :key="group.label"
          :title="group.label"
          :menus="group.items"
          :icon-class="getGroupIconClass(group.label)"
        />
        
      </div>

      <div v-if="!categoryMenus.length" class="text-center py-12">
        <p class="text-gray-500 text-lg">Aucun menu disponible pour cette catégorie</p>
        <router-link to="/" class="mt-4 inline-block text-blue-500 hover:underline">
          Retour à l'accueil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useData } from '../utils/fetchDataPwa'
import CourseCard from '../components/features/pages/CourseCard.vue'
import SeoHead from '../components/layout/SeoHead.vue'
import { storeToRefs } from 'pinia'
import NiveauFilter from '../components/ui/NiveauFilter.vue'
import { useNiveauStore } from "@/stores/niveauCoursStore";



const route = useRoute()
const { fetchMenus, menus, cats } = useData()

//const pour les bouton selected niveaux
const niveauStore = useNiveauStore()
const { selectedNiveau } = storeToRefs(niveauStore)
   console.log("localstorage",selectedNiveau.value)
const categoryName = computed(() => route.params.category)

// Récupérer les données SEO directement depuis la catégorie
const seoData = computed(() => {
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Si la catégorie a un SEO défini, l'utiliser
  if (currentCategory?.seo) {
    return currentCategory.seo
  }

  // Sinon, générer un SEO par défaut depuis les données de la catégorie
  if (currentCategory) {
    return {
      title: `${categoryDisplayName.value} - Cours et Tutoriels`,
      metaDescription: currentCategory.description ?
        currentCategory.description.replace(/<[^>]*>/g, '').substring(0, 160) :
        `Découvrez nos cours et tutoriels sur ${categoryDisplayName.value}`,
      ogTitle: `${categoryDisplayName.value} - Formation`,
      ogDescription: currentCategory.description ?
        currentCategory.description.replace(/<[^>]*>/g, '').substring(0, 200) :
        `Apprenez ${categoryDisplayName.value} avec nos cours complets`
    }
  }

  return null
})

const categoryDisplayName = computed(() => {
  const name = categoryName.value
  const displayNames = {
    // Langages de programmation
    'javascript': 'JavaScript',
    'php': 'PHP',
    'python': 'Python',
    'java': 'Java',
    'csharp': 'C#',
    'BDD': 'Base de données',
    'c': 'C',
    'ruby': 'Ruby',
    'go': 'Go',
    'rust': 'Rust',
    'swift': 'Swift',
    'kotlin': 'Kotlin',
    'scala': 'Scala',
    'perl': 'Perl',
    'lua': 'Lua',
    'dart': 'Dart',
    'typescript': 'TypeScript',
    'r': 'R',
    'matlab': 'MATLAB',
    'julia': 'Julia',
    'elixir': 'Elixir',
    'erlang': 'Erlang',
    'haskell': 'Haskell',
    'clojure': 'Clojure',
    'fsharp': 'F#',
    
    // Technologies web
    'html': 'HTML',
    'css': 'CSS',
    'sass': 'Sass',
    'scss': 'SCSS',
    'less': 'Less',
    'tailwind': 'Tailwind CSS',
    'bootstrap': 'Bootstrap',
    
    // Frameworks JavaScript
    'react': 'React',
    'vue': 'Vue.js',
    'angular': 'Angular',
    'svelte': 'Svelte',
    'nextjs': 'Next.js',
    'nuxtjs': 'Nuxt.js',
    'gatsby': 'Gatsby',
    'ember': 'Ember.js',
    'backbone': 'Backbone.js',
    'jquery': 'jQuery',
    'express': 'Express.js',
    'nestjs': 'NestJS',
    'fastify': 'Fastify',
    
    // Frameworks backend
    'symfony': 'Symfony',
    'laravel': 'Laravel',
    'codeigniter': 'CodeIgniter',
    'cakephp': 'CakePHP',
    'zend': 'Zend',
    'django': 'Django',
    'flask': 'Flask',
    'fastapi': 'FastAPI',
    'spring': 'Spring',
    'springboot': 'Spring Boot',
    'dotnet': '.NET',
    'aspnet': 'ASP.NET',
    'rails': 'Ruby on Rails',
    'sinatra': 'Sinatra',
    'gin': 'Gin',
    'fiber': 'Fiber',
    'echo': 'Echo',
    
    // Bases de données
    'sql': 'SQL',
    'mysql': 'MySQL',
    'postgresql': 'PostgreSQL',
    'sqlite': 'SQLite',
    'mongodb': 'MongoDB',
    'redis': 'Redis',
    'elasticsearch': 'Elasticsearch',
    'cassandra': 'Cassandra',
    'neo4j': 'Neo4j',
    'mariadb': 'MariaDB',
    'oracle': 'Oracle',
    'sqlserver': 'SQL Server',
    
    // Outils et technologies
    'git': 'Git',
    'docker': 'Docker',
    'kubernetes': 'Kubernetes',
    'jenkins': 'Jenkins',
    'gitlab': 'GitLab',
    'github': 'GitHub',
    'bitbucket': 'Bitbucket',
    'terraform': 'Terraform',
    'ansible': 'Ansible',
    'vagrant': 'Vagrant',
    'webpack': 'Webpack',
    'vite': 'Vite',
    'rollup': 'Rollup',
    'gulp': 'Gulp',
    'grunt': 'Grunt',
    'npm': 'NPM',
    'yarn': 'Yarn',
    'pnpm': 'PNPM',
    'composer': 'Composer',
    'pip': 'Pip',
    
    // Cloud et services
    'aws': 'AWS',
    'azure': 'Azure',
    'gcp': 'Google Cloud',
    'firebase': 'Firebase',
    'netlify': 'Netlify',
    'vercel': 'Vercel',
    'heroku': 'Heroku',
    'digitalocean': 'DigitalOcean',
    
    // Mobile
    'android': 'Android',
    'ios': 'iOS',
    'reactnative': 'React Native',
    'flutter': 'Flutter',
    'ionic': 'Ionic',
    'xamarin': 'Xamarin',
    'cordova': 'Cordova',
    
    // Testing
    'jest': 'Jest',
    'mocha': 'Mocha',
    'chai': 'Chai',
    'jasmine': 'Jasmine',
    'cypress': 'Cypress',
    'selenium': 'Selenium',
    'puppeteer': 'Puppeteer',
    'playwright': 'Playwright',
    
    // CMS
    'wordpress': 'WordPress',
    'drupal': 'Drupal',
    'joomla': 'Joomla',
    'strapi': 'Strapi',
    'contentful': 'Contentful',
    'sanity': 'Sanity',
    
    // Autres technologies
    'graphql': 'GraphQL',
    'rest': 'REST API',
    'websockets': 'WebSockets',
    'grpc': 'gRPC',
    'microservices': 'Microservices',
    'serverless': 'Serverless',
    'jamstack': 'JAMstack',
    'pwa': 'PWA',
    'webassembly': 'WebAssembly',
    'blockchain': 'Blockchain',
    'ai': 'Intelligence Artificielle',
    'ml': 'Machine Learning',
    'dl': 'Deep Learning',
    'iot': 'IoT',
    'ar': 'Réalité Augmentée',
    'vr': 'Réalité Virtuelle'
  }
  return displayNames[name] || name.charAt(0).toUpperCase() + name.slice(1)
})

const categoryBgClass = computed(() => {
  // Chercher la catégorie dans l'API par son nom
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Retourner la couleur de l'API ou une couleur par défaut
  return currentCategory?.couleur || 'bg-gray-200'
})

const categoryIconClass = computed(() => {
  // D'abord, chercher l'icône dans l'API (category.logo.logo)
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Si l'icône existe dans l'API, l'utiliser
  if (currentCategory?.logo?.logo) {
    return currentCategory.logo.logo
  }

  
  return  'fas fa-code'
})

const categoryDescription = computed(() => {
  // Chercher la catégorie dans l'API par son nom
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Retourner la description de l'API ou une description par défaut
  // On enlève les balises HTML pour une description courte
  if (currentCategory?.description) {
    const tempDiv = document.createElement('div')
    tempDiv.innerHTML = currentCategory.description
    return tempDiv.textContent || tempDiv.innerText || 'Ressources de programmation'
  }

  return 'Ressources de programmation'
})

const categoryPresentation = computed(() => {
  // Chercher la catégorie dans l'API par son nom
  const currentCategory = cats.value?.find(
    cat => cat.name.toLowerCase() === categoryName.value.toLowerCase()
  )

  // Retourner la description HTML de l'API
  if (currentCategory?.description) {
    return currentCategory.description
    
  }

  return '<div>Découvrez les ressources disponibles pour cette technologie.</div>'
})

const categoryMenus = computed(() => {
  if (!menus.value.length) return []

  console.log('🔍 Debug CategoryPage:')
  console.log('  - categoryName from URL:', categoryName.value)
  console.log('  - Total menus:', menus.value.length)

  // Afficher toutes les catégories disponibles
  const uniqueCategories = [...new Set(menus.value.map(m => m.category?.name).filter(Boolean))]
  console.log('  - Available categories in API:', uniqueCategories)

  const grouped = {}

  // Étape 1: Filtrer par catégorie
  let filteredMenus = menus.value.filter((menu) => {
    if (!menu.category) return false
    const matches = menu.category.name.toLowerCase() === categoryName.value.toLowerCase()
  
    // if (matches) {
    //   console.log('  ✅ Match found:', menu.title, '- Category:', menu)
   
    // }
    return matches
  })

  // console.log('  - Filtered menus count:', filteredMenus.length)
 // Étape 2: Filtrer par niveau (comme dans App.vue)
if(selectedNiveau.value){

  console.log("eee",filteredMenus)
  // Filtrage 1: Filtrer par le niveau du menu (menu.niveauCours)
  filteredMenus = filteredMenus.filter((item) => {
    const menuCatOrdre = item.menu?.niveauCours?.ordre
    const selectedOrdre = selectedNiveau.value.ordre
    const hasNoNiveau = !item.menu?.niveauCours?.name
    return menuCatOrdre <= selectedOrdre || hasNoNiveau
  })

  // Filtrage 2: Filtrer par le niveau du PageContent (niveauCours direct)
  filteredMenus = filteredMenus.filter((menu) => {
    const menuOrdre = menu.niveauCours?.ordre
    const selectedOrdre = selectedNiveau.value.ordre
    const hasNoNiveau = !menu.niveauCours?.name
    //garder menu si
    //1 ordre <=ordre du niv selectionne dans localstorage
    //2 ou si pas niveau defini 
    return menuOrdre <=selectedOrdre || hasNoNiveau
  })
}
  // Étape 3: Regrouper par label
  filteredMenus.forEach((menu) => {
    const label = menu.menu.label

    if (!grouped[label]) {
      grouped[label] = {
        label: label,
        slug: label.toLowerCase().replace(/\s+/g, '-'),
        items: [],
      }
    }

    grouped[label].items.push(menu)
  })

  return Object.values(grouped)
})

const getGroupIconClass = (groupLabel) => {
  const label = groupLabel.toLowerCase()
  
  if (label.includes('intro') || label.includes('base')) return 'fas fa-graduation-cap'
  if (label.includes('api')) return 'fas fa-plug'
  if (label.includes('form')) return 'fas fa-wpforms'
  if (label.includes('security')) return 'fas fa-shield-alt'
  if (label.includes('bdd') || label.includes('bdd')) return 'fas fa-database'
  if (label.includes('controller')) return 'fas fa-cogs'
  if (label.includes('view') || label.includes('template')) return 'fas fa-eye'
  if (label.includes('model')) return 'fas fa-layer-group'
  if (label.includes('command')) return 'fas fa-terminal'
  if (label.includes('repository')) return 'fas fa-folder-open'
  if (label.includes('css')) return 'fab fa-css3-alt'
  if (label.includes('html')) return 'fab fa-html5'
  if (label.includes('function')) return 'fas fa-code'
  if (label.includes('object')) return 'fas fa-cube'
  if (label.includes('array')) return 'fas fa-list'
  
  return 'fas fa-book'
}

onMounted(() => {
  fetchMenus()
  niveauStore.fetchNiveaux()
})
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