/* global gsap, ScrollTrigger, SplitText */
// Title reveal — words: when a section title scrolls in, its words rise in
// one after another (fade + a short lift + a light blur that clears).
// Titles: section titles (.h2), big statements / page titles (.h3, .h1--sm)
// inside <main>; the hero h1 has its own intro (app-hero.js).
// The split is reverted afterwards, so the heading is plain text again.
// Pre-hidden by CSS (html.js … :not(.is-title-ready)); a CSS fail-safe
// shows them after 3s if this script never runs.
(function () {
  "use strict";

  var SELECTOR = "main .h2, main .h3, main .h1--sm";
  var titles = Array.prototype.slice.call(document.querySelectorAll(SELECTOR)).filter(function (el) {
    return !el.closest(".home-hero") && !el.closest("[data-title-anim='off']");
  });
  if (!titles.length) return;

  var ok = typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined" && typeof SplitText !== "undefined" && !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function ready(el) {
    el.classList.add("is-title-ready");
  }

  if (!ok) {
    titles.forEach(ready);
    return;
  }

  gsap.registerPlugin(ScrollTrigger, SplitText);

  var DURATION = 0.8; // s one word takes
  var MAX_SPREAD = 0.9; // s from the first to the last word, long titles included

  function reveal(el) {
    var split = new SplitText(el, { type: "words", wordsClass: "title-word", aria: "auto" });
    var words = split.words;
    gsap.set(words, { opacity: 0, y: "0.4em", filter: "blur(6px)" });
    ready(el);

    ScrollTrigger.create({
      trigger: el,
      start: "top 88%",
      once: true,
      onEnter: function () {
        gsap.to(words, {
          opacity: 1,
          y: 0,
          filter: "blur(0px)",
          duration: DURATION,
          ease: "power3.out",
          stagger: Math.min(0.08, MAX_SPREAD / Math.max(words.length, 1)),
          onComplete: function () {
            split.revert();
            el.classList.add("is-title-done");
          },
        });
      },
    });
  }

  // after the fonts: the words wrap as in the final layout
  var start = function () {
    titles.forEach(reveal);
    ScrollTrigger.refresh();
  };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start);
  else start();
})();
