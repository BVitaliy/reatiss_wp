/* global $, ScrollTrigger */
// Scroll lock + modals + corner toast.
// Lock (window.ReatissScrollLock) is shared with the header search and the
// mobile menu; owners are ref-counted so closing one never unlocks another.
// Pattern from Ajour: stop Lenis, drop the page bar and give its width back as
// padding (no layout shift), block wheel / touch / scroll keys outside inner
// scroll areas. Phones without a classic bar pin <body> instead.
$(function () {
  var $html = $("html");
  var $body = $("body");

  // ── Scroll lock ──────────────────────────────────────────────────
  var owners = Object.create(null);
  var locked = false;
  var lockedY = 0;

  var SCROLL_KEYS = { ArrowUp: 1, ArrowDown: 1, PageUp: 1, PageDown: 1, Home: 1, End: 1, " ": 1 };
  // fixed full-width elements that must get the scrollbar compensation too
  var COMP_TARGETS = ".js-header";

  function scrollableAncestor(el) {
    var node = el;
    while (node && node !== document.body && node !== document.documentElement) {
      if (node instanceof HTMLElement) {
        var oy = getComputedStyle(node).overflowY;
        if ((oy === "auto" || oy === "scroll") && node.scrollHeight > node.clientHeight + 1) return node;
      }
      node = node.parentElement;
    }
    return null;
  }

  function onWheel(e) {
    var box = scrollableAncestor(e.target);
    if (box) {
      var atTop = box.scrollTop <= 0;
      var atBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 1;
      if ((e.deltaY < 0 && atTop) || (e.deltaY > 0 && atBottom)) e.preventDefault();
      return;
    }
    e.preventDefault();
  }

  function onTouchMove(e) {
    if (scrollableAncestor(e.target)) return;
    if (e.cancelable) e.preventDefault();
  }

  function onKeydown(e) {
    if (!SCROLL_KEYS[e.key]) return;
    if (e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"]')) return;
    e.preventDefault();
  }

  // While <body> is pinned (touch lock) window.scrollY reads 0 — scrubbed
  // ScrollTriggers (hero parallax…) would chase progress 0 and ease back on
  // unlock. Freeze them in place for the lock, snap them on release (Ajour).
  function freezeScrollTriggers() {
    if (typeof ScrollTrigger === "undefined") return;
    ScrollTrigger.getAll().forEach(function (st) {
      st.disable(false); // false = keep current values
    });
  }

  function thawScrollTriggers() {
    if (typeof ScrollTrigger === "undefined") return;
    ScrollTrigger.getAll().forEach(function (st) {
      st.enable();
    });
    ScrollTrigger.update();
    ScrollTrigger.getAll().forEach(function (st) {
      var scrub = st.getTween && st.getTween();
      if (scrub) scrub.progress(1);
    });
  }

  function applyLock() {
    if (locked) return;
    locked = true;
    lockedY = Math.round((window.lenis ? window.lenis.scroll : window.scrollY) || 0);
    if (window.lenis) window.lenis.stop();

    var sbw = Math.max(0, window.innerWidth - document.documentElement.clientWidth);
    $html.addClass("is-scroll-locked");
    $body.addClass("is-scroll-locked");

    if (sbw > 0) {
      document.body.style.paddingRight = sbw + "px";
      $(COMP_TARGETS).css("padding-right", sbw + "px");
    } else if (window.matchMedia("(hover: none), (pointer: coarse)").matches) {
      // iOS ignores overflow on body — pin it at the current offset
      freezeScrollTriggers();
      document.body.style.top = -lockedY + "px";
      $body.addClass("is-scroll-pinned");
    }

    window.addEventListener("wheel", onWheel, { passive: false });
    window.addEventListener("touchmove", onTouchMove, { passive: false });
    window.addEventListener("keydown", onKeydown);
  }

  function releaseLock() {
    if (!locked) return;
    locked = false;
    window.removeEventListener("wheel", onWheel);
    window.removeEventListener("touchmove", onTouchMove);
    window.removeEventListener("keydown", onKeydown);

    var pinned = $body.hasClass("is-scroll-pinned");
    document.body.style.paddingRight = "";
    document.body.style.top = "";
    $(COMP_TARGETS).css("padding-right", "");
    $html.removeClass("is-scroll-locked");
    $body.removeClass("is-scroll-locked is-scroll-pinned");

    if (pinned) {
      // the page was one screen tall while <body> was pinned — Lenis measured
      // that (its resize is debounced) and would clamp the restore to 0:
      // re-measure first, then put the page back where it was
      window.scrollTo(0, lockedY);
      if (window.lenis) {
        window.lenis.resize();
        window.lenis.start();
        window.lenis.scrollTo(lockedY, { immediate: true, force: true });
      }
    } else if (window.lenis) {
      window.lenis.start();
    }
    if (pinned) requestAnimationFrame(thawScrollTriggers);
  }

  window.ReatissScrollLock = {
    lock: function (owner) {
      owners[owner] = true;
      applyLock();
    },
    unlock: function (owner) {
      delete owners[owner];
      if (!Object.keys(owners).length) releaseLock();
    },
  };

  // ── Modals ───────────────────────────────────────────────────────
  var $modal = $(".js-modal");
  var closeTimer = null;
  var lastFocus = null;

  function openModal(name) {
    var $panel = $modal.find('.modal__panel[data-modal="' + name + '"]');
    if (!$panel.length) return;

    clearTimeout(closeTimer);
    // header search / mobile menu give way to the modal
    $(document).trigger("reatiss:overlay-open");
    // already open (a form → "thanks"): swap the panel, keep the first focus
    var swap = $modal.hasClass("is-open");
    if (!swap) lastFocus = document.activeElement;

    $modal.find(".modal__panel").removeClass("is-active is-swapped");
    $panel.addClass("is-active").toggleClass("is-swapped", swap);
    // next frame so the enter transition runs after display: block
    requestAnimationFrame(function () {
      $modal.addClass("is-open").attr("aria-hidden", "false");
    });
    window.ReatissScrollLock.lock("modal");
    $panel.find(".modal__close").trigger("focus");
  }

  function closeModal() {
    if (!$modal.hasClass("is-open")) return;
    $modal.removeClass("is-open").attr("aria-hidden", "true");
    clearTimeout(closeTimer);
    // unlock after the leave transition — restoring the bar earlier would
    // shift the still-visible panel sideways
    closeTimer = setTimeout(function () {
      $modal.find(".modal__panel").removeClass("is-active");
      window.ReatissScrollLock.unlock("modal");
      if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
      $(document).trigger("reatiss:modal-closed");
    }, 450);
  }

  window.ReatissModal = { open: openModal, close: closeModal };

  $(document).on("click", "[data-modal-open]", function (e) {
    e.preventDefault();
    hideToast();
    openModal($(this).data("modal-open"));
  });

  $(document).on("click", ".js-modal-close", function (e) {
    e.preventDefault();
    closeModal();
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") closeModal();
  });

  // ── Corner toast ─────────────────────────────────────────────────
  // Shown once per session after data-delay ms.
  var $toast = $(".js-toast");
  var TOAST_KEY = "reatiss-toast-closed";

  function hideToast() {
    if (!$toast.hasClass("is-visible")) return;
    $toast.removeClass("is-visible");
    try {
      sessionStorage.setItem(TOAST_KEY, "1");
    } catch (err) {}
  }

  if ($toast.length) {
    var dismissed = false;
    try {
      dismissed = sessionStorage.getItem(TOAST_KEY) === "1";
    } catch (err) {}

    if (!dismissed) {
      setTimeout(function showToast() {
        // a popup is open (the promo one too): come in a while after it closes
        if ($modal.hasClass("is-open")) {
          $(document).one("reatiss:modal-closed", function () {
            setTimeout(showToast, 4000);
          });
          return;
        }
        // the picture is fetched only now (data-srcset / data-src in the
        // markup), and the toast slides in once it is decoded
        $toast.find("[data-srcset]").each(function () {
          this.srcset = this.getAttribute("data-srcset");
        });
        var img = $toast.find("img[data-src]").get(0);
        var show = function () {
          if (!$modal.hasClass("is-open")) $toast.addClass("is-visible");
        };
        if (!img) return show();
        img.src = img.getAttribute("data-src");
        if (img.decode) img.decode().then(show, show);
        else show();
      }, parseInt($toast.data("delay"), 10) || 6000);
    }

    $toast.on("click", ".js-toast-close", hideToast);
    // followed a link → don't show it again on the next page
    $toast.on("click", "a", hideToast);
  }

  // ── Promo popup ──────────────────────────────────────────────────
  // A panel with data-auto-open="ms" opens by itself once per session, unless
  // another popup / the search / the mobile menu holds the screen then (it
  // waits for that to close).
  $modal.find(".modal__panel[data-auto-open]").each(function () {
    var name = $(this).data("modal");
    var key = "reatiss-promo-" + name;
    try {
      if (sessionStorage.getItem(key) === "1") return;
    } catch (err) {}

    setTimeout(function tryOpen() {
      if ($modal.hasClass("is-open") || $html.hasClass("is-scroll-locked")) {
        setTimeout(tryOpen, 3000);
        return;
      }
      try {
        sessionStorage.setItem(key, "1");
      } catch (err) {}
      openModal(name);
    }, parseInt($(this).data("auto-open"), 10) || 8000);
  });
});
