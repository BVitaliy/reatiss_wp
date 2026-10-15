// Editor button “Insert CTA box” (classic editor + ACF WYSIWYG): a dialog
// with the text, button label, link (a site page or a custom URL) and size —
// it writes the [rts_cta] shortcode, so editors never type it. In the visual
// editor the shortcode shows as a box (wp.mce.views); clicking it reopens
// the dialog with its values. Data: window.rtsCta (functions/admin-mce-cta.php).
(function () {
  "use strict";

  var data = window.rtsCta || { pages: [], i18n: {} };
  var t = data.i18n;

  // Shortcode attributes can't hold straight double quotes.
  function clean(value) {
    return String(value || "").replace(/"/g, "”").replace(/[\[\]]/g, "").trim();
  }

  function shortcode(values) {
    var parts = ["rts_cta", 'title="' + clean(values.title) + '"'];
    if (values.button) parts.push('button="' + clean(values.button) + '"');
    if (values.url) parts.push('url="' + clean(values.url) + '"');
    if (values.big) parts.push('size="big"');
    return "[" + parts.join(" ") + "]";
  }

  function open(editor, values, done) {
    values = values || {};
    var pageValue = "";
    data.pages.forEach(function (page) {
      if (page.value === values.url) pageValue = page.value;
    });

    editor.windowManager.open({
      title: t.title,
      width: 560,
      height: 330,
      body: [
        { type: "textbox", name: "title", label: t.text, value: values.title || "", multiline: true, minHeight: 70 },
        { type: "textbox", name: "button", label: t.button, value: values.button || "" },
        {
          type: "listbox",
          name: "page",
          label: t.page,
          value: pageValue,
          values: [{ text: t.custom, value: "" }].concat(data.pages),
        },
        { type: "textbox", name: "url", label: t.url, value: pageValue ? "" : values.url || "", tooltip: t.urlHint },
        { type: "checkbox", name: "big", text: t.big, checked: values.size === "big" },
      ],
      onsubmit: function (event) {
        var d = event.data;
        if (!clean(d.title)) {
          editor.windowManager.alert(t.needText);
          return false;
        }
        done(shortcode({ title: d.title, button: d.button, url: d.page || d.url, big: d.big }));
      },
    });
  }

  if (window.tinymce) {
    tinymce.PluginManager.add("rts_cta", function (editor) {
      editor.addButton("rts_cta", {
        icon: "dashicon dashicons-megaphone",
        tooltip: t.tooltip,
        onclick: function () {
          open(editor, {}, function (code) {
            editor.insertContent(code);
          });
        },
      });
    });
  }

  // Visual editor: show the shortcode as a box; a click edits it.
  if (window.wp && wp.mce && wp.mce.views && wp.shortcode) {
    var esc = function (value) {
      var div = document.createElement("div");
      div.textContent = value || "";
      return div.innerHTML;
    };

    wp.mce.views.register("rts_cta", {
      initialize: function () {
        var a = this.shortcode.attrs.named;
        this.render(
          '<div style="display:flex;gap:16px;align-items:center;justify-content:space-between;padding:16px 20px;border-radius:6px;background:#f8f9fa;font-family:sans-serif">' +
            '<span style="font-size:' + (a.size === "big" ? "20px" : "16px") + ';color:#021016">' + esc(a.title) + "</span>" +
            (a.button ? '<span style="flex:none;padding:8px 14px;border-radius:3px;background:#001d77;color:#fff;font-size:13px">↘ ' + esc(a.button) + "</span>" : "") +
            "</div>"
        );
      },
      edit: function (text, update) {
        var parsed = wp.shortcode.next("rts_cta", text);
        var a = parsed ? parsed.shortcode.attrs.named : {};
        open(tinymce.activeEditor, a, function (code) {
          update(code);
        });
      },
    });
  }
})();
