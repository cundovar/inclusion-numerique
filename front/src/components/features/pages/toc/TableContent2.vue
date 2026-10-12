<template>
  <Teleport to="body">
    <nav
      v-if="toc.length > 0"
    >
      <div>
        <TocList
          v-if="activeTab === 'sommaire'"
          :toc="toc"
          :active-id="activeId"
          :show-progress="showProgress"
          :is-open="true"
          :is-mobile="isMobile()"
          :collapsed-on-mobile="collapsedOnMobile"
          :bg-categorie="bgCategorie"
          @scroll-to="handleScrollTo"
          @close-mobile="toggleClosed"
        />
      </div>
    </nav>
  </Teleport>
</template>
<script setup>
    import { ref } from 'vue' 
    import TocList from './TocList.vue'
    



const activeTab = ref('sommaire')
    
defineProps({
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
