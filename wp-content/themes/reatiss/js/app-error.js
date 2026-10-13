/* global gsap, DrawSVGPlugin */
// 404 art (template-parts/error-page.php, .js-error-art — img/404-art.svg
// inline: path 0 = the left 4, 1–7 = the mark's traces and pads, 8 = the
// right 4). On load the shapes draw their outlines like circuit traces (left
// 4 → the mark, trace by trace → right 4), then fill in; after that a blue
// signal runs along the mark's traces every few seconds.
// Pre-hidden in style/_error-page.scss (3s CSS fail-safe); reduced motion /
// no GSAP: shown as is.
(function () {
  "use strict";

  var art = document.querySelector(".js-error-art");
  if (!art) return;
  var ready = function () { art.classList.add("is-ready"); };
  if (typeof gsap === "undefined" || typeof DrawSVGPlugin === "undefined") return ready();
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return ready();
  gsap.registerPlugin(DrawSVGPlugin);

  var paths = Array.prototype.slice.call(art.querySelectorAll("path"));
  if (paths.length < 9) return ready();

  var four1 = paths[0];
  var four2 = paths[8];
  // the mark in drawing order: outer traces in, the stripes, the pads
  var mark = [paths[7], paths[6], paths[1], paths[5], paths[4], paths[2], paths[3]];
  var ACCENT = getComputedStyle(document.documentElement).getPropertyValue("--clr-accent").trim() || "#2358fc";
  var BASE = getComputedStyle(art).color; // the art's own colour (--clr-main)

  paths.forEach(function (p) {
    p.setAttribute("stroke", "currentColor");
    p.setAttribute("stroke-width", "1.5");
    p.setAttribute("stroke-linejoin", "round");
  });
  gsap.set(paths, { fillOpacity: 0, drawSVG: "0%" });
  ready();

  var tl = gsap.timeline({ delay: 0.3 });
  tl.to(four1, { drawSVG: "100%", duration: 1.1, ease: "power2.inOut" })
    .to(mark, { drawSVG: "100%", duration: 0.9, ease: "power2.inOut", stagger: 0.12 }, "-=0.6")
    .to(four2, { drawSVG: "100%", duration: 1.1, ease: "power2.inOut" }, "-=0.7")
    // fill in, outlines go
    .to([four1].concat(mark, [four2]), { fillOpacity: 1, duration: 0.6, ease: "power1.out", stagger: 0.05 }, "-=0.4")
    .set(paths, { attr: { "stroke-width": 0 } })
    .add(signal, "+=0.6");

  // a pulse of accent along the mark, trace after trace, then a pause
  function signal() {
    gsap.timeline({ repeat: -1, repeatDelay: 3.2 })
      .to(mark, { fill: ACCENT, duration: 0.25, ease: "power1.out", stagger: 0.08 })
      .to(mark, { fill: BASE, duration: 0.6, ease: "power1.in", stagger: 0.08 }, 0.3);
  }
})();
