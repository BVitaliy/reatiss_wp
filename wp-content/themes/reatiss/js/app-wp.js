// WordPress-only glue for editor content.
//
// Editors can't add data-modal-open to links in the classic editor, so a link
// to "#consultation" (or "#reports") opens that popup instead of jumping.
(function () {
  "use strict";

  var POPUPS = ["consultation", "reports"];

  document.addEventListener("click", function (event) {
    var link = event.target.closest && event.target.closest('a[href^="#"]');
    if (!link || !window.ReatissModal) return;

    var name = link.getAttribute("href").slice(1);
    if (POPUPS.indexOf(name) === -1 || link.hasAttribute("data-modal-open")) return;

    event.preventDefault();
    window.ReatissModal.open(name);
  });
})();
