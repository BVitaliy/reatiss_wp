/* global $, Swiper */
// Sliders.
// .js-grid-slider — a card grid on desktop that becomes a Swiper below
// data-slider-below (px, default 1200). Swiper is created/destroyed on the
// breakpoint, so resizing either way never leaves a half-initialised slider.
// Markup: <div class="grid-slider swiper js-grid-slider"><div class="swiper-wrapper">
//           <div class="swiper-slide">card</div>…</div><div class="grid-slider__pagination"></div></div>
$(function () {
  if (typeof Swiper === "undefined") return;

  $(".js-grid-slider").each(function () {
    var el = this;
    var below = parseInt(el.getAttribute("data-slider-below"), 10) || 1200;
    var mq = window.matchMedia("(max-width: " + (below - 0.02) + "px)");
    var swiper = null;

    function toggle() {
      if (mq.matches && !swiper) {
        swiper = new Swiper(el, {
          slidesPerView: 1.12,
          spaceBetween: 10,
          speed: 600,
          grabCursor: true,
          watchOverflow: true,
          breakpoints: {
            576: { slidesPerView: 1.6 },
            768: { slidesPerView: 2.2 },
          },
          pagination: {
            el: el.querySelector(".grid-slider__pagination"),
            clickable: true,
          },
        });
      } else if (!mq.matches && swiper) {
        swiper.destroy(true, true);
        swiper = null;
      }
    }

    mq.addEventListener("change", toggle);
    toggle();
  });
});
