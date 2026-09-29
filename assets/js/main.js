document.addEventListener("DOMContentLoaded", function () {
  /* ---------------------------------------------------------
       1. Menu mobile
       --------------------------------------------------------- */
  var toggleBtn = document.getElementById("mobileToggle");
  var mainNav = document.getElementById("mainNav");

  if (toggleBtn && mainNav) {
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      var isOpen = mainNav.classList.toggle("open");
      toggleBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
      toggleBtn.querySelector("i").className = isOpen ? "fa-solid fa-xmark" : "fa-solid fa-bars";
    });

    document.addEventListener("click", function (e) {
      if (mainNav.classList.contains("open") && !mainNav.contains(e.target) && !toggleBtn.contains(e.target)) {
        mainNav.classList.remove("open");
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.querySelector("i").className = "fa-solid fa-bars";
      }
    });

    mainNav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        mainNav.classList.remove("open");
        toggleBtn.setAttribute("aria-expanded", "false");
        toggleBtn.querySelector("i").className = "fa-solid fa-bars";
      });
    });
  }

  /* ---------------------------------------------------------
       2. User dropdown (mobile / touch)
       --------------------------------------------------------- */
  var userMenu = document.getElementById("userMenu");
  if (userMenu) {
    var trigger = userMenu.querySelector(".user-trigger");
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      userMenu.classList.toggle("open");
      trigger.setAttribute("aria-expanded", userMenu.classList.contains("open") ? "true" : "false");
    });

    document.addEventListener("click", function (e) {
      if (userMenu.classList.contains("open") && !userMenu.contains(e.target)) {
        userMenu.classList.remove("open");
        trigger.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* ---------------------------------------------------------
       3. Tự động submit khi đổi bộ lọc danh mục
       --------------------------------------------------------- */
  var categorySelect = document.querySelector('.filter-bar select[name="category"]');
  if (categorySelect) {
    categorySelect.addEventListener("change", function () {
      categorySelect.closest("form").submit();
    });
  }

  /* ---------------------------------------------------------
       4. Trạng thái loading khi bấm "Đăng ký học"
       --------------------------------------------------------- */
  var enrollForm = document.querySelector(".side-box form");
  if (enrollForm) {
    enrollForm.addEventListener("submit", function () {
      var btn = enrollForm.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
      }
    });
  }

  /* ---------------------------------------------------------
       5. Ảnh lỗi (404) -> thay bằng placeholder gradient
       --------------------------------------------------------- */
  document.querySelectorAll("img").forEach(function (img) {
    if (img.closest(".user-avatar") && img.complete && img.naturalWidth === 0) {
      img.remove();
    }
    img.addEventListener(
      "error",
      function () {
        var wrap = img.closest(".course-media, .detail-media");
        if (wrap) {
          wrap.classList.add("course-media--ph", "detail-media--ph");
          img.remove();
        }
      },
      { once: true }
    );
  });
});