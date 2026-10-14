// Header search (WordPress): live results under the field and a real submit
// to the results page. The markup's app-header.js opens / closes the field;
// this file fills .js-search-results from wp_ajax_rts_live_search.
(function () {
  "use strict";

  var form = document.querySelector(".js-search");
  if (!form || !window.reatiss) return;

  var input = form.querySelector(".js-search-input");
  var box = form.querySelector(".js-search-results");
  var timer = 0;
  var controller = null;
  var last = "";

  function hide() {
    box.classList.remove("is-visible");
  }

  function load(term) {
    if (controller) controller.abort();
    controller = "AbortController" in window ? new AbortController() : null;

    var body = new FormData();
    body.append("action", "rts_live_search");
    body.append("nonce", window.reatiss.nonce);
    body.append("s", term);

    fetch(window.reatiss.ajaxUrl, {
      method: "POST",
      body: body,
      credentials: "same-origin",
      signal: controller ? controller.signal : undefined
    })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        if (input.value.trim() !== term) return; // typed on meanwhile
        var html = json && json.success ? json.data.html : "";
        box.innerHTML = html;
        box.classList.toggle("is-visible", html !== "");
      })
      .catch(function () {}); // aborted or offline — keep the last results
  }

  input.addEventListener("input", function () {
    var term = input.value.trim();
    clearTimeout(timer);

    if (term.length < 2) {
      last = "";
      hide();
      return;
    }

    if (term === last) return;
    timer = setTimeout(function () {
      last = term;
      load(term);
    }, 250);
  });

  // Empty field: nothing to search — stay in place.
  form.addEventListener("submit", function (event) {
    if (input.value.trim() === "") event.preventDefault();
  });
})();
