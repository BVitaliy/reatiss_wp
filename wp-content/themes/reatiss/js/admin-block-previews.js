/**
 * Builder “Add block” menu: a screenshot of the hovered (or focused) block
 * appears to the left of the menu. ACF appends the menu (.acf-fc-popup) to
 * <body> each time it opens, so the listeners are delegated.
 */
(function () {
  'use strict';

  var previews = window.rtsBlockPreviews || {};
  var WIDTH = 420;
  var box = null;

  function getBox() {
    if (box) return box;

    box = document.createElement('div');
    box.setAttribute('aria-hidden', 'true');
    box.style.cssText = [
      'position:absolute',
      'z-index:100000',
      'width:' + WIDTH + 'px',
      'padding:6px',
      'background:#fff',
      'border-radius:8px',
      'box-shadow:0 8px 30px rgba(0,0,0,.25)',
      'pointer-events:none',
      'display:none',
    ].join(';');

    var img = document.createElement('img');
    img.alt = '';
    img.style.cssText = 'display:block;width:100%;height:auto;max-height:360px;object-fit:cover;object-position:top;border-radius:4px';
    box.appendChild(img);

    document.body.appendChild(box);
    return box;
  }

  function show(link) {
    var src = previews[link.getAttribute('data-layout')];
    var popup = link.closest('.acf-fc-popup');

    if (!src || !popup) {
      hide();
      return;
    }

    var el = getBox();
    var img = el.firstChild;

    if (img.getAttribute('src') !== src) img.setAttribute('src', src);

    var popupRect = popup.getBoundingClientRect();
    var linkRect = link.getBoundingClientRect();
    var left = popupRect.left - WIDTH - 12;

    // Not enough room on the left — show it on the right of the menu.
    if (left < 8) left = popupRect.right + 12;

    el.style.left = left + window.scrollX + 'px';
    el.style.top = Math.max(8, linkRect.top - 40) + window.scrollY + 'px';
    el.style.display = 'block';
  }

  function hide() {
    if (box) box.style.display = 'none';
  }

  ['mouseover', 'focusin'].forEach(function (type) {
    document.addEventListener(type, function (event) {
      var link = event.target.closest && event.target.closest('.acf-fc-popup [data-layout]');
      if (link) show(link);
    });
  });

  ['mouseout', 'focusout'].forEach(function (type) {
    document.addEventListener(type, function (event) {
      var link = event.target.closest && event.target.closest('.acf-fc-popup [data-layout]');
      var next = event.relatedTarget && event.relatedTarget.closest && event.relatedTarget.closest('.acf-fc-popup [data-layout]');
      if (link && !next) hide();
    });
  });

  // The menu closes on pick, Esc or a click outside.
  document.addEventListener('click', hide);
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') hide();
  });
})();
