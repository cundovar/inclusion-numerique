<template>
  <div class="md:rounded-2xl md:mt-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-full" :class="categoryBgClass">
    <div class="">
      <div class="header-section">
        <div class="tech-header">
          <i :class="categoryIconClass + ' tech-icon'"></i>
          <h1 class="tech-title">{{ categoryDisplayName }}</h1>
        </div>
        <p class="tech-description">{{ categoryDescription }}</p>
        
        <div class="framework-presentation">
          <p>{{ categoryPresentation }}</p>
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
import { useData } from '../utlis/fetchDataPwa'
import CourseCard from './components/CourseCard.vue'

const route = useRoute()
const { fetchMenus, menus } = useData()

const categoryName = computed(() => route.params.category)

const categoryDisplayName = computed(() => {
  const name = categoryName.value
  const displayNames = {
    // Langages de programmation
    'javascript': 'JavaScript',
    'php': 'PHP',
    'python': 'Python',
    'java': 'Java',
    'csharp': 'C#',
    'cpp': 'C++',
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
  const bgClasses = {
    // Langages de programmation
    'javascript': 'bg-yellow-200',
    'php': 'bg-purple-200',
    'python': 'bg-blue-200',
    'java': 'bg-red-200',
    'csharp': 'bg-purple-200',
    'cpp': 'bg-blue-200',
    'c': 'bg-gray-200',
    'ruby': 'bg-red-200',
    'go': 'bg-cyan-200',
    'rust': 'bg-orange-200',
    'swift': 'bg-orange-200',
    'kotlin': 'bg-purple-200',
    'scala': 'bg-red-200',
    'perl': 'bg-blue-200',
    'lua': 'bg-blue-200',
    'dart': 'bg-blue-200',
    'typescript': 'bg-blue-200',
    'r': 'bg-blue-200',
    'matlab': 'bg-orange-200',
    'julia': 'bg-purple-200',
    'elixir': 'bg-purple-200',
    'erlang': 'bg-red-200',
    'haskell': 'bg-purple-200',
    'clojure': 'bg-green-200',
    'fsharp': 'bg-blue-200',
    
    // Technologies web
    'html': 'bg-orange-200',
    'css': 'bg-blue-200',
    'sass': 'bg-pink-200',
    'scss': 'bg-pink-200',
    'less': 'bg-blue-200',
    'tailwind': 'bg-cyan-200',
    'bootstrap': 'bg-purple-200',
    
    // Frameworks JavaScript
    'react': 'bg-cyan-200',
    'vue': 'bg-green-200',
    'angular': 'bg-red-200',
    'svelte': 'bg-orange-200',
    'nextjs': 'bg-gray-200',
    'nuxtjs': 'bg-green-200',
    'gatsby': 'bg-purple-200',
    'ember': 'bg-orange-200',
    'backbone': 'bg-blue-200',
    'jquery': 'bg-blue-200',
    'express': 'bg-gray-200',
    'nestjs': 'bg-red-200',
    'fastify': 'bg-gray-200',
    
    // Frameworks backend
    'symfony': 'bg-gray-200',
    'laravel': 'bg-red-200',
    'codeigniter': 'bg-orange-200',
    'cakephp': 'bg-red-200',
    'zend': 'bg-green-200',
    'django': 'bg-green-200',
    'flask': 'bg-gray-200',
    'fastapi': 'bg-teal-200',
    'spring': 'bg-green-200',
    'springboot': 'bg-green-200',
    'dotnet': 'bg-blue-200',
    'aspnet': 'bg-blue-200',
    'rails': 'bg-red-200',
    'sinatra': 'bg-red-200',
    'gin': 'bg-cyan-200',
    'fiber': 'bg-blue-200',
    'echo': 'bg-blue-200',
    
    // Bases de données
    'sql': 'bg-green-200',
    'mysql': 'bg-orange-200',
    'postgresql': 'bg-blue-200',
    'sqlite': 'bg-blue-200',
    'mongodb': 'bg-green-200',
    'redis': 'bg-red-200',
    'elasticsearch': 'bg-yellow-200',
    'cassandra': 'bg-blue-200',
    'neo4j': 'bg-blue-200',
    'mariadb': 'bg-blue-200',
    'oracle': 'bg-red-200',
    'sqlserver': 'bg-blue-200',
    
    // Outils et technologies
    'git': 'bg-orange-200',
    'docker': 'bg-blue-200',
    'kubernetes': 'bg-blue-200',
    'jenkins': 'bg-blue-200',
    'gitlab': 'bg-orange-200',
    'github': 'bg-gray-200',
    'bitbucket': 'bg-blue-200',
    'terraform': 'bg-purple-200',
    'ansible': 'bg-red-200',
    'vagrant': 'bg-blue-200',
    'webpack': 'bg-blue-200',
    'vite': 'bg-purple-200',
    'rollup': 'bg-red-200',
    'gulp': 'bg-red-200',
    'grunt': 'bg-orange-200',
    'npm': 'bg-red-200',
    'yarn': 'bg-blue-200',
    'pnpm': 'bg-orange-200',
    'composer': 'bg-brown-200',
    'pip': 'bg-blue-200',
    
    // Cloud et services
    'aws': 'bg-orange-200',
    'azure': 'bg-blue-200',
    'gcp': 'bg-blue-200',
    'firebase': 'bg-orange-200',
    'netlify': 'bg-teal-200',
    'vercel': 'bg-gray-200',
    'heroku': 'bg-purple-200',
    'digitalocean': 'bg-blue-200',
    
    // Mobile
    'android': 'bg-green-200',
    'ios': 'bg-gray-200',
    'reactnative': 'bg-cyan-200',
    'flutter': 'bg-blue-200',
    'ionic': 'bg-blue-200',
    'xamarin': 'bg-blue-200',
    'cordova': 'bg-gray-200',
    
    // Testing
    'jest': 'bg-red-200',
    'mocha': 'bg-brown-200',
    'chai': 'bg-red-200',
    'jasmine': 'bg-purple-200',
    'cypress': 'bg-gray-200',
    'selenium': 'bg-green-200',
    'puppeteer': 'bg-blue-200',
    'playwright': 'bg-green-200',
    
    // CMS
    'wordpress': 'bg-blue-200',
    'drupal': 'bg-blue-200',
    'joomla': 'bg-red-200',
    'strapi': 'bg-purple-200',
    'contentful': 'bg-blue-200',
    'sanity': 'bg-red-200',
    
    // Autres technologies
    'graphql': 'bg-pink-200',
    'rest': 'bg-blue-200',
    'websockets': 'bg-green-200',
    'grpc': 'bg-blue-200',
    'microservices': 'bg-purple-200',
    'serverless': 'bg-orange-200',
    'jamstack': 'bg-pink-200',
    'pwa': 'bg-purple-200',
    'webassembly': 'bg-purple-200',
    'blockchain': 'bg-yellow-200',
    'ai': 'bg-indigo-200',
    'ml': 'bg-green-200',
    'dl': 'bg-blue-200',
    'iot': 'bg-green-200',
    'ar': 'bg-purple-200',
    'vr': 'bg-blue-200'
  }
  return bgClasses[categoryName.value] || 'bg-gray-200'
})

const categoryIconClass = computed(() => {
  const iconClasses = {
    // Langages de programmation
    'javascript': 'fab fa-js-square',
    'php': 'fab fa-php',
    'python': 'fab fa-python',
    'java': 'fab fa-java',
    'csharp': 'fas fa-code',
    'cpp': 'fas fa-code',
    'c': 'fas fa-code',
    'ruby': 'fas fa-gem',
    'go': 'fas fa-bolt',
    'rust': 'fas fa-cog',
    'swift': 'fab fa-swift',
    'kotlin': 'fas fa-mobile',
    'scala': 'fas fa-code',
    'perl': 'fas fa-code',
    'lua': 'fas fa-moon',
    'dart': 'fas fa-bullseye',
    'typescript': 'fas fa-code',
    'r': 'fas fa-chart-line',
    'matlab': 'fas fa-calculator',
    'julia': 'fas fa-infinity',
    'elixir': 'fas fa-flask',
    'erlang': 'fas fa-sitemap',
    'haskell': 'fas fa-lambda',
    'clojure': 'fas fa-code',
    'fsharp': 'fas fa-code',
    
    // Technologies web  
    'html': 'fab fa-html5',
    'css': 'fab fa-css3-alt',
    'sass': 'fab fa-sass',
    'scss': 'fab fa-sass',
    'less': 'fab fa-less',
    'tailwind': 'fas fa-wind',
    'bootstrap': 'fab fa-bootstrap',
    
    // Frameworks JavaScript
    'react': 'fab fa-react',
    'vue': 'fab fa-vuejs',
    'angular': 'fab fa-angular',
    'svelte': 'fas fa-fire',
    'nextjs': 'fas fa-forward',
    'nuxtjs': 'fab fa-vuejs',
    'gatsby': 'fas fa-rocket',
    'ember': 'fas fa-fire',
    'backbone': 'fas fa-bone',
    'jquery': 'fas fa-dollar-sign',
    'express': 'fab fa-node-js',
    'nestjs': 'fas fa-cat',
    'fastify': 'fas fa-tachometer-alt',
    
    // Frameworks backend
    'symfony': 'fab fa-symfony',
    'laravel': 'fab fa-laravel',
    'codeigniter': 'fas fa-fire',
    'cakephp': 'fas fa-birthday-cake',
    'zend': 'fas fa-code',
    'django': 'fas fa-dragon',
    'flask': 'fas fa-flask',
    'fastapi': 'fas fa-rocket',
    'spring': 'fas fa-leaf',
    'springboot': 'fas fa-leaf',
    'dotnet': 'fab fa-microsoft',
    'aspnet': 'fab fa-microsoft',
    'rails': 'fas fa-gem',
    'sinatra': 'fas fa-microphone',
    'gin': 'fas fa-cocktail',
    'fiber': 'fas fa-spider',
    'echo': 'fas fa-volume-up',
    
    // Bases de données
    'sql': 'fas fa-database',
    'mysql': 'fas fa-database',
    'postgresql': 'fas fa-elephant',
    'sqlite': 'fas fa-database',
    'mongodb': 'fas fa-leaf',
    'redis': 'fas fa-memory',
    'elasticsearch': 'fas fa-search',
    'cassandra': 'fas fa-server',
    'neo4j': 'fas fa-project-diagram',
    'mariadb': 'fas fa-database',
    'oracle': 'fas fa-database',
    'sqlserver': 'fab fa-microsoft',
    
    // Outils et technologies
    'git': 'fab fa-git-alt',
    'docker': 'fab fa-docker',
    'kubernetes': 'fas fa-dharmachakra',
    'jenkins': 'fas fa-hammer',
    'gitlab': 'fab fa-gitlab',
    'github': 'fab fa-github',
    'bitbucket': 'fab fa-bitbucket',
    'terraform': 'fas fa-mountain',
    'ansible': 'fas fa-robot',
    'vagrant': 'fas fa-box',
    'webpack': 'fas fa-cube',
    'vite': 'fas fa-bolt',
    'rollup': 'fas fa-scroll',
    'gulp': 'fas fa-glass-water',
    'grunt': 'fas fa-grunt',
    'npm': 'fab fa-npm',
    'yarn': 'fab fa-yarn',
    'pnpm': 'fas fa-package',
    'composer': 'fas fa-music',
    'pip': 'fas fa-package',
    
    // Cloud et services
    'aws': 'fab fa-aws',
    'azure': 'fab fa-microsoft',
    'gcp': 'fab fa-google',
    'firebase': 'fas fa-fire',
    'netlify': 'fas fa-globe',
    'vercel': 'fas fa-triangle',
    'heroku': 'fas fa-cloud',
    'digitalocean': 'fab fa-digital-ocean',
    
    // Mobile
    'android': 'fab fa-android',
    'ios': 'fab fa-apple',
    'reactnative': 'fab fa-react',
    'flutter': 'fas fa-mobile',
    'ionic': 'fas fa-mobile',
    'xamarin': 'fab fa-microsoft',
    'cordova': 'fas fa-mobile-alt',
    
    // Testing
    'jest': 'fas fa-vial',
    'mocha': 'fas fa-coffee',
    'chai': 'fas fa-mug-hot',
    'jasmine': 'fas fa-seedling',
    'cypress': 'fas fa-tree',
    'selenium': 'fas fa-robot',
    'puppeteer': 'fas fa-theater-masks',
    'playwright': 'fas fa-masks-theater',
    
    // CMS
    'wordpress': 'fab fa-wordpress',
    'drupal': 'fab fa-drupal',
    'joomla': 'fab fa-joomla',
    'strapi': 'fas fa-layer-group',
    'contentful': 'fas fa-file-alt',
    'sanity': 'fas fa-brain',
    
    // Autres technologies
    'graphql': 'fas fa-project-diagram',
    'rest': 'fas fa-exchange-alt',
    'websockets': 'fas fa-plug',
    'grpc': 'fas fa-satellite',
    'microservices': 'fas fa-cubes',
    'serverless': 'fas fa-cloud',
    'jamstack': 'fas fa-layer-group',
    'pwa': 'fas fa-mobile-alt',
    'webassembly': 'fas fa-microchip',
    'blockchain': 'fas fa-link',
    'ai': 'fas fa-brain',
    'ml': 'fas fa-robot',
    'dl': 'fas fa-network-wired',
    'iot': 'fas fa-wifi',
    'ar': 'fas fa-eye',
    'vr': 'fas fa-vr-cardboard'
  }
  return iconClasses[categoryName.value] || 'fas fa-code'
})

const categoryDescription = computed(() => {
  const descriptions = {
    // Langages de programmation
    'javascript': 'Langage de programmation moderne et polyvalent',
    'php': 'Langage de script côté serveur pour le développement web',
    'python': 'Langage polyvalent et facile à apprendre',
    'java': 'Langage orienté objet pour applications d\'entreprise',
    'csharp': 'Langage Microsoft pour développement .NET',
    'cpp': 'Extension orientée objet du langage C',
    'c': 'Langage de programmation système de bas niveau',
    'ruby': 'Langage élégant et expressif',
    'go': 'Langage Google pour systèmes distribués',
    'rust': 'Langage système sûr et performant',
    'swift': 'Langage Apple pour iOS et macOS',
    'kotlin': 'Langage moderne pour Android et JVM',
    'scala': 'Langage fonctionnel pour la JVM',
    'typescript': 'JavaScript avec typage statique',
    'r': 'Langage pour analyse statistique et data science',
    
    // Technologies web
    'html': 'Langage de balisage pour la structure des pages web',
    'css': 'Langage de style pour la présentation des pages web',
    'sass': 'Préprocesseur CSS avec fonctionnalités avancées',
    'tailwind': 'Framework CSS utility-first',
    'bootstrap': 'Framework CSS responsive et mobile-first',
    
    // Frameworks JavaScript
    'react': 'Bibliothèque JavaScript pour interfaces utilisateur',
    'vue': 'Framework progressif pour applications web',
    'angular': 'Plateforme complète pour applications web',
    'svelte': 'Framework compilé pour applications rapides',
    'nextjs': 'Framework React pour production',
    'nuxtjs': 'Framework Vue.js universel',
    
    // Frameworks backend
    'symfony': 'Framework PHP moderne et puissant',
    'laravel': 'Framework PHP élégant et expressif',
    'django': 'Framework Python de haut niveau',
    'flask': 'Microframework Python flexible',
    'spring': 'Framework Java complet pour applications d\'entreprise',
    'rails': 'Framework Ruby pour développement web rapide',
    
    // Bases de données
    'sql': 'Langage de requête pour bases de données relationnelles',
    'mysql': 'Système de gestion de base de données relationnelle',
    'postgresql': 'Base de données relationnelle-objet avancée',
    'mongodb': 'Base de données NoSQL orientée document',
    'redis': 'Structure de données en mémoire',
    
    // Outils et technologies
    'git': 'Système de contrôle de version distribué',
    'docker': 'Plateforme de conteneurisation',
    'kubernetes': 'Orchestrateur de conteneurs',
    
    // Cloud et services
    'aws': 'Plateforme cloud Amazon',
    'azure': 'Plateforme cloud Microsoft',
    'firebase': 'Plateforme de développement Google',
    
    // Mobile et autres
    'android': 'Système d\'exploitation mobile Google',
    'ios': 'Système d\'exploitation mobile Apple',
    'reactnative': 'Framework mobile basé sur React',
    'flutter': 'Framework mobile Google'
  }
  return descriptions[categoryName.value] || 'Ressources de programmation'
})

const categoryPresentation = computed(() => {
  const presentations = {
    'javascript': 'JavaScript est un langage de programmation de haut niveau, dynamique et interprété. Il est principalement utilisé pour développer des applications web interactives côté client, mais aussi côté serveur avec Node.js. JavaScript supporte la programmation orientée objet, fonctionnelle et procédurale, ce qui en fait un langage très polyvalent pour créer des interfaces utilisateur riches et des applications web modernes.',
    'php': 'PHP est un langage de script open source spécialement conçu pour le développement web. Il peut être intégré dans du HTML et est particulièrement adapté au développement web côté serveur. PHP offre une grande flexibilité avec de nombreuses fonctionnalités pour la gestion des bases de données, la manipulation de fichiers et la création d\'applications web dynamiques.',
    'css': 'CSS (Cascading Style Sheets) est un langage de feuille de style utilisé pour décrire la présentation d\'un document écrit en HTML ou XML. CSS permet de séparer le contenu de la présentation, offrant un contrôle précis sur la mise en page, les couleurs, les polices et les animations des pages web.',
    'html': 'HTML (HyperText Markup Language) est le langage de balisage standard pour créer des pages web. Il décrit la structure et le contenu d\'une page web à l\'aide d\'éléments et de balises. HTML est la fondation de toutes les pages web et travaille en synergie avec CSS et JavaScript.',
    'sql': 'SQL (Structured Query Language) est un langage de programmation conçu pour gérer et manipuler des bases de données relationnelles. Il permet de créer, modifier, interroger et administrer des bases de données de manière efficace et standardisée.',
    'git': 'Git est un système de contrôle de version distribué gratuit et open source, conçu pour gérer tout type de projet avec rapidité et efficacité. Il permet de suivre les modifications du code source, de collaborer en équipe et de maintenir un historique complet des versions.'
  }
  return presentations[categoryName.value] || 'Découvrez les ressources disponibles pour cette technologie.'
})

const categoryMenus = computed(() => {
  if (!menus.value.length) return []
  
  const grouped = {}
  
  menus.value
    .filter((menu) => menu.category && menu.category.name === categoryName.value)
    .forEach((menu) => {
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
  if (label.includes('database') || label.includes('bdd')) return 'fas fa-database'
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