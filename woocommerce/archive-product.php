<?php
get_header();
$current_term = get_queried_object();
// 🔹 Ana kategori tespiti
if ($current_term && isset($current_term->term_id)) {
  if ($current_term->parent != 0) {
    // Alt kategorideysek ana kategoriyi al
    $parent_term = get_term($current_term->parent, 'product_cat');
  } else {
    // Zaten ana kategorideysek kendisini kullan
    $parent_term = $current_term;
  }
}
?>

<main class="archive-products-wrapper">

 <!-- 🔹 SİDEBAR -->
<aside class="archive-sidebar">
  <h3><?php echo  $parent_term->name ?></h3>
  <ul>
    <?php
    // Şu anki kategori
   

    // Eğer kategori seçilmemişse hiçbir şey gösterme
    if ($current_term && isset($current_term->term_id)) {

      // Eğer ALT kategoriye tıklanmışsa → Ana kategoriyi bul
      $parent_id = ($current_term->parent != 0) ? $current_term->parent : $current_term->term_id;

      // Ana kategori alt kategorilerini getir
      $subcategories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => $parent_id
      ]);

      if (!empty($subcategories) && !is_wp_error($subcategories)) {
        foreach ($subcategories as $term) {
          $active = ($term->term_id == $current_term->term_id) ? 'current-cat' : '';
          echo '<li class="' . esc_attr($active) . '">
                  <a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>
                </li>';
        }
      }
    }
    ?>
  </ul>
</aside>


  <!-- 🔹 ÜRÜNLER -->
  <section class="archive-products">
    <?php if (woocommerce_product_loop()) : ?>
      <ul class="products-grid">
        <?php while (have_posts()) : the_post(); global $product; ?>
          <li class="event-card">
            <div class="event-date">
              <?php
              $event_date = get_field('etkinlik_tarihi');
              echo $event_date ? esc_html($event_date) : 'TARİH BELİRTİLMEMİŞ';
              ?>
            </div>

            <a href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('funny-event'); ?>
              <?php endif; ?>
            </a>

            <div class="event-details">
              <h4><?php the_title(); ?></h4>
              <div class="price"><?php echo wc_price($product->get_price()); ?></div>
              <a href="<?php the_permalink(); ?>" class="event-link">Etkinliği Gör</a>
            </div>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else : ?>
      <p>Henüz etkinlik bulunamadı.</p>
    <?php endif; ?>
  </section>

</main>

<?php get_footer(); ?>
