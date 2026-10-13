/* global ScrollTrigger */
// Page background follows the dark sections (ref. hinaus.art). On a page
// whose <main> has .js-page-theme, every [data-page-theme="dark"] section
// switches html.is-page-dark while it holds the middle of the screen: the
// page colours (registered in main/_global-settings.scss) transition, so the
// blocks before and after go dark together with it. The dark sections have no
// background of their own then (html.has-page-theme): the whole page changes
// colour, no dark block slides in with a visible edge (ref. lxlcreative.co.uk).
// Their content uses the page tokens, so it reads on both colours. Off with
// reduced motion (the sections keep their own dark background).
(function () {
  "use strict";

  var main = document.querySelector("main.js-page-theme");
  if (!main || typeof ScrollTrigger === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  var root = document.documentElement;
  var active = 0;
  // the dark sections drop their own background and follow the page
  root.classList.add("has-page-theme");

  main.querySelectorAll('[data-page-theme="dark"]').forEach(function (section) {
    ScrollTrigger.create({
      trigger: section,
      start: "top 55%",
      end: "bottom 45%",
      onToggle: function (self) {
        active += self.isActive ? 1 : -1;
        root.classList.toggle("is-page-dark", active > 0);
      },
    });
  });
})();
