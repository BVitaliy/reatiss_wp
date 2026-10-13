/* global $ */
// FAQ accordion — one item open at a time; height animates in CSS
// (grid-template-rows 0fr → 1fr, 0.5s), JS only toggles the class.
// When the item that closes is above the clicked one, its answer folding up
// would pull the page (with the clicked question) up — on phones straight
// into the next block. While the heights animate, the page scroll follows so
// the clicked question stays where it was on screen.
$(function () {
  var DURATION = 600; // the CSS transition + a frame or two

  function holdInPlace(el) {
    var top = el.getBoundingClientRect().top;
    var end = performance.now() + DURATION;
    (function step() {
      var shift = el.getBoundingClientRect().top - top;
      if (Math.abs(shift) > 0.5) {
        if (window.lenis) window.lenis.scrollTo(window.scrollY + shift, { immediate: true, force: true });
        else window.scrollBy(0, shift);
      }
      if (performance.now() < end) requestAnimationFrame(step);
    })();
  }

  $(document).on("click", ".js-faq-head", function () {
    var head = this;
    var $item = $(head).closest(".faq__item");
    var open = !$item.hasClass("is-open");
    var $closing = $item.siblings(".faq__item.is-open");

    // an open item above this one is about to fold up
    var above = $closing.length && $closing[0].compareDocumentPosition($item[0]) & Node.DOCUMENT_POSITION_FOLLOWING;

    $closing.removeClass("is-open").find(".js-faq-head").attr("aria-expanded", "false");
    $item.toggleClass("is-open", open);
    $(head).attr("aria-expanded", open ? "true" : "false");

    if (above) holdInPlace(head);
  });
});
