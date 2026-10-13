/* global gsap, ScrollTrigger, SplitText */
// Title reveal — "decode": when a section title scrolls in, its letters
// resolve left to right; each one flickers through 0/1 bits in the accent
// colour for a moment and then settles into the real letter (the site is
// about reverse engineering — reading a chip back into a design).
// Not the line-mask rise used on PEK.
// Titles: section titles (.h2), big statements / page titles (.h3, .h1--sm)
// inside <main>; the hero h1 has its own intro (app-hero.js).
// Letters get a fixed width before the flicker, so the bits never reflow
// the line; the split is kept afterwards (reverting shifts the letters).
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

  var BITS = "01";
  var FLICKER = 0.34; // s a letter spends as bits
  var TICK = 0.055; // s between bit changes
  var MAX_SPREAD = 1.1; // s from the first to the last letter, long titles included

  // width of a glyph in the title's font
  function bitWidth(el, ch) {
    var probe = document.createElement("span");
    probe.style.cssText = "position:absolute;visibility:hidden;white-space:pre";
    probe.textContent = ch;
    el.appendChild(probe);
    var w = probe.getBoundingClientRect().width;
    probe.remove();
    return w;
  }

  function decode(el) {
    var split = new SplitText(el, { type: "words,chars", charsClass: "title-char", wordsClass: "title-word", aria: "auto" });
    var chars = split.chars;

    // lock every letter to its final width (bits are wider than i / l / t)
    // (in em: follows the root scale if the window is resized before the reveal)
    var fs = parseFloat(getComputedStyle(el).fontSize) || 16;
    var zero = bitWidth(el, "0");
    chars.forEach(function (c) {
      var w = c.getBoundingClientRect().width;
      c.dataset.char = c.textContent;
      // narrow letters (i, l, r, t…) only flicker "1": a "0" would spill over
      c.dataset.bits = w >= zero * 0.72 ? BITS : "1";
      c.style.width = w / fs + "em";
    });
    gsap.set(chars, { opacity: 0 });
    ready(el);

    var stagger = Math.min(0.03, MAX_SPREAD / Math.max(chars.length, 1));

    ScrollTrigger.create({
      trigger: el,
      start: "top 88%",
      once: true,
      onEnter: function () {
        chars.forEach(function (c, i) {
          var final = c.dataset.char;
          if (!final.trim()) {
            gsap.set(c, { opacity: 1, delay: i * stagger });
            return;
          }
          var state = { t: 0 };
          var last = -1;
          gsap.to(state, {
            t: 1,
            duration: FLICKER,
            delay: i * stagger,
            ease: "none",
            onStart: function () {
              c.classList.add("is-bit");
              gsap.set(c, { opacity: 1 });
            },
            onUpdate: function () {
              var step = Math.floor((state.t * FLICKER) / TICK);
              if (step === last) return;
              last = step;
              var bits = c.dataset.bits;
              c.textContent = bits.charAt((Math.random() * bits.length) | 0);
            },
            onComplete: function () {
              c.textContent = final;
              c.classList.remove("is-bit");
            },
          });
        });

        // The split stays: reverting to one text node brings kerning back and
        // the letters visibly shift at the end. Widths are in em and words
        // wrap as usual, so the split heading behaves like plain text.
        gsap.delayedCall(chars.length * stagger + FLICKER + 0.1, function () {
          gsap.set(chars, { clearProps: "opacity" });
          el.classList.add("is-title-done");
        });
      },
    });
  }

  // after the fonts: widths are measured from the real glyphs
  var start = function () {
    titles.forEach(decode);
    ScrollTrigger.refresh();
  };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start);
  else start();
})();
