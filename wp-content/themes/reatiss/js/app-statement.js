/* global gsap, ScrollTrigger, SplitText */
// Statement text (template-parts/statement*.php, .js-statement). Plays by
// itself once it comes into view (not tied to the scroll — manager), at a
// steady pace for its length. Word fill (ref. PEK "home-statement"): words
// wait pale, the word at the front of the fill is lit in accent, read words
// turn black.
// Visible from the start (pale); without JS / with reduced motion the text
// is plain black.
(function () {
  "use strict";

  var blocks = document.querySelectorAll(".js-statement");
  if (!blocks.length) return;
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined" || typeof SplitText === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  gsap.registerPlugin(ScrollTrigger, SplitText);

  // once in view: progress 0 → 1 over time (~45ms a word, 1.2–3s), plays once
  function trigger(el, paint, perItem, count) {
    var state = { p: 0 };
    ScrollTrigger.create({
      trigger: el,
      start: "top 85%",
      once: true,
      onEnter: function () {
        gsap.to(state, {
          p: 1,
          duration: Math.min(3, Math.max(1.2, count * perItem)),
          ease: "none",
          onUpdate: function () {
            paint(state.p);
          },
          onComplete: function () {
            paint(1, true);
          },
        });
      },
    });
  }

  function wordFill(el) {
    var words = new SplitText(el, { type: "words", wordsClass: "statement__word" }).words;
    var count = words.length;
    var front = -1;

    function paint(progress, edge) {
      // n words reached; the last of them is the lit front
      var n = edge ? (progress ? count + 1 : 0) : Math.round(progress * (count + 1));
      if (n === front) return;
      front = n;
      words.forEach(function (w, i) {
        w.classList.toggle("is-on", i < n - 1);
        w.classList.toggle("is-lit", i === n - 1);
      });
    }

    paint(0, true);
    trigger(el, paint, 0.045, count);
  }

  // after the fonts: the words wrap as in the final layout
  var start = function () {
    blocks.forEach(wordFill);
    ScrollTrigger.refresh();
  };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start);
  else start();
})();
