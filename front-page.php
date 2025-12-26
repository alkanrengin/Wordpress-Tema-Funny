<?php
/* Template Name: Ana Sayfa */
get_header();
?>

<main class="home-main">

  <!-- 🎠 SLIDER -->
  <section class="hero">
    <?php
    $slider_urunleri = get_field('slider_urunleri');
    $slider_images   = [];

    if (!empty($slider_urunleri) && is_array($slider_urunleri)) {
      foreach ($slider_urunleri as $item) {
        $urun_id = is_object($item) ? $item->ID : (is_numeric($item) ? (int)$item : 0);
        if ($urun_id) {
          $resim = get_the_post_thumbnail_url($urun_id, 'large');
          if ($resim) {
            $slider_images[] = esc_url($resim);
          }
        }
      }
    }

    if (empty($slider_images)) {
      $slider_images = [
        'https://picsum.photos/1600/600?random=1',
        'https://picsum.photos/1600/600?random=2'
      ];
    }

    $shop_id   = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
    $shop_link = $shop_id && $shop_id > 0 ? get_permalink($shop_id) : home_url('/');
    ?>

    <img src="<?php echo esc_url($slider_images[0]); ?>" alt="Hero Görseli" class="hero-image">

    <div class="hero-content">
      <h2><?php echo esc_html(get_bloginfo('name')); ?></h2>
      <a href="<?php echo esc_url(site_url('/organizator-ol')) ?>">Etkinlik Oluştur</a>
    </div>

    <div class="slider-arrows">
      <button class="prev"><i class="fa fa-chevron-left"></i></button>
      <button class="next"><i class="fa fa-chevron-right"></i></button>
    </div>
  </section>

<!-- 🎟️ YAKLAŞAN ETKİNLİKLER -->
<section class="events">
  <h2>Yaklaşan Etkinlikler</h2>
  <div class="event-grid">
    <?php
    // 🔹 Bugünden itibaren olan etkinlikleri al
    $today = date('Y-m-d');
    $args = [
      'post_type'      => 'product',
      'posts_per_page' => 10,
      'meta_key'       => 'etkinlik_baslangic',
      'orderby'        => 'meta_value',
      'order'          => 'ASC',
      'meta_query'     => [
        [
          'key'     => 'etkinlik_baslangic',
          'value'   => $today,
          'compare' => '>=',
          'type'    => 'DATE'
        ]
      ]
    ];

    $yaklasan = new WP_Query($args);

    if ($yaklasan->have_posts()) :
      while ($yaklasan->have_posts()) : $yaklasan->the_post();
        $resim = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://picsum.photos/300/200?random=' . get_the_ID();
        $baslik = get_the_title();
        $link   = get_permalink();
        $tarih  = get_post_meta(get_the_ID(), 'etkinlik_tarihi', true);
        $fiyat  = function_exists('wc_get_product') ? wc_get_product(get_the_ID())->get_price_html() : '';
        ?>
        <div class="event-card">
          <a href="<?php echo esc_url($link); ?>">
            <img src="<?php echo esc_url($resim); ?>" alt="<?php echo esc_attr($baslik); ?>" />
            <div class="event-info">
              <h3><?php echo esc_html($baslik); ?></h3>
              <?php if ($tarih): ?>
                <div class="event-date"><?php echo date_i18n('d M Y', strtotime($tarih)); ?></div>
              <?php endif; ?>
              <p class="price"><?php echo $fiyat ?: 'Ücretsiz'; ?></p>
            </div>
          </a>
        </div>
      <?php
      endwhile;
      wp_reset_postdata();
    else :
      echo '<p style="text-align:center;">Henüz yaklaşan etkinlik yok.</p>';
    endif;
    ?>
  </div>
</section>




  <!-- 💎 ORGANİZATÖR BANNER -->
  <section class="organizer-banner">
    <div class="banner-content">
      <div class="banner-text">
        <h2>Profesyonel Organizasyonlar <span>etkinlik-s</span>’de!</h2>
        <p>Etkinlik sayfası oluşturma, dijital biletleme, bilet satış, kayıt toplama ve çok daha fazlası...</p>
      </div>
      <a href="<?php echo esc_url(site_url('/organizator-ol')); ?>" class="banner-btn">Detaylı Bilgi</a>
    </div>
  </section>


  <!-- 🏷️ KATEGORİLER -->
  <section class="categories">
    <h2>Kategorilere Göz At</h2>
    <div class="category-wrapper">
      <button class="arrow left"><i class="fa fa-chevron-left"></i></button>
      <div class="category-slider">
        <?php
        $product_cats = get_terms([
          'taxonomy'   => 'product_cat',
          'hide_empty' => false,
          'parent'     => 0,
          'number'     => 12,
        ]);

        if (!empty($product_cats) && !is_wp_error($product_cats)) :
          foreach ($product_cats as $cat) :
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $cat_img      = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
            if (!$cat_img) $cat_img = 'https://picsum.photos/300/200?random=' . $cat->term_id;

            $cat_link = get_term_link($cat);
            if (is_wp_error($cat_link)) continue;
            ?>
            <div class="category-card">
              <a href="<?php echo esc_url($cat_link); ?>">
                <img src="<?php echo esc_url($cat_img); ?>" alt="<?php echo esc_attr($cat->name); ?>">
                <h4><?php echo esc_html($cat->name); ?></h4>
              </a>
            </div>
          <?php endforeach;
        else :
          echo '<p>Kategori bulunamadı.</p>';
        endif;
        ?>
      </div>
      <button class="arrow right"><i class="fa fa-chevron-right"></i></button>
    </div>
  </section>

</main>


<!-- 🧠 JS: Slider & Kategori Kaydırma -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const heroImages = <?php echo json_encode(array_values($slider_images)); ?>;
  const hero  = document.querySelector(".hero img");
  const prev  = document.querySelector(".hero .prev");
  const next  = document.querySelector(".hero .next");

  let currentSlide = 0;
  function showSlide(index) {
    if (!heroImages.length || !hero) return;
    hero.style.opacity = 0;
    setTimeout(() => {
      hero.src = heroImages[index];
      hero.style.opacity = 1;
    }, 300);
  }

  if (prev && next && heroImages.length > 1) {
    prev.addEventListener("click", () => {
      currentSlide = (currentSlide - 1 + heroImages.length) % heroImages.length;
      showSlide(currentSlide);
    });
    next.addEventListener("click", () => {
      currentSlide = (currentSlide + 1) % heroImages.length;
      showSlide(currentSlide);
    });
    setInterval(() => {
      currentSlide = (currentSlide + 1) % heroImages.length;
      showSlide(currentSlide);
    }, 6000);
  }

  const slider = document.querySelector(".category-slider");
  const left   = document.querySelector(".arrow.left");
  const right  = document.querySelector(".arrow.right");
  if (slider && left && right) {
    left.addEventListener("click", () => slider.scrollBy({ left: -240, behavior: "smooth" }));
    right.addEventListener("click", () => slider.scrollBy({ left: 240, behavior: "smooth" }));
  }
});
</script>

<?php get_footer(); ?>
