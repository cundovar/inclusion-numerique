<template>


<div class="class" v-html="clean" ></div>


</template>

<script setup>

    import { computed } from 'vue';
    import DOMPurify from "dompurify"


    const props=defineProps({
        html:{type:String, defaulf:""},
        class:{type:String,defaulf:""}
    })

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
      "a","details","summary"

        ],
        ALLOWED_ATTR:[
               "href","title","target","rel",
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