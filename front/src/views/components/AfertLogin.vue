<template>
  <div class="min-h-screen flex flex-wrap justify-center items-start xl:p-20 max-xl:pb-96 " >
    

 
    <div class=" 2xl:border-r max-2xl:mb-5 max-2xl:border-b max-2xl:pb-10 max-2xl:w-full flex flex-col  2xl:w-1/2 xl:h-full h-auto">
      <p class="text-[5rem] max-md:text-[3rem] font-bold text-blue-400">
        bienvenue
      </p>

      <div
        class="flex 2xl:w-full 2xl:pr-10 self-center xl:text-2xl max-xl:text-xl gap-y-10 max-md:p-5 max-xl:p-20 text-justify flex-col md:text-xl xl:w-5/6  gap-2 font-bold"
      >
        <p>
          ici vous trouverez des ressources pour apprendre et développer vos
          projets.
        </p>

        <p>
          Cette application n'a pas pour but de remplacer les cours de formation
          dans l'IDE (éditeur de code), mais d'un pense-bête à consulter
          n'importe où
        </p>

        <p>
          Le mieux étant d'ouvrir votre editeur de code et de tester par vous
          même !!
        </p>
      </div>
    </div>
    

        <!-- Categories Grid -->
        <div class=" w-1/2 max-2xl:w-full grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-3 gap-6 2xl:grid-cols-4   ">
          <router-link 
            v-for="(groups, category) in menusByCategory" 
            :key="category"
            :to="`/category/${category}`"
            class="group relative overflow-hidden   hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 hover:scale-105"
          >
            <!-- Category Card -->
            <div class="aspect-square p-6 flex flex-col items-center justify-center text-center">
              <!-- Category Icon -->
              <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-all duration-300 group-hover:scale-110"
                   :class="getCategoryBgClass(category)">
                <i :class="getCategoryIconClass(category) + ' text-2xl text-white'"></i>
              </div>
              
              <!-- Category Name -->
              <h3 class="font-bold text-gray-800 text-lg capitalize mb-2 group-hover:text-blue-600 transition-colors duration-300">
                {{ getCategoryDisplayName(category) }}
              </h3>
              
              <!-- Course Count -->
              <p class="text-sm text-gray-500 group-hover:text-gray-600 transition-colors duration-300">
                {{ groups.length }} menu{{ groups.length > 1 ? 's' : '' }}
              </p>
              
              <!-- Hover Arrow -->
              <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                <i class="fas fa-arrow-right text-blue-500"></i>
              </div>
            </div>
            
            <!-- Gradient Overlay on Hover -->
            <div class="absolute inset-0 bg-gradient-to-t from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </router-link>
        </div>

        <!-- Call to Action -->
        
      </div>

</template>


<script setup>


import { onMounted, ref, computed } from "vue";
import { useData } from "../../utlis/fetchDataPwa";

const filteredCategories = ["wordpress", "vue", "react"]; // noms de catégories à exclure
const { fetchMenus, cats, menus } = useData();
onMounted(() => {
  fetchMenus();
});
const menusByCategory = computed(() => {
  const result = {};
  const uniqueCategories = new Set();

  menus.value.forEach((menu) => {
    if (menu.category && menu.category.name) {
      uniqueCategories.add(menu.category.name);
    }
  });

  uniqueCategories.forEach((categoryName) => {
  
    if (filteredCategories.includes(categoryName.toLowerCase())) {
      return;
    }

    const grouped = {};

    menus.value
      .filter((menu) => menu.category.name === categoryName)
      .forEach((menu) => {
        const label = menu.menu.label;

        if (!grouped[label]) {
          grouped[label] = {
            label: label,
            slug: label.toLowerCase().replace(/\s+/g, "-"),
            items: [],
          };
        }

        grouped[label].items.push(menu);
      });

    result[categoryName] = Object.values(grouped);
  });

  return result;
});

// Helper functions for category styling
const getCategoryDisplayName = (category) => {
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
  };
  return displayNames[category] || category.charAt(0).toUpperCase() + category.slice(1);
};

const getCategoryIconClass = (category) => {
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
  };
  return iconClasses[category] || 'fas fa-code';
};

const getCategoryBgClass = (category) => {
  const bgClasses = {
    // Langages de programmation
    'javascript': 'bg-gradient-to-br from-yellow-400 to-yellow-600',
    'php': 'bg-gradient-to-br from-purple-500 to-purple-700',
    'python': 'bg-gradient-to-br from-blue-500 to-yellow-400',
    'java': 'bg-gradient-to-br from-red-500 to-orange-600',
    'csharp': 'bg-gradient-to-br from-purple-600 to-blue-500',
    'cpp': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'c': 'bg-gradient-to-br from-gray-600 to-blue-500',
    'ruby': 'bg-gradient-to-br from-red-500 to-red-700',
    'go': 'bg-gradient-to-br from-cyan-400 to-blue-500',
    'rust': 'bg-gradient-to-br from-orange-500 to-red-600',
    'swift': 'bg-gradient-to-br from-orange-400 to-red-500',
    'kotlin': 'bg-gradient-to-br from-purple-500 to-pink-500',
    'scala': 'bg-gradient-to-br from-red-600 to-red-800',
    'perl': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'lua': 'bg-gradient-to-br from-blue-400 to-indigo-600',
    'dart': 'bg-gradient-to-br from-blue-500 to-cyan-400',
    'typescript': 'bg-gradient-to-br from-blue-600 to-blue-800',
    'r': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'matlab': 'bg-gradient-to-br from-orange-500 to-red-500',
    'julia': 'bg-gradient-to-br from-purple-500 to-red-500',
    'elixir': 'bg-gradient-to-br from-purple-600 to-purple-800',
    'erlang': 'bg-gradient-to-br from-red-500 to-red-700',
    'haskell': 'bg-gradient-to-br from-purple-600 to-indigo-700',
    'clojure': 'bg-gradient-to-br from-green-500 to-blue-600',
    'fsharp': 'bg-gradient-to-br from-blue-600 to-purple-600',
    
    // Technologies web
    'html': 'bg-gradient-to-br from-orange-400 to-orange-600',
    'css': 'bg-gradient-to-br from-blue-400 to-blue-600',
    'sass': 'bg-gradient-to-br from-pink-500 to-pink-700',
    'scss': 'bg-gradient-to-br from-pink-500 to-pink-700',
    'less': 'bg-gradient-to-br from-blue-500 to-indigo-600',
    'tailwind': 'bg-gradient-to-br from-cyan-400 to-blue-500',
    'bootstrap': 'bg-gradient-to-br from-purple-600 to-indigo-700',
    
    // Frameworks JavaScript
    'react': 'bg-gradient-to-br from-cyan-400 to-blue-500',
    'vue': 'bg-gradient-to-br from-green-400 to-green-600',
    'angular': 'bg-gradient-to-br from-red-500 to-red-700',
    'svelte': 'bg-gradient-to-br from-orange-500 to-red-600',
    'nextjs': 'bg-gradient-to-br from-gray-800 to-gray-900',
    'nuxtjs': 'bg-gradient-to-br from-green-500 to-teal-600',
    'gatsby': 'bg-gradient-to-br from-purple-600 to-purple-800',
    'ember': 'bg-gradient-to-br from-orange-600 to-red-600',
    'backbone': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'jquery': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'express': 'bg-gradient-to-br from-gray-700 to-gray-900',
    'nestjs': 'bg-gradient-to-br from-red-500 to-pink-600',
    'fastify': 'bg-gradient-to-br from-gray-800 to-black',
    
    // Frameworks backend
    'symfony': 'bg-gradient-to-br from-gray-800 to-black',
    'laravel': 'bg-gradient-to-br from-red-500 to-orange-600',
    'codeigniter': 'bg-gradient-to-br from-orange-500 to-red-600',
    'cakephp': 'bg-gradient-to-br from-red-600 to-red-800',
    'zend': 'bg-gradient-to-br from-green-600 to-green-800',
    'django': 'bg-gradient-to-br from-green-600 to-green-800',
    'flask': 'bg-gradient-to-br from-gray-700 to-gray-900',
    'fastapi': 'bg-gradient-to-br from-teal-500 to-green-600',
    'spring': 'bg-gradient-to-br from-green-500 to-green-700',
    'springboot': 'bg-gradient-to-br from-green-600 to-green-800',
    'dotnet': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'aspnet': 'bg-gradient-to-br from-blue-600 to-purple-700',
    'rails': 'bg-gradient-to-br from-red-600 to-red-800',
    'sinatra': 'bg-gradient-to-br from-red-500 to-red-700',
    'gin': 'bg-gradient-to-br from-cyan-500 to-blue-600',
    'fiber': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'echo': 'bg-gradient-to-br from-blue-500 to-cyan-600',
    
    // Bases de données
    'sql': 'bg-gradient-to-br from-green-400 to-green-600',
    'mysql': 'bg-gradient-to-br from-orange-500 to-orange-700',
    'postgresql': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'sqlite': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'mongodb': 'bg-gradient-to-br from-green-600 to-green-800',
    'redis': 'bg-gradient-to-br from-red-600 to-red-800',
    'elasticsearch': 'bg-gradient-to-br from-yellow-500 to-orange-600',
    'cassandra': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'neo4j': 'bg-gradient-to-br from-blue-500 to-green-600',
    'mariadb': 'bg-gradient-to-br from-blue-600 to-brown-600',
    'oracle': 'bg-gradient-to-br from-red-600 to-orange-700',
    'sqlserver': 'bg-gradient-to-br from-blue-600 to-gray-700',
    
    // Outils et technologies
    'git': 'bg-gradient-to-br from-orange-600 to-red-600',
    'docker': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'kubernetes': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'jenkins': 'bg-gradient-to-br from-blue-600 to-gray-700',
    'gitlab': 'bg-gradient-to-br from-orange-600 to-red-600',
    'github': 'bg-gradient-to-br from-gray-800 to-black',
    'bitbucket': 'bg-gradient-to-br from-blue-600 to-blue-800',
    'terraform': 'bg-gradient-to-br from-purple-600 to-indigo-700',
    'ansible': 'bg-gradient-to-br from-red-600 to-red-800',
    'vagrant': 'bg-gradient-to-br from-blue-600 to-cyan-600',
    'webpack': 'bg-gradient-to-br from-blue-500 to-cyan-600',
    'vite': 'bg-gradient-to-br from-purple-600 to-yellow-500',
    'rollup': 'bg-gradient-to-br from-red-500 to-orange-600',
    'gulp': 'bg-gradient-to-br from-red-500 to-red-700',
    'grunt': 'bg-gradient-to-br from-orange-500 to-yellow-600',
    'npm': 'bg-gradient-to-br from-red-600 to-red-800',
    'yarn': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'pnpm': 'bg-gradient-to-br from-orange-500 to-yellow-600',
    'composer': 'bg-gradient-to-br from-brown-600 to-brown-800',
    'pip': 'bg-gradient-to-br from-blue-500 to-yellow-500',
    
    // Cloud et services
    'aws': 'bg-gradient-to-br from-orange-500 to-yellow-600',
    'azure': 'bg-gradient-to-br from-blue-600 to-blue-800',
    'gcp': 'bg-gradient-to-br from-blue-500 to-green-500',
    'firebase': 'bg-gradient-to-br from-orange-500 to-yellow-600',
    'netlify': 'bg-gradient-to-br from-teal-500 to-cyan-600',
    'vercel': 'bg-gradient-to-br from-gray-800 to-black',
    'heroku': 'bg-gradient-to-br from-purple-600 to-purple-800',
    'digitalocean': 'bg-gradient-to-br from-blue-500 to-blue-700',
    
    // Mobile
    'android': 'bg-gradient-to-br from-green-500 to-green-700',
    'ios': 'bg-gradient-to-br from-gray-600 to-gray-800',
    'reactnative': 'bg-gradient-to-br from-cyan-500 to-blue-600',
    'flutter': 'bg-gradient-to-br from-blue-500 to-cyan-600',
    'ionic': 'bg-gradient-to-br from-blue-500 to-blue-700',
    'xamarin': 'bg-gradient-to-br from-blue-600 to-purple-600',
    'cordova': 'bg-gradient-to-br from-gray-600 to-gray-800',
    
    // Testing
    'jest': 'bg-gradient-to-br from-red-600 to-orange-600',
    'mocha': 'bg-gradient-to-br from-brown-600 to-brown-800',
    'chai': 'bg-gradient-to-br from-red-500 to-red-700',
    'jasmine': 'bg-gradient-to-br from-purple-600 to-pink-600',
    'cypress': 'bg-gradient-to-br from-gray-700 to-gray-900',
    'selenium': 'bg-gradient-to-br from-green-600 to-green-800',
    'puppeteer': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'playwright': 'bg-gradient-to-br from-green-600 to-blue-600',
    
    // CMS
    'wordpress': 'bg-gradient-to-br from-blue-600 to-gray-700',
    'drupal': 'bg-gradient-to-br from-blue-600 to-blue-800',
    'joomla': 'bg-gradient-to-br from-red-600 to-orange-600',
    'strapi': 'bg-gradient-to-br from-purple-600 to-indigo-700',
    'contentful': 'bg-gradient-to-br from-blue-500 to-teal-600',
    'sanity': 'bg-gradient-to-br from-red-500 to-pink-600',
    
    // Autres technologies
    'graphql': 'bg-gradient-to-br from-pink-500 to-purple-600',
    'rest': 'bg-gradient-to-br from-blue-500 to-green-600',
    'websockets': 'bg-gradient-to-br from-green-500 to-blue-600',
    'grpc': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'microservices': 'bg-gradient-to-br from-purple-600 to-blue-600',
    'serverless': 'bg-gradient-to-br from-orange-500 to-red-600',
    'jamstack': 'bg-gradient-to-br from-pink-500 to-red-600',
    'pwa': 'bg-gradient-to-br from-purple-600 to-indigo-700',
    'webassembly': 'bg-gradient-to-br from-purple-600 to-blue-600',
    'blockchain': 'bg-gradient-to-br from-yellow-500 to-orange-600',
    'ai': 'bg-gradient-to-br from-indigo-600 to-purple-700',
    'ml': 'bg-gradient-to-br from-green-500 to-teal-600',
    'dl': 'bg-gradient-to-br from-blue-600 to-indigo-700',
    'iot': 'bg-gradient-to-br from-green-500 to-blue-600',
    'ar': 'bg-gradient-to-br from-purple-500 to-pink-600',
    'vr': 'bg-gradient-to-br from-blue-500 to-purple-600'
  };
  return bgClasses[category] || 'bg-gradient-to-br from-gray-400 to-gray-600';
};








</script>
