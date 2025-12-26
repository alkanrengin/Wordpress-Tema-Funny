<?php
/* Template Name: Organizatör Ol */
get_header();
?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/organizator-ol.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/organizator-ol.css'); ?>">

<section class="org-hero">
  <div class="overlay"></div>
  <div class="container">
    <h1>Organizatör Ol</h1>
    <p>Etkinliklerini binlerce kişiye ulaştır, satışını hemen başlat!</p>
  </div>
</section>

<section class="org-why">
  <h2>Neden Etkinlik-S?</h2>
  <div class="org-benefits">
    <div class="benefit">
      <i class="icon">👥</i>
      <h3>Geniş Kitleye Ulaş</h3>
      <p>Farklı kategorilerden binlerce katılımcıya ulaş.</p>
    </div>
    <div class="benefit">
      <i class="icon">⚙️</i>
      <h3>Kolay ve Şeffaf Yönetim</h3>
      <p>Satış, gelir ve ödeme sürecini kolayca takip et.</p>
    </div>
    <div class="benefit">
      <i class="icon">💳</i>
      <h3>Sadece Kazanınca Öde</h3>
      <p>Kayıt ve listeleme ücretsiz, sadece satıştan komisyon.</p>
    </div>
  </div>
</section>

<section class="org-forwho">
  <h2>Kimler İçin?</h2>
  <div class="org-groups">
   
    <div class="group">🎨 Atölye &amp; Workshop Sahipleri</div>
    <div class="group">📚 Eğitmenler &amp; Birebir Ders Verenler</div>
     <div class="group">🎭 Tiyatro Grupları</div>
    <div class="group">🎵 Müzisyenler &amp; Konser Organizatörleri</div>
    <div class="group">🗺️ Kültür &amp; Gezi Turları</div>
    <div class="group">💻 Online Etkinlik Üreticileri</div>
    <div class="group">🎉 Sosyal Etkinlik Organizatörleri</div>
    <div class="group">🏛️ Fuarlar</div>
    
  </div>
</section>

<section class="org-how">
  <h2>Nasıl Çalışır?</h2>
  <div class="steps">
    <div class="step"><span>1</span> Kayıt Ol</div>
    <div class="step"><span>2</span> Etkinliğini Paylaş</div>
    <div class="step"><span>3</span> Yayına Al</div>
    <div class="step"><span>4</span> Kazanmaya Başla</div>
  </div>
</section>

<section class="org-commission">
  <h2>İlk Organizasyonlara Özel Komisyon Kampanyası!</h2>
  <div class="commission-box">
    <div><strong>Standart oran:</strong> %7</div>
    <div><strong>Kampanya oranı:</strong> %0</div>
    <p>Tüm vergiler dahil, şeffaf kazanç modeli.</p>
  </div>
</section>

<section class="org-support">
  <div class="col">
    <h3>Destek ve Görünürlük</h3>
    <p>Etkinlik-S platformunda etkinliğinizi öne çıkarın, sosyal medya ve bültenlerle daha fazla kişiye ulaşın.</p>
  </div>
  <div class="col">
    <h3>Şeffaflık ve Güven</h3>
    <p>Komisyon oranları, gelir paylaşımı ve ödeme süreçleri tamamen şeffaftır.</p>
  </div>
</section>

<div class="org-cta">
  <a href="<?php echo esc_url(site_url('/kayit')); ?>" class="btn">Hemen Başla 🚀</a>
</div>

<?php get_footer(); ?>
