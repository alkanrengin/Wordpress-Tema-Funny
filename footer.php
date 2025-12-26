<footer class="site-footer">
  <div class="footer-container">

    <!-- 🔹 Sol Taraf -->
    <div class="footer-left">
      <div class="footer-logo">
        <?php
        if ( function_exists('the_custom_logo') && has_custom_logo() ) {
          the_custom_logo();
        } else {
          echo '<a href="' . esc_url(home_url('/')) . '" class="site-title">' . esc_html(get_bloginfo('name')) . '</a>';
        }
        ?>
      </div>
      <p class="footer-desc"><?php bloginfo('description'); ?></p>
    </div>

    <!-- 🔹 Orta Menü + Sözleşmeler -->
    <div class="footer-menu-container">

      <?php
      if ( has_nav_menu('primary') ) {
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'footer-menu',
          'fallback_cb'    => false
        ]);
      }
      ?>
    </div>

      <div class="footer-legal-block">
        <h4>Sözleşmeler</h4>
        <ul class="footer-legal">
          <li><a href="<?php echo esc_url(site_url('/mesafeli-satis-sozlesmesi')); ?>">Mesafeli Satış Sözleşmesi</a></li>
          <li><a href="<?php echo esc_url(site_url('/on-bilgilendirme-formu')); ?>">Ön Bilgilendirme Formu</a></li>
          <li><a href="<?php echo esc_url(site_url('/gizlilik-politikasi')); ?>">Gizlilik Politikası / KVKK</a></li>
          <li><a href="<?php echo esc_url(site_url('/cerez-politikasi')); ?>">Çerez (Cookie) Politikası</a></li>
          <li><a href="<?php echo esc_url(site_url('/uyelik-ve-kullanim-kosullari')); ?>">Üyelik ve Kullanım Koşulları</a></li>
          <li><a href="<?php echo esc_url(site_url('/yasal-uyari')); ?>">Yasal Uyarı</a></li>
          <li><a href="<?php echo esc_url(site_url('/sorumluluk-reddi')); ?>">Sorumluluk Reddi</a></li>
          <li><a href="<?php echo esc_url(site_url('/organizator-sozlesmesi')); ?>">Organizatör Sözleşmesi</a></li>
        </ul>
      </div>

    

    <!-- 🔹 Sosyal Medya -->
    <div class="footer-social">
      <h4>Bizi Takip Edin</h4>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-x-twitter"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

  </div><!-- /.footer-container -->

  <div class="footer-bottom">
    <p>© <?php echo date('Y'); ?> <?php bloginfo('name'); ?> | Tüm Hakları Saklıdır.</p>
  </div>

  <script>
  document.querySelectorAll('.main-menu li.menu-item-has-children > a').forEach(link => {
    link.addEventListener('click', e => {
      if (window.innerWidth <= 992) {
        e.preventDefault();
        const parent = link.parentElement;
        parent.classList.toggle('open');
      }
    });
  });
  </script>

</footer>

<?php wp_footer(); ?>
</body>
</html>
