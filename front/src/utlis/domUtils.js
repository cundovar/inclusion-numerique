export function scrollToTop(smooth = true) {
    window.scrollTo({
      top: 0,
      behavior: smooth ? 'smooth' : 'auto'
    });
  }
  
  export function focusElement(selector) {
    const el = document.querySelector(selector);
    if (el) el.focus();
  }