/* global gsap, ScrollTrigger */
// Content reveal on scroll (ref. cofactr.com), on every page; a page opts out
// with <main data-reveal="off"> (elements.php). Titles are left alone
// (app-titles.js decodes them), so are the hero / page banners, the statement,
// the pinned scenes (solutions, capabilities) and the about drift / timeline.
//
// Rules: one CSS selector (inside <main>) → one reveal; elements of a rule
// that cross the line together come in one after another (ScrollTrigger.batch).
//   rise     text / buttons / small blocks: up 32, from blur 8px, fade in
//   fade     cards: up 60 and fade, one after another — nothing else moves
//   card     photo cards (labs, team): the same + their photo zooms out of
//            1.25 and brightens (cut shapes keep their clip-path, only the
//            img moves)
//   line     step lines: wiped in from the left
//   trace    the running stepped lines: wiped in along x
// Each reveal plays once. Reduced motion / no GSAP: nothing is hidden.
(function () {
  "use strict";

  var main = document.querySelector("main:not([data-reveal='off'])");
  if (!main) return;
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  gsap.registerPlugin(ScrollTrigger);

  var EASE = "power3.out";
  var START = "top 88%";
  // blocks with motion of their own — nothing inside them is touched
  var SKIP = ".home-hero, .js-solutions, .page-banner, .statement, .capabilities, .js-history, .about-intro, .about-stats, .contacts-map";

  var RULES = [
    // section heads: the link next to a title
    { sel: ".section-head .link-line, .home-faq__head .link-line", type: "rise" },
    { sel: ".home-expertise__more", type: "rise" },

    // cards
    { sel: ".expertise-card, .stat-card, .news-card, .info-card, .reveal-card, .offer-card", type: "fade" },
    { sel: ".lab-card", type: "card" },

    // left-right blocks: the text column (the photo only drifts)
    { sel: ".left-right__line", type: "line" },
    { sel: ".left-right__text > *, .left-right__btn", type: "rise" },

    // lists, FAQ, boxes
    { sel: ".result-list > li", type: "rise" },
    { sel: ".faq__item", type: "rise" },
    { sel: ".faq .step-line", type: "line" },
    { sel: ".form-box, .cta-box, .seo-block__box, .pager", type: "rise" },

    // decorative running lines
    { sel: ".run-line__svg", type: "trace" },
  ];

  // ── start states ────────────────────────────────────────────────
  function hide(el, type) {
    switch (type) {
      case "rise":
        gsap.set(el, { autoAlpha: 0, y: 32, filter: "blur(8px)" });
        break;
      case "fade":
        gsap.set(el, { autoAlpha: 0, y: 60 });
        break;
      case "card": {
        gsap.set(el, { autoAlpha: 0, y: 60 });
        var img = el.querySelector("img");
        // no CSS transition while the tween runs (the hover zoom would drag it)
        if (img) gsap.set(img, { transition: "none", scale: 1.25, filter: "brightness(0.55)" });
        break;
      }
      case "line":
        gsap.set(el, { clipPath: "inset(0% 100% 0% 0%)" });
        break;
      case "trace":
        gsap.set(el, { clipPath: "inset(-10% 100% -10% 0%)" });
        break;
    }
  }

  // ── reveals ─────────────────────────────────────────────────────
  function play(batch, type) {
    switch (type) {
      case "rise":
        gsap.to(batch, {
          autoAlpha: 1, y: 0, filter: "blur(0px)",
          duration: 1.1, ease: EASE, stagger: 0.09,
          clearProps: "filter,transform",
        });
        break;
      case "fade":
        gsap.to(batch, {
          autoAlpha: 1, y: 0,
          duration: 1, ease: EASE, stagger: 0.12,
          clearProps: "transform",
        });
        break;
      case "card":
        batch.forEach(function (el, i) {
          var delay = i * 0.12;
          gsap.to(el, { autoAlpha: 1, y: 0, duration: 1, ease: EASE, delay: delay, clearProps: "transform" });
          var img = el.querySelector("img");
          if (img) {
            gsap.to(img, { scale: 1, filter: "brightness(1)", duration: 1.8, ease: "expo.out", delay: delay, clearProps: "transition,filter,scale,transform" });
          }
        });
        break;
      case "line":
        gsap.to(batch, {
          clipPath: "inset(0% 0% 0% 0%)",
          duration: 1.3, ease: "power2.inOut", stagger: 0.08,
          clearProps: "clipPath",
        });
        break;
      case "trace":
        gsap.to(batch, {
          clipPath: "inset(-10% 0% -10% 0%)",
          duration: 2.4, ease: "power2.inOut",
          clearProps: "clipPath",
        });
        break;
    }
  }

  RULES.forEach(function (rule) {
    var els = Array.prototype.slice.call(main.querySelectorAll(rule.sel)).filter(function (el) {
      return !el.closest(SKIP);
    });
    if (!els.length) return;
    els.forEach(function (el) { hide(el, rule.type); });
    ScrollTrigger.batch(els, {
      start: START,
      once: true,
      onEnter: function (batch) { play(batch, rule.type); },
    });
  });
})();
