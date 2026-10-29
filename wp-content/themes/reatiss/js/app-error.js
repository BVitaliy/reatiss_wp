/* global gsap, DrawSVGPlugin */
// 404 art (template-parts/error-page.php, .js-error-art — img/404-art.svg
// inline: path 0 = the left 4, 1 = the 0, 2 = the right 4; the 0 replaced the
// logo mark — client). On load the digits draw their outlines like circuit
// traces (4 → 0 → 4), then fill in; after that a blue signal runs round the
// 0's outline every few seconds.
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
  if (paths.length < 3) return ready();

  var zero = paths[1];
  var ACCENT = getComputedStyle(document.documentElement).getPropertyValue("--clr-accent").trim() || "#2358fc";

  paths.forEach(function (p) {
    p.setAttribute("stroke", "currentColor");
    p.setAttribute("stroke-width", "1.5");
    p.setAttribute("stroke-linejoin", "round");
  });
  gsap.set(paths, { fillOpacity: 0, drawSVG: "0%" });

  // the signal: a short accent dash over the 0's outline
  var pulse = zero.cloneNode(false);
  pulse.setAttribute("fill", "none");
  pulse.setAttribute("stroke", ACCENT);
  pulse.setAttribute("stroke-width", "4");
  pulse.setAttribute("stroke-linecap", "round");
  zero.parentNode.appendChild(pulse);
  gsap.set(pulse, { drawSVG: "0% 0%", autoAlpha: 0 });
  ready();

  var tl = gsap.timeline({ delay: 0.3 });
  tl.to(paths, { drawSVG: "100%", duration: 1.1, ease: "power2.inOut", stagger: 0.4 })
    // fill in, outlines go
    .to(paths, { fillOpacity: 1, duration: 0.6, ease: "power1.out", stagger: 0.08 }, "-=0.4")
    .set(paths, { attr: { "stroke-width": 0 } })
    .add(signal, "+=0.6");

  function signal() {
    gsap.timeline({ repeat: -1, repeatDelay: 3 })
      .set(pulse, { autoAlpha: 1, drawSVG: "0% 0%" })
      .to(pulse, { drawSVG: "0% 14%", duration: 0.35, ease: "power1.in" })
      .to(pulse, { drawSVG: "86% 100%", duration: 1.6, ease: "none" })
      .to(pulse, { drawSVG: "100% 100%", duration: 0.35, ease: "power1.out" })
      .set(pulse, { autoAlpha: 0 });
  }
})();
