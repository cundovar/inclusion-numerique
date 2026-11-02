import { ref } from 'vue';

const normalizeSearchText = (value = '') =>
  String(value)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

export function useSearch(menus, user, isMobile, onSearchComplete = null) {
  const search = ref('');
  const searchResults = ref([]);
  const searchAnalysis = ref(null);

  const closeSearchResults = () => {
    search.value = '';
    searchResults.value = [];
    searchAnalysis.value = null;
  };

  const launchSearch = async () => {
    if (!search.value.trim()) {
      searchResults.value = [];
      return;
    }

    if (search.value.trim().length < 2) {
      searchResults.value = [];
      return;
    }

    // Recherche locale dans les cours déjà chargés : aucun service externe ni compte requis
    const query = normalizeSearchText(search.value.trim());
    const stopWords = ['comment', 'faire', 'une', 'un', 'le', 'la', 'les', 'de', 'du', 'des', 'pour', 'avec', 'dans', 'sur', 'en', 'et', 'ou', 'que', 'qui', 'quoi', 'ou', 'quand'];
    const keywords = query
      .split(/\s+/)
      .filter(word => word.length > 2 && !stopWords.includes(word))
      .map(word => word.replace(/[?!.,;:]/g, ''));

    searchResults.value = menus.value.filter((menu) => {
      const title = menu.title?.toLowerCase() || "";
      const content = menu.content?.toLowerCase() || "";
      const label = menu.menu?.label?.toLowerCase() || "";
      const code = menu.code?.toLowerCase() || "";
      const slug = menu.page?.slug?.toLowerCase() || "";
      const category = menu.category?.name?.toLowerCase() || "";
      const type = menu.type?.toLowerCase() || "";

      let blockContent = "";
      if (menu.pageBlocks && Array.isArray(menu.pageBlocks)) {
        blockContent = menu.pageBlocks.map(block => {
          const blockTitle = block.title?.toLowerCase() || "";
          const blockContent = block.content?.toLowerCase() || "";
          const blockCode = block.code?.toLowerCase() || "";
          return blockTitle + " " + blockContent + " " + blockCode;
        }).join(" ");
      }

      const searchableText = normalizeSearchText([
        title,
        content,
        label,
        code,
        slug,
        category,
        type,
        blockContent
      ].join(" "));

      if (keywords.length > 0) {
        return keywords.some(keyword => searchableText.includes(keyword));
      }

      return searchableText.includes(query);
    });

    searchResults.value.sort((a, b) => {
      const aTitle = normalizeSearchText(a.title);
      const bTitle = normalizeSearchText(b.title);
      const aContent = normalizeSearchText(a.content);
      const bContent = normalizeSearchText(b.content);
      const aCategory = normalizeSearchText(a.category?.name);
      const bCategory = normalizeSearchText(b.category?.name);

      let scoreA = 0;
      let scoreB = 0;

      if (keywords.length > 0) {
        keywords.forEach(keyword => {
          if (aTitle.includes(keyword)) scoreA += 3;
          if (bTitle.includes(keyword)) scoreB += 3;
          if (aCategory.includes(keyword)) scoreA += 2;
          if (bCategory.includes(keyword)) scoreB += 2;
          if (aContent.includes(keyword)) scoreA += 1;
          if (bContent.includes(keyword)) scoreB += 1;
        });
      } else {
        if (aTitle.includes(query)) scoreA += 3;
        if (bTitle.includes(query)) scoreB += 3;
        if (aContent.includes(query)) scoreA += 1;
        if (bContent.includes(query)) scoreB += 1;
      }

      return scoreB - scoreA;
    });

    // Sur mobile, toujours ouvrir le modal (avec ou sans résultats)
    if (isMobile.value && onSearchComplete) {
      onSearchComplete();
    }

    // Sur desktop, scroller vers les résultats si présents
    if (searchResults.value.length > 0 && !isMobile.value) {
      setTimeout(() => {
        const resultsElement = document.querySelector('.search-results');
        if (resultsElement) {
          resultsElement.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
          });
        }
      }, 100);
    }
  };

  return {
    search,
    searchResults,
    searchAnalysis,
    launchSearch,
    closeSearchResults
  };
}
