/* global gsap, ScrollTrigger */
// Home hero (refs: dsgninterior.se, hut8.com).
// Intro: photo settles from a slight zoom, content rises in.
// Scroll (scrubbed): photo drifts down at ~half speed and zooms out a touch,
// hexagons drift slower still, content lifts (no fade), shade deepens —
// so the next section reads as sliding over the banner.
// Transforms are split between wrapper and child so intro and scroll never
// fight over the same property.
(function () {
  "use strict";

  var hero = document.querySelector(".js-hero");
  if (!hero) return;

  var content = hero.querySelector(".js-hero-content");
  var items = content ? content.children : [];
  var canAnimate =
    typeof gsap !== "undefined" &&
    typeof ScrollTrigger !== "undefined" &&
    !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (!canAnimate) {
    hero.classList.add("is-ready");
    return;
  }

  var media = hero.querySelector(".js-hero-media");
  // the picture, and the background loop in the video variant
  var img = media ? media.querySelectorAll("img, video") : [];
  var shade = hero.querySelector(".js-hero-shade");
  var grid = hero.querySelector(".js-hero-grid");

  // ── intro ──
  gsap.set(items, { autoAlpha: 0, y: 40 });
  hero.classList.add("is-ready");

  gsap.fromTo(img, { scale: 1.15 }, { scale: 1, duration: 2.2, ease: "power3.out" });
  gsap.to(items, {
    autoAlpha: 1,
    y: 0,
    duration: 1.2,
    ease: "power3.out",
    stagger: 0.12,
    delay: 0.25,
    clearProps: "transform",
  });

  // ── scroll ──
  gsap
    .timeline({
      defaults: { ease: "none" },
      scrollTrigger: {
        trigger: hero,
        start: "top top",
        end: "bottom top",
        scrub: true,
        invalidateOnRefresh: true,
      },
    })
    .fromTo(media, { yPercent: 0, scale: 1.08 }, { yPercent: 45, scale: 1 }, 0)
    .to(grid, { yPercent: 20 }, 0)
    .to(content, { y: function () { return -hero.offsetHeight * 0.25; } }, 0) // lifts, stays fully opaque
    .to(shade, { opacity: 0.75 }, 0);
})();
