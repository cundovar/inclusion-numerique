<template>
  <div class="home min-h-screen px-4 pb-40 pt-6 md:px-10 xl:pt-0">
    <!-- Présentation -->
    <section
      class="mx-auto max-w-5xl text-center"
      aria-labelledby="home-title"
    >
      <h1
        id="home-title"
        class="text-4xl font-bold md:text-5xl"
      >
        Le numérique, pas à pas
      </h1>
      <p class="mx-auto mt-5 max-w-3xl text-xl text-gray-700 md:text-2xl">
        Des cours gratuits et sans inscription, à suivre seul chez soi ou en atelier.
      </p>
    </section>

    <!-- Les deux entrées du site -->
    <nav
      class="mx-auto mt-10 grid max-w-5xl gap-6 md:grid-cols-2"
      aria-label="Choisir votre parcours"
    >
      <article
        v-for="audience in AUDIENCES"
        :key="audience.key"
        :class="[
          'surface-card entry-card group relative flex flex-col p-6 md:p-8',
          audience.cardBgClass
        ]"
        :data-testid="`entry-${audience.key}`"
      >
        <div
          :class="[audience.iconBgClass, 'mb-5 flex h-16 w-16 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110']"
          aria-hidden="true"
        >
          <i :class="[audience.icon, 'text-3xl text-gray-900']" />
        </div>

        <h2 class="text-2xl font-bold text-gray-900 md:text-3xl">
          <!-- Lien étiré : toute la carte est cliquable, le nom accessible reste le titre -->
          <router-link
            :to="audience.path"
            class="entry-link"
          >
            {{ audience.title }}
          </router-link>
        </h2>

        <p class="mt-3 text-lg text-gray-700">
          {{ audience.lead }}
        </p>

        <p class="mt-5 text-lg font-semibold text-gray-800">
          Par exemple :
        </p>
        <ul class="mt-2 space-y-2 text-lg text-gray-700">
          <li
            v-for="example in audience.examples"
            :key="example"
            class="flex items-start gap-3"
          >
            <i
              class="fas fa-check mt-1.5 text-green-700"
              aria-hidden="true"
            />
            <span>{{ example }}</span>
          </li>
        </ul>

        <span
          class="mt-6 inline-flex items-center gap-2 self-start text-lg font-bold text-blue-800 group-hover:underline"
          aria-hidden="true"
        >
          Commencer
          <i class="fas fa-arrow-right transition-transform duration-300 group-hover:translate-x-1" />
        </span>
      </article>
    </nav>

    <!-- Comment ça marche -->
    <section
      class="mx-auto mt-14 max-w-5xl"
      aria-labelledby="how-title"
    >
      <h2
        id="how-title"
        class="text-center text-2xl font-bold text-gray-900 md:text-3xl"
      >
        Comment ça marche ?
      </h2>
      <ul class="mt-6 grid gap-5 md:grid-cols-3">
        <li
          v-for="point in howItWorks"
          :key="point.title"
          class="rounded-2xl bg-white p-6 shadow"
        >
          <i
            :class="[point.icon, 'text-2xl text-blue-800']"
            aria-hidden="true"
          />
          <h3 class="mt-3 text-xl font-bold text-gray-900">
            {{ point.title }}
          </h3>
          <p class="mt-2 text-lg text-gray-700">
            {{ point.text }}
          </p>
        </li>
      </ul>
    </section>

    <!-- Le site n'est pas un service officiel -->
    <aside
      class="mx-auto mt-10 flex max-w-5xl items-start gap-4 rounded-2xl border-l-8 border-amber-500 bg-amber-50 p-6"
      aria-label="À savoir"
    >
      <i
        class="fas fa-circle-info mt-1 text-2xl text-amber-700"
        aria-hidden="true"
      />
      <p class="text-lg text-gray-800">
        Ce site n'est pas un service de l'administration. Il vous apprend à vous repérer :
        pour faire une démarche, utilisez toujours le site officiel (CAF, France Travail, Ameli…).
        N'indiquez jamais vos identifiants ou votre numéro de sécurité sociale sur ce site.
      </p>
    </aside>
  </div>
</template>

<script setup>
import { AUDIENCES } from '@/config/audiences'

const howItWorks = [
  {
    icon: 'fas fa-gift',
    title: 'Gratuit, sans compte',
    text: 'Aucune inscription : vous ouvrez un cours et vous commencez.',
  },
  {
    icon: 'fas fa-shoe-prints',
    title: 'Pas à pas',
    text: 'Chaque cours montre le geste, puis vous le refaites vous-même, à votre rythme.',
  },
  {
    icon: 'fas fa-people-roof',
    title: 'Seul ou en atelier',
    text: 'Les cours se suivent chez soi ou avec un médiateur, sur ordinateur, tablette ou téléphone.',
  },
]
</script>

<style scoped>
/* Le lien du titre couvre toute la carte */
.entry-link::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 1rem;
}

.entry-link:focus {
  outline: none;
}

/* Contour de focus bien visible au clavier, sur toute la carte */
</style>
