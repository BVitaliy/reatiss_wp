/* global google */
// Contacts map (template-parts/contacts-map.php | contacts-map-image.php).
// Both variants: the pin toggles the office card, × closes it.
// Live variant (.js-map-canvas + data-api-key): the Maps script loads only
// when the block nears the viewport; the marker markup is moved onto the map
// with an OverlayView (HTML pin + card, styled in CSS — no Marker API) and
// the view is framed like the mockup (pin left of centre, card to the right;
// on phones the card hangs under the pin). Style: Snazzy Maps "Muted Blue".
(function () {
  "use strict";

  var roots = document.querySelectorAll(".js-map");
  if (!roots.length) return;

  var STYLE = [
    { featureType: "all", stylers: [{ saturation: 0 }, { hue: "#e7ecf0" }] },
    { featureType: "road", stylers: [{ saturation: -70 }] },
    { featureType: "transit", stylers: [{ visibility: "off" }] },
    { featureType: "poi", stylers: [{ visibility: "off" }] },
    { featureType: "water", stylers: [{ visibility: "simplified" }, { saturation: -60 }] },
  ];
  var mobile = window.matchMedia("(max-width: 767px)");
  var loading = null;

  function loadMaps(key) {
    if (window.google && google.maps && google.maps.Map) return Promise.resolve();
    if (loading) return loading;
    loading = new Promise(function (resolve, reject) {
      window.__reatissMapsReady = resolve;
      var s = document.createElement("script");
      s.src = "https://maps.googleapis.com/maps/api/js?key=" + encodeURIComponent(key) + "&callback=__reatissMapsReady&loading=async&language=en";
      s.async = true;
      s.onerror = reject;
      document.head.appendChild(s);
    });
    return loading;
  }

  roots.forEach(function (root) {
    var marker = root.querySelector(".js-map-marker");
    var pin = root.querySelector(".js-map-pin");
    var close = root.querySelector(".js-map-close");
    var canvas = root.querySelector(".js-map-canvas");
    if (!marker || !pin) return;

    function setOpen(open) {
      root.classList.toggle("is-card-closed", !open);
      pin.setAttribute("aria-expanded", open ? "true" : "false");
    }
    pin.addEventListener("click", function () {
      setOpen(root.classList.contains("is-card-closed"));
    });
    if (close) close.addEventListener("click", function () {
      setOpen(false);
    });
    root.addEventListener("keydown", function (e) {
      if (e.key === "Escape") setOpen(false);
    });

    var key = root.getAttribute("data-api-key");
    if (!canvas || !key) return; // picture variant

    function start() {
      loadMaps(key)
        .then(function () {
          init(root, canvas, marker);
        })
        .catch(function () {}); // no map → marker stays centred on the grey frame
    }

    if ("IntersectionObserver" in window) {
      var io = new IntersectionObserver(
        function (entries) {
          if (!entries[0].isIntersecting) return;
          io.disconnect();
          start();
        },
        { rootMargin: "600px 0px" }
      );
      io.observe(root);
    } else {
      start();
    }
  });

  function init(root, canvas, marker) {
    var g = google.maps;
    var pos = { lat: parseFloat(root.getAttribute("data-lat")), lng: parseFloat(root.getAttribute("data-lng")) };

    var map = new g.Map(canvas, {
      center: pos,
      zoom: parseInt(root.getAttribute("data-zoom"), 10) || 15,
      styles: STYLE,
      disableDefaultUI: true,
      zoomControl: true,
      clickableIcons: false,
      keyboardShortcuts: false,
      gestureHandling: "cooperative", // page scroll isn't hijacked
    });

    function Overlay() {}
    Overlay.prototype = new g.OverlayView();
    Overlay.prototype.onAdd = function () {
      g.OverlayView.preventMapHitsAndGesturesFrom(marker);
      marker.classList.add("is-on-map");
      this.getPanes().floatPane.appendChild(marker);
    };
    Overlay.prototype.draw = function () {
      var p = this.getProjection().fromLatLngToDivPixel(new g.LatLng(pos));
      if (!p) return;
      marker.style.left = p.x + "px";
      marker.style.top = p.y + "px";
    };
    Overlay.prototype.onRemove = function () {};
    new Overlay().setMap(map);

    // Figma @1920: tip 111 left of / 25 below the centre (pin + card centred);
    // phones: card under the tip → tip 80 above the centre
    function frame() {
      var k = parseFloat(getComputedStyle(document.documentElement).fontSize) / 16;
      map.setCenter(pos);
      if (mobile.matches) map.panBy(0, 80);
      else map.panBy(Math.round(111 * k), Math.round(-25 * k));
    }
    g.event.addListenerOnce(map, "idle", frame);

    var lastW = window.innerWidth;
    var timer = null;
    window.addEventListener("resize", function () {
      if (window.innerWidth === lastW) return;
      lastW = window.innerWidth;
      clearTimeout(timer);
      timer = setTimeout(frame, 200);
    });

    // invalid / restricted key: Google swaps the map for an error box —
    // drop it and put the marker back in the centre of the frame
    window.gm_authFailure = function () {
      marker.classList.remove("is-on-map");
      marker.style.left = marker.style.top = "";
      root.appendChild(marker);
      canvas.remove();
    };
  }
})();
