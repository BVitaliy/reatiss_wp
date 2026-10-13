/* global $ */
// Form validation (markup only — no backend). Required fields + email format;
// errors clear as the user types. On success the form resets and the "thanks"
// popup opens (in a popup it takes the form's place). CF7 on integration: open
// it on the wpcf7mailsent event instead.
$(function () {
  var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function fieldError(input) {
    var value = $.trim(input.value);
    if (input.required && !value) return true;
    if (input.type === "email" && value && !EMAIL.test(value)) return true;
    return false;
  }

  function mark(input, bad) {
    $(input).toggleClass("is-error", bad);
    if (bad) input.setAttribute("aria-invalid", "true");
    else input.removeAttribute("aria-invalid");
  }

  $(document).on("submit", ".js-form", function (e) {
    e.preventDefault();
    var valid = true;

    $(this).find(".field__input").each(function () {
      var bad = fieldError(this);
      mark(this, bad);
      if (bad) valid = false;
    });

    if (!valid) {
      $(this).find(".is-error").first().trigger("focus");
      return;
    }

    this.reset();
    if (window.ReatissModal) window.ReatissModal.open("thanks");
  });

  $(document).on("input", ".js-form .field__input.is-error", function () {
    if (!fieldError(this)) mark(this, false);
  });
});
