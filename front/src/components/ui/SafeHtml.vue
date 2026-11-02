<template>
  <!-- eslint-disable vue/no-v-html, vue/no-v-text-v-html-on-component -- contenu nettoyé par DOMPurify -->
  <component
    :is="tag"
    v-html="clean"
  />
  <!-- eslint-enable vue/no-v-html, vue/no-v-text-v-html-on-component -->
</template>

<script>
import DOMPurify from "dompurify"

// Dossier public des captures (voir CourseMedia::PUBLIC_DIR côté Symfony)
const CAPTURE_DIR = "/uploads/course-media/"

// Crochet enregistré une seule fois pour le module, et non à chaque affichage du composant
//securisation des liens
DOMPurify.addHook("afterSanitizeAttributes",(node)=>{
    if(node.tagName==="A"){
        const href=node.getAttribute("href") || ""
        //bloquage js: et data:
        if (/^\s*(javascript:|data:)/i.test(href)) node.removeAttribute("href");
        // si lien externe et target blank
        if (node.getAttribute("target") === "_blank") {
      node.setAttribute("rel", "noopener noreferrer");
    }

    }
    // Seules les captures gérées dans le back-office (source, date, texte alternatif) sont affichées :
    // pas d'image d'un autre site, donc pas de traceur ni d'image sans description
    if(node.tagName==="IMG"){
        const src=node.getAttribute("src") || ""
        if (!src.startsWith(CAPTURE_DIR) || src.includes("..")) node.remove();
    }
})
</script>

<script setup>

    import { computed } from 'vue';



    const props=defineProps({
        html: {
          type: String,
          default: ''
        },
        tag: {
          type: String,
          default: 'div'
        }
    })


const clean=computed(()=>{
    return DOMPurify.sanitize(props.html ?? "",{
        //accpeter
        ALLOWED_TAGS: [
           "h1","h2","h3","h4","h5","h6",
      "p","br","hr",
      "strong","em","u","s","mark","small",
      "ul","ol","li",
      "blockquote",
      "pre","code",
      "span","div","main",
      "table","thead","tbody","tr","th","td",
      "a","details","summary",
      "figure","figcaption","img"

        ],
        ALLOWED_ATTR:[
               "href","title","target","rel",
      "src","alt","width","height","loading","role","aria-label",
      // SI CLASS POUR STYLE
      "class"
        ],
        FORBID_TAGS: [
            "script","iframe","object","embed"
        ],
        FORBID_ATTR:[
             "style", 
      "onerror","onclick","onload","onmouseover" // en pratique DOMPurify bloque déjà les on*
        ]
    })
})



</script>
