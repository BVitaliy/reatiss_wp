/* global Lenis, gsap, ScrollTrigger */
// Smooth scroll (Lenis) driven by the GSAP ticker so ScrollTrigger pins /
// scrubs stay in sync with the smoothed position. Exposed as window.lenis
// for modals (stop/start) and anchor links.
(function () {
  "use strict";

  var hasGsap = typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined";
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (hasGsap) {
    gsap.registerPlugin(ScrollTrigger);
    // mobile address bar resizes the viewport — not a reason to recalc pins
    ScrollTrigger.config({ ignoreMobileResize: true });
  }

  // ── Lenis ──────────────────────────────────────────────────────
  // On every device, touch included (Ajour / PEK pattern): Lenis drives the
  // scroll on phones too (syncTouch), so everything tied to the scroll — the
  // sticky "In this article" bar, ScrollTrigger reveals — moves in the same
  // frame. With native touch scroll the phones' browsers shook those.
  // Touch tuning as Ajour (a swipe carries further, the glide ends sooner).
  // QA on a real phone: ?nativeScroll=1 switches touch back to the native
  // scroll (remembered; ?nativeScroll=0 undoes it).
  var lenis = null;
  var isTouchScroll = window.matchMedia("(hover: none), (pointer: coarse), (max-width: 767.98px)").matches;

  function preferNativeScroll() {
    if (!isTouchScroll) return false;
    try {
      var params = new URLSearchParams(window.location.search);
      if (params.has("nativeScroll")) {
        var raw = params.get("nativeScroll");
        var on = raw === "1" || raw === "true" || raw === "";
        localStorage.setItem("reatiss-native-scroll", on ? "1" : "0");
        return on;
      }
      return localStorage.getItem("reatiss-native-scroll") === "1";
    } catch (err) {
      return false;
    }
  }

  if (typeof Lenis !== "undefined" && !reduceMotion && !preferNativeScroll()) {
    lenis = new Lenis({
      lerp: isTouchScroll ? 0.14 : 0.1,
      smoothWheel: true,
      touchMultiplier: isTouchScroll ? 1.85 : 1,
      syncTouch: true,
      syncTouchLerp: isTouchScroll ? 0.26 : 0.075,
      touchInertiaExponent: isTouchScroll ? 1.35 : 1.7,
      // long text in a textarea scrolls itself, not the page
      prevent: function (node) {
        return node.nodeName === "TEXTAREA";
      },
    });
    window.lenis = lenis;

    if (hasGsap) {
      lenis.on("scroll", ScrollTrigger.update);
      gsap.ticker.add(function (time) {
        lenis.raf(time * 1000);
      });
      gsap.ticker.lagSmoothing(0);
    } else {
      var raf = function (t) {
        lenis.raf(t);
        requestAnimationFrame(raf);
      };
      requestAnimationFrame(raf);
    }
  }

  // ── Anchor links ───────────────────────────────────────────────
  document.addEventListener("click", function (e) {
    var link = e.target.closest('a[href^="#"]');
    if (!link) return;
    var id = link.getAttribute("href");
    if (id.length < 2) return;
    var target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    var offset = -parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--header-h")) || 0;
    if (lenis) lenis.scrollTo(target, { offset: offset, duration: 1.2 });
    else target.scrollIntoView({ behavior: "smooth" });
  });

  // ── Search field clear (search page) ────────────────────────────
  document.addEventListener("click", function (e) {
    var btn = e.target.closest(".js-search-clear");
    if (!btn) return;
    var input = btn.closest(".search-field").querySelector("input");
    input.value = "";
    input.focus();
  });

  // ── ScrollTrigger refresh ──────────────────────────────────────
  // Trigger bounds are measured before fonts / media arrive and those change
  // the page height — re-measure on every real height change, not only load.
  if (hasGsap) {
    var refresh = function () {
      ScrollTrigger.refresh();
    };
    window.addEventListener("load", refresh);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(refresh);

    if ("ResizeObserver" in window) {
      var lastHeight = document.body.offsetHeight;
      var timer = null;
      new ResizeObserver(function () {
        var h = document.body.offsetHeight;
        if (h === lastHeight) return;
        lastHeight = h;
        clearTimeout(timer);
        timer = setTimeout(refresh, 150);
      }).observe(document.body);
    }
  }
})();
