// On Shelf: a report row opens the "reports" popup (data-modal-open, app-modals.js);
// its title goes into the form's hidden "your-report" field so the request
// says which report it is about.
(function () {
  "use strict";

  document.addEventListener("click", function (event) {
    var row = event.target.closest && event.target.closest("[data-report]");
    if (!row) return;

    var field = document.querySelector('[data-modal="reports"] input[name="your-report"]');
    if (field) field.value = row.getAttribute("data-report");
  });
})();
