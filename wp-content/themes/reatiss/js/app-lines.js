/* global gsap, ScrollTrigger, MotionPathPlugin */
// Running lines (ref: terminal-industries.com/why-terminal).
// A blue dash travels along each thin stepped line on a loop, with a fading
// tail: copies of the dash that run the same path a moment later, each
// fainter and thinner, so the tail follows every bend of the line.
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
  var SPEED = 1.3; // client: a bit faster than the data-duration of each line
  var TAIL = 12; // trail copies behind the dash
  var TAIL_LAG = 0.022; // s between two copies (the tail's length follows the speed)

  document.querySelectorAll(".js-run-line").forEach(function (line) {
    var path = line.querySelector(".js-run-path");
    var dot = line.querySelector(".js-run-dot");
    if (!path || !dot) return;

    var duration = (parseFloat(line.getAttribute("data-duration")) || 18) / SPEED;
    var visible = false;
    var tl = null;

    // the tail: copies behind the dash, fading out and narrowing
    var tail = [];
    if (!reduceMotion) {
      for (var i = 1; i <= TAIL; i++) {
        var ghost = document.createElement("span");
        var k = 1 - i / (TAIL + 1);
        ghost.className = "run-line__dot run-line__dot--tail";
        ghost.style.setProperty("--tail-o", (0.55 * k * k).toFixed(3));
        ghost.style.setProperty("--tail-s", (0.35 + 0.65 * k).toFixed(3));
        dot.parentNode.insertBefore(ghost, dot);
        tail.push(ghost);
      }
    }

    function run(el, delay) {
      return gsap.to(el, {
        duration: duration,
        ease: EASE,
        motionPath: { path: path, align: path, alignOrigin: [0.5, 0.5], autoRotate: true },
        delay: delay,
      });
    }

    // Alignment is measured when the tween is created, so rebuild on resize
    // and continue from the same point of the loop.
    function build() {
      // start where the mockup draws the dash (data-start, 0–1 along the line)
      var start = parseFloat(line.getAttribute("data-start")) || 0;
      var time = tl ? tl.time() : start * duration;
      if (tl) tl.kill();

      line.classList.add("is-running");
      gsap.set([dot].concat(tail), { clearProps: "transform" });

      tl = gsap.timeline({ repeat: -1, repeatDelay: 1.5, paused: true });
      tl.add(run(dot, 0), 0);
      tail.forEach(function (ghost, n) {
        var lag = (n + 1) * TAIL_LAG;
        // hidden until its own run starts (it would sit at the line's corner)
        tl.set(ghost, { visibility: "hidden" }, 0);
        tl.set(ghost, { visibility: "visible" }, lag);
        tl.add(run(ghost, 0), lag);
      });
      tl.time(time);

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
