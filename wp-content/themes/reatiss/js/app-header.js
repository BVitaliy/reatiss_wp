/* global $ */
// Header: glass state on scroll, search (open / close / results), mobile menu.
$(function () {
  var $header = $(".js-header");
  if (!$header.length) return;

  var $search = $header.find(".js-search");
  var $input = $header.find(".js-search-input");
  var $results = $header.find(".js-search-results");
  var $menu = $(".js-mobile-menu");
  var $burger = $header.find(".js-burger");
  var lock = window.ReatissScrollLock;

  // ── Glass bar once the page leaves the top ─────────────────────
  function onScroll(y) {
    $header.toggleClass("is-scrolled", y > 40);
  }

  if (window.lenis) {
    window.lenis.on("scroll", function (e) {
      onScroll(e.scroll);
    });
  }
  window.addEventListener("scroll", function () {
    onScroll(window.scrollY);
  }, { passive: true });
  onScroll(window.scrollY);

  // ── Current page (static demo only) ─────────────────────────────
  // WordPress marks the item itself (.current-menu-item). Home has no
  // active item (Figma); inner pages light their section.
  var page = location.pathname.split("/").pop() || "index.php";
  var SECTION = { "event.php": "events.php", "article.php": "blog.php", "article-nav.php": "blog.php", "author.php": "blog.php", "contacts-image.php": "contacts.php" };
  var current = SECTION[page] || page;
  if (current !== "index.php") {
    $(".header__link, .mobile-menu__link").filter(function () {
      return this.getAttribute("href") === current;
    }).addClass("is-active").attr("aria-current", "page");
  }

  // ── Search ─────────────────────────────────────────────────────
  function openSearch() {
    closeMenu();
    $header.addClass("is-search-open");
    setTimeout(function () {
      $input.trigger("focus");
    }, 150);
  }

  function closeSearch() {
    if (!$header.hasClass("is-search-open")) return;
    $header.removeClass("is-search-open");
    $results.removeClass("is-visible");
    $input.val("");
  }

  $header.on("click", ".js-search-open", openSearch);
  $header.on("click", ".js-search-close", closeSearch);

  // Demo: results appear once something is typed (backend fills the list)
  $input.on("input", function () {
    $results.toggleClass("is-visible", $.trim(this.value).length > 1);
  });

  $search.on("submit", function (e) {
    e.preventDefault();
  });

  $(document).on("click", function (e) {
    if ($header.hasClass("is-search-open") && !$(e.target).closest(".js-search, .js-search-open").length) {
      closeSearch();
    }
  });

  // ── Mobile menu ────────────────────────────────────────────────
  function openMenu() {
    closeSearch();
    $menu.addClass("is-open");
    $header.addClass("is-menu-open");
    $burger.attr("aria-expanded", "true");
    if (lock) lock.lock("menu");
  }

  function closeMenu() {
    if (!$menu.hasClass("is-open")) return;
    $menu.removeClass("is-open");
    $header.removeClass("is-menu-open");
    $burger.attr("aria-expanded", "false");
    if (lock) lock.unlock("menu");
  }

  $burger.on("click", function () {
    if ($menu.hasClass("is-open")) closeMenu();
    else openMenu();
  });

  $menu.on("click", "a", closeMenu);

  // desktop has no burger — drop the menu state when crossing xl
  window.matchMedia("(min-width: 1200px)").addEventListener("change", function (mq) {
    if (mq.matches) closeMenu();
  });

  // ── Shared ─────────────────────────────────────────────────────
  $(document).on("keydown", function (e) {
    if (e.key !== "Escape") return;
    closeSearch();
    closeMenu();
  });

  // a modal opening takes over from search / menu
  $(document).on("reatiss:overlay-open", function () {
    closeSearch();
    closeMenu();
  });
});
