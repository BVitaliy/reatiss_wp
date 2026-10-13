/* global gsap, ScrollTrigger, SplitText */
// Statement text (template-parts/statement*.php, .js-statement). Plays by
// itself once it comes into view (not tied to the scroll — manager), at a
// steady pace for its length. Two variants to compare (keep the one that wins):
//   word fill (.statement)          ref. PEK "home-statement": words wait
//     pale, the word at the front of the fill is lit in accent, read words
//     turn black.
//   decode (.statement--decode)     the title "decode" motif: letters wait
//     pale; each letter the scroll reaches flickers through 0/1 bits in
//     accent for a moment and settles into a black letter.
//     Letters are locked to their glyph width (em) so bits never reflow.
// Visible from the start (pale); without JS / with reduced motion the text
// is plain black.
(function () {
  "use strict";

  var blocks = document.querySelectorAll(".js-statement");
  if (!blocks.length) return;
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined" || typeof SplitText === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  gsap.registerPlugin(ScrollTrigger, SplitText);

  var FLICKER = 0.34; // decode: s a letter spends as bits (as the titles)
  var TICK = 0.055; // decode: s between bit changes
  var STAGGER = 0.014; // decode: s between letters reached in one jump

  // once in view: progress 0 → 1 over time (~45ms a word / 12ms a letter,
  // 1.2–3s), plays once
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

  // ── word fill ────────────────────────────────────────────────────
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

  // ── decode ───────────────────────────────────────────────────────
  function glyphWidth(el, ch) {
    var probe = document.createElement("span");
    probe.style.cssText = "position:absolute;visibility:hidden;white-space:pre";
    probe.textContent = ch;
    el.appendChild(probe);
    var w = probe.getBoundingClientRect().width;
    probe.remove();
    return w;
  }

  function decode(el) {
    var split = new SplitText(el, { type: "words,chars", wordsClass: "statement__word", charsClass: "statement__char", aria: "auto" });
    var chars = split.chars;
    var count = chars.length;
    var fs = parseFloat(getComputedStyle(el).fontSize) || 16;
    var zero = glyphWidth(el, "0");

    chars.forEach(function (c) {
      var w = c.getBoundingClientRect().width;
      c.dataset.char = c.textContent;
      c.dataset.bits = w >= zero * 0.72 ? "01" : "1"; // narrow letters: "1" only
      c.style.width = w / fs + "em";
    });

    var reached = 0; // letters the scroll has passed

    function settle(c, state) {
      if (c._tw) {
        c._tw.kill();
        c._tw = null;
      }
      c.textContent = c.dataset.char;
      c.classList.remove("is-bit");
      c.classList.toggle("is-on", state === "on");
    }

    // a short burst of bits, then the real letter stays
    function flicker(c, delay) {
      if (!c.dataset.char.trim()) return settle(c, "on");
      var state = { t: 0 };
      var last = -1;
      var bits = c.dataset.bits;
      c._tw = gsap.to(state, {
        t: 1,
        duration: FLICKER,
        delay: delay,
        ease: "none",
        onStart: function () {
          c.classList.add("is-bit");
        },
        onUpdate: function () {
          var step = Math.floor((state.t * FLICKER) / TICK);
          if (step === last) return;
          last = step;
          c.textContent = bits.charAt((Math.random() * bits.length) | 0);
        },
        onComplete: function () {
          c._tw = null;
          settle(c, "on");
        },
      });
    }

    function paint(progress, edge) {
      var n = edge ? (progress ? count : 0) : Math.round(progress * count);
      if (n === reached) return;
      var i;
      if (n > reached) {
        // forward: the newly reached letters decode left → right
        for (i = reached; i < n; i++) flicker(chars[i], (i - reached) * STAGGER);
      } else {
        // back: letters past the front wait pale again
        for (i = n; i < reached; i++) settle(chars[i], "wait");
      }
      reached = n;
    }

    chars.forEach(function (c) { settle(c, "wait"); });
    trigger(el, paint, 0.012, count);
  }

  // after the fonts: decode measures the real glyphs
  var start = function () {
    blocks.forEach(function (el) {
      if (el.closest(".statement--decode")) decode(el);
      else wordFill(el);
    });
    ScrollTrigger.refresh();
  };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start);
  else start();
})();
