/* global gsap, ScrollTrigger */
// Scroll parallax for media blocks (pattern from Ajour's wide media).
// Markup: [data-parallax-media][data-parallax-intensity="160"] >
//         [data-parallax-target]  (the layer is taller than the block by
//         --parallax-room in CSS and drifts -I/2 → +I/2 while the block
//         crosses the screen). Phones: 60% of the travel.
(function () {
  "use strict";

  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  gsap.registerPlugin(ScrollTrigger);

  var mqMobile = window.matchMedia("(max-width: 767.98px)");

  document.querySelectorAll("[data-parallax-media]").forEach(function (section) {
    var target = section.querySelector("[data-parallax-target]");
    if (!target) return;

    var base = parseFloat(section.getAttribute("data-parallax-intensity")) || 120;
    // never travel further than the layer's overflow (its negative top inset,
    // i.e. half of --parallax-room) — otherwise the block edge could show
    var intensity = function () {
      var room = -2 * (parseFloat(getComputedStyle(target).top) || 0);
      var wanted = base * (mqMobile.matches ? 0.6 : 1);
      return room > 0 ? Math.min(wanted, room) : wanted;
    };

    gsap.fromTo(
      target,
      { y: function () { return -intensity() / 2; } },
      {
        y: function () { return intensity() / 2; },
        ease: "none",
        scrollTrigger: {
          trigger: section,
          start: "top bottom",
          end: "bottom top",
          scrub: 1,
          invalidateOnRefresh: true,
          // composite only while the scrub is live
          onToggle: function (self) {
            target.style.willChange = self.isActive ? "transform" : "auto";
          },
        },
      }
    );
  });
})();
