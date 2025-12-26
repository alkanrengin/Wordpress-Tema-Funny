<?php
/* 
Template Name: Destek & İletişim
*/
get_header(); 
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/destek.css?v=<?php echo time(); ?>">

<main class="support-page container">
  <section class="support-hero">
    <h1>💬 Destek Hattı & İletişim</h1>
    <p>Herhangi bir soru, öneri veya şikayetiniz varsa bizimle iletişime geçmekten çekinmeyin.</p>
  </section>

  <section class="support-content">
    <div class="support-card">
      <h2>📱 WhatsApp Destek Hattı</h2>
      <p>Etkinlik-S destek ekibine WhatsApp üzerinden kolayca ulaşabilirsiniz.</p>
      <div class="support-number">
        <a href="https://wa.me/905555555555" target="_blank" class="whatsapp-btn">
          <i class="fa-brands fa-whatsapp"></i>  +90 555 555 55 55
        </a>
      </div>
      <p class="note">📌 Şu anda tüm talepler WhatsApp üzerinden alınmaktadır.  
      Yakında site içi canlı destek sistemimiz devreye alınacaktır.</p>
    </div>

    <div class="support-card alt">
      <h2>📧 E-Posta İletişim</h2>
      <p>Bize e-posta ile de ulaşabilirsiniz:</p>
      <p><a href="mailto:destek@etkinlik-s.com" class="email-link">destek@etkinlik-s.com</a></p>
    </div>
  </section>
</main>

<?php get_footer(); ?>
