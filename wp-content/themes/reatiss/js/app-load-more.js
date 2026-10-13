// "Show more" on the blog, events and author lists (.pager .js-load-more):
// the next page comes from admin-ajax (rts_load_more, nonce) and is appended
// to the card grid; the page number lights up, the button goes at the end.
// Without JS the button is a plain link to the next page.
(function () {
  "use strict";

  if (!window.reatiss) return;

  document.addEventListener("click", function (event) {
    var btn = event.target.closest && event.target.closest(".js-load-more");
    if (!btn) return;

    var pager = btn.closest(".pager");
    var list = pager && pager.parentNode.querySelector(".js-load-more-list");
    if (!list || btn.classList.contains("is-loading")) return;

    event.preventDefault();
    btn.classList.add("is-loading");

    var next = parseInt(btn.getAttribute("data-page"), 10) + 1;
    var body = new FormData();
    body.append("action", "rts_load_more");
    body.append("nonce", window.reatiss.nonce);
    body.append("page", String(next));
    ["type", "taxonomy", "term", "author"].forEach(function (key) {
      body.append(key, btn.getAttribute("data-" + key) || "");
    });

    fetch(window.reatiss.ajaxUrl, { method: "POST", body: body, credentials: "same-origin" })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        if (!json || !json.success) throw new Error("load more failed");

        list.insertAdjacentHTML("beforeend", json.data.html);
        btn.setAttribute("data-page", String(next));

        var number = pager.querySelector('.pagination__item[data-page="' + next + '"]');
        if (number) number.classList.add("is-active");

        if (!json.data.more) btn.remove();
        if (window.ScrollTrigger) window.ScrollTrigger.refresh();
      })
      .catch(function () {
        // fall back to the plain link
        window.location.href = btn.href;
      })
      .finally(function () {
        btn.classList.remove("is-loading");
      });
  });
})();
