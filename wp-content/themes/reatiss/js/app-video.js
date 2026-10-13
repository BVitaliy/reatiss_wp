// Video: background loops + fullscreen popup.
//
// Background (.js-bg-video, pattern from Ajour's video-media):
// - the file is attached only when the section nears the viewport
//   (preload="none" + data-src), so the page doesn't pull megabytes up front;
// - desktop / mobile cut (data-src / data-src-mob) is picked by viewport and
//   swapped when it crosses 768 (a native <source media> is resolved once);
// - plays only while on screen, fades in over the poster once it plays;
// - reduced motion: no autoplay, the poster stays.
//
// Popup (.js-video-modal): [data-video-open="src"] (+ data-video-poster).
// src is set on open and removed on close so nothing buffers in the
// background; page scroll is held by window.ReatissScrollLock.
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var mobileMq = window.matchMedia("(max-width: 767.98px)");

  // ── Background loops ─────────────────────────────────────────────
  document.querySelectorAll(".js-bg-video").forEach(function (video) {
    if (reduceMotion) return;

    var section = video.closest(".js-bg-video-section") || video.parentElement;
    var inView = false;

    function wantedSrc() {
      var mob = video.getAttribute("data-src-mob");
      return mobileMq.matches && mob ? mob : video.getAttribute("data-src");
    }

    function attach() {
      var src = wantedSrc();
      if (video.getAttribute("src") === src) return;
      video.classList.remove("is-playing");
      video.setAttribute("src", src);
      video.load();
    }

    function play() {
      var p = video.play();
      if (p && p.catch) p.catch(function () {}); // autoplay refused → poster stays
    }

    video.addEventListener("playing", function () {
      video.classList.add("is-playing");
    });

    mobileMq.addEventListener("change", function () {
      if (!video.getAttribute("src")) return; // not attached yet
      attach();
      if (inView) play();
    });

    if (!("IntersectionObserver" in window)) {
      attach();
      play();
      return;
    }

    new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          inView = entry.isIntersecting;
          if (inView) {
            attach();
            play();
          } else if (video.getAttribute("src")) {
            video.pause();
          }
        });
      },
      { rootMargin: "200px 0px" }
    ).observe(section);
  });

  // ── Fullscreen popup ─────────────────────────────────────────────
  var modal = document.querySelector(".js-video-modal");
  if (!modal) return;

  var player = modal.querySelector(".js-video-player");
  var closeTimer = null;
  var lastFocus = null;

  function open(trigger) {
    var src = trigger.getAttribute("data-video-open");
    if (!src) return;

    clearTimeout(closeTimer);
    document.dispatchEvent(new CustomEvent("reatiss:overlay-open"));
    lastFocus = document.activeElement;

    var poster = trigger.getAttribute("data-video-poster");
    if (poster) player.setAttribute("poster", poster);
    player.setAttribute("src", src);

    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    if (window.ReatissScrollLock) window.ReatissScrollLock.lock("video");

    var p = player.play();
    if (p && p.catch) p.catch(function () {});
    modal.querySelector(".video-modal__close").focus({ preventScroll: true });
  }

  function close() {
    if (!modal.classList.contains("is-open")) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    player.pause();

    // release after the fade so restoring the scrollbar doesn't shift the
    // still-visible video; drop the source so it stops buffering
    closeTimer = setTimeout(function () {
      player.removeAttribute("src");
      player.removeAttribute("poster");
      player.load();
      if (window.ReatissScrollLock) window.ReatissScrollLock.unlock("video");
      if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }, 350);
  }

  document.addEventListener("click", function (e) {
    var trigger = e.target.closest("[data-video-open]");
    if (trigger) {
      e.preventDefault();
      open(trigger);
      return;
    }
    // close button, or a click on the dark stage around the video
    if (modal.classList.contains("is-open")) {
      var closer = e.target.closest(".js-video-close");
      if (closer && (closer.classList.contains("video-modal__close") || e.target === closer)) close();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") close();
  });
})();
