// === 🔹 Mobil Menü (Header) ===
document.addEventListener("DOMContentLoaded", function() {
  const menuToggle = document.getElementById("mobileMenuToggle");
  const mainMenu = document.querySelector(".main-menu");

  if (menuToggle && mainMenu) {
    menuToggle.addEventListener("click", function() {
      mainMenu.classList.toggle("open");
      menuToggle.classList.toggle("active");
      document.body.classList.toggle("no-scroll");
    });
  }

  // === 🔹 Dashboard Sekme Geçişleri ===
  const menuItems = document.querySelectorAll(".dashboard-menu li");
  const tabs = document.querySelectorAll(".tab-content");

  if (menuItems.length > 0 && tabs.length > 0) {
    menuItems.forEach(item => {
      item.addEventListener("click", () => {
        // Aktif menü
        menuItems.forEach(i => i.classList.remove("active"));
        item.classList.add("active");

        // İlgili sekmeyi göster
        const tabId = item.getAttribute("data-tab");
        tabs.forEach(tab => {
          if (tab.id === tabId) {
            tab.classList.add("active", "fade-in");
          } else {
            tab.classList.remove("active", "fade-in");
          }
        });
      });
    });
  }
});
