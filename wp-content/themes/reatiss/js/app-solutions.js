/* global gsap, ScrollTrigger */
// Home — Advanced Engineering Solutions (template-parts/home-solutions.php).
// Scroll scene from Oleg's prototype (https://reatiss.vercel.app/, scroll
// variant), on the site's Lenis + ScrollTrigger (app-global.js):
//   sticky screen (CSS) → scroll drives video.currentTime:
//   phase 1 chip1.mp4 opens the chip into layers (eases out at the end),
//   phase 2 chip2.mp4 zooms to each layer and back; while a layer is close
//   its service fades in with a lead line from its number to the layer.
// Last frame of chip1 = first frame of chip2, so the swap is invisible.
// ≥1200 only; below that (and with reduced motion) the layers clip just
// loops in view under the plain list.
(function () {
  "use strict";

  var section = document.querySelector(".js-solutions");
  if (!section) return;

  var reveal = section.querySelector(".js-solutions-reveal");
  var layers = section.querySelector(".js-solutions-layers");
  var items = section.querySelectorAll(".js-solutions-item");
  var stage = section.querySelector(".js-solutions-stage");
  var svg = section.querySelector(".js-solutions-leads");
  if (!reveal || !layers) return;

  var hasGsap = typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined";
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // loop only while on screen (phones / tablets / no scrub)
  function loopInView(v) {
    v.loop = true;
    if (reduceMotion) return function () {};
    if (!("IntersectionObserver" in window)) {
      v.play().catch(function () {});
      return function () { v.pause(); };
    }
    var io = new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting) v.play().catch(function () {});
      else v.pause();
    });
    io.observe(v);
    return function () {
      io.disconnect();
      v.pause();
    };
  }

  if (!hasGsap || reduceMotion) {
    loopInView(layers);
    return;
  }

  // share of the sticky range: reveal, layers; the rest is a tail where the
  // last service stays on screen before the scene leaves
  var PHASE = { reveal: 0.18, layers: 0.72 };

  // Phase 2 = four "zoom in to a layer → out" cycles; bounds measured on the
  // clip (seconds). w — scroll weight (zoom-out runs faster), item — service
  // shown on that zoom-in, aim — the layer point in frame fractions.
  var ZOOM = [
    { to: 1.19, w: 1, item: 0, aim: { x: 0.78, y: 0.24 } },
    { to: 2.0, w: 0.7 },
    { to: 3.23, w: 1, item: 1, aim: { x: 0.2, y: 0.4 } },
    { to: 4.08, w: 0.7 },
    { to: 5.4, w: 1, item: 2, aim: { x: 0.8, y: 0.57 } },
    { to: 6.29, w: 0.7 },
    { to: 7.4, w: 1, item: 3, aim: { x: 0.22, y: 0.78 } },
    { to: 8.38, w: 0.7 },
  ];
  var TOTAL = ZOOM.reduce(function (sum, seg) { return sum + seg.w; }, 0);

  // scroll px the sticky scene lives
  function range() {
    return section.offsetHeight - window.innerHeight;
  }

  function whenMeta(v, fn) {
    if (v.readyState >= 1) fn();
    else v.addEventListener("loadedmetadata", fn, { once: true });
  }

  var blobbed = false;

  ScrollTrigger.matchMedia({
    "(min-width: 1200px)": function () {
      var triggers = [];
      var timelines = [];
      var leads = [];
      var pin = section.querySelector(".home-solutions__bg");

      // whole files in memory (blob): every seek is instant, no range requests
      triggers.push(ScrollTrigger.create({
        trigger: section,
        start: "top 200%",
        once: true,
        onEnter: function () {
          if (blobbed) return;
          blobbed = true;
          [reveal, layers].forEach(function (v) {
            v.preload = "auto";
            fetch(v.currentSrc || v.querySelector("source").src)
              .then(function (r) { return r.blob(); })
              .then(function (blob) {
                var t = v.currentTime;
                v.src = URL.createObjectURL(blob);
                v.currentTime = t + 0.01;
              })
              .catch(function () {}); // keep streaming from the network
          });
        },
      }));

      function afterReveal() {
        return "top top-=" + range() * PHASE.reveal;
      }

      // phase 1: steady, easing out on the last frames (speeds match at the
      // joint of the two tweens: 75% of frames over 60% of the scroll)
      whenMeta(reveal, function () {
        var d = reveal.duration || 1;
        var tl = gsap.timeline({
          scrollTrigger: { trigger: pin, start: "top top", end: function () { return "+=" + range() * PHASE.reveal; }, scrub: 1, invalidateOnRefresh: true },
        });
        tl.fromTo(reveal, { currentTime: 0 }, { currentTime: d * 0.75, ease: "none", duration: 0.6 })
          .to(reveal, { currentTime: d, ease: "power2.out", duration: 0.4 });
        timelines.push(tl);
      });

      // clip swap at the joint — till the end of the scene, or the reveal
      // clip would come back on the tail
      triggers.push(ScrollTrigger.create({
        trigger: pin,
        start: afterReveal,
        end: function () { return "+=" + range(); },
        invalidateOnRefresh: true,
        onToggle: function (self) {
          gsap.set(layers, { opacity: self.isActive ? 1 : 0 });
          gsap.set(reveal, { opacity: self.isActive ? 0 : 1 });
        },
      }));

      // phase 2: frames only, linear (the render already has camera easing)
      whenMeta(layers, function () {
        var tl = gsap.timeline({
          scrollTrigger: { trigger: pin, start: afterReveal, end: function () { return "+=" + range() * PHASE.layers; }, scrub: 1, invalidateOnRefresh: true },
        });
        var pos = 0;
        ZOOM.forEach(function (seg, i) {
          tl.fromTo(layers,
            { currentTime: i === 0 ? 0 : ZOOM[i - 1].to },
            { currentTime: seg.to, ease: "none", duration: seg.w, immediateRender: i === 0 },
            pos);
          pos += seg.w;
        });
        timelines.push(tl);
        drawLeads();
      });

      // a service lives from the middle of its zoom-in to the middle of the
      // next zoom-out; the class toggles, CSS draws the fade
      function at(weight) {
        return range() * (PHASE.reveal + (PHASE.layers * weight) / TOTAL);
      }

      var cursor = 0;
      ZOOM.forEach(function (seg, i) {
        var from = cursor;
        cursor += seg.w;
        if (seg.item === undefined || !items[seg.item]) return;

        var item = items[seg.item];
        // lead target from the markup (data-aim="x,y"), mockup default otherwise
        var aimAttr = (item.getAttribute("data-aim") || "").split(",");
        var aim = aimAttr.length === 2 && !isNaN(parseFloat(aimAttr[0])) ? { x: parseFloat(aimAttr[0]), y: parseFloat(aimAttr[1]) } : seg.aim;
        var lead = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
        lead.setAttribute("class", "home-solutions__lead");
        lead.setAttribute("vector-effect", "non-scaling-stroke");
        svg.appendChild(lead);
        leads.push({ item: item, lead: lead, aim: aim });

        var out = ZOOM[i + 1];
        var last = seg.item === items.length - 1;

        triggers.push(ScrollTrigger.create({
          trigger: pin,
          start: function () { return "top top-=" + at(from + seg.w * 0.5); },
          end: function () {
            return last ? "top top-=" + range() * (PHASE.reveal + PHASE.layers) : "top top-=" + at(from + seg.w + out.w * 0.5);
          },
          invalidateOnRefresh: true,
          onToggle: function (self) {
            item.classList.toggle("is-active", self.isActive);
            lead.classList.toggle("is-active", self.isActive);
          },
        }));
      });

      // keep every service block inside the screen: its preferred top
      // (--sol-top) clamped between the header and the bottom edge — longer
      // CMS text or a low window must not push it out of the pinned scene
      function placeItems() {
        var h = stage.clientHeight;
        var title = section.querySelector(".home-solutions__title");
        // the fixed header stays over the pinned scene
        var headerH = parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--header-h")) || 0;
        var minTop = headerH + 20;
        // left blocks share the side with the title — stay below it
        var minLeft = Math.max(minTop, title.offsetTop + title.offsetHeight + 24);
        Array.prototype.forEach.call(items, function (item) {
          item.style.top = "";
          item.classList.remove("is-compact");
          var min = item.classList.contains("home-solutions__item--left") ? minLeft : minTop;
          // still too tall (low window + long text): drop the decorative art
          if (item.offsetHeight > h - 24 - min) item.classList.add("is-compact");
          var want = item.offsetTop; // from --sol-top
          var maxTop = h - item.offsetHeight - 24;
          item.style.top = Math.max(min, Math.min(want, maxTop)) + "px";
        });
      }

      // lead: horizontal from the number, a step, horizontal into the layer —
      // from the real frame box, so it hits the layer at any width
      function drawLeads() {
        placeItems();
        var box = svg.getBoundingClientRect();
        var frame = layers.getBoundingClientRect();
        if (!frame.width || !box.width) return;
        svg.setAttribute("width", box.width);
        svg.setAttribute("height", box.height);

        leads.forEach(function (l) {
          var num = l.item.querySelector(".home-solutions__num");
          var x1 = frame.left + frame.width * l.aim.x - box.left;
          var y1 = frame.top + frame.height * l.aim.y - box.top;
          // offsets, not rects: independent of the fade transforms
          var nx = l.item.offsetLeft + num.offsetLeft;
          var ny = l.item.offsetTop + num.offsetTop;
          // start on the side facing the target, not across the pill
          var x0 = x1 > nx + num.offsetWidth / 2 ? nx + num.offsetWidth : nx;
          var y0 = ny + num.offsetHeight / 2;
          var dx = x1 - x0;
          l.lead.setAttribute("points", x0 + "," + y0 + " " + (x0 + dx * 0.42) + "," + y0 + " " + (x0 + dx * 0.72) + "," + y1 + " " + x1 + "," + y1);
        });
      }

      drawLeads();
      // heights change once the illustrations arrive
      Array.prototype.forEach.call(section.querySelectorAll(".home-solutions__art img"), function (img) {
        if (!img.complete) img.addEventListener("load", drawLeads, { once: true });
      });
      ScrollTrigger.addEventListener("refresh", drawLeads);
      window.addEventListener("resize", drawLeads);

      return function () {
        ScrollTrigger.removeEventListener("refresh", drawLeads);
        window.removeEventListener("resize", drawLeads);
        triggers.forEach(function (t) { t.kill(); });
        timelines.forEach(function (tl) {
          if (tl.scrollTrigger) tl.scrollTrigger.kill();
          tl.kill();
        });
        while (svg.firstChild) svg.removeChild(svg.firstChild);
        Array.prototype.forEach.call(items, function (item) {
          item.classList.remove("is-active", "is-compact");
          item.style.top = "";
        });
        gsap.set([reveal, layers], { clearProps: "all" }); // the flow layout takes its own
      };
    },

    "(max-width: 1199px)": function () {
      return loopInView(layers);
    },
  });
})();
