/**
 * Trie un tableau d'objets ou de nombres par ordre croissant ou décroissant
 * @param {Array} arr - Le tableau à trier
 * @param {Boolean} [asc=true] - true pour ordre croissant

 */
export function orderMenuTitle(arr, asc = true) {
    if (!Array.isArray(arr)) return [];
  
    return [...arr].sort((a, b) => {
      const numA = extractLeadingNumber(a.title);
      const numB = extractLeadingNumber(b.title);
      return asc ? numA - numB : numB - numA;
    });
  }
  
  function extractLeadingNumber(title) {
    const match = title.match(/^\d+/);
    return match ? parseInt(match[0], 10) : 0;
  }
  