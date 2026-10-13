// 3D model (template-parts/model-3d.php, .js-model[data-src]).
// Google's <model-viewer> (js/vendors/model-viewer.min.js, an ES module with
// three.js inside, ~1 MB) is fetched only when a model block nears the
// viewport, then the element is created over the poster picture; the poster
// fades out once the model has loaded. No WebGL / load error → the poster
// simply stays.
//   turn   mouse / finger drag (camera-controls)
//   zoom   the +/− buttons, Ctrl/⌘ + wheel (a touchpad pinch sends exactly
//          that), two-finger pinch on touch screens. model-viewer's own zoom
//          stays off: it would swallow every wheel over the (large) stage, so
//          the page could not be scrolled past the model. Zoom = field of
//          view, between the start framing (out) and 1/4 of it (in);
//          model-viewer eases the camera to each new value.
//   plain wheel / vertical swipe: the page scrolls (touch-action: pan-y).
// Slow auto-rotate after a pause, off with reduced motion.
(function () {
  "use strict";

  var stages = document.querySelectorAll(".js-model");
  if (!stages.length) return;

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var libReady = null;
  var ZOOM_STEP = 1.35; // a button press
  var ZOOM_MIN = 0.25; // fov at most zoomed in, of the start fov

  function loadLib() {
    if (libReady) return libReady;
    libReady = new Promise(function (resolve, reject) {
      if (window.customElements && window.customElements.get("model-viewer")) return resolve();
      var s = document.createElement("script");
      s.type = "module";
      s.src = (window.reatissThemeUrl || "") + "js/vendors/model-viewer.min.js";
      s.onload = function () {
        window.customElements.whenDefined("model-viewer").then(resolve);
      };
      s.onerror = reject;
      document.head.appendChild(s);
    });
    return libReady;
  }

  function webgl() {
    try {
      var c = document.createElement("canvas");
      return !!(c.getContext("webgl2") || c.getContext("webgl"));
    } catch (e) {
      return false;
    }
  }

  function zoomControls(stage, mv) {
    var maxFov = mv.getFieldOfView(); // the start framing
    var minFov = maxFov * ZOOM_MIN;
    var fov = maxFov;
    var buttons = stage.querySelectorAll(".js-model-zoom");

    function apply(next) {
      fov = Math.min(maxFov, Math.max(minFov, next));
      mv.fieldOfView = fov + "deg";
      buttons.forEach(function (b) {
        var dir = b.getAttribute("data-zoom");
        b.disabled = dir === "in" ? fov <= minFov + 0.01 : fov >= maxFov - 0.01;
      });
    }

    mv.setAttribute("min-field-of-view", minFov + "deg");
    mv.setAttribute("max-field-of-view", maxFov + "deg");
    apply(maxFov);

    buttons.forEach(function (b) {
      b.addEventListener("click", function () {
        apply(b.getAttribute("data-zoom") === "in" ? fov / ZOOM_STEP : fov * ZOOM_STEP);
      });
    });

    // Ctrl/⌘ + wheel and touchpad pinch; a plain wheel scrolls the page
    stage.addEventListener("wheel", function (e) {
      if (!e.ctrlKey && !e.metaKey) return;
      e.preventDefault();
      e.stopPropagation(); // not Lenis, not the browser page zoom
      apply(fov * Math.exp(e.deltaY * 0.004)); // a wheel notch (~100) ≈ ×0.67
    }, { passive: false });

    // two-finger pinch on touch screens
    var touches = {};
    var startDist = 0;
    var startFov = fov;
    function dist() {
      var p = Object.keys(touches).map(function (k) { return touches[k]; });
      return p.length < 2 ? 0 : Math.hypot(p[0].x - p[1].x, p[0].y - p[1].y);
    }
    stage.addEventListener("pointerdown", function (e) {
      if (e.pointerType !== "touch") return;
      touches[e.pointerId] = { x: e.clientX, y: e.clientY };
      if (Object.keys(touches).length === 2) {
        startDist = dist();
        startFov = fov;
      }
    });
    stage.addEventListener("pointermove", function (e) {
      if (!touches[e.pointerId]) return;
      touches[e.pointerId] = { x: e.clientX, y: e.clientY };
      if (Object.keys(touches).length === 2 && startDist) apply(startFov * (startDist / Math.max(1, dist())));
    });
    function lift(e) {
      delete touches[e.pointerId];
      if (Object.keys(touches).length < 2) startDist = 0;
    }
    stage.addEventListener("pointerup", lift);
    stage.addEventListener("pointercancel", lift);
  }

  function mount(stage) {
    if (stage.dataset.mounted) return;
    stage.dataset.mounted = "1";

    loadLib().then(function () {
      var mv = document.createElement("model-viewer");
      mv.className = "model-3d__viewer";
      mv.setAttribute("src", stage.getAttribute("data-src"));
      mv.setAttribute("alt", stage.getAttribute("data-alt") || "");
      mv.setAttribute("camera-controls", "");
      mv.setAttribute("disable-zoom", ""); // ours: buttons / Ctrl+wheel / pinch
      mv.setAttribute("disable-pan", "");
      mv.setAttribute("touch-action", "pan-y");
      mv.setAttribute("interaction-prompt", "none");
      mv.setAttribute("camera-orbit", stage.getAttribute("data-orbit") || "-20deg 62deg auto");
      mv.setAttribute("environment-image", "neutral");
      mv.setAttribute("exposure", "1.05");
      mv.setAttribute("shadow-intensity", "0.35");
      mv.setAttribute("shadow-softness", "1");
      if (!reduceMotion) {
        mv.setAttribute("auto-rotate", "");
        mv.setAttribute("auto-rotate-delay", "1500");
        mv.setAttribute("rotation-per-second", "12deg");
      }
      mv.addEventListener("load", function () {
        stage.classList.add("is-loaded");
        zoomControls(stage, mv);
      }, { once: true });
      mv.addEventListener("error", function () {
        mv.remove(); // the poster stays
      });
      // under the zoom buttons
      stage.insertBefore(mv, stage.querySelector(".model-3d__zoom"));
    }).catch(function () {});
  }

  if (!webgl()) return;

  if (!("IntersectionObserver" in window)) {
    stages.forEach(mount);
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      mount(e.target);
    });
  }, { rootMargin: "800px 0px" });

  stages.forEach(function (s) {
    io.observe(s);
  });
})();
