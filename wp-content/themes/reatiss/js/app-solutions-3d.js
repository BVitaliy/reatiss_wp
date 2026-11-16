// Home — Advanced Engineering Solutions, 3D scene (≥1200 with WebGL).
// ES module, imported on demand by app-solutions.js; three.js from
// js/vendors (three.module.min.js + three.core.min.js, `gulp updateJS`).
//
// Story, scrubbed by the scroll over the sticky section (one GSAP timeline):
//   laptop, lid slightly open → it opens → the keyboard (top case) lifts and
//   tips up on its back edge (body and screen stay) → the cooler comes off →
//   the motherboard rises and slides out towards the camera, the body drops
//   out of the frame → traces light up, camera to the CPU
//   → the package lifts off the board and flips (pads up), back →
//   the lid (IHS) comes off, the die rises out of its cavity →
//   package layers spread on the left (lid hanging below), die layers on the
//   right → the services stand next to their layers with lead lines.
// The laptop, board and parts are built in code (no model to license or
// download); the package and die layers wear the client's photos
// (img/home/solutions-3d/). Services: .js-solutions-item, data-layer = the
// layer the lead points at (pkg-top, pkg-inner, pkg-bottom, pkg-lid, die-1…4).
import * as THREE from "./vendors/three.module.min.js";

const IMG = "img/home/solutions-3d/";

export function mount(section, { gsap, ScrollTrigger, debugProgress = null, imgBase = "" }) {
  const host = section.querySelector(".js-solutions-scene");
  const inner = section.querySelector(".home-solutions__inner");
  const title = section.querySelector(".home-solutions__title");
  const svg = section.querySelector(".js-solutions-leads");
  const items = Array.from(section.querySelectorAll(".js-solutions-item"));

  const canvas = document.createElement("canvas");
  host.appendChild(canvas);

  // ------------------------------------------------------------ renderer

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: "high-performance", preserveDrawingBuffer: debugProgress !== null });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;

  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  const envRT = pmrem.fromScene(studio(), 0.03);
  scene.environment = envRT.texture;
  scene.environmentIntensity = 0.75;
  pmrem.dispose();

  const camera = new THREE.PerspectiveCamera(30, 1, 0.5, 500);

  scene.add(new THREE.HemisphereLight(0xe6ecff, 0x0a0a1a, 0.35));
  const key = new THREE.DirectionalLight(0xffffff, 2.3);
  key.position.set(14, 28, 16);
  const rim = new THREE.DirectionalLight(0xa8c4ff, 1.3);
  rim.position.set(-20, 12, -26);
  const fill = new THREE.DirectionalLight(0xffe6cc, 0.45);
  fill.position.set(-16, 8, 20);
  scene.add(key, rim, fill);

  const maxAniso = renderer.capabilities.getMaxAnisotropy();
  const disposables = [];

  // ------------------------------------------------------------ helpers

  // neutral studio for reflections: dark room, a big soft box above,
  // strips on the sides (cool on the right)
  function studio() {
    const s = new THREE.Scene();
    const box = new THREE.BoxGeometry(1, 1, 1);
    const room = new THREE.Mesh(box, new THREE.MeshBasicMaterial({ color: 0x15171c, side: THREE.BackSide }));
    room.scale.set(40, 22, 40);
    room.position.y = 8;
    s.add(room);
    const panel = (hex, k, pos, scale) => {
      const m = new THREE.Mesh(box, new THREE.MeshBasicMaterial({ color: new THREE.Color(hex).multiplyScalar(k) }));
      m.position.set(...pos);
      m.scale.set(...scale);
      s.add(m);
    };
    panel(0xffffff, 5, [0, 18.5, 0], [18, 0.2, 12]);
    panel(0xffffff, 2.6, [-19.5, 8, 2], [0.2, 7, 18]);
    panel(0xbcd2ff, 2.2, [19.5, 9, -6], [0.2, 5, 12]);
    panel(0xffffff, 1.2, [0, 5, -19.5], [24, 3, 0.2]);
    panel(0xffffff, 0.8, [6, 3, 19.5], [10, 2, 0.2]);
    return s;
  }

  // seeded random — generated textures look the same on every load
  function rng(seed) {
    let v = seed >>> 0;
    return () => {
      v = (v * 1664525 + 1013904223) >>> 0;
      return v / 4294967296;
    };
  }

  function canvasTex(c, srgb = true) {
    const t = new THREE.CanvasTexture(c);
    if (srgb) t.colorSpace = THREE.SRGBColorSpace;
    t.anisotropy = maxAniso;
    disposables.push(t);
    return t;
  }

  function makeCanvas(w, h) {
    const c = document.createElement("canvas");
    c.width = w;
    c.height = h;
    return c;
  }

  function rrShape(w, d, r) {
    r = Math.min(r, w / 2, d / 2);
    const s = new THREE.Shape();
    const x = -w / 2;
    const y = -d / 2;
    s.moveTo(x + r, y);
    s.lineTo(x + w - r, y);
    s.quadraticCurveTo(x + w, y, x + w, y + r);
    s.lineTo(x + w, y + d - r);
    s.quadraticCurveTo(x + w, y + d, x + w - r, y + d);
    s.lineTo(x + r, y + d);
    s.quadraticCurveTo(x, y + d, x, y + d - r);
    s.lineTo(x, y + r);
    s.quadraticCurveTo(x, y, x + r, y);
    return s;
  }

  // top-down planar UVs over a w × d footprint (image top = far edge);
  // crop = part of the image in fractions, y from the top
  function planarUV(g, w, d, crop) {
    const p = g.attributes.position;
    const uv = g.attributes.uv;
    const c = crop || { x: 0, y: 0, w: 1, h: 1 };
    for (let i = 0; i < p.count; i++) {
      const u = p.getX(i) / w + 0.5;
      const v = 0.5 - p.getZ(i) / d;
      uv.setXY(i, c.x + u * c.w, 1 - c.y - c.h + v * c.h);
    }
    uv.needsUpdate = true;
  }

  // a flat part from a 2D outline: y 0 → h, bevelled edges, planar UVs.
  // Material groups: 0 = top + bottom faces, 1 = sides.
  function extrude(shape, w, d, h, b, crop) {
    const g = new THREE.ExtrudeGeometry(shape, {
      depth: Math.max(h - 2 * b, 0.0001),
      bevelEnabled: b > 0,
      bevelThickness: b,
      bevelSize: b,
      bevelSegments: b > 0 ? 3 : 0,
      curveSegments: 10,
    });
    g.rotateX(-Math.PI / 2);
    g.translate(0, b, 0);
    planarUV(g, w, d, crop);
    disposables.push(g);
    return g;
  }

  // rounded box, y 0 → h
  function rbox(w, h, d, r = 0.05, b = Math.min(0.04, h / 3), crop) {
    return extrude(rrShape(w - 2 * b, d - 2 * b, Math.max(r - b, 0.002)), w, d, h, b, crop);
  }

  // flat decal / panel facing up
  function flat(shape, w, d, crop) {
    const g = new THREE.ShapeGeometry(shape, 10);
    g.rotateX(-Math.PI / 2);
    planarUV(g, w, d, crop);
    disposables.push(g);
    return g;
  }

  function mesh(geo, mat, x = 0, y = 0, z = 0, parent) {
    const m = new THREE.Mesh(geo, mat);
    m.position.set(x, y, z);
    if (parent) parent.add(m);
    return m;
  }

  // fading a group: its meshes get their own material copies first
  function fader(group) {
    const mats = [];
    group.traverse((o) => {
      if (!o.isMesh) return;
      o.material = Array.isArray(o.material) ? o.material.map((m) => m.clone()) : o.material.clone();
      (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => {
        // its own see-through look (grilles, backlight) comes back at v = 1
        m.userData.base = { opacity: m.opacity, transparent: m.transparent, depthWrite: m.depthWrite };
        mats.push(m);
      });
    });
    const state = { v: 1 };
    state.apply = () => {
      mats.forEach((m) => {
        const b = m.userData.base;
        m.opacity = b.opacity * state.v;
        m.transparent = b.transparent || state.v < 1;
        m.depthWrite = b.depthWrite && state.v > 0.5;
      });
      group.visible = state.v > 0.005;
    };
    return state;
  }

  // ------------------------------------------------------------ textures

  // brushed metal: horizontal streaks in the roughness
  function brushed() {
    const c = makeCanvas(512, 512);
    const x = c.getContext("2d");
    const r = rng(11);
    x.fillStyle = "rgb(120,120,120)";
    x.fillRect(0, 0, 512, 512);
    for (let i = 0; i < 2600; i++) {
      const g = 108 + r() * 26;
      x.fillStyle = `rgba(${g},${g},${g},.35)`;
      x.fillRect(r() * 512, r() * 512, 40 + r() * 260, 1);
    }
    const t = canvasTex(c, false);
    t.wrapS = t.wrapT = THREE.RepeatWrapping;
    t.repeat.set(6, 6);
    return t;
  }

  // PCB: solder mask, copper pours, routed buses with 45° turns, vias,
  // pad fields, silkscreen. Emissive map = traces only (the glow).
  function pcb({ w, h, seed, base = ["#103029", "#081a16"], trace = "rgba(80,160,120,.55)", pad = "#c7a35a", buses = 46, singles = 140, silk = true }) {
    const map = makeCanvas(w, h);
    const glow = makeCanvas(w, h);
    const c = map.getContext("2d");
    const g = glow.getContext("2d");
    const r = rng(seed);

    const grad = c.createLinearGradient(0, 0, w, h);
    grad.addColorStop(0, base[0]);
    grad.addColorStop(1, base[1]);
    c.fillStyle = grad;
    c.fillRect(0, 0, w, h);
    g.fillStyle = "#000";
    g.fillRect(0, 0, w, h);

    // copper pours under the mask
    c.fillStyle = "rgba(60,120,95,.18)";
    for (let i = 0; i < 12; i++) c.fillRect(r() * w, r() * h, 80 + r() * 300, 40 + r() * 200);

    const dirs = [[1, 0], [0, 1], [-1, 0], [0, -1], [0.7071, 0.7071], [0.7071, -0.7071], [-0.7071, 0.7071], [-0.7071, -0.7071]];
    const route = (x0, y0, segs) => {
      let x = x0;
      let y = y0;
      let d = dirs[Math.floor(r() * 4)];
      const pts = [[x, y]];
      for (let s = 0; s < segs; s++) {
        const len = 30 + r() * 220;
        x += d[0] * len;
        y += d[1] * len;
        pts.push([x, y]);
        const i = dirs.indexOf(d);
        d = i < 4 ? dirs[4 + Math.floor(r() * 4)] : dirs[Math.floor(r() * 4)];
      }
      return pts;
    };
    const stroke = (pts, off, width, alpha) => {
      [[c, trace], [g, `rgba(120,205,255,${alpha})`]].forEach(([ctx, col]) => {
        ctx.strokeStyle = col;
        ctx.lineWidth = width;
        ctx.lineJoin = "round";
        ctx.beginPath();
        pts.forEach(([px, py], k) => {
          // parallel copies: offset across the first segment's direction
          const nx = px + off[0];
          const ny = py + off[1];
          if (k) ctx.lineTo(nx, ny);
          else ctx.moveTo(nx, ny);
        });
        ctx.stroke();
      });
    };

    // buses: 3–10 parallel traces
    for (let b = 0; b < buses; b++) {
      const pts = route(r() * w, r() * h, 2 + Math.floor(r() * 3));
      const n = 3 + Math.floor(r() * 8);
      const [ax, ay] = pts[0];
      const [bx, by] = pts[1];
      const len = Math.hypot(bx - ax, by - ay) || 1;
      const px = -(by - ay) / len;
      const py = (bx - ax) / len;
      const alpha = 0.25 + r() * 0.55;
      for (let k = 0; k < n; k++) stroke(pts, [px * (k - n / 2) * 7, py * (k - n / 2) * 7], 2.2, alpha);
    }
    for (let s = 0; s < singles; s++) stroke(route(r() * w, r() * h, 1 + Math.floor(r() * 3)), [0, 0], 1.6 + r() * 2.5, 0.2 + r() * 0.5);

    // vias
    for (let i = 0; i < 520; i++) {
      const x = r() * w;
      const y = r() * h;
      c.fillStyle = pad;
      c.beginPath();
      c.arc(x, y, 3.2, 0, Math.PI * 2);
      c.fill();
      c.fillStyle = "#06100d";
      c.beginPath();
      c.arc(x, y, 1.4, 0, Math.PI * 2);
      c.fill();
    }

    // pad fields
    c.fillStyle = pad;
    for (let i = 0; i < 34; i++) {
      const x = r() * w;
      const y = r() * h;
      const cols = 2 + Math.floor(r() * 12);
      const rows = 1 + Math.floor(r() * 3);
      for (let a = 0; a < cols; a++) for (let b = 0; b < rows; b++) c.fillRect(x + a * 11, y + b * 16, 6, 10);
    }

    if (silk) {
      c.strokeStyle = "rgba(235,240,235,.38)";
      c.fillStyle = "rgba(235,240,235,.42)";
      c.lineWidth = 2;
      c.font = "600 15px monospace";
      for (let i = 0; i < 60; i++) {
        const x = r() * w;
        const y = r() * h;
        const bw = 24 + r() * 110;
        const bh = 16 + r() * 60;
        c.strokeRect(x, y, bw, bh);
        c.fillText(["C", "R", "U", "L", "Q", "J", "FB"][Math.floor(r() * 7)] + Math.floor(1 + r() * 980), x + 2, y - 5);
      }
      // mounting holes
      for (let i = 0; i < 6; i++) {
        const x = 60 + r() * (w - 120);
        const y = 60 + r() * (h - 120);
        c.fillStyle = pad;
        c.beginPath();
        c.arc(x, y, 16, 0, Math.PI * 2);
        c.fill();
        c.fillStyle = "#050807";
        c.beginPath();
        c.arc(x, y, 9, 0, Math.PI * 2);
        c.fill();
      }
    }
    return { map: canvasTex(map), glow: canvasTex(glow) };
  }

  // chip top: dark epoxy, faint marking
  function chipTex(lines, { bg = "#141518", ink = "rgba(170,175,182,.55)", dot = true } = {}) {
    const c = makeCanvas(256, 256);
    const x = c.getContext("2d");
    x.fillStyle = bg;
    x.fillRect(0, 0, 256, 256);
    x.fillStyle = ink;
    x.font = "600 26px Arial, sans-serif";
    lines.forEach((l, i) => x.fillText(l, 28, 96 + i * 34));
    if (dot) {
      x.beginPath();
      x.arc(30, 30, 9, 0, Math.PI * 2);
      x.fill();
    }
    return canvasTex(c);
  }

  // label sticker (battery, SSD)
  function labelTex(w, h, { bg, ink, lines, barcode = true, size = 30 }) {
    const c = makeCanvas(w, h);
    const x = c.getContext("2d");
    x.fillStyle = bg;
    x.fillRect(0, 0, w, h);
    x.fillStyle = ink;
    x.font = `700 ${size}px Arial, sans-serif`;
    lines.forEach((l, i) => {
      x.font = `${i ? 400 : 700} ${i ? size * 0.7 : size}px Arial, sans-serif`;
      x.fillText(l, w * 0.06, h * 0.2 + i * size * 1.25);
    });
    if (barcode) {
      const r = rng(lines.join("").length);
      let bx = w * 0.06;
      while (bx < w * 0.55) {
        const bw = 1 + Math.floor(r() * 4);
        x.fillRect(bx, h * 0.72, bw, h * 0.16);
        bx += bw + 1 + Math.floor(r() * 3);
      }
      x.strokeStyle = ink;
      x.lineWidth = 3;
      [0.68, 0.78, 0.88].forEach((fx, i) => {
        x.beginPath();
        x.moveTo(w * fx, h * 0.86);
        x.lineTo(w * fx + 22, h * 0.86);
        x.lineTo(w * fx + 11, h * 0.86 - 20);
        x.closePath();
        if (i === 1) x.stroke();
        else x.fill();
      });
    }
    return canvasTex(c);
  }

  // speaker grille: dark dots on transparent
  function grilleTex() {
    const c = makeCanvas(128, 512);
    const x = c.getContext("2d");
    x.fillStyle = "#fff";
    for (let row = 0; row < 64; row++) {
      for (let col = 0; col < 8; col++) {
        x.beginPath();
        x.arc(10 + col * 15 + (row % 2) * 7, 6 + row * 8, 2.4, 0, Math.PI * 2);
        x.fill();
      }
    }
    return canvasTex(c, false);
  }

  // ------------------------------------------------------------ materials

  const brush = brushed();
  const M = {
    alu: new THREE.MeshStandardMaterial({ color: 0x8b9199, metalness: 1, roughness: 0.4, roughnessMap: brush }),
    aluInner: new THREE.MeshStandardMaterial({ color: 0x3a3e44, metalness: 0.9, roughness: 0.5 }),
    aluDark: new THREE.MeshStandardMaterial({ color: 0x24272c, metalness: 1, roughness: 0.45 }),
    key: new THREE.MeshStandardMaterial({ color: 0x0c0d10, metalness: 0.15, roughness: 0.6 }),
    glass: new THREE.MeshStandardMaterial({ color: 0x050608, metalness: 0.6, roughness: 0.06 }),
    pad: new THREE.MeshStandardMaterial({ color: 0x5c626b, metalness: 0.9, roughness: 0.2 }),
    plastic: new THREE.MeshStandardMaterial({ color: 0x0b0c0e, metalness: 0.05, roughness: 0.5 }),
    rubber: new THREE.MeshStandardMaterial({ color: 0x08090a, metalness: 0, roughness: 0.9 }),
    ic: new THREE.MeshStandardMaterial({ color: 0x141518, metalness: 0.25, roughness: 0.35 }),
    copper: new THREE.MeshStandardMaterial({ color: 0xc8794a, metalness: 1, roughness: 0.26 }),
    nickel: new THREE.MeshStandardMaterial({ color: 0xb9bec6, metalness: 1, roughness: 0.28 }),
    shield: new THREE.MeshStandardMaterial({ color: 0x80858c, metalness: 1, roughness: 0.48, roughnessMap: brush }),
    gold: new THREE.MeshStandardMaterial({ color: 0xd8b060, metalness: 1, roughness: 0.3 }),
    cap: new THREE.MeshStandardMaterial({ color: 0x9a7650, metalness: 0.2, roughness: 0.5 }),
    choke: new THREE.MeshStandardMaterial({ color: 0x45484d, metalness: 0.5, roughness: 0.5 }),
    pcbEdge: new THREE.MeshStandardMaterial({ color: 0x0d2620, metalness: 0.1, roughness: 0.6 }),
    flex: new THREE.MeshStandardMaterial({ color: 0xb8742a, metalness: 0.3, roughness: 0.45, side: THREE.DoubleSide }),
    substrateEdge: new THREE.MeshStandardMaterial({ color: 0x123425, metalness: 0.1, roughness: 0.55 }),
    dieEdge: new THREE.MeshStandardMaterial({ color: 0x5d5034, metalness: 0.8, roughness: 0.35 }),
    port: new THREE.MeshStandardMaterial({ color: 0x050506, metalness: 0.3, roughness: 0.6 }),
  };
  Object.values(M).forEach((m) => disposables.push(m));

  // ------------------------------------------------------------ laptop

  const W = 31.2; // 14" class, cm
  const D = 22;
  const R = 0.95; // corner radius
  const TUB_H = 0.95;
  const TOP_H = 0.24;
  const DECK_Y = TUB_H + TOP_H; // top of the keyboard deck

  const laptop = new THREE.Group();
  scene.add(laptop);

  // bottom case: floor + walls (hollow, so the parts show once the top is off)
  const tub = new THREE.Group();
  laptop.add(tub);
  mesh(rbox(W, 0.22, D, R, 0.06), [M.alu, M.alu], 0, 0, 0, tub);
  {
    const t = 0.32;
    const ring = rrShape(W - 0.08, D - 0.08, R - 0.04);
    ring.holes.push(rrShape(W - 2 * t, D - 2 * t, R - t));
    mesh(extrude(ring, W, D, TUB_H, 0.04), [M.alu, M.alu], 0, 0, 0, tub);
    mesh(flat(rrShape(W - 2 * t, D - 2 * t, R - t), W, D), M.aluInner, 0, 0.225, 0, tub);
    // USB-C / audio on the sides
    const portGeo = new THREE.PlaneGeometry(0.95, 0.32);
    disposables.push(portGeo);
    [[-1, -3.2], [-1, -1.7], [1, -3.2], [1, 2.4]].forEach(([side, z], i) => {
      const p = mesh(i === 3 ? new THREE.CircleGeometry(0.17, 20) : portGeo, M.port, side * (W / 2 + 0.002), 0.62, z, tub);
      p.rotation.y = side * Math.PI / 2;
    });
    // rubber feet
    [-1, 1].forEach((s) => mesh(rbox(W - 6, 0.12, 0.7, 0.35, 0.05), M.rubber, 0, -0.1, s * (D / 2 - 2.2), tub));
  }

  // parts inside the body
  const body = new THREE.Group(); // battery, speakers, fans — leave with the tub
  tub.add(body);
  const FLOOR = 0.225;
  {
    const cellLabel = labelTex(512, 512, { bg: "#18191c", ink: "#e8e8e8", lines: ["Li-ion Polymer Battery", "11.55V ⎓ 72Wh 6234mAh", "Rechargeable · Model A2389", "Do not puncture or crush"], size: 34 });
    const cellTop = new THREE.MeshStandardMaterial({ map: cellLabel, metalness: 0.15, roughness: 0.55 });
    disposables.push(cellTop);
    [-8.4, 0, 8.4].forEach((x) => mesh(rbox(7.8, 0.6, 7.6, 0.35, 0.08), [cellTop, M.plastic], x, FLOOR, 5.5, body));
    // flex from the battery to the board
    const flexGeo = new THREE.PlaneGeometry(2.2, 2.2);
    flexGeo.rotateX(-Math.PI / 2);
    disposables.push(flexGeo);
    mesh(flexGeo, M.flex, 0.8, FLOOR + 0.62, 0.8, body);

    const grille = new THREE.MeshStandardMaterial({ color: 0x101113, alphaMap: grilleTex(), transparent: true, metalness: 0.4, roughness: 0.5 });
    disposables.push(grille);
    [-1, 1].forEach((s) => {
      const spk = new THREE.Group();
      mesh(rbox(2.3, 0.62, 6.4, 0.4, 0.08), [M.plastic, M.plastic], 0, 0, 0, spk);
      mesh(flat(rrShape(1.6, 5.2, 0.3), 1.6, 5.2), grille, 0, 0.625, 0, spk);
      spk.position.set(s * 13.35, FLOOR, 5.4);
      body.add(spk);
    });
  }

  const fans = [];
  {
    const bladeGeo = new THREE.BoxGeometry(0.95, 0.42, 0.035);
    disposables.push(bladeGeo);
    [-1, 1].forEach((s) => {
      const fan = new THREE.Group();
      const shell = new THREE.CylinderGeometry(2.55, 2.55, 0.6, 56, 1, true);
      disposables.push(shell);
      mesh(shell, M.plastic, 0, 0.3, 0, fan);
      // top cover with the intake ring, exhaust duct towards the back
      const cover = rrShape(5.3, 5.3, 2.6);
      cover.holes.push(new THREE.Path().absarc(0, 0, 1.55, 0, Math.PI * 2, true));
      mesh(extrude(cover, 5.3, 5.3, 0.06, 0), [M.plastic, M.plastic], 0, 0.6, 0, fan);
      mesh(rbox(3.2, 0.6, 1.4, 0.1, 0.04), [M.plastic, M.plastic], s * 0.2, 0, -2.6, fan);
      const hub = mesh(new THREE.CylinderGeometry(0.85, 0.85, 0.5, 32), M.plastic, 0, 0.3, 0, fan);
      disposables.push(hub.geometry);
      mesh(new THREE.CircleGeometry(0.62, 32).rotateX(-Math.PI / 2), M.nickel, 0, 0.555, 0, fan);
      const blades = new THREE.InstancedMesh(bladeGeo, M.plastic, 23);
      const q = new THREE.Quaternion();
      const e = new THREE.Euler();
      const m4 = new THREE.Matrix4();
      for (let i = 0; i < 23; i++) {
        const a = (i / 23) * Math.PI * 2;
        e.set(0.3, -a + 0.5, 0, "YXZ");
        q.setFromEuler(e);
        m4.compose(new THREE.Vector3(Math.cos(a) * 1.6, 0.3, Math.sin(a) * 1.6), q, new THREE.Vector3(1, 1, 1));
        blades.setMatrixAt(i, m4);
      }
      fan.add(blades);
      fan.userData.blades = blades;
      fan.position.set(s * 10.4, FLOOR, -5.4);
      body.add(fan);
      fans.push(fan);
    });
  }

  // motherboard: root slides out of the body; its "deck" (PCB + parts)
  // later drops away while the package (also a child) rises
  const BOARD_Y = FLOOR + 0.12; // standoffs
  const boardRoot = new THREE.Group();
  boardRoot.position.set(0, BOARD_Y, -5.6);
  laptop.add(boardRoot);
  const boardDeck = new THREE.Group();
  boardRoot.add(boardDeck);
  const BW = 27.2;
  const BD = 9.4;
  const pcbTex = pcb({ w: 2048, h: 708, seed: 7 });
  const boardTopMat = new THREE.MeshStandardMaterial({ map: pcbTex.map, emissive: 0x47a8ff, emissiveMap: pcbTex.glow, emissiveIntensity: 0, roughness: 0.55, metalness: 0.15 });
  disposables.push(boardTopMat);
  {
    // outline: main body between the fans + wings to the side ports
    const pts = [[-7.9, -4.7], [7.9, -4.7], [7.9, 2.8], [13.6, 2.8], [13.6, 4.7], [-13.6, 4.7], [-13.6, 2.8], [-7.9, 2.8]];
    const sh = new THREE.Shape(pts.map(([x, z]) => new THREE.Vector2(x, -z)));
    // two mounting holes in the wings
    [-12.8, 12.8].forEach((x) => sh.holes.push(new THREE.Path().absarc(x, -3.75, 0.22, 0, Math.PI * 2, true)));
    mesh(extrude(sh, BW, BD, 0.1, 0), [boardTopMat, M.pcbEdge], 0, 0, 0, boardDeck);

    const top = 0.1;
    const add = (geo, mat, x, z, y = top) => mesh(geo, mat, x, y, z, boardDeck);
    const ramMat = new THREE.MeshStandardMaterial({ map: chipTex(["LPDDR5X", "8GB 7500", "2638 KR"]), metalness: 0.25, roughness: 0.38 });
    const pchMat = new THREE.MeshStandardMaterial({ map: chipTex(["RTS-PCH", "SRKH4  X12", "L3A1 7745"]), metalness: 0.25, roughness: 0.38 });
    const smallMat = new THREE.MeshStandardMaterial({ map: chipTex(["TPS65", "994AE"], { dot: false }), metalness: 0.25, roughness: 0.38 });
    disposables.push(ramMat, pchMat, smallMat);
    // memory: four BGA packages right of the CPU
    [[2.9, -2.6], [5.3, -2.6], [2.9, -0.4], [5.3, -0.4]].forEach(([x, z]) => add(rbox(2.0, 0.12, 1.5, 0.06, 0.02), [ramMat, M.ic], x, z));
    add(rbox(2.2, 0.14, 2.2, 0.08, 0.03), [pchMat, M.ic], 2.1, 2.4);
    [[-5.6, -1.4], [6.7, 1.9], [-0.8, 3.6]].forEach(([x, z]) => add(rbox(0.9, 0.1, 0.9, 0.04, 0.02), [smallMat, M.ic], x, z));

    // M.2 SSD with its sticker
    const ssd = new THREE.Group();
    const ssdLabel = new THREE.MeshStandardMaterial({ map: labelTex(1024, 340, { bg: "#ecedef", ink: "#16181b", lines: ["NVMe SSD  1TB  PCIe 4.0", "M.2 2280 · S/N 7F3A9C21B04", "Made in Korea"], size: 44 }), metalness: 0.05, roughness: 0.6 });
    disposables.push(ssdLabel);
    mesh(rbox(6.8, 0.08, 2.2, 0.08, 0.02), [new THREE.MeshStandardMaterial({ map: pcb({ w: 512, h: 166, seed: 31, buses: 8, singles: 20, silk: false }).map, roughness: 0.5 }), M.pcbEdge], 0, 0, 0, ssd);
    mesh(rbox(5.6, 0.1, 1.9, 0.05, 0.01), [ssdLabel, M.ic], -0.4, 0.08, 0, ssd);
    mesh(rbox(0.5, 0.05, 1.7, 0.02, 0.01), [M.gold, M.gold], 3.15, 0.05, 0, ssd);
    ssd.position.set(4.2, top + 0.05, 3.3);
    boardDeck.add(ssd);

    // Wi-Fi card under its shield + antenna leads
    add(rbox(2.4, 0.22, 2.6, 0.08, 0.03), [M.shield, M.shield], -5.6, 3.2);
    [[-5.1, 2.3], [-6.1, 2.3]].forEach(([x, z]) => add(new THREE.CylinderGeometry(0.12, 0.12, 0.12, 16), M.gold, x, z, top + 0.28));
    // VRM: chokes + MOSFETs above the CPU
    const chokes = new THREE.InstancedMesh(rbox(0.72, 0.46, 0.72, 0.06, 0.03), M.choke, 7);
    const fets = new THREE.InstancedMesh(rbox(0.55, 0.1, 0.45, 0.03, 0.02), M.ic, 7);
    const m4 = new THREE.Matrix4();
    for (let i = 0; i < 7; i++) {
      chokes.setMatrixAt(i, m4.makeTranslation(-6.8 + i * 0.95, top, -3.9));
      fets.setMatrixAt(i, m4.makeTranslation(-6.8 + i * 0.95, top, -2.9));
    }
    boardDeck.add(chokes, fets);
    // passives, kept clear of the big parts
    const keep = [[-3.8, 0.2, -3.0, 1.9], [1.8, 6.4, -3.5, 0.5], [0.9, 3.4, 1.2, 3.6], [0.6, 7.8, 2.1, 4.6], [-7, -4.2, 1.8, 4.6], [-7.3, -0.3, -4.4, -2.5]];
    const r = rng(5);
    const caps = new THREE.InstancedMesh(rbox(0.22, 0.12, 0.34, 0.03, 0.02), M.cap, 170);
    const tiny = new THREE.InstancedMesh(rbox(0.14, 0.06, 0.24, 0.02, 0.01), M.ic, 120);
    const place = (inst, n) => {
      let i = 0;
      while (i < n) {
        const x = (r() - 0.5) * 15.2;
        const z = -4.4 + r() * 8.8;
        if (keep.some(([x0, x1, z0, z1]) => x > x0 && x < x1 && z > z0 && z < z1)) continue;
        m4.makeRotationY(r() < 0.5 ? 0 : Math.PI / 2).setPosition(x, top, z);
        inst.setMatrixAt(i++, m4);
      }
    };
    place(caps, 170);
    place(tiny, 120);
    boardDeck.add(caps, tiny);
    // connectors: battery / keyboard / display flex, USB-C on the wings
    [[-1.6, 4.3, 2.4], [1.6, 4.3, 1.6], [-3.6, -4.3, 2.0]].forEach(([x, z, w]) => {
      add(rbox(w, 0.18, 0.42, 0.04, 0.02), [M.plastic, M.plastic], x, z);
      add(rbox(w - 0.2, 0.03, 0.12, 0.02, 0.01), [M.nickel, M.nickel], x, z + 0.05, top + 0.18);
    });
    [[-13.2, 3.4], [-13.2, 4.2], [13.2, 3.4]].forEach(([x, z]) => {
      const p = add(rbox(0.95, 0.34, 0.8, 0.14, 0.04), [M.nickel, M.nickel], x, z);
      p.rotation.y = Math.PI / 2;
    });
  }

  // cooler: cold plate on the CPU, two flattened heat pipes to the fin stacks
  const CPU = new THREE.Vector3(-1.8, 0, -0.6); // on the board (local)
  const cooler = new THREE.Group();
  cooler.position.copy(boardRoot.position);
  laptop.add(cooler);
  {
    const plateY = 0.48;
    mesh(rbox(4.4, 0.14, 4.4, 0.25, 0.04), [M.copper, M.copper], CPU.x, plateY, CPU.z, cooler);
    [[-1, -1], [1, -1], [-1, 1], [1, 1]].forEach(([sx, sz]) => {
      mesh(rbox(1.3, 0.05, 0.5, 0.2, 0.02), [M.nickel, M.nickel], CPU.x + sx * 2.6, plateY + 0.06, CPU.z + sz * 2.1, cooler);
      const screw = mesh(new THREE.CylinderGeometry(0.2, 0.2, 0.12, 20), M.nickel, CPU.x + sx * 3.1, plateY + 0.12, CPU.z + sz * 2.1, cooler);
      disposables.push(screw.geometry);
    });
    [-1, 1].forEach((s, k) => {
      // fans sit at board-local z ≈ 0.2, their exhausts (fin stacks) at -2.6
      const curve = new THREE.CatmullRomCurve3([
        new THREE.Vector3(CPU.x + s * 0.6, plateY + 0.2, CPU.z + 0.9 - k * 1.8),
        new THREE.Vector3(CPU.x + s * 2.8, plateY + 0.22, CPU.z - 1.6),
        new THREE.Vector3(s * 7, plateY + 0.22, -2.3 - k * 0.3),
        new THREE.Vector3(s * 10.4, plateY + 0.2, -2.6),
      ]);
      const tube = new THREE.TubeGeometry(curve, 90, 0.26, 14);
      tube.scale(1, 0.5, 1);
      disposables.push(tube);
      mesh(tube, M.copper, 0, 0, 0, cooler);
      // fin stack at the fan's exhaust
      const fin = new THREE.InstancedMesh(new THREE.BoxGeometry(0.035, 0.55, 1.4), M.copper, 30);
      disposables.push(fin.geometry);
      const m4 = new THREE.Matrix4();
      for (let i = 0; i < 30; i++) fin.setMatrixAt(i, m4.makeTranslation(s * 10.4 + (i - 14.5) * 0.11, plateY - 0.05, -2.6));
      cooler.add(fin);
    });
  }

  // top case: deck + keyboard + trackpad + lid (all lift off together)
  // hinged at its back edge: lifts, then tips up like a hood
  const deckHinge = new THREE.Group();
  deckHinge.position.set(0, TUB_H, -D / 2);
  laptop.add(deckHinge);
  const topCase = new THREE.Group();
  topCase.position.set(0, -TUB_H, D / 2);
  deckHinge.add(topCase);
  mesh(rbox(W, TOP_H, D, R, 0.07), [M.alu, M.alu], 0, TUB_H, 0, topCase);
  const KB_Z = -3.6; // keyboard centre
  {
    // keyboard well (dark recess) with the backlight under the keys
    mesh(flat(rrShape(27, 11.6, 0.5), 27, 11.6), M.aluDark, 0, DECK_Y + 0.001, KB_Z, topCase);
    const backlight = new THREE.MeshBasicMaterial({ color: 0x8cb6ff, transparent: true, opacity: 0.0, blending: THREE.AdditiveBlending, depthWrite: false });
    disposables.push(backlight);
    mesh(flat(rrShape(26.2, 10.8, 0.4), 26.2, 10.8), backlight, 0, DECK_Y + 0.004, KB_Z, topCase);
    topCase.userData.backlight = backlight;

    // layout in key units (u), rows from the back; [width, height] per key
    const u = 1.78;
    const gap = 0.2;
    const rows = [
      { h: 0.62, keys: [1.5, ...Array(12).fill(1), 1] },
      { h: 1, keys: [...Array(13).fill(1), 1.5] },
      { h: 1, keys: [1.5, ...Array(13).fill(1)] },
      { h: 1, keys: [1.75, ...Array(11).fill(1), 1.75] },
      { h: 1, keys: [2.25, ...Array(10).fill(1), 2.25] },
      { h: 1, keys: [1, 1, 1, 1.25, 5, 1.25, 1, "arrows"] },
    ];
    const totalD = rows.reduce((s, r) => s + r.h * u, 0);
    const bySize = new Map();
    const put = (w, h, x, z) => {
      const k = w.toFixed(2) + "x" + h.toFixed(2);
      if (!bySize.has(k)) bySize.set(k, { w, h, at: [] });
      bySize.get(k).at.push([x, z]);
    };
    let z = KB_Z - totalD / 2;
    rows.forEach((row) => {
      const rowW = 14.5 * u;
      let x = -rowW / 2;
      const zc = z + (row.h * u) / 2;
      row.keys.forEach((k) => {
        if (k === "arrows") {
          // ← ↑↓ →: half-height up / down in the middle
          const kw = u - gap;
          put(kw, row.h * u - gap, x + u / 2, zc + u * 0.12);
          put(kw, (row.h * u) / 2 - gap, x + u * 1.5, zc - u * 0.25);
          put(kw, (row.h * u) / 2 - gap, x + u * 1.5, zc + u * 0.25);
          put(kw, row.h * u - gap, x + u * 2.5, zc + u * 0.12);
          x += 3 * u;
          return;
        }
        put(k * u - gap, row.h * u - gap, x + (k * u) / 2, zc);
        x += k * u;
      });
      z += row.h * u;
    });
    const m4 = new THREE.Matrix4();
    bySize.forEach(({ w, h, at }) => {
      const inst = new THREE.InstancedMesh(rbox(w, 0.11, h, 0.16, 0.03), [M.key, M.key], at.length);
      at.forEach(([x, zz], i) => inst.setMatrixAt(i, m4.makeTranslation(x, DECK_Y - 0.03, zz)));
      topCase.add(inst);
    });

    // trackpad (glass) + speaker grilles either side of the keyboard
    mesh(rbox(12.6, 0.02, 7.8, 0.45, 0.008), [M.pad, M.pad], 0, DECK_Y, 5.9, topCase);
    const grille = new THREE.MeshStandardMaterial({ color: 0x0b0c0e, alphaMap: grilleTex(), transparent: true, metalness: 0.3, roughness: 0.6 });
    disposables.push(grille);
    [-1, 1].forEach((s) => mesh(flat(rrShape(1.25, 11.2, 0.3), 1.25, 11.2), grille, s * 14.25, DECK_Y + 0.002, KB_Z, topCase));
  }

  // lid on its hinge at the back of the deck
  const lidPivot = new THREE.Group();
  lidPivot.position.set(0, DECK_Y, -D / 2 + 0.45);
  tub.add(lidPivot); // the lid stays with the body when the keyboard lifts
  const LD = D - 0.45;
  mesh(rbox(W, 0.34, LD, R, 0.08), [M.alu, M.alu], 0, 0.02, LD / 2, lidPivot);
  {
    const hinge = mesh(new THREE.CylinderGeometry(0.24, 0.24, W - 7, 24), M.aluDark, 0, 0, -0.05, lidPivot);
    hinge.rotation.z = Math.PI / 2;
    disposables.push(hinge.geometry);

    // display: glass over the whole inner face, the panel under it
    const glassGeo = new THREE.ShapeGeometry(rrShape(W - 0.25, LD - 0.25, R - 0.12), 10);
    glassGeo.rotateX(Math.PI / 2);
    disposables.push(glassGeo);
    mesh(glassGeo, M.glass, 0, 0.012, LD / 2, lidPivot);

    const wall = makeCanvas(1600, 1040);
    const screenTex = canvasTex(wall);
    {
      const x = wall.getContext("2d");
      const g = x.createRadialGradient(800, 560, 60, 800, 520, 1100);
      g.addColorStop(0, "#14315f");
      g.addColorStop(0.55, "#071024");
      g.addColorStop(1, "#020306");
      x.fillStyle = g;
      x.fillRect(0, 0, 1600, 1040);
      x.strokeStyle = "rgba(120,170,255,.16)";
      x.lineWidth = 1.5;
      for (let gx = 0; gx <= 1600; gx += 50) {
        x.beginPath();
        x.moveTo(gx, 0);
        x.lineTo(gx, 1040);
        x.stroke();
      }
      for (let gy = 0; gy <= 1040; gy += 50) {
        x.beginPath();
        x.moveTo(0, gy);
        x.lineTo(1600, gy);
        x.stroke();
      }
      screenTex.needsUpdate = true;
    }
    const screenGeo = new THREE.PlaneGeometry(W - 1.3, LD - 1.6);
    screenGeo.rotateX(Math.PI / 2);
    disposables.push(screenGeo);
    const screenMat = new THREE.MeshBasicMaterial({ map: screenTex, toneMapped: false });
    screenMat.color.setScalar(0.9);
    disposables.push(screenMat);
    mesh(screenGeo, screenMat, 0, 0.008, LD / 2 + 0.25, lidPivot);
    // camera in the top bezel
    const camDot = mesh(new THREE.CircleGeometry(0.12, 20), M.port, 0, 0.006, LD - 0.42, lidPivot);
    camDot.rotation.x = Math.PI / 2;
  }

  // ------------------------------------------------------------ package

  const S = 3.75; // substrate, cm
  const DIE = { w: 0.875, d: 1.22, z: -0.06 }; // measured on package-open
  const pkg = new THREE.Group(); // child of the board root: rides out with it
  pkg.position.set(CPU.x, 0.1 + 0.2, CPU.z);
  boardRoot.add(pkg);
  const lidGroup = new THREE.Group();
  const slabTop = new THREE.Group();
  const slabInner = new THREE.Group();
  const slabBottom = new THREE.Group();
  const dieGroup = new THREE.Group();
  const dieLayers = [0, 1, 2, 3].map(() => new THREE.Group());
  dieLayers.forEach((l) => dieGroup.add(l));
  pkg.add(slabBottom, slabInner, slabTop, dieGroup, lidGroup);

  // closed stack heights (local y, centred for the flip)
  const Y = { bottom: -0.2, inner: -0.14, top: -0.1, die: -0.04, lid: -0.04 };
  slabBottom.position.y = Y.bottom;
  slabInner.position.y = Y.inner;
  slabTop.position.y = Y.top;
  lidGroup.position.y = Y.lid;
  dieGroup.position.set(0, Y.die, DIE.z);
  dieLayers.forEach((l, i) => (l.position.y = 0.036 - i * 0.012));

  const anchors = {};
  function anchor(name, parent, x, y, z) {
    const a = new THREE.Object3D();
    a.position.set(x, y, z);
    parent.add(a);
    anchors[name] = a;
  }
  anchor("pkg-top", slabTop, -S / 2, 0.06, S / 2);
  anchor("pkg-inner", slabInner, -S / 2, 0.04, S / 2);
  anchor("pkg-bottom", slabBottom, -S / 2, 0, -S / 2); // front-left once flipped pads-up
  anchor("pkg-lid", lidGroup, -S * 0.43, 0.2, S * 0.43);
  dieLayers.forEach((l, i) => anchor("die-" + (i + 1), l, DIE.w / 2, 0.012, DIE.d / 2));

  function buildPackage(tex) {
    // substrate top: the photo with the die taken out (a dark cavity left)
    const img = tex.open.image;
    const c = makeCanvas(img.width, img.height);
    const x = c.getContext("2d");
    x.drawImage(img, 0, 0);
    const dw = (DIE.w / S) * c.width;
    const dh = (DIE.d / S) * c.height;
    const dx = c.width / 2 - dw / 2;
    const dy = c.height / 2 + (DIE.z / S) * c.height - dh / 2;
    x.fillStyle = "#0a0b0c";
    x.beginPath();
    x.roundRect(dx - 8, dy - 8, dw + 16, dh + 16, 18);
    x.fill();
    x.strokeStyle = "rgba(200,170,90,.55)";
    x.lineWidth = 4;
    x.strokeRect(dx + 10, dy + 10, dw - 20, dh - 20);
    const openTex = canvasTex(c);

    const photo = (map, extra = {}) => {
      const m = new THREE.MeshStandardMaterial({ map, roughness: 0.5, metalness: 0.15, ...extra });
      disposables.push(m);
      return m;
    };

    mesh(rbox(S, 0.06, S, 0.12, 0.012), [photo(openTex), M.substrateEdge], 0, 0, 0, slabTop);
    const routing = pcb({ w: 1024, h: 1024, seed: 21, base: ["#14382a", "#0b2219"], trace: "rgba(222,168,86,.85)", pad: "#e0b864", buses: 40, singles: 120, silk: false });
    mesh(rbox(S, 0.04, S, 0.12, 0.01), [photo(routing.map, { metalness: 0.35, roughness: 0.4 }), M.substrateEdge], 0, 0, 0, slabInner);
    mesh(rbox(S, 0.06, S, 0.12, 0.012), [photo(tex.bottom, { metalness: 0.45, roughness: 0.38 }), M.substrateEdge], 0, 0, 0, slabBottom);

    // integrated heat spreader: photo top (cropped to the lid), steel rest
    const L = S * 0.865;
    const lidTop = photo(tex.top, { metalness: 0.75, roughness: 0.3 });
    mesh(rbox(L, 0.22, L, 0.22, 0.04, { x: 0.068, y: 0.05, w: 0.865, h: 0.865 }), [lidTop, M.nickel], 0, 0.04, 0, lidGroup);
    mesh(rbox(S * 0.9, 0.05, S * 0.92, 0.18, 0.015), [M.nickel, M.nickel], 0, 0.0, 0, lidGroup);

    // die: four layers, top metal first
    tex.die.forEach((t, i) => mesh(rbox(DIE.w, 0.012, DIE.d, 0.015, 0.003), [photo(t, { metalness: 0.45, roughness: 0.3 }), M.dieEdge], 0, 0, 0, dieLayers[i]));

  }

  // ------------------------------------------------------------ timeline

  const fades = {
    top: fader(topCase),
    cooler: fader(cooler),
    tub: fader(tub),
    deck: fader(boardDeck),
  };
  // the fader cloned the materials — keep the live ones for the effects
  const boardGlowMat = boardDeck.children[0].material[0];
  const backlightMat = topCase.children[2].material;
  const glow = { v: 0, kb: 0 };

  const BOARD_OUT = new THREE.Vector3(0, 3, 3.4); // where the board comes to rest
  const P0 = new THREE.Vector3(0, 9.6, 2.8); // where the package comes to rest (world)
  const P0local = P0.clone().sub(BOARD_OUT);
  const CPU_OUT = new THREE.Vector3(BOARD_OUT.x + CPU.x, BOARD_OUT.y + 0.4, BOARD_OUT.z + CPU.z);

  // Camera: one continuous spline through the key shots, driven by the
  // timeline time — no stop-and-go between the stages. [t, position, target]
  const CAM = [
    [0.0, [33, 24, 51], [-7, 1.6, 0.6]],
    [1.0, [33, 30, 62], [-7, 6, -1]],
    [2.2, [26, 36, 60], [-5.4, 5.6, -1]],
    [3.2, [15, 27, 41], [-2, 2.8, 0]],
    [4.1, [8, 21, 31], [BOARD_OUT.x - 1, BOARD_OUT.y, BOARD_OUT.z]],
    [4.8, [3, 12.5, 13.5], [CPU_OUT.x, CPU_OUT.y, CPU_OUT.z]],
    [5.7, [1.5, 15, 15], [-0.9, 6.8, 2.8]],
    [6.5, [0, 16.4, 16], [P0.x - 0.3, P0.y, P0.z]],
    [7.9, [0, 19, 19.5], [P0.x, P0.y + 0.3, P0.z]],
    [9.3, [0, P0.y + 13.8, P0.z + 22], [P0.x, P0.y - 0.5, P0.z]],
    [11, [0, P0.y + 14, P0.z + 22.4], [P0.x, P0.y - 0.5, P0.z]],
  ];
  const camPosCurve = new THREE.CatmullRomCurve3(CAM.map((k) => new THREE.Vector3(...k[1])), false, "centripetal");
  const camTgtCurve = new THREE.CatmullRomCurve3(CAM.map((k) => new THREE.Vector3(...k[2])), false, "centripetal");
  const camPos = new THREE.Vector3();
  const camTgt = new THREE.Vector3();
  const camDir = new THREE.Vector3();
  let camFit = 1; // narrower screens: the camera steps back

  function cameraAt(t, fit) {
    let i = 0;
    while (i < CAM.length - 2 && t > CAM[i + 1][0]) i++;
    const f = THREE.MathUtils.clamp((t - CAM[i][0]) / (CAM[i + 1][0] - CAM[i][0]), 0, 1);
    const u = (i + f) / (CAM.length - 1);
    camPosCurve.getPoint(u, camPos);
    camTgtCurve.getPoint(u, camTgt);
    camDir.subVectors(camPos, camTgt).multiplyScalar(fit);
    camPos.copy(camTgt).add(camDir);
  }

  function fadeTo(tl, name, v, at, dur = 0.3) {
    const f = fades[name];
    tl.to(f, { v, duration: dur, ease: "none", onUpdate: f.apply }, at);
  }

  const tl = gsap.timeline({ paused: true, defaults: { ease: "power2.inOut" } });
  lidPivot.rotation.x = -0.24; // a little open

  // 0 → 1.1: the lid opens, the keyboard lights up
  tl.to(lidPivot.rotation, { x: -1.9, duration: 1.1 }, 0).to(glow, { kb: 1, duration: 0.8 }, 0.1);

  // 1.0 → 2.3: the keyboard (top case) lifts off; body and screen stay
  // (lifts a little, then tips up on its back edge — the whole inside shows)
  tl.to(deckHinge.position, { y: TUB_H + 1.4, duration: 0.6, ease: "power2.out" }, 1.0)
    .to(deckHinge.rotation, { x: -1.22, duration: 1.1, ease: "power3.inOut" }, 1.25)
    .to(glow, { kb: 0, duration: 0.6 }, 1.6);

  // 2.0 → 2.9: the cooler comes off the CPU
  tl.to(cooler.position, { y: "+=3.2", duration: 0.9, ease: "power3.inOut" }, 2.0);

  // 2.6 → 4.1: the board rises out of the body and slides out to the camera;
  // the rest leaves the frame (up / down), fades only once off screen
  tl.to(boardRoot.position, { y: BOARD_Y + 2.2, duration: 0.6, ease: "power2.out" }, 2.6)
    .to(boardRoot.position, { x: BOARD_OUT.x, y: BOARD_OUT.y, z: BOARD_OUT.z, duration: 1.1, ease: "power3.inOut" }, 3.0)
    .to(deckHinge.position, { y: 40, duration: 1.1, ease: "power2.in" }, 2.95)
    .to(cooler.position, { y: 30, duration: 1.0, ease: "power2.in" }, 3.0)
    .to(tub.position, { y: -46, duration: 1.15, ease: "power2.in" }, 2.95)
    .to(glow, { v: 1.6, duration: 0.9, ease: "power1.inOut" }, 3.5);
  fadeTo(tl, "top", 0, 3.85);
  fadeTo(tl, "cooler", 0, 3.75);
  fadeTo(tl, "tub", 0, 4.1, 0.2);

  // 4.7 → 6.5: the package lifts off and flips (pads up), then back;
  // the board deck drops away under it
  tl.to(pkg.position, { x: P0local.x, y: P0local.y, z: P0local.z, duration: 1.8, ease: "power3.inOut" }, 4.7)
    .to(pkg.rotation, { x: -Math.PI, duration: 0.8 }, 4.9)
    .to(pkg.rotation, { x: -Math.PI * 2, duration: 0.8 }, 5.8)
    .to(boardDeck.position, { y: -12, duration: 1.5, ease: "power2.in" }, 5.0)
    .to(glow, { v: 0, duration: 0.8 }, 5.0);
  fadeTo(tl, "deck", 0, 6.15);

  // 6.6 → 7.8: the IHS comes off, the die rises out of its cavity
  tl.to(lidGroup.position, { y: 1.9, duration: 0.8 }, 6.6).to(dieGroup.position, { y: 1.0, duration: 0.8 }, 7.1);

  // 7.9 → 9.5: package layers to the left (lid hangs below), die to the right
  const L = -2.1;
  const RX = 2.05;
  tl.to(slabTop.position, { x: L, y: 1.5, duration: 1.3 }, 7.9)
    .to(slabInner.position, { x: L, y: -0.05, duration: 1.3 }, 7.95)
    .to(slabBottom.position, { x: L, y: -1.6, duration: 1.3 }, 8.0)
    .to(slabBottom.rotation, { x: Math.PI, duration: 1.2 }, 8.05)
    .to(lidGroup.position, { x: L, y: -3.2, duration: 1.4 }, 7.9)
    .to(dieGroup.position, { x: RX, y: 0, z: 0, duration: 1.3 }, 8.0)
    .to(dieGroup.scale, { x: 2.2, y: 2.2, z: 2.2, duration: 1.3 }, 8.0);
  dieLayers.forEach((layer, i) => tl.to(layer.position, { y: 1.3 - i * 0.87, duration: 1.2 }, 8.6 + i * 0.07));

  // → 11: hold with the services on screen
  tl.to({}, { duration: 1 }, 10);

  // the services show from these times on (package side / die side)
  const T = { pkgLabels: 9.2, dieLabels: 9.6 };

  // ------------------------------------------------------------ services

  const fallback = ["die-1", "pkg-top", "die-4", "pkg-bottom", "die-2", "pkg-inner", "die-3", "pkg-lid"];
  const leads = items.map((item, i) => {
    const line = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
    line.setAttribute("class", "home-solutions__lead");
    line.setAttribute("vector-effect", "non-scaling-stroke");
    svg.appendChild(line);
    const name = anchors[item.dataset.layer] ? item.dataset.layer : fallback[i % fallback.length];
    return { item, line, name, side: item.classList.contains("home-solutions__item--left") ? "left" : "right", group: name.indexOf("pkg") === 0 ? "pkg" : "die" };
  });

  // cached per resize: stage box, the offset of the services box inside the
  // canvas, the free band for the blocks
  const box = { w: 1, h: 1, ox: 0, oy: 0, minTop: 0, minLeft: 0, zoneC: 0, fit: 1 };
  const FINAL_W = 7.4; // width of the final composition, world units (+ air)
  const FINAL_DIST = 26.4; // camera distance of the last key shot

  function measure() {
    const cr = canvas.getBoundingClientRect();
    const ir = inner.getBoundingClientRect();
    box.ox = ir.left - cr.left;
    box.oy = ir.top - cr.top;
    const header = document.querySelector(".js-header");
    const headerB = header ? header.getBoundingClientRect().bottom - (canvas.getBoundingClientRect().top + box.oy) : 0;
    box.minTop = Math.max(headerB, 0) + 24;
    box.minLeft = Math.max(box.minTop, title.offsetTop + title.offsetHeight + 24);
    box.ih = inner.clientHeight;
    leads.forEach((l) => {
      l.item.classList.remove("is-compact");
      l.item.style.top = "0px";
    });
    // a side that can't hold its blocks with their art drops the art
    ["left", "right"].forEach((side) => {
      const list = leads.filter((l) => l.side === side);
      const need = list.reduce((s, l) => s + l.item.offsetHeight + 24, 0);
      const room = box.ih - (side === "left" ? box.minLeft : box.minTop) - 24;
      if (need > room) list.forEach((l) => l.item.classList.add("is-compact"));
    });
    leads.forEach((l) => {
      const num = l.item.querySelector(".home-solutions__num");
      l.h = l.item.offsetHeight;
      l.left = l.item.offsetLeft;
      l.w = l.item.offsetWidth;
      l.numX = num.offsetLeft;
      l.numY = num.offsetTop + num.offsetHeight / 2;
      l.numW = num.offsetWidth;
    });
    // the free band between the two columns of services (canvas px): the
    // final composition is centred in it and the camera steps back to fit
    const lefts = leads.filter((l) => l.side === "left").map((l) => box.ox + l.left + l.w);
    const rights = leads.filter((l) => l.side === "right").map((l) => box.ox + l.left);
    const z0 = lefts.length ? Math.max(...lefts) : 0;
    const z1 = rights.length ? Math.min(...rights) : box.w;
    box.zoneC = (z0 + z1) / 2;
    const room = Math.max(z1 - z0 - 80, 200);
    const need = (FINAL_W * box.h) / (2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * room);
    box.fit = Math.max(camFit, need / FINAL_DIST);
  }

  const v3 = new THREE.Vector3();

  // blocks follow their layers: number at the layer's height, kept apart
  // and inside the screen; leads from the number to the layer corner — along
  // the number's row, the step outside the text block, then to the layer
  function layoutServices() {
    leads.forEach((l) => {
      anchors[l.name].getWorldPosition(v3).project(camera);
      l.ax = (v3.x * 0.5 + 0.5) * box.w - box.ox;
      l.ay = (-v3.y * 0.5 + 0.5) * box.h - box.oy;
      l.top = l.ay - l.numY;
    });
    ["left", "right"].forEach((side) => {
      const list = leads.filter((l) => l.side === side).sort((a, b) => a.top - b.top);
      const min = side === "left" ? box.minLeft : box.minTop;
      const max = box.ih - 24;
      list.forEach((l, i) => {
        l.top = Math.max(l.top, i ? list[i - 1].top + list[i - 1].h + 24 : min);
      });
      // pushed off the bottom: shift the stack up as far as the top allows
      for (let i = list.length - 1; i >= 0; i--) {
        const limit = i === list.length - 1 ? max - list[i].h : list[i + 1].top - 24 - list[i].h;
        list[i].top = Math.max(min, Math.min(list[i].top, limit));
      }
    });
    leads.forEach((l) => {
      l.item.style.top = l.top + "px";
      const nx = l.left + l.numX;
      const y0 = l.top + l.numY;
      const toRight = l.ax > nx + l.numW / 2;
      const x0 = toRight ? nx + l.numW : nx;
      // the step goes past the text (close to the layer if the gap is tight)
      let xs = toRight ? Math.min(l.left + l.w + 16, l.ax - 6) : Math.max(l.left - 16, l.ax + 6);
      if (toRight ? xs < l.left + l.w : xs > l.left) xs = (x0 + l.ax) / 2;
      l.line.setAttribute("points", `${x0},${y0} ${xs},${y0} ${xs},${l.ay} ${l.ax},${l.ay}`);
    });
  }

  let shown = null;
  function setStates(t) {
    const state = t >= T.dieLabels ? 2 : t >= T.pkgLabels ? 1 : 0;
    if (state === shown) return;
    shown = state;
    leads.forEach((l) => {
      const on = l.group === "pkg" ? state >= 1 : state >= 2;
      l.item.classList.toggle("is-active", on);
      l.line.classList.toggle("is-active", on);
    });
  }

  // ------------------------------------------------------------ loop

  const pointer = { x: 0, y: 0, sx: 0, sy: 0 };
  const onPointer = (e) => {
    pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (e.clientY / window.innerHeight) * 2 - 1;
  };
  window.addEventListener("pointermove", onPointer, { passive: true });

  function resize() {
    box.w = host.clientWidth;
    box.h = host.clientHeight;
    renderer.setSize(box.w, box.h, false);
    camera.aspect = box.w / box.h;
    camera.updateProjectionMatrix();
    camFit = Math.max(1, 1.75 / camera.aspect);
    measure();
    camera.updateProjectionMatrix();
  }

  let visible = false;
  let last = performance.now();
  let elapsed = 0;

  function render(now) {
    const dt = Math.min((now - last) / 1000, 0.05);
    last = now;
    if (!visible) return;
    elapsed += dt;

    pointer.sx += (pointer.x - pointer.sx) * Math.min(1, dt * 3);
    pointer.sy += (pointer.y - pointer.sy) * Math.min(1, dt * 3);
    const t = tl.time();
    const k = THREE.MathUtils.smoothstep(t, 7.4, 9.2); // into the final shot
    cameraAt(t, THREE.MathUtils.lerp(camFit, box.fit, k));
    camera.setViewOffset(box.w, box.h, -(box.zoneC - box.w / 2) * k, 0, box.w, box.h);
    camera.position.set(camPos.x + pointer.sx * 1.2, camPos.y - pointer.sy * 0.8, camPos.z);
    camera.lookAt(camTgt);

    fans.forEach((f) => (f.userData.blades.rotation.y -= dt * 3));
    boardGlowMat.emissiveIntensity = glow.v * (0.85 + 0.15 * Math.sin(elapsed * 2.2));
    backlightMat.opacity = glow.kb * 0.22 * fades.top.v;

    renderer.render(scene, camera);
    setStates(t);
    if (shown) layoutServices();
  }

  // ------------------------------------------------------------ start

  const io = new IntersectionObserver(([entry]) => {
    visible = entry.isIntersecting;
  });
  io.observe(section);
  window.addEventListener("resize", resize);
  resize();

  const st = ScrollTrigger.create({
    trigger: section,
    start: "top top",
    end: "bottom bottom",
    scrub: debugProgress !== null ? true : 1.2,
    animation: tl,
    onRefresh: resize,
  });

  let alive = true;
  const loader = new THREE.TextureLoader();
  const load = (name) =>
    loader.loadAsync(imgBase + IMG + name).then((t) => {
      t.colorSpace = THREE.SRGBColorSpace;
      t.anisotropy = maxAniso;
      disposables.push(t);
      return t;
    });
  Promise.all([load("package-top.webp"), load("package-open.webp"), load("package-bottom.webp"), ...[1, 2, 3, 4].map((i) => load(`die-${i}.webp`))]).then(([top, open, bottom, ...die]) => {
    if (!alive) return;
    buildPackage({ top, open, bottom, die });
    section.classList.add("is-ready");
    renderer.setAnimationLoop(render);
  });

  return function destroy() {
    alive = false;
    renderer.setAnimationLoop(null);
    st.kill();
    tl.kill();
    io.disconnect();
    window.removeEventListener("resize", resize);
    window.removeEventListener("pointermove", onPointer);
    leads.forEach((l) => {
      l.line.remove();
      l.item.classList.remove("is-active", "is-compact");
      l.item.style.top = "";
    });
    section.classList.remove("is-ready");
    disposables.forEach((d) => d.dispose());
    envRT.dispose();
    renderer.dispose();
    renderer.forceContextLoss();
    canvas.remove();
  };
}
