// Runs in <head> before first paint so there's no light/dark flash.
(function () {
  try {
    var saved = localStorage.getItem('theme');
    if (saved === 'light' || saved === 'dark') {
      document.documentElement.setAttribute('data-theme', saved);
    }
  } catch (e) { /* storage blocked — fall back to system preference */ }
})();
