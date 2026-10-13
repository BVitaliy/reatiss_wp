/* global Swiper, lightGallery, lgZoom */
// Gallery strip (template-parts/gallery-strip.php): a full-width row of
// images that moves on its own and can be dragged (mouse / touch).
// Centred, looped; slide widths come from CSS (457 at 1920, 38vw tablet,
// 78vw phone), the gap follows the root scale (11 at 1920).
// Loop needs enough slides around the visible ones, so short galleries are
// cloned in the DOM before init. Autoplay pauses under the cursor and with
// reduced motion stays off.
//
// Lightbox (as Ajour's product gallery): a click on a slide opens
// lightGallery (zoom, no thumbnail strip) over the ORIGINAL images only — clones
// open their original's index. Swiper swallows the click after a drag, so
// dragging never opens it. The library (css + 3 scripts, ~100 KB) is fetched
// only when the strip comes near the viewport (or on the first click). Page scroll is held by
// ReatissScrollLock while open; the strip's autoplay pauses.
(function () {
  "use strict";

  if (typeof Swiper === "undefined") return;

  var strips = document.querySelectorAll(".js-gallery-strip");
  if (!strips.length) return;

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var MIN_SLIDES = 12;

  // ── lazy lightGallery ────────────────────────────────────────────
  var lgReady = null;

  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      var s = document.createElement("script");
      s.src = src;
      s.onload = resolve;
      s.onerror = reject;
      document.body.appendChild(s);
    });
  }

  function loadLightGallery() {
    if (lgReady) return lgReady;
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = (window.reatissThemeUrl || "") + "css/vendors/lightgallery-bundle.min.css";
    // before our style.css, so the re-skin (_lightgallery.scss) wins ties
    var ours = document.querySelector('link[href*="css/style.css"]');
    document.head.insertBefore(link, ours || null);
    lgReady = loadScript((window.reatissThemeUrl || "") + "js/vendors/lightgallery.min.js")
      .then(function () { return loadScript((window.reatissThemeUrl || "") + "js/vendors/lg-zoom.min.js"); });
    return lgReady;
  }

  // our arrow (img/icons/arrow.svg points ↘ — turned in CSS)
  var ARROW =
    '<svg viewBox="0 0 17 17" fill="none" aria-hidden="true"><path d="M3 8.5h11m0 0L9.5 4m4.5 4.5L9.5 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

  function gap() {
    if (window.innerWidth < 1200) return 10;
    var root = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
    return Math.round((11 * root) / 16);
  }

  strips.forEach(function (el) {
    var wrapper = el.querySelector(".swiper-wrapper");
    var originals = Array.prototype.slice.call(wrapper.children);
    if (!originals.length) return;

    // lightbox items, in the editor's order
    var items = originals.map(function (slide, i) {
      slide.dataset.index = i;
      var img = slide.querySelector("img");
      return {
        src: slide.getAttribute("href") || (img && img.currentSrc) || "",
        thumb: img ? img.currentSrc || img.src : "",
        alt: img ? img.alt : "",
      };
    });

    while (wrapper.children.length < MIN_SLIDES) {
      originals.forEach(function (slide) {
        var copy = slide.cloneNode(true);
        copy.setAttribute("aria-hidden", "true");
        copy.setAttribute("tabindex", "-1");
        wrapper.appendChild(copy);
      });
    }

    var swiper = new Swiper(el, {
      slidesPerView: "auto",
      centeredSlides: true,
      loop: true,
      spaceBetween: gap(),
      speed: 900,
      grabCursor: true,
      initialSlide: Math.min(2, originals.length - 1),
      autoplay: reduceMotion
        ? false
        : { delay: 2600, disableOnInteraction: false, pauseOnMouseEnter: true },
      a11y: { enabled: true },
    });

    window.addEventListener("resize", function () {
      var g = gap();
      if (swiper.params.spaceBetween === g) return;
      swiper.params.spaceBetween = g;
      swiper.update();
    });

    // ── lightbox ──
    var gallery = null;

    function open(index) {
      loadLightGallery().then(function () {
        if (!gallery) {
          gallery = lightGallery(el, {
            dynamic: true,
            dynamicEl: items.map(function (it) {
              return { src: it.src, thumb: it.thumb, alt: it.alt };
            }),
            plugins: [lgZoom],
            licenseKey: "0000-0000-000-0000",
            addClass: "lg-reatiss",
            download: false,
            counter: true,
            getCaptionFromTitleOrAlt: false,
            prevHtml: ARROW,
            nextHtml: ARROW,
            speed: 500,
            backdropDuration: 300,
            mobileSettings: { controls: false, showCloseIcon: true, download: false },
            showZoomInOutIcons: false,
            actualSize: false,
          });
          el.addEventListener("lgBeforeOpen", function () {
            if (window.ReatissScrollLock) window.ReatissScrollLock.lock("gallery");
            if (swiper.autoplay && swiper.autoplay.running) swiper.autoplay.stop();
          });
          el.addEventListener("lgAfterClose", function () {
            if (window.ReatissScrollLock) window.ReatissScrollLock.unlock("gallery");
            if (!reduceMotion && swiper.autoplay) swiper.autoplay.start();
          });
        }
        gallery.openGallery(index);
      });
    }

    // fetch the library ahead of the first click, once the strip is near
    if ("IntersectionObserver" in window) {
      var io = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) return;
        io.disconnect();
        loadLightGallery();
      }, { rootMargin: "600px 0px" });
      io.observe(el);
    }

    wrapper.addEventListener("click", function (e) {
      var slide = e.target.closest(".gallery-strip__slide");
      if (!slide) return;
      e.preventDefault();
      open(parseInt(slide.dataset.index, 10) || 0);
    });
  });
})();
