<template>
  <!-- Ce composant n'a pas de rendu visuel, il gère seulement les meta tags -->
</template>

<script setup>
import { watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  seoData: {
    type: Object,
    default: null
  }
})

// Plus de valeurs par défaut - on garde ce qui est déjà dans le HTML si rien n'est fourni

// Stocker les références aux éléments créés pour les nettoyer plus tard
const createdElements = []

// Fonction pour créer ou mettre à jour une meta tag
function setMetaTag(name, content, isProperty = false) {
  const attr = isProperty ? 'property' : 'name'
  let meta = document.querySelector(`meta[${attr}="${name}"]`)

  if (!meta) {
    meta = document.createElement('meta')
    meta.setAttribute(attr, name)
    document.head.appendChild(meta)
    createdElements.push(meta)
  }

  meta.setAttribute('content', content)
}

// Fonction pour créer ou mettre à jour un link tag
function setLinkTag(rel, href) {
  let link = document.querySelector(`link[rel="${rel}"]`)

  if (!link) {
    link = document.createElement('link')
    link.setAttribute('rel', rel)
    document.head.appendChild(link)
    createdElements.push(link)
  }

  link.setAttribute('href', href)
}

// Fonction pour supprimer une meta tag
function removeMetaTag(name, isProperty = false) {
  const attr = isProperty ? 'property' : 'name'
  const meta = document.querySelector(`meta[${attr}="${name}"]`)
  if (meta) {
    meta.remove()
  }
}

// Fonction pour mettre à jour les meta tags
function updateSeo() {
  // Ne rien faire si pas de données SEO fournies
  if (!props.seoData) {
    return
  }

  const seo = props.seoData

  // Titre de la page (seulement si fourni)
  if (seo.title) {
    document.title = seo.title
  }

  // Meta description (seulement si fournie)
  if (seo.metaDescription) {
    setMetaTag('description', seo.metaDescription)
  }

  // Meta keywords
  if (seo.metaKeywords) {
    setMetaTag('keywords', seo.metaKeywords)
  } else {
    removeMetaTag('keywords')
  }

  // Robots meta
  if (seo.noIndex || seo.noFollow) {
    const robotsContent = [
      seo.noIndex ? 'noindex' : 'index',
      seo.noFollow ? 'nofollow' : 'follow'
    ].join(', ')
    setMetaTag('robots', robotsContent)
  } else {
    removeMetaTag('robots')
  }

  // Open Graph (seulement si fourni)
  if (seo.ogTitle || seo.title) {
    setMetaTag('og:title', seo.ogTitle || seo.title, true)
  }
  if (seo.ogDescription || seo.metaDescription) {
    setMetaTag('og:description', seo.ogDescription || seo.metaDescription, true)
  }
  setMetaTag('og:type', 'website', true)

  if (seo.ogImage) {
    setMetaTag('og:image', seo.ogImage, true)
  }

  // Twitter Card (seulement si fourni)
  setMetaTag('twitter:card', 'summary_large_image')
  if (seo.ogTitle || seo.title) {
    setMetaTag('twitter:title', seo.ogTitle || seo.title)
  }
  if (seo.ogDescription || seo.metaDescription) {
    setMetaTag('twitter:description', seo.ogDescription || seo.metaDescription)
  }

  if (seo.ogImage) {
    setMetaTag('twitter:image', seo.ogImage)
  }

  // Canonical URL
  if (seo.canonicalUrl) {
    setLinkTag('canonical', seo.canonicalUrl)
  }
}

// Mettre à jour les meta tags au montage et à chaque changement de seoData
onMounted(() => {
  updateSeo()
})

watch(() => props.seoData, () => {
  updateSeo()
}, { deep: true })

// Nettoyer les éléments créés lors du démontage
onUnmounted(() => {
  createdElements.forEach(element => {
    if (element.parentNode) {
      element.parentNode.removeChild(element)
    }
  })
  createdElements.length = 0
})
</script>
