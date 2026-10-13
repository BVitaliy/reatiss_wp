/* global gsap, ScrollTrigger, MotionPathPlugin */
// Running lines (ref: terminal-industries.com/why-terminal).
// A blue dash travels along each thin stepped line on a loop.
// Runs only while the line is on screen.
// Markup (see home-expertise.php):
//   .js-run-line[data-duration][data-start]
//     svg > path.js-run-path
//     span.js-run-dot
(function () {
  "use strict";

  if (typeof gsap === "undefined" || typeof MotionPathPlugin === "undefined") return;
  gsap.registerPlugin(MotionPathPlugin, ScrollTrigger);

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var EASE = "none"; // steady speed — an in-out ease doubles the pace mid-line

  document.querySelectorAll(".js-run-line").forEach(function (line) {
    var path = line.querySelector(".js-run-path");
    var dot = line.querySelector(".js-run-dot");
    if (!path || !dot) return;

    var duration = parseFloat(line.getAttribute("data-duration")) || 18;
    var visible = false;
    var tl = null;

    // Alignment is measured when the tween is created, so rebuild on resize
    // and continue from the same point of the loop.
    function build() {
      // start where the mockup draws the dash (data-start, 0–1 along the line)
      var progress = tl ? tl.progress() : parseFloat(line.getAttribute("data-start")) || 0;
      if (tl) tl.kill();

      line.classList.add("is-running");
      gsap.set(dot, { clearProps: "transform" });

      tl = gsap.timeline({ repeat: -1, repeatDelay: 1.5, paused: true });
      tl.to(
        dot,
        {
          duration: duration,
          ease: EASE,
          motionPath: { path: path, align: path, alignOrigin: [0.5, 0.5], autoRotate: true },
        },
        0
      );
      tl.progress(progress);

      if (reduceMotion) return; // static dash at its mockup position
      if (visible) tl.play();
    }

    build();

    if (!reduceMotion) {
      ScrollTrigger.create({
        trigger: line,
        start: "top bottom",
        end: "bottom top",
        onToggle: function (self) {
          visible = self.isActive;
          if (visible) tl.play();
          else tl.pause();
        },
      });
    }

    var lastW = window.innerWidth;
    var timer = null;
    window.addEventListener("resize", function () {
      if (window.innerWidth === lastW) return;
      lastW = window.innerWidth;
      clearTimeout(timer);
      timer = setTimeout(build, 200);
    });
  });
})();
