// Contact Form 7 → design integration.
//
// The markup's app-form.js validated `.js-form` and opened "thanks" without a
// backend. CF7 forms (`form.rts-form`) are sent by CF7; this file maps its
// lifecycle onto the design: the field error state (.field__input.is-error +
// .field__error), a busy button while sending, and the "thanks" popup on
// `wpcf7mailsent`.
(function () {
  "use strict";

  var FORM = "form.rts-form";
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function fieldError(input) {
    var value = String(input.value || "").trim();
    var required = input.classList.contains("wpcf7-validates-as-required");
    if (required && !value) return true;
    if (input.type === "email" && value && !EMAIL.test(value)) return true;
    return false;
  }

  function mark(input, bad) {
    var field = input.closest(".field");
    input.classList.toggle("is-error", bad);
    if (field) field.classList.toggle("is-invalid", bad);
    input.setAttribute("aria-invalid", bad ? "true" : "false");
  }

  function inputs(form) {
    return form.querySelectorAll(".field__input");
  }

  // Capture phase: runs before CF7's own submit listener, so an invalid form
  // never reaches the server.
  document.addEventListener("submit", function (event) {
    var form = event.target.closest && event.target.closest(FORM);
    if (!form) return;

    var first = null;
    inputs(form).forEach(function (input) {
      var bad = fieldError(input);
      mark(input, bad);
      if (bad && !first) first = input;
    });

    if (first) {
      event.preventDefault();
      event.stopImmediatePropagation();
      first.focus();
    }
  }, true);

  // Clear a field's error as soon as it becomes valid.
  document.addEventListener("input", function (event) {
    var input = event.target;
    if (!input.closest || !input.closest(FORM) || !input.classList.contains("is-error")) return;
    if (!fieldError(input)) mark(input, false);
  });

  // Busy button while CF7 sends.
  document.addEventListener("submit", function (event) {
    var form = event.target.closest && event.target.closest(FORM);
    if (!form) return;
    var btn = form.querySelector("[type=submit]");
    if (btn) btn.classList.add("is-loading");
  });

  ["wpcf7submit", "wpcf7invalid", "wpcf7spam", "wpcf7mailfailed", "wpcf7mailsent"].forEach(function (name) {
    document.addEventListener(name, function (event) {
      var btn = event.target.querySelector("[type=submit]");
      if (btn) btn.classList.remove("is-loading");
    });
  });

  // Server-side validation failed: show the design's errors on those fields.
  document.addEventListener("wpcf7invalid", function (event) {
    var form = event.target;
    var invalid = (event.detail && event.detail.apiResponse && event.detail.apiResponse.invalid_fields) || [];
    var first = null;

    invalid.forEach(function (item) {
      var wrap = form.querySelector('[data-name="' + item.field + '"]');
      var input = wrap && wrap.querySelector(".field__input");
      if (!input) return;
      mark(input, true);
      if (!first) first = input;
    });

    if (first) first.focus();
  });

  document.addEventListener("wpcf7mailsent", function (event) {
    inputs(event.target).forEach(function (input) {
      mark(input, false);
    });
    if (window.ReatissModal) window.ReatissModal.open("thanks");
  });

  // A failed send is not a validation problem — announce the message CF7 shows.
  ["wpcf7mailfailed", "wpcf7spam"].forEach(function (name) {
    document.addEventListener(name, function (event) {
      var output = event.target.querySelector(".wpcf7-response-output");
      if (output) output.setAttribute("role", "alert");
    });
  });
})();
