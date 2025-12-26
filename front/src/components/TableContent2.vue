<template>
    <Teleport to="body">
 <nav
      v-if="toc.length > 0"
     >

     <div>
           <TocList
        v-if="activeTab === 'sommaire'"
        :toc="toc"
        :activeId="activeId"
        :showProgress="showProgress"
        :isOpen="true"
        :isMobile="isMobile()"
        :collapsedOnMobile="collapsedOnMobile"
        :bgCategorie="bgCategorie"
        @scroll-to="handleScrollTo"
        @close-mobile="toggleClosed"
      />
     </div>
      

 
    </nav>
    </Teleport>



</template>
<script setup>
    import { ref } from 'vue' 
    import TocList from './table-of-contents/TocList.vue'
    



const activeTab = ref('sommaire')
    const toc = ref([])
    
const props = defineProps({
  toc: {
    type: Array,
    required: true
  },
  
  activeId: {
    type: String,
    default: ''
  },
  
  showProgress: {
    type: Boolean,
    default: true
  },
  
  bgCategorie: {
    type: String,
    default: ''
  }
})
</script>