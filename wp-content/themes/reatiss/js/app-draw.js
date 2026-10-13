/* global gsap, ScrollTrigger */
// Line illustrations "draw" themselves (manager's idea: expertise cards and
// the stats block). The pictures come from the CMS as any image (PNG / WebP /
// SVG as <img>), so instead of stroking vector paths the drawing is faked on
// the raster: a diagonal wipe uncovers the art while a blue "pen tip" glows
// on the lines along the wipe edge (a layer masked by the same image).
//   .js-draw        wrapper around the <img> (the image fills it)
//   first scroll-in: draws in once (1.8s)
//   hover:          when the wrapper sits in a link card, redraws quickly
// Reduced motion / no GSAP: static picture.
(function () {
  "use strict";

  var els = document.querySelectorAll(".js-draw");
  if (!els.length) return;
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  gsap.registerPlugin(ScrollTrigger);
  var canHover = window.matchMedia("(min-width: 1200px)");

  Array.prototype.forEach.call(els, function (el) {
    var img = el.querySelector("img");
    if (!img) return;

    // the pen's CSS mask fetches the picture in CORS mode; a plain <img> is a
    // no-cors request, so the browser downloaded every drawing twice. A lazy
    // image not requested yet is switched to CORS too → one shared download.
    if (!img.complete && !img.crossOrigin) img.crossOrigin = "anonymous";

    // the pen: the same picture as a mask, tinted along the wipe edge
    var pen = document.createElement("span");
    pen.className = "draw-pen";
    pen.setAttribute("aria-hidden", "true");
    el.appendChild(pen);

    function setSource() {
      var src = img.currentSrc || img.src;
      if (!src) return;
      el.style.setProperty("--draw-src", 'url("' + src + '")');
      var fit = getComputedStyle(img).objectFit;
      el.style.setProperty("--draw-fit", fit === "contain" || fit === "cover" ? fit : "100% 100%");
    }
    if (img.complete && img.currentSrc) setSource();
    else img.addEventListener("load", setSource, { once: true });

    var tween = null;
    function draw(duration, ease) {
      if (tween && tween.isActive()) return;
      el.classList.add("is-drawing");
      tween = gsap.fromTo(el, { "--draw": 0 }, {
        "--draw": 1,
        duration: duration,
        ease: ease,
        onComplete: function () {
          el.classList.remove("is-drawing");
        },
      });
    }

    gsap.set(el, { "--draw": 0 });
    ScrollTrigger.create({
      trigger: el,
      start: "top 85%",
      once: true,
      onEnter: function () {
        draw(1.8, "power1.inOut");
      },
    });

    var card = el.closest("a, [data-draw-hover]");
    if (card) {
      card.addEventListener("mouseenter", function () {
        if (canHover.matches && !el.classList.contains("is-drawing")) draw(1, "power2.out");
      });
    }
  });
})();
