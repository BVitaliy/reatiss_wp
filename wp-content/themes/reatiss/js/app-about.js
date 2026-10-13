/* global gsap, ScrollTrigger, DrawSVGPlugin */
// About page (about.php).
//   drift      .js-drift[data-drift="±px"] — scrubbed vertical drift while its
//              section crosses the screen (positive = moves down): the intro
//              photos part ways, the stats numbers / photos / texts move at
//              different speeds. ≥1200 only (below, the blocks are a column).
//   history    .js-history — year tabs (Ajour about-timeline logic): the
//              notch under the line slides to the active year, ← / → step,
//              the year row scrolls inside itself only. Panels swap with a
//              GSAP transition: out to the side, the block eases to the new
//              height, the new text / photo come in from the side of travel.
//   map        .js-map — the outline of Ukraine draws itself once when it
//              scrolls into view (DrawSVG, 2.6s).
//   clients    .js-clients — the step lines draw in once (DrawSVG) and the
//              cell rules grow (.is-drawn, CSS).
//   team       .js-team-open — before the shared "team" popup opens
//              (app-modals.js, data-modal-open), the card's <template> is
//              copied into it.
// Reduced motion: no drift / drawing, everything shown as is.
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var hasGsap = typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined";
  if (hasGsap) gsap.registerPlugin(ScrollTrigger);
  var hasDraw = hasGsap && typeof DrawSVGPlugin !== "undefined";
  if (hasDraw) gsap.registerPlugin(DrawSVGPlugin);

  // ── drift ────────────────────────────────────────────────────────
  var drifters = document.querySelectorAll(".js-drift");
  if (drifters.length && hasGsap && !reduceMotion) {
    var mm = gsap.matchMedia();
    mm.add("(min-width: 1200px)", function () {
      drifters.forEach(function (el) {
        var d = parseFloat(el.getAttribute("data-drift")) || 0;
        var section = el.closest("section") || el;
        gsap.fromTo(el, { y: -d / 2 }, {
          y: d / 2,
          ease: "none",
          scrollTrigger: { trigger: section, start: "top bottom", end: "bottom top", scrub: 1 },
        });
      });
    });
  }

  // ── history timeline ─────────────────────────────────────────────
  document.querySelectorAll(".js-history").forEach(function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll("[data-history-tab]"));
    var panels = Array.prototype.slice.call(root.querySelectorAll("[data-history-panel]"));
    var scroller = root.querySelector(".about-history__years-wrap");
    var notch = root.querySelector(".about-history__notch");
    var prev = root.querySelector(".js-history-prev");
    var next = root.querySelector(".js-history-next");
    if (!tabs.length) return;
    var index = 0;

    function placeNotch() {
      var tab = tabs[index];
      var x = tab.offsetLeft + tab.offsetWidth / 2 - notch.getBoundingClientRect().width / 2;
      root.style.setProperty("--notch-x", Math.max(0, x) + "px");
    }

    // keep the active year visible inside the row only (never scrollIntoView:
    // it would move the page). The row moves only when the year is cut — by
    // the left fade or the arrows on the right — and just enough to show it
    // with its neighbour's edge, so a click doesn't jerk the whole row.
    var nav = root.querySelector(".about-history__nav");
    function reveal(smooth) {
      var tab = tabs[index];
      var max = scroller.scrollWidth - scroller.clientWidth;
      if (max <= 0) return;
      var box = scroller.getBoundingClientRect();
      var r = tab.getBoundingClientRect();
      var left = r.left - box.left; // in the visible row
      var right = r.right - box.left;
      // clear of the left fade, and of the arrows' fade on the right
      var safeL = index === 0 ? 0 : r.width * 0.6;
      var navBox = nav && nav.offsetWidth ? nav.getBoundingClientRect() : null; // no arrows on phones
      var safeR = (navBox ? navBox.left - box.left + navBox.width * 0.12 : box.width) - (index === tabs.length - 1 ? 0 : r.width * 0.6);
      var delta = 0;
      if (left < safeL) delta = left - safeL;
      else if (right > safeR) delta = right - safeR;
      // the first / last year: all the way to that end (no fade left on it)
      if (index === 0) delta = -scroller.scrollLeft;
      else if (index === tabs.length - 1 && delta > 0) delta = max - scroller.scrollLeft;
      if (!delta) return;
      var target = Math.max(0, Math.min(max, scroller.scrollLeft + delta));
      scroller.scrollTo({ left: target, behavior: smooth ? "smooth" : "auto" });
    }

    // the fades only on the cut sides
    function onScroll() {
      var max = scroller.scrollWidth - scroller.clientWidth;
      scroller.classList.toggle("is-scrolled", scroller.scrollLeft > 2);
      scroller.classList.toggle("has-more", scroller.scrollLeft < max - 2);
    }
    scroller.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    onScroll();

    // drag the row with the mouse (fingers scroll it natively); a drag never
    // counts as a click on a year
    var drag = null;
    var dragged = false;
    scroller.addEventListener("pointerdown", function (e) {
      if (e.pointerType !== "mouse" || e.button !== 0) return;
      if (scroller.scrollWidth <= scroller.clientWidth) return;
      drag = { x: e.clientX, left: scroller.scrollLeft };
      dragged = false;
    });
    window.addEventListener("pointermove", function (e) {
      if (!drag) return;
      var dx = e.clientX - drag.x;
      if (!dragged && Math.abs(dx) > 5) {
        dragged = true;
        scroller.classList.add("is-dragging");
      }
      if (dragged) scroller.scrollLeft = drag.left - dx;
    });
    window.addEventListener("pointerup", function () {
      if (!drag) return;
      drag = null;
      scroller.classList.remove("is-dragging");
    });
    scroller.addEventListener("click", function (e) {
      if (!dragged) return;
      dragged = false;
      e.preventDefault();
      e.stopPropagation();
    }, true);
    scroller.addEventListener("dragstart", function (e) { e.preventDefault(); });

    var wrap = root.querySelector(".about-history__panels");
    var swap = null; // the running panel transition

    function panelById(id) {
      return panels.filter(function (p) { return p.getAttribute("data-history-panel") === id; })[0];
    }

    function showOnly(panel) {
      panels.forEach(function (p) {
        p.hidden = p !== panel;
        p.classList.toggle("is-active", p === panel);
      });
    }

    // old panel fades / slides out, the block eases to the new height, the
    // new one comes in from the side of travel (text staggered, photo settles
    // from a slight zoom)
    function animate(from, to, dir) {
      if (swap) swap.progress(1).kill();
      var fromParts = from.querySelectorAll(".about-history__panel-title, .about-history__text, .about-history__media");
      var toText = to.querySelectorAll(".about-history__panel-title, .about-history__text");
      var toMedia = to.querySelector(".about-history__media");
      var toImg = to.querySelector(".about-history__media img");
      var h0 = wrap.offsetHeight;

      swap = gsap.timeline({
        defaults: { overwrite: "auto" },
        onComplete: function () {
          gsap.set(wrap, { clearProps: "height,overflow" });
          gsap.set([].concat(Array.prototype.slice.call(toText), [toMedia, toImg]), { clearProps: "all" });
          swap = null;
          ScrollTrigger.refresh(); // panel heights differ
        },
      });
      swap
        .to(fromParts, { autoAlpha: 0, x: -40 * dir, duration: 0.35, ease: "power2.in", stagger: 0.04 })
        .add(function () {
          showOnly(to);
          var h1 = to.offsetHeight;
          gsap.fromTo(wrap, { height: h0, overflow: "hidden" }, { height: h1, duration: 0.5, ease: "power3.inOut" });
        })
        .fromTo(toMedia, { autoAlpha: 0, x: 60 * dir }, { autoAlpha: 1, x: 0, duration: 0.8, ease: "power3.out" }, ">")
        .fromTo(toImg, { scale: 1.08 }, { scale: 1, duration: 1.1, ease: "power3.out" }, "<")
        .fromTo(toText, { autoAlpha: 0, x: 40 * dir }, { autoAlpha: 1, x: 0, duration: 0.7, ease: "power3.out", stagger: 0.08 }, "<0.1");
    }

    function setActive(i, smooth) {
      var prevIndex = index;
      index = Math.max(0, Math.min(tabs.length - 1, i));
      tabs.forEach(function (t, k) {
        t.classList.toggle("is-active", k === index);
        t.setAttribute("aria-selected", k === index ? "true" : "false");
        t.tabIndex = k === index ? 0 : -1;
      });
      var to = panelById(tabs[index].getAttribute("data-history-tab"));
      var from = panels.filter(function (p) { return !p.hidden; })[0];
      if (smooth && hasGsap && !reduceMotion && from && to && from !== to) {
        animate(from, to, index > prevIndex ? 1 : -1);
      } else {
        showOnly(to);
        if (hasGsap) ScrollTrigger.refresh();
      }
      if (prev) prev.disabled = index === 0;
      if (next) next.disabled = index === tabs.length - 1;
      placeNotch();
      reveal(smooth);
    }

    tabs.forEach(function (t, k) {
      t.addEventListener("click", function () { setActive(k, true); });
      t.addEventListener("keydown", function (e) {
        if (e.key === "ArrowRight" || e.key === "ArrowLeft") {
          e.preventDefault();
          setActive(index + (e.key === "ArrowRight" ? 1 : -1), true);
          tabs[index].focus();
        }
      });
    });
    if (prev) prev.addEventListener("click", function () { setActive(index - 1, true); });
    if (next) next.addEventListener("click", function () { setActive(index + 1, true); });
    window.addEventListener("resize", placeNotch);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(placeNotch);

    setActive(0, false);
  });

  // ── map draw ─────────────────────────────────────────────────────
  if (hasDraw && !reduceMotion) {
    document.querySelectorAll(".js-map").forEach(function (map) {
      var path = map.querySelector(".js-map-path");
      if (!path) return;
      gsap.set(path, { drawSVG: "0%" });
      ScrollTrigger.create({
        trigger: map,
        start: "top 75%",
        once: true,
        onEnter: function () {
          gsap.to(path, { drawSVG: "100%", duration: 2.6, ease: "power2.inOut" });
        },
      });
    });
  }

  // ── clients lines ────────────────────────────────────────────────
  document.querySelectorAll(".js-clients").forEach(function (grid) {
    grid.querySelectorAll(".client-card").forEach(function (c, i) {
      c.style.setProperty("--i", i);
    });
    var lines = grid.querySelectorAll(".about-clients__lines path");
    if (!hasGsap || reduceMotion) {
      grid.classList.add("is-drawn");
      return;
    }
    if (hasDraw) gsap.set(lines, { drawSVG: "0%" });
    ScrollTrigger.create({
      trigger: grid,
      start: "top 80%",
      once: true,
      onEnter: function () {
        grid.classList.add("is-drawn");
        if (hasDraw) gsap.to(lines, { drawSVG: "100%", duration: 1.6, ease: "power2.inOut", stagger: 0.12 });
      },
    });
  });

  // ── team popup ───────────────────────────────────────────────────
  var teamBody = document.querySelector(".js-team-modal-body");
  if (teamBody) {
    document.querySelectorAll(".js-team-open").forEach(function (card) {
      var tpl = card.parentElement.querySelector(".js-team-bio");
      if (!tpl) return;
      // on the card itself: runs before the delegated opener on document
      card.addEventListener("click", function () {
        teamBody.innerHTML = "";
        teamBody.appendChild(tpl.content.cloneNode(true));
      });
    });
  }
})();
