document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");

  if (!sidebarToggle || !sidebar || !mainContent) {
    console.error(
      "Elemen sidebarToggle, sidebar, atau mainContent tidak ditemukan!"
    );
    return;
  }

  function isMobile() {
    return window.innerWidth <= 992;
  }

  function loadSidebarState() {
    const saved = localStorage.getItem("sidebarCollapsed");
    if (saved !== null) {
      return saved === "true";
    }
    return isMobile();
  }

  function applySidebarState(collapsed) {
    if (collapsed) {
      sidebar.classList.add("collapsed");
      mainContent.classList.add("expanded");
      sidebarToggle.setAttribute("aria-expanded", "false");
    } else {
      sidebar.classList.remove("collapsed");
      mainContent.classList.remove("expanded");
      sidebarToggle.setAttribute("aria-expanded", "true");
    }
  }

  applySidebarState(loadSidebarState());

  sidebarToggle.addEventListener("click", () => {
    const collapsed = sidebar.classList.toggle("collapsed");
    mainContent.classList.toggle("expanded");
    sidebarToggle.setAttribute("aria-expanded", !collapsed);
    localStorage.setItem("sidebarCollapsed", collapsed);
  });

  window.addEventListener("resize", () => {
    if (isMobile()) {
      if (!sidebar.classList.contains("collapsed")) {
        applySidebarState(true);
        localStorage.setItem("sidebarCollapsed", true);
      }
    } else {
      if (sidebar.classList.contains("collapsed")) {
        applySidebarState(false);
        localStorage.setItem("sidebarCollapsed", false);
      }
    }
  });

  document.addEventListener("click", (e) => {
    if (
      isMobile() &&
      !sidebar.contains(e.target) &&
      !sidebarToggle.contains(e.target) &&
      !sidebar.classList.contains("collapsed")
    ) {
      applySidebarState(true);
      localStorage.setItem("sidebarCollapsed", true);
    }
  });
});
