/* global Swiper */
// Article page (logic from Ajour's app-article.js):
// - photo sliders in the content (.js-article-slider)        ┐ editor blocks:
// - YouTube facade (.js-youtube): poster → iframe on click   ┘ any page
// - "In this article" nav (only on .article-page--nav): built from the
//   content's <h2>s, smooth-scroll on click, scroll-spy highlights the part
//   in view; below 992 it is a sticky dropdown bar
// - share buttons (facebook / x open a share window, "copy" copies the URL)
(function () {
  "use strict";

  // ── Sliders ──────────────────────────────────────────────────────
  // Height follows the active image (autoHeight); lazy images change it
  // after load, so re-measure when each one arrives (Ajour).
  if (typeof Swiper !== "undefined") {
    document.querySelectorAll(".js-article-slider").forEach(function (el) {
      var swiper = new Swiper(el, {
        slidesPerView: 1,
        autoHeight: true,
        speed: 700,
        grabCursor: true,
        loop: el.querySelectorAll(".swiper-slide").length > 1,
        pagination: { el: el.querySelector(".swiper-pagination"), clickable: true },
      });

      el.querySelectorAll("img").forEach(function (img) {
        if (img.complete) return;
        img.addEventListener("load", function () {
          swiper.update();
          swiper.updateAutoHeight(250);
        }, { once: true });
      });
    });
  }

  // ── YouTube facade ───────────────────────────────────────────────
  // .js-youtube[data-youtube="ID or URL"]: poster until clicked, then the
  // button is swapped for the youtube-nocookie iframe (autoplay) — the
  // player's scripts load only if the visitor actually wants the video.
  function youtubeId(value) {
    var m = String(value || "").match(/(?:youtu\.be\/|v=|embed\/|shorts\/)([\w-]{11})/);
    return m ? m[1] : /^[\w-]{11}$/.test(value) ? value : "";
  }

  document.querySelectorAll(".js-youtube").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = youtubeId(btn.getAttribute("data-youtube"));
      if (!id) return;
      var box = document.createElement("div");
      box.className = "article-video article-video--embed";
      var frame = document.createElement("iframe");
      frame.src = "https://www.youtube-nocookie.com/embed/" + id + "?autoplay=1&rel=0&playsinline=1";
      frame.title = btn.getAttribute("aria-label") || "YouTube video";
      frame.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
      frame.allowFullscreen = true;
      box.appendChild(frame);
      btn.replaceWith(box);
      frame.focus();
    });
  });

  // the rest belongs to the article page only
  var root = document.querySelector("[data-article-page]");
  if (!root) return;

  // ── Share ────────────────────────────────────────────────────────
  // the Clipboard API needs https; plain http falls back to a hidden textarea
  function copyLink(text) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).catch(function () { legacyCopy(text); });
    } else {
      legacyCopy(text);
    }
  }

  function legacyCopy(text) {
    var area = document.createElement("textarea");
    area.value = text;
    area.setAttribute("readonly", "");
    area.style.cssText = "position:fixed;top:0;left:0;opacity:0";
    document.body.appendChild(area);
    area.select();
    try { document.execCommand("copy"); } catch (err) {}
    area.remove();
  }

  root.querySelectorAll(".js-share").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      var url = encodeURIComponent(location.href);
      var title = encodeURIComponent(document.title);
      var type = btn.getAttribute("data-share");
      if (type === "copy") {
        e.preventDefault();
        // phones: the system share sheet (Telegram, Viber, WhatsApp, Instagram…,
        // "Copy" is in it too); desktop / no support: copy the link
        if (navigator.share && window.matchMedia("(pointer: coarse)").matches) {
          navigator.share({ title: document.title, url: location.href }).catch(function () {}); // closed = fine
          return;
        }
        copyLink(location.href);
        btn.classList.add("is-copied");
        setTimeout(function () { btn.classList.remove("is-copied"); }, 1500);
        return;
      }
      var href = type === "facebook"
        ? "https://www.facebook.com/sharer/sharer.php?u=" + url
        : "https://twitter.com/intent/tweet?url=" + url + "&text=" + title;
      e.preventDefault();
      window.open(href, "_blank", "noopener,width=640,height=560");
    });
  });

  // ── In this article ──────────────────────────────────────────────
  var toc = root.querySelector("[data-article-toc]");
  if (!toc || !root.classList.contains("article-page--nav")) return;

  var content = root.querySelector(".article-content");
  var header = document.querySelector(".js-header");
  var toggle = toc.querySelector("[data-article-toc-toggle]");
  var current = toc.querySelector("[data-article-toc-current]");
  var list = toc.querySelector(".article-toc__list");
  var headings = content ? Array.prototype.slice.call(content.querySelectorAll("h2")) : [];
  var mqStack = window.matchMedia("(max-width: 991.98px)");

  if (!headings.length || !list) {
    toc.hidden = true;
    return;
  }

  var links = headings.map(function (h, i) {
    if (!h.id) h.id = "article-part-" + (i + 1);
    var li = document.createElement("li");
    var a = document.createElement("a");
    a.className = "article-toc__link";
    a.href = "#" + h.id;
    a.textContent = h.textContent.replace(/\s+/g, " ").trim();
    li.appendChild(a);
    list.appendChild(li);
    return a;
  });

  var lockUntil = 0;

  function setOpen(open) {
    toc.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
  }

  var activeId = null;

  // only when the section changes: rewriting the bar's text on every scroll
  // frame made the stuck bar repaint (and shake on iOS) during a swipe
  function setActive(id) {
    if (id === activeId) return;
    activeId = id;
    links.forEach(function (a) {
      var on = a.getAttribute("href") === "#" + id;
      a.classList.toggle("is-active", on);
      if (on) a.setAttribute("aria-current", "true");
      else a.removeAttribute("aria-current");
      if (on && current) current.textContent = a.textContent;
    });
  }

  function marker() {
    var h = header ? header.getBoundingClientRect().bottom : 0;
    return h + (mqStack.matches ? toggle.offsetHeight + 24 : 24) + 4;
  }

  // last heading that has passed the marker line under the header
  function spy() {
    if (Date.now() < lockUntil) return;
    var line = marker();
    var id = headings[0].id;
    headings.forEach(function (h) {
      if (h.getBoundingClientRect().top <= line) id = h.id;
    });
    setActive(id);
  }

  toggle.addEventListener("click", function () {
    setOpen(!toc.classList.contains("is-open"));
  });

  links.forEach(function (a) {
    a.addEventListener("click", function (e) {
      var target = document.getElementById(a.getAttribute("href").slice(1));
      if (!target) return;
      e.preventDefault();
      e.stopPropagation(); // app-global's anchor handler would scroll with its own offset
      setActive(target.id);
      // collapse at once (below 992 the list overlays the content)
      toc.classList.add("is-instant");
      setOpen(false);
      void toc.offsetHeight;
      requestAnimationFrame(function () { toc.classList.remove("is-instant"); });
      lockUntil = Date.now() + 1300; // keep the clicked item active while scrolling
      var offset = -(marker() - 4);
      if (window.lenis) window.lenis.scrollTo(target, { offset: offset, duration: 1.1 });
      else window.scrollTo({ top: target.getBoundingClientRect().top + scrollY + offset, behavior: "smooth" });
    });
  });

  document.addEventListener("click", function (e) {
    if (toc.classList.contains("is-open") && !toc.contains(e.target)) setOpen(false);
  });

  // one pass per frame at most (Lenis and the native event both fire)
  var spyQueued = false;
  function queueSpy() {
    if (spyQueued) return;
    spyQueued = true;
    requestAnimationFrame(function () {
      spyQueued = false;
      spy();
    });
  }

  if (window.lenis) window.lenis.on("scroll", queueSpy);
  window.addEventListener("scroll", queueSpy, { passive: true });
  spy();
})();
