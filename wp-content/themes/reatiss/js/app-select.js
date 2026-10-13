// Custom dropdown for .field__select (progressive enhancement).
// The native <select> stays in the markup (form submit, validation, CF7/WP)
// and is kept in sync; JS adds a combobox trigger + a listbox. The list is
// rendered in <body> and positioned under the field — the form box has a
// clip-path cut (.cut) that would crop it. Opens upwards when there is no
// room below. Keyboard: ↑ ↓ Home End Enter Space Esc Tab, type-ahead.
(function () {
  "use strict";

  var uid = 0;
  var openInst = null;

  document.querySelectorAll(".field__select").forEach(function (wrap) {
    var select = wrap.querySelector("select");
    if (!select || wrap.classList.contains("is-enhanced")) return;
    wrap.classList.add("is-enhanced");
    var id = "select-" + ++uid;

    var trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "field__input select__trigger";
    trigger.setAttribute("role", "combobox");
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");
    trigger.setAttribute("aria-controls", id + "-list");
    var valueEl = document.createElement("span");
    valueEl.className = "select__value";
    trigger.appendChild(valueEl);
    select.insertAdjacentElement("afterend", trigger);
    select.tabIndex = -1;
    select.setAttribute("aria-hidden", "true");

    var label = wrap.closest(".field") && wrap.closest(".field").querySelector(".field__label");
    if (label) {
      label.id = label.id || id + "-label";
      trigger.setAttribute("aria-labelledby", label.id);
    }

    var list = document.createElement("ul");
    list.className = "select__list";
    list.id = id + "-list";
    list.setAttribute("role", "listbox");
    list.setAttribute("data-lenis-prevent", "");
    if (label) list.setAttribute("aria-labelledby", label.id);

    var placeholder = "";
    var options = [];
    Array.prototype.forEach.call(select.options, function (opt, i) {
      if (opt.value === "" && opt.disabled) {
        placeholder = opt.textContent;
        return;
      }
      var li = document.createElement("li");
      li.className = "select__option";
      li.id = id + "-opt-" + i;
      li.setAttribute("role", "option");
      li.textContent = opt.textContent;
      li.dataset.index = i;
      list.appendChild(li);
      options.push(li);
    });
    document.body.appendChild(list);

    var active = -1;

    function sync() {
      var opt = select.options[select.selectedIndex];
      var empty = !opt || (opt.value === "" && opt.disabled);
      valueEl.textContent = empty ? placeholder : opt.textContent;
      trigger.classList.toggle("is-placeholder", empty);
      options.forEach(function (li) {
        var on = +li.dataset.index === select.selectedIndex;
        li.classList.toggle("is-selected", on);
        li.setAttribute("aria-selected", on ? "true" : "false");
      });
    }

    function setActive(i, scroll) {
      if (!options.length) return;
      active = Math.max(0, Math.min(options.length - 1, i));
      options.forEach(function (li, n) {
        li.classList.toggle("is-active", n === active);
      });
      trigger.setAttribute("aria-activedescendant", options[active].id);
      if (scroll) {
        var li = options[active];
        if (li.offsetTop < list.scrollTop) list.scrollTop = li.offsetTop - 6;
        else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight) list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight + 6;
      }
    }

    function place() {
      var r = trigger.getBoundingClientRect();
      var gap = 6;
      list.style.width = r.width + "px";
      list.style.left = r.left + window.scrollX + "px";
      var h = list.scrollHeight;
      var below = window.innerHeight - r.bottom - gap - 10;
      var up = below < Math.min(h, 220) && r.top > below;
      list.classList.toggle("is-up", up);
      list.style.top = (up ? r.top + window.scrollY - gap - Math.min(h, list.offsetHeight || h) : r.bottom + window.scrollY + gap) + "px";
    }

    function open() {
      if (openInst && openInst !== inst) openInst.close();
      openInst = inst;
      list.style.display = "block";
      place();
      place(); // second pass: height is known now (upward placement)
      wrap.classList.add("is-open");
      list.classList.add("is-open");
      trigger.setAttribute("aria-expanded", "true");
      var sel = options.findIndex(function (li) {
        return li.classList.contains("is-selected");
      });
      setActive(sel < 0 ? 0 : sel, true);
    }

    function close() {
      if (openInst === inst) openInst = null;
      wrap.classList.remove("is-open");
      list.classList.remove("is-open");
      trigger.setAttribute("aria-expanded", "false");
      trigger.removeAttribute("aria-activedescendant");
    }

    function choose(i) {
      var li = options[i];
      if (!li) return;
      select.selectedIndex = +li.dataset.index;
      select.dispatchEvent(new Event("input", { bubbles: true }));
      select.dispatchEvent(new Event("change", { bubbles: true }));
      sync();
      close();
      trigger.focus();
    }

    var inst = { close: close, place: place, list: list, wrap: wrap };

    trigger.addEventListener("click", function () {
      if (wrap.classList.contains("is-open")) close();
      else open();
    });

    // the field is a <label>: a click on its text would "activate" the
    // hidden <select> — send it to the trigger instead
    var field = wrap.closest("label");
    if (field) field.addEventListener("click", function (e) {
      if (e.target === trigger || trigger.contains(e.target)) return;
      e.preventDefault();
      trigger.focus();
    });

    list.addEventListener("mousedown", function (e) {
      e.preventDefault(); // keep focus on the trigger
    });
    list.addEventListener("click", function (e) {
      var li = e.target.closest(".select__option");
      if (li) choose(options.indexOf(li));
    });
    list.addEventListener("mousemove", function (e) {
      var li = e.target.closest(".select__option");
      if (li && options.indexOf(li) !== active) setActive(options.indexOf(li), false);
    });

    var typed = "";
    var typedTimer = null;
    trigger.addEventListener("keydown", function (e) {
      var isOpen = wrap.classList.contains("is-open");
      switch (e.key) {
        case "ArrowDown":
        case "ArrowUp":
          e.preventDefault();
          if (!isOpen) open();
          else setActive(active + (e.key === "ArrowDown" ? 1 : -1), true);
          return;
        case "Home":
        case "End":
          if (!isOpen) return;
          e.preventDefault();
          setActive(e.key === "Home" ? 0 : options.length - 1, true);
          return;
        case "Enter":
        case " ":
          e.preventDefault();
          if (isOpen) choose(active);
          else open();
          return;
        case "Escape":
          if (isOpen) {
            e.preventDefault();
            e.stopPropagation(); // don't close a surrounding modal
            close();
          }
          return;
        case "Tab":
          if (isOpen) close();
          return;
      }
      if (e.key.length === 1 && /\S/.test(e.key)) {
        typed += e.key.toLowerCase();
        clearTimeout(typedTimer);
        typedTimer = setTimeout(function () {
          typed = "";
        }, 600);
        var hit = options.findIndex(function (li) {
          return li.textContent.toLowerCase().indexOf(typed) === 0;
        });
        if (hit < 0) return;
        if (isOpen) setActive(hit, true);
        else {
          select.selectedIndex = +options[hit].dataset.index;
          select.dispatchEvent(new Event("change", { bubbles: true }));
          sync();
        }
      }
    });

    select.addEventListener("change", sync);
    if (select.form) select.form.addEventListener("reset", function () {
      setTimeout(sync, 0);
    });
    sync();
  });

  document.addEventListener("mousedown", function (e) {
    if (!openInst) return;
    if (openInst.wrap.contains(e.target) || openInst.list.contains(e.target)) return;
    openInst.close();
  });
  window.addEventListener("resize", function () {
    if (openInst) openInst.close();
  });
  // a scrolled page moves the field; the list lives in <body> and moves with
  // it, but re-check the up/down room
  window.addEventListener("scroll", function () {
    if (openInst) openInst.place();
  }, { passive: true });
})();
