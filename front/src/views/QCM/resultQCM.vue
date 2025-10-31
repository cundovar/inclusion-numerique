<template>
  <div
    class="md:rounded-2xl md:mt-10 p-10 xl:mt-0 pb-96 text-blue-500 min-h-screen w-ful bg-cyan-100"
  >
    <h1 class="text-3xl font-bold text-blue-600 mb-6 text-center">
      Résultats du QCM
    </h1>

    <!-- Score global -->
    <div class="flex flex-wrap justify-center items-center">
      <h2>Score : {{ score }} / {{ totalQuestions }}</h2>
      <p>Pourcentage : {{ pourcentage }}%</p>
    </div>

    <!-- Détails des questions -->
    <div v-for="(qcm, index) in qcmStore.qcms" :key="qcm.id">
      <h3>Question {{ index + 1 }} : {{ qcm.titre }}</h3>

      <div class="border">
        <div class="border flex flex-wrap">
          <div>
            <p><strong>Votre réponse :</strong></p>
            <div class="border">
              {{ getAnswerText(qcm, index) }}
              <span v-if="isCorrect(qcm, index)">✅ Correct</span>
              <span v-else>❌ Incorrect</span>
            </div>
          </div>

          <div>
            <!-- Afficher la bonne réponse si erreur -->
            <div v-if="!isCorrect(qcm, index) || isCorrect(qcm, index)">
              <p><strong>Bonne réponse :</strong></p>
              <div>{{ getCorrectAnswerText(qcm) }}</div>
            </div>
          </div>
        </div>

        <!-- Explication (si disponible) -->
        <div class="flex flex-wrap">

          <div class="lareponse border w-1/3" v-if="getExplication(qcm)">
            <p><strong>Explication :</strong></p>
            <h1>solution</h1>
            <p>{{ getExplication(qcm) }}</p>
          </div>
          <div class="solution border w-2/3" v-html="getSolution(qcm)"></div>


        </div>
      </div>

      <hr />
    </div>

    <!-- Bouton recommencer -->
    <div>
      <button @click="recommencer">Recommencer le QCM</button>
      <button @click="retourAccueil">Retour à l'accueil</button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUpdated, nextTick, watch } from "vue";
import { useQCMStore } from "../../store/QCM";
import { useRouter } from "vue-router";
import Prism from "prismjs";
// N'importer QUE les langages (le thème est déjà dans main.js)
// IMPORTANT: L'ordre d'import est crucial pour les dépendances
import "prismjs/components/prism-markup"; // HTML (doit être avant markup-templating)
import "prismjs/components/prism-css";
import "prismjs/components/prism-clike"; // Requis pour JavaScript, PHP, etc.
import "prismjs/components/prism-javascript";
import "prismjs/components/prism-markup-templating"; // Requis pour PHP
import "prismjs/components/prism-php";
import "prismjs/components/prism-python";
import "prismjs/components/prism-sql";

const qcmStore = useQCMStore();
const router = useRouter();

console.log("=== DEBUG QCM SOLUTION ===");
console.log("Nombre de QCMs:", qcmStore.qcms.length);
if (qcmStore.qcms.length > 0) {
  console.log("Solution du premier QCM:", qcmStore.qcms[0].solution);
  console.log("Type:", typeof qcmStore.qcms[0].solution);
}
// Calculer le score
const score = computed(() => {
  let correct = 0;
  qcmStore.qcms.forEach((qcm, index) => {
    if (isCorrect(qcm, index)) {
      correct++;
    }
  });
  return correct;
});

const totalQuestions = computed(() => qcmStore.qcms.length);

const pourcentage = computed(() => {
  if (totalQuestions.value === 0) return 0;
  return Math.round((score.value / totalQuestions.value) * 100);
});

// Vérifier si la réponse est correcte
const isCorrect = (qcm, index) => {
  const userAnswer = qcmStore.answers[index];
  if (!userAnswer) return false;

  const correctChoice = qcm.choicesQCMs?.find((choice) => choice.isCorrect);
  return correctChoice?.id === userAnswer;
};

// Obtenir le texte de la réponse de l'utilisateur
const getAnswerText = (qcm, index) => {
  const userAnswer = qcmStore.answers[index];
  if (!userAnswer) return "Aucune réponse";

  const choice = qcm.choicesQCMs?.find((c) => c.id === userAnswer);
  return choice?.question || "Réponse introuvable";
};

// Obtenir le texte de la bonne réponse
const getCorrectAnswerText = (qcm) => {
  const correctChoice = qcm.choicesQCMs?.find((choice) => choice.isCorrect);
  return correctChoice?.question || "Réponse introuvable";
};

// Obtenir l'explication
const getExplication = (qcm) => {
  const correctChoice = qcm.choicesQCMs?.find((choice) => choice.isCorrect);

  return correctChoice?.explication || null;
};

const getSolution = (qcm) => {
  if (!qcm.solution) return "";

  // Si la solution contient déjà une classe language-*, on la retourne telle quelle
  if (
    qcm.solution.includes('class="language-') ||
    qcm.solution.includes("class='language-")
  ) {
    return qcm.solution;
  }

  // Sinon, on ajoute language-javascript par défaut sur les balises <code>
  let solution = qcm.solution;

  // Remplacer <code> par <code class="language-javascript">
  solution = solution.replace(/<code>/gi, '<code class="language-javascript">');

  // Remplacer <pre> par <pre class="language-javascript">
  solution = solution.replace(/<pre>/gi, '<pre class="language-javascript">');

  return solution;
};

// Fonction pour appliquer la coloration syntaxique
const applyPrismHighlight = async () => {
  await nextTick();
  // Attendre un court délai pour s'assurer que le DOM est mis à jour
  setTimeout(() => {
    Prism.highlightAll();
    console.log("Prism highlighting applied");
  }, 100);
};

// Appliquer Prism après le montage du composant
onMounted(() => {
  applyPrismHighlight();
});

// Réappliquer Prism à chaque mise à jour du DOM
onUpdated(() => {
  applyPrismHighlight();
});

// Watcher pour réappliquer Prism quand les QCMs changent
watch(
  () => qcmStore.qcms,
  () => {
    applyPrismHighlight();
  },
  { deep: true, immediate: true }
);

// Recommencer le QCM
const recommencer = () => {
  router.push({
    name: "questionQCM",
    params: { index: 0 },
    query: {
      language: qcmStore.filterQcms.language,
      niveau: qcmStore.filterQcms.niveau,
    },
  });
};

// Retour à l'accueil
const retourAccueil = () => {
  qcmStore.$reset();
  router.push({ name: "PageQCM" });
};
</script>

<style scoped>
/* Container principal */
.solution {
  margin: 20px 0;
  padding: 20px;
  background-color: #7c7e80;
  border-radius: 8px;
  color: #ffea2a;
}

/* Styles pour les titres dans le HTML injecté */
.solution :deep(h1) {
  font-size: 2rem;
  color: #2c3e50;
  margin-bottom: 1rem;
  margin-top: 0.5rem;
  border-bottom: 2px solid #3498db;
  padding-bottom: 0.5rem;
  font-weight: 600;
}

.solution :deep(h2) {
  font-size: 1.5rem;

  margin-top: 1.5rem;
  margin-bottom: 0.8rem;
  font-weight: 600;
}

.solution :deep(h3) {
  font-size: 1.2rem;

  margin-top: 1rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.solution :deep(h4) {
  font-size: 1.1rem;

  margin-top: 0.8rem;
  margin-bottom: 0.4rem;
  font-weight: 600;
}

/* Paragraphes */
.solution :deep(p) {
  line-height: 1.6;
  margin-bottom: 1rem;

  font-size: 1rem;
}

/* Blocs de code */
.solution :deep(pre) {
  margin: 1.5rem 0;
  border-radius: 6px;
  overflow-x: auto;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  background-color: #272822 !important; /* Force le fond du thème okaidia */
  padding: 1rem !important;
}

.solution :deep(pre[class*="language-"]) {
  margin: 1.5rem 0;
  border-radius: 6px;
  background-color: #272822 !important;
}

.solution :deep(code) {
  font-family: "Fira Code", "Consolas", "Monaco", "Courier New", monospace;
  font-size: 0.9rem;
  background: transparent !important;
}

.solution :deep(code[class*="language-"]) {
  color: #f8f8f2 !important; /* Couleur du texte okaidia */
  text-shadow: 0 1px rgba(0, 0, 0, 0.3);
  font-family: "Fira Code", "Consolas", "Monaco", "Courier New", monospace;
  direction: ltr;
  text-align: left;
  white-space: pre;
  word-spacing: normal;
  word-break: normal;
  line-height: 1.5;
  tab-size: 4;
}

/* Forcer les tokens Prism à s'afficher correctement */
.solution :deep(.token.comment),
.solution :deep(.token.prolog),
.solution :deep(.token.doctype),
.solution :deep(.token.cdata) {
  color: #8292a2 !important;
}

.solution :deep(.token.punctuation) {
  color: #f8f8f2 !important;
}

.solution :deep(.token.property),
.solution :deep(.token.tag),
.solution :deep(.token.constant),
.solution :deep(.token.symbol),
.solution :deep(.token.deleted) {
  color: #f92672 !important;
}

.solution :deep(.token.boolean),
.solution :deep(.token.number) {
  color: #ae81ff !important;
}

.solution :deep(.token.selector),
.solution :deep(.token.attr-name),
.solution :deep(.token.string),
.solution :deep(.token.char),
.solution :deep(.token.builtin),
.solution :deep(.token.inserted) {
  color: #a6e22e !important;
}

.solution :deep(.token.operator),
.solution :deep(.token.entity),
.solution :deep(.token.url),
.solution :deep(.language-css .token.string),
.solution :deep(.style .token.string),
.solution :deep(.token.variable) {
  color: #f8f8f2 !important;
}

.solution :deep(.token.atrule),
.solution :deep(.token.attr-value),
.solution :deep(.token.function),
.solution :deep(.token.class-name) {
  color: #e6db74 !important;
}

.solution :deep(.token.keyword) {
  color: #66d9ef !important;
}

.solution :deep(.token.regex),
.solution :deep(.token.important) {
  color: #fd971f !important;
}

/* Code inline */
.solution :deep(p code),
.solution :deep(li code) {
  background-color: #e8f4f8;
  color: #e74c3c;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 0.9em;
}

/* Listes */
.solution :deep(ul),
.solution :deep(ol) {
  margin-left: 2rem;
  margin-bottom: 1rem;
  line-height: 1.6;
}

.solution :deep(li) {
  margin-bottom: 0.5rem;
  color: #333;
}

.solution :deep(ul) {
  list-style-type: disc;
}

.solution :deep(ol) {
  list-style-type: decimal;
}

/* Texte en gras et italique */
.solution :deep(strong) {
  color: #2c3e50;
  font-weight: 700;
}

.solution :deep(em) {
  font-style: italic;
  color: #555;
}

/* Liens */
.solution :deep(a) {
  color: #3498db;
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: border-bottom 0.2s;
}

.solution :deep(a:hover) {
  border-bottom: 1px solid #3498db;
}

/* Tableaux */
.solution :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}

.solution :deep(th),
.solution :deep(td) {
  border: 1px solid #ddd;
  padding: 8px 12px;
  text-align: left;
}

.solution :deep(th) {
  background-color: #3498db;
  color: white;
  font-weight: 600;
}

.solution :deep(tr:nth-child(even)) {
  background-color: #f2f2f2;
}

/* Citations */
.solution :deep(blockquote) {
  border-left: 4px solid #3498db;
  padding-left: 1rem;
  margin: 1rem 0;
  color: #666;
  font-style: italic;
  background-color: #f0f7fb;
  padding: 1rem;
  border-radius: 4px;
}

/* Séparateurs */
.solution :deep(hr) {
  border: none;
  border-top: 2px solid #e0e0e0;
  margin: 2rem 0;
}
</style>
