// On Shelf: a report row opens the "reports" popup (data-modal-open, app-modals.js).
// Figma 8422:37147: the title turns into "Request access to" (Site Settings)
// with the report's part number under it, the report's description shows in a
// plate above the form, and the part number goes into the form's hidden
// "your-report" field so the request says which report it is about.
// Opened from anywhere else (an editor link to #reports): the plain title, no
// part number, no plate, the field empty.
(function () {
  "use strict";

  document.addEventListener("click", function (event) {
    var opener = event.target.closest && event.target.closest('[data-modal-open="reports"]');
    if (!opener) return;

    var panel = document.querySelector('[data-modal="reports"]');
    if (!panel) return;

    var report = opener.getAttribute("data-report") || "";
    var text = opener.getAttribute("data-report-text") || "";

    var field = panel.querySelector('input[name="your-report"]');
    if (field) field.value = report;

    var heading = panel.querySelector(".js-report-heading");
    if (heading) heading.textContent = heading.getAttribute(report ? "data-item" : "data-default");

    var part = panel.querySelector(".js-report-part");
    if (part) {
      part.textContent = report;
      part.hidden = !report;
    }

    var note = panel.querySelector(".js-report-note");
    if (note) {
      note.querySelector(".js-report-note-text").textContent = text;
      note.hidden = !(report && text);
    }
  });
})();
