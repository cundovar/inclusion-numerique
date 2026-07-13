export function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
  
  export function slugify(text) {
    return text.toString().toLowerCase()
      .normalize('NFD')                   // remove accents
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, '-')               // spaces to -
      .replace(/[^\w\-]+/g, '')           // remove non-words
      .replace(/\-\-+/g, '-')             // collapse --
      .replace(/^-+/, '')                 // trim -
      .replace(/-+$/, '');                // trim -
  }