/* global gsap, ScrollTrigger */
// Lab detail — capabilities scroll story (template-parts/lab-capabilities.php).
// ≥1200 (no reduced motion): the stage is sticky (CSS); while its track
// scrolls by, the row matching the scroll position gets .is-active and the
// ones before it .is-past — the photo wipe and the panel stagger are CSS
// transitions, so the story plays both ways. The track length follows the
// number of rows (--n). Below 1200 the rows are a Swiper slider (init /
// destroy on the breakpoint, as the grid sliders), the first row stays
// .is-active for the CSS.
(function () {
  "use strict";

  var blocks = document.querySelectorAll(".js-capabilities");
  if (!blocks.length) return;

  // ── below 1200: the rows are a swipe slider ──
  if (typeof Swiper !== "undefined") {
    var mqSlider = window.matchMedia("(max-width: 1199.98px)");
    blocks.forEach(function (block) {
      var stage = block.querySelector(".capabilities__stage");
      var slider = null;
      function sync() {
        if (mqSlider.matches && !slider) {
          slider = new Swiper(stage, {
            slidesPerView: 1.08,
            spaceBetween: 10,
            speed: 600,
            grabCursor: true,
            breakpoints: { 768: { slidesPerView: 1.4 } },
            pagination: { el: block.querySelector(".capabilities__pagination"), clickable: true },
          });
        } else if (!mqSlider.matches && slider) {
          slider.destroy(true, true);
          slider = null;
        }
      }
      sync();
      mqSlider.addEventListener("change", sync);
    });
  }

  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  gsap.registerPlugin(ScrollTrigger);

  blocks.forEach(function (block) {
    var track = block.querySelector(".capabilities__track");
    var stage = block.querySelector(".capabilities__stage");
    var items = Array.prototype.slice.call(block.querySelectorAll(".capability"));
    if (!track || items.length < 2) return;
    block.style.setProperty("--n", items.length);
    var current = 0;

    function setActive(index) {
      if (index === current) return;
      current = index;
      items.forEach(function (it, k) {
        it.classList.toggle("is-active", k === index);
        it.classList.toggle("is-past", k < index);
      });
    }

    var mm = gsap.matchMedia();
    mm.add("(min-width: 1200px)", function () {
      var offset = function () {
        return parseFloat(getComputedStyle(stage).top) || 0;
      };
      ScrollTrigger.create({
        trigger: track,
        start: function () { return "top " + offset(); },
        end: function () { return "bottom " + (offset() + stage.offsetHeight); },
        invalidateOnRefresh: true,
        onUpdate: function (self) {
          // switch half way between two rows
          setActive(Math.min(items.length - 1, Math.floor(self.progress * (items.length - 1) + 0.5)));
        },
      });
      return function () {
        setActive(0);
      };
    });
  });
})();
