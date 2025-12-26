<?php get_header(); ?>
<main class="home-main">
<!-- 🎠 ANA SLIDER -->
<?php if( have_rows('ana_slider') ): ?>
  <section class="etkinlik-slider">
    <div class="slider-wrapper">

      <?php while( have_rows('ana_slider') ): the_row(); 
        $resim = get_sub_field('slider_gorsel');
        $baslik = get_sub_field('slider_baslik');
        $aciklama = get_sub_field('slider_aciklama');
        $buton = get_sub_field('slider_link');
      ?>
        <div class="slide">
          <img src="<?php echo esc_url($resim['url']); ?>" alt="<?php echo esc_attr($baslik); ?>">
          <div class="slide-caption">
            <h2><?php echo esc_html($baslik); ?></h2>
            <p><?php echo esc_html($aciklama); ?></p>
            <?php if( $buton ): ?>
              <a href="<?php echo esc_url($buton['url']); ?>" class="slider-btn"><?php echo esc_html($buton['title']); ?></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>

    </div>

    <div class="slider-nav">
      <button class="prev">&#10094;</button>
      <button class="next">&#10095;</button>
    </div>
  </section>
<?php endif; ?>

<!-- 🎟️ POPÜLER ETKİNLİKLER (ACF ile) -->
<?php if( have_rows('populer_etkinlikler') ): ?>
<section class="populer-etkinlikler">
  <div class="container">
    <h2 class="section-title">Popüler Etkinlikler</h2>
    <div class="etkinlik-grid">

      <?php while( have_rows('populer_etkinlikler') ): the_row(); 
        $etkinlik_resim = get_sub_field('etkinlik_resim');
        $etkinlik_baslik = get_sub_field('etkinlik_baslik');
        $etkinlik_fiyat = get_sub_field('etkinlik_fiyat');
        $etkinlik_link = get_sub_field('etkinlik_link');
      ?>
        <div class="etkinlik-card">
          <a href="<?php echo esc_url($etkinlik_link['url']); ?>">
            <img src="<?php echo esc_url($etkinlik_resim['url']); ?>" alt="<?php echo esc_attr($etkinlik_baslik); ?>">
            <h3><?php echo esc_html($etkinlik_baslik); ?></h3>
            <span class="price"><?php echo esc_html($etkinlik_fiyat); ?></span>
          </a>
        </div>
      <?php endwhile; ?>

    </div>
  </div>
</section>
<?php endif; ?>

<!-- 🔁 KATEGORİLER SLIDER (ACF ile) -->
<?php if( have_rows('kategori_slider') ): ?>
  <section class="kategori-slider-section">
    <div class="container">
      <h2 class="section-title">Kategorilere Göz At</h2>

      <div class="kategori-slider">
        <?php while( have_rows('kategori_slider') ): the_row(); 
          $kategori_resim = get_sub_field('kategori_resim');
          $kategori_ad = get_sub_field('kategori_ad');
          $kategori_link = get_sub_field('kategori_link');
        ?>
          <div class="kategori-card">
            <a href="<?php echo esc_url($kategori_link['url']); ?>">
              <img src="<?php echo esc_url($kategori_resim['url']); ?>" alt="<?php echo esc_attr($kategori_ad); ?>">
              <span><?php echo esc_html($kategori_ad); ?></span>
            </a>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php get_footer(); ?>
