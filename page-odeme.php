<?php
/* Template Name: Ödeme Sayfası */
get_header();
?>

<main class="checkout-wrapper">
  <div class="checkout-box">
    
    <!-- 🔹 Sol Taraf -->
    <section class="checkout-left">
      <h2>Katılımcı Bilgileri</h2>

      <form class="checkout-form" method="post">
        <div class="form-group">
          <input type="text" name="first_name" placeholder="Ad" required>
        </div>

        <div class="form-group">
          <input type="text" name="last_name" placeholder="Soyad" required>
        </div>

        <div class="form-group">
          <input type="email" name="email" placeholder="E-posta Adresi" required>
        </div>
        <div class="form-group">
          <input type="tel" name="phone" placeholder="Telefon" required>
        </div>

        <h2>Ödeme Bilgileri</h2>
        <div class="form-group">
          <input type="text" name="card_name" placeholder="💳 Kart Üzerindeki İsim"  required>
        </div>
        <div class="form-group">
          <input type="text" name="card_number" placeholder="💳 Kart Numarası" maxlength="19" required>
        </div>

        <div class="form-row">
          <div class="form-group half">
            <input type="text" name="expiry" placeholder="MM/YY" required>
          </div>
          <div class="form-group half">
            <input type="text" name="cvv" placeholder="CVC" maxlength="4" required>
          </div>
        </div>

        <button type="submit" class="btn-submit">Siparişi Gönder</button>
      </form>
    </section>

    <!-- 🔹 Sağ Taraf - Sipariş Özeti -->
    <aside class="checkout-right">
      <h2>Sipariş Özeti</h2>
      <div class="summary-card">
        <div class="event-image">
          <img src="https://etkinlik-s.com/wp-content/uploads/2025/10/male-mime-artist-giving-white-rose-surprised-female-mime-1536x1024.jpg" alt="Etkinlik Görseli">
        </div>

        <div class="event-details">
          <h3>Etkinlik Deneme</h3>
          <div class="event-meta">
            <div>
              <strong>Tarih</strong>
              <p>Belirtilmemiş</p>
            </div>
            <div>
              <strong>Saat</strong>
              <p>Belirtilmemiş</p>
            </div>
          </div>
        </div>

        <div class="price-summary">
          <div class="line">
            <span>Ara Toplam</span>
            <strong>₺550,00</strong>
          </div>
          <div class="line total">
            <span>Toplam</span>
            <strong class="blue">₺550,00</strong>
          </div>
        </div>
      </div>
    </aside>

  </div>
</main>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/odeme.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/odeme.css'); ?>">

<?php get_footer(); ?>
