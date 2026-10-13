/* global gsap, ScrollTrigger */
// Stats section: cursor-reactive dot grid + count-up numbers.
// The same dot grid runs on the home solutions scene background, invisible
// at rest (AREAS below).
//
// Dots — each .js-stat-dots gets a canvas redrawing the mockup grid (5px dots,
// 25px step, white 5%). Near the cursor dots swell, push away a little and
// light up in the accent blue, then ease back. The frame loop runs only while
// the pointer is over the section or dots are still settling. Works with a
// mouse, a touchpad and a finger (touch laptops included); small touch-only
// screens and reduced motion keep the static CSS grid (no canvas).
//
// Numbers — [data-count] counts from 0 on first view, keeping the mockup
// format ("2 000+", ">50%"), in every .js-stats section. The final text is in
// the markup, so no-JS is complete.
(function () {
  "use strict";

  var section = document.querySelector(".js-stats");

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  // Any device with a big screen or any precise pointer — touch laptops /
  // convertibles report a coarse *primary* pointer while a mouse or touchpad
  // is in use, so "(hover: hover) and (pointer: fine)" left them without dots.
  var canPoint = window.matchMedia("(min-width: 1200px), (any-pointer: fine)").matches;

  // ── Count-up ─────────────────────────────────────────────────────
  if (section && !reduceMotion && typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    document.querySelectorAll(".js-stats [data-count]").forEach(function (el) {
      var end = parseFloat(el.getAttribute("data-count")) || 0;
      // text around the number stays: "" … "+", ">" … "%"
      var parts = el.textContent.trim().match(/^([^\d]*)[\d\s ]+(.*)$/) || ["", "", ""];
      var prefix = parts[1];
      var suffix = parts[2];
      // thousands separator: a space ("2 000+") unless data-count-sep says otherwise
      var sep = el.hasAttribute("data-count-sep") ? el.getAttribute("data-count-sep") : "\u00a0";
      var state = { v: 0 };
      var format = function (n) {
        return prefix + String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, sep) + suffix;
      };

      el.textContent = format(0);
      ScrollTrigger.create({
        trigger: el,
        start: "top 90%",
        once: true,
        onEnter: function () {
          gsap.to(state, {
            v: end,
            duration: 1.8,
            ease: "power2.out",
            onUpdate: function () {
              el.textContent = format(state.v);
            },
          });
        },
      });
    });
  }

  // ── Dot grid ─────────────────────────────────────────────────────
  if (reduceMotion || !canPoint) return;

  // Zones with the same dot grid. base = dot opacity at rest:
  //   stats cards — faint grid always visible (mockup)
  //   solutions scene / expertise stat rows — invisible, dots only by the cursor
  var AREAS = [
    { root: ".js-stats", host: ".js-stat-dots", base: 0.05 },
    { root: ".js-solutions", host: ".js-sol-dots", base: 0 },
    // any dark block: <div class="dot-field js-dot-field"> as its first child
    // (expertise stat rows, footer) — reacts to the cursor over its parent
    { host: ".js-dot-field", base: 0, own: true },
  ];
  var ACCENT = [35, 88, 252];
  var grids = [];
  var running = false;

  function remPx(px) {
    return (px * parseFloat(getComputedStyle(document.documentElement).fontSize)) / 16;
  }

  function Grid(host, pointer, base) {
    this.host = host;
    this.pointer = pointer;
    this.base = base;
    this.canvas = document.createElement("canvas");
    this.ctx = this.canvas.getContext("2d");
    host.appendChild(this.canvas);
    host.classList.add("is-live");
    this.resize();
  }

  Grid.prototype.resize = function () {
    var rect = this.host.getBoundingClientRect();
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    this.w = rect.width;
    this.h = rect.height;
    this.canvas.width = Math.round(rect.width * dpr);
    this.canvas.height = Math.round(rect.height * dpr);
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    this.step = remPx(25);
    this.r = remPx(2.5);
    this.reach = remPx(170); // influence radius
    this.push = remPx(6); // max displacement

    var cols = Math.floor((this.w - this.r * 2) / this.step) + 1;
    var rows = Math.floor((this.h - this.r * 2) / this.step) + 1;
    // centred across: the same margin left and right
    var x0 = this.r + (this.w - this.r * 2 - (cols - 1) * this.step) / 2;
    this.n = cols * rows;
    this.x = new Float32Array(this.n);
    this.y = new Float32Array(this.n);
    this.f = new Float32Array(this.n); // eased influence 0..1
    var i = 0;
    for (var row = 0; row < rows; row++) {
      for (var col = 0; col < cols; col++) {
        this.x[i] = x0 + col * this.step;
        this.y[i] = this.r + row * this.step;
        i++;
      }
    }
    this.draw(true);
  };

  // returns true while any dot is still moving
  Grid.prototype.draw = function (force) {
    var ctx = this.ctx;
    var pointer = this.pointer;
    var base = this.base;
    var rect = this.host.getBoundingClientRect();
    var px = pointer.inside ? pointer.x - rect.left : -9999;
    var py = pointer.inside ? pointer.y - rect.top : -9999;
    var busy = false;

    ctx.clearRect(0, 0, this.w, this.h);

    // resting dots in one batch (nothing to paint when they rest invisible)
    ctx.fillStyle = "rgba(255,255,255," + base + ")";
    ctx.beginPath();
    var active = [];
    for (var i = 0; i < this.n; i++) {
      var dx = this.x[i] - px;
      var dy = this.y[i] - py;
      var d = Math.sqrt(dx * dx + dy * dy);
      var target = d < this.reach ? 1 - d / this.reach : 0;
      target *= target; // soft falloff
      // ease in fast, out slow — reads as a trail behind the cursor
      this.f[i] += (target - this.f[i]) * (target > this.f[i] ? 0.25 : 0.07);
      if (this.f[i] < 0.002) {
        this.f[i] = 0;
        if (base) {
          ctx.moveTo(this.x[i] + this.r, this.y[i]);
          ctx.arc(this.x[i], this.y[i], this.r, 0, Math.PI * 2);
        }
      } else {
        busy = true;
        active.push(i, dx, dy, d);
      }
    }
    ctx.fill();

    // lit dots
    for (var k = 0; k < active.length; k += 4) {
      var j = active[k];
      var f = this.f[j];
      var len = active[k + 3] || 1;
      var ox = (active[k + 1] / len) * this.push * f;
      var oy = (active[k + 2] / len) * this.push * f;
      // calmer than the first take: smaller swell, softer blue
      var mix = Math.min(1, f * 1.1);
      var c = [
        Math.round(255 + (ACCENT[0] - 255) * mix),
        Math.round(255 + (ACCENT[1] - 255) * mix),
        Math.round(255 + (ACCENT[2] - 255) * mix),
      ];
      ctx.fillStyle = "rgba(" + c[0] + "," + c[1] + "," + c[2] + "," + (base + 0.5 * f) + ")";
      ctx.beginPath();
      ctx.arc(this.x[j] + ox, this.y[j] + oy, this.r * (1 + 0.35 * f), 0, Math.PI * 2);
      ctx.fill();
    }

    return busy || force;
  };

  function loop() {
    var busy = false;
    for (var i = 0; i < grids.length; i++) {
      if (grids[i].draw() || grids[i].pointer.inside) busy = true;
    }
    if (busy) {
      requestAnimationFrame(loop);
    } else {
      running = false;
    }
  }

  function kick() {
    if (running) return;
    running = true;
    requestAnimationFrame(loop);
  }

  AREAS.forEach(function (area) {
    if (area.own) {
      document.querySelectorAll(area.host).forEach(function (host) {
        setup(host.parentElement, [host], area.base);
      });
      return;
    }
    var root = document.querySelector(area.root);
    if (!root) return;
    var hosts = root.querySelectorAll(area.host);
    if (!hosts.length) return;
    setup(root, hosts, area.base);
  });

  function setup(root, hosts, base) {
    var pointer = { x: -9999, y: -9999, inside: false };
    var area = { base: base };

    hosts.forEach(function (host) {
      grids.push(new Grid(host, pointer, area.base));
    });

    root.addEventListener("pointermove", function (e) {
      pointer.x = e.clientX;
      pointer.y = e.clientY;
      pointer.inside = true;
      kick();
    });

    function leave() {
      pointer.inside = false;
      kick(); // let the dots settle back
    }
    root.addEventListener("pointerleave", leave);
    // a finger: dots follow while it drags, fade once it lifts
    root.addEventListener("pointerdown", function (e) {
      if (e.pointerType !== "touch") return;
      pointer.x = e.clientX;
      pointer.y = e.clientY;
      pointer.inside = true;
      kick();
    });
    root.addEventListener("pointerup", function (e) {
      if (e.pointerType === "touch") leave();
    });
    root.addEventListener("pointercancel", leave);

    // a pinned scene scrolls under a still cursor: redraw on scroll too
    window.addEventListener("scroll", function () {
      if (pointer.inside) kick();
    }, { passive: true });
  }

  if ("ResizeObserver" in window) {
    var ro = new ResizeObserver(function () {
      grids.forEach(function (g) {
        g.resize();
      });
    });
    grids.forEach(function (g) {
      ro.observe(g.host);
    });
  }
})();
