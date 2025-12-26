// === DASHBOARD SEKME GEÇİŞLERİ ===
document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".dashboard-menu li");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      const target = tab.getAttribute("data-tab");

      // Aktif sekme
      tabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");

      // İçerik geçişi
      contents.forEach(c => {
        c.classList.remove("active");
        if (c.id === target) {
          c.classList.add("active");
        }
      });
    });
  });
});

let activeReceiver = null; // 🔹 Global tanımla
const chatBody = document.querySelector(".chat-body");

// === MESAJLARI GETİR (GLOBAL FONKSİYON) ===
function loadMessages(receiverId) {
  activeReceiver = receiverId;

  const chatHeaderTitle = document.querySelector(".chat-header h3");
  const chatBody = document.querySelector(".chat-body");

  if (chatHeaderTitle) chatHeaderTitle.textContent = "Yükleniyor...";
  if (chatBody) chatBody.innerHTML = "<p class='loading'>Yükleniyor...</p>";

  fetch(ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "etkinliks_get_messages",
      receiver_id: receiverId,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (!chatBody) return;

      chatBody.innerHTML = "";

      // 🟣 Başlıkta kiminle konuşulduğunu belirle
      if (chatHeaderTitle) {
        let name = "Mesajlaşma";
        if (data && data.data && data.data.length > 0) {
          const sample = data.data[0];
          name =
            sample.sender_id == currentUserId
              ? sample.receiver_name
              : sample.sender_name;
        }
        chatHeaderTitle.textContent = `${name} ile konuşuyorsunuz`;
      }

      // 🟣 Mesaj yoksa
      if (!data.success || !data.data.length) {
        chatBody.innerHTML = "<p>Henüz mesaj yok.</p>";
        return;
      }

      // 🟣 Mesajları ekrana yaz
      data.data.forEach((msg) => {
        const bubble = document.createElement("div");
        bubble.className =
          msg.sender_id == currentUserId
            ? "message outgoing"
            : "message incoming";
        bubble.innerHTML = `
          <p>${msg.message}</p>
          <span class="time">${msg.created_at || ""}</span>
        `;
        chatBody.appendChild(bubble);
      });

      chatBody.scrollTop = chatBody.scrollHeight;
    })
    .catch((err) => {
      console.error("Mesaj yükleme hatası:", err);
      if (chatBody) chatBody.innerHTML = "<p>Bir hata oluştu.</p>";
    });
}


// === SOLDAKİ KONUYA TIKLANDIĞINDA ===
document.addEventListener("click", function (e) {
  const item = e.target.closest(".conversation-item");
  if (item) {
    const receiverId = item.dataset.receiver;
    document.querySelectorAll(".conversation-item").forEach(el => el.classList.remove("active"));
    item.classList.add("active");
    loadMessages(receiverId);
  }
});

// === MESAJ GÖNDERME ===
document.addEventListener("DOMContentLoaded", () => {
  const sendBtn = document.querySelector(".send-btn");
  const messageInput = document.querySelector(".chat-footer input");

  if (sendBtn && messageInput) {
    sendBtn.addEventListener("click", () => {
      const msg = messageInput.value.trim();
      if (!msg) return alert("Mesaj boş olamaz!");
      if (!activeReceiver) return alert("Bir alıcı seçiniz!");

      fetch(ajaxurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "etkinliks_send_message",
          receiver_id: activeReceiver,
          event_id: 0,
          message: msg,
        }),
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const bubble = document.createElement("div");
            bubble.className = "message outgoing";
            bubble.innerHTML = `<p>${msg}</p><span class="time">Şimdi</span>`;
            chatBody.appendChild(bubble);
            messageInput.value = "";
            chatBody.scrollTop = chatBody.scrollHeight;
          } else {
            alert(data.data.message || "Mesaj gönderilemedi.");
          }
        });
    });
  }
});
