document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab-btn");
  const contents = document.querySelectorAll(".tab-content");
  const orgType = document.getElementById("orgType");
  const dynamicFields = document.getElementById("dynamicFields");

  // === Sekme geçişleri ===
  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");

      const target = tab.dataset.tab;
      contents.forEach(c => {
        c.classList.remove("active");
        if (c.id === target) c.classList.add("active");
      });
    });
  });

  // === Organizatör tipi değişince dinamik alan oluştur ===
  orgType?.addEventListener("change", e => {
    const type = e.target.value;
    dynamicFields.innerHTML = "";

    if (type === "bireysel") {
      dynamicFields.innerHTML = `
        <div class="input-group">
          <input type="text" name="adsoyad" placeholder="Ad Soyad" required>
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="E-posta Adresi" required>
        </div>
        <div class="input-group">
          <input type="tel" name="phone" placeholder="Telefon Numarası" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Şifre" required>
        </div>
        <div class="input-group">
          <input type="password" name="password2" placeholder="Şifre Tekrarı" required>
        </div>
      `;
    }

    if (type === "kurumsal") {
      dynamicFields.innerHTML = `
        <div class="input-group">
          <input type="text" name="company_name" placeholder="Firma Adı" required>
        </div>
        <div class="input-group">
          <input type="text" name="contact_person" placeholder="İletişim Kurulacak Kişi" required>
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="Kurumsal E-posta" required>
        </div>
        <div class="input-group">
          <input type="tel" name="phone" placeholder="İletişim Numarası" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Şifre" required>
        </div>
        <div class="input-group">
          <input type="password" name="password2" placeholder="Şifre Tekrarı" required>
        </div>
      `;
    }
  });

  // === Form submit'te butonu kilitle ===
  const forms = document.querySelectorAll(".register-form");
  forms.forEach(form => {
    form.addEventListener("submit", () => {
      const btn = form.querySelector(".btn-register");
      if (btn) {
        btn.disabled = true;
        btn.innerText = "Gönderiliyor...";
      }
    });
  });
});
