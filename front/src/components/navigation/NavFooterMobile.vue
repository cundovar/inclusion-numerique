<template>
  <footer
    ref="footerRef"
    class="min-w-[320px] p-3 md:p-4 rounded-t-2xl fixed bottom-0 left-0 right-0 h-20 md:h-24 bg-sky-50 z-50 flex items-center justify-around xl:hidden gap-2 md:gap-4 nav-footer"
  >
    <nav
      aria-label="Navigation principale"
      class="contents"
    >
      <router-link
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        :class="[
          'shadow-sm p-2 md:p-3 rounded-xl min-w-14 md:w-24 h-14 md:h-16 text-gray-900 font-bold hover:underline flex flex-col items-center justify-center transition-colors duration-200',
          `nav-btn nav-btn--${link.color}`
        ]"
      >
        <i
          :class="link.icon"
          class="text-2xl md:text-3xl mb-1"
          aria-hidden="true"
        />
        <span class="text-xs md:text-sm">{{ link.label }}</span>
      </router-link>
    </nav>
  </footer>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Headroom from 'headroom.js';

const footerRef = ref(null);
let headroom = null;

// Les deux entrées du site restent accessibles depuis toutes les pages sur mobile
const links = [
  { to: '/', label: 'Accueil', icon: 'fas fa-house', color: 'blue' },
  { to: '/apprendre', label: 'Apprendre', icon: 'fas fa-laptop', color: 'green' },
  { to: '/accompagner', label: 'Accompagner', icon: 'fas fa-people-group', color: 'amber' },
  { to: '/qcm', label: 'QCM', icon: 'fas fa-brain', color: 'purple' },
];

onMounted(() => {
  // Headroom.js pour le footer (logique inversée)
  if (footerRef.value) {
    headroom = new Headroom(footerRef.value, {
      offset: 100,
      tolerance: {
        up: 10,
        down: 10
      },
      classes: {
        initial: 'footer-headroom',
        pinned: 'footer-headroom--pinned',
        unpinned: 'footer-headroom--unpinned',
        top: 'footer-headroom--top',
        notTop: 'footer-headroom--not-top'
      }
    });
    headroom.init();
  }
});

onUnmounted(() => {
  if (headroom) {
    headroom.destroy();
  }
});
</script>



<style scoped>

.nav-footer {
  box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
}

.nav-btn {
  background: #dbeafe;
}

.nav-btn:hover {
  background: #bfdbfe;
}

.nav-btn:active,
.nav-btn:focus-visible {
  background: #bfdbfe;
}

.nav-btn:focus-visible {
  outline: 3px solid #94a3b8;
  outline-offset: 2px;
}

/* Page en cours */
.nav-btn.router-link-active {
  box-shadow: inset 0 -4px 0 #fff, 0 0 0 3px #cbd5e1;
}

.nav-btn--purple {
  background: #f3e8ff;
}

.nav-btn--purple:hover,
.nav-btn--purple:active,
.nav-btn--purple:focus-visible {
  background: #e9d5ff;
}

.nav-btn--green {
  background: #dcfce7;
}

.nav-btn--green:hover,
.nav-btn--green:active,
.nav-btn--green:focus-visible {
  background: #bbf7d0;
}

.nav-btn--amber {
  background: #fef3c7;
}

.nav-btn--amber:hover,
.nav-btn--amber:active,
.nav-btn--amber:focus-visible {
  background: #fde68a;
}

.footer-mobil{
    z-index: 1000 !important;
}
</style>

<style>
/* Headroom.js pour footer - logique inversée (glisse vers le bas) */
.footer-headroom {
  will-change: transform;
  transition: transform 0.3s ease-in-out !important;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

.footer-headroom--pinned {
  transform: translateY(0%) !important;
}

.footer-headroom--unpinned {
  transform: translateY(100%) !important;
}

.footer-headroom--top {
  transform: translateY(0%) !important;
}

.footer-headroom--not-top.footer-headroom--pinned {
  transform: translateY(0%) !important;
}

.footer-headroom--not-top.footer-headroom--unpinned {
  transform: translateY(100%) !important;
}
</style>
