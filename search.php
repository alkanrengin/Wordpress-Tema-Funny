<?php
/* Template Name: Search Results */
echo '<!-- 🔍 Search.php aktif -->';
get_header();
?>

<div class="page-wrapper">

  <main class="search-results-page">
    <div class="container">

      <!-- Başlık -->
      <div class="search-header">
        <h2 class="page-title">Arama Sonuçları</h2>
        <?php if ( get_search_query() ) : ?>
          <p class="search-subtitle">
            "<?php echo esc_html( get_search_query() ); ?>" için <?php echo $wp_query->found_posts; ?> sonuç bulundu.
          </p>
        <?php endif; ?>
      </div>

      <!-- Sonuçlar -->
      <div class="event-grid">
        <?php if ( have_posts() ) : ?>
          <?php while ( have_posts() ) : the_post(); ?>
            <div class="event-card">
              <a href="<?php the_permalink(); ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                  <?php the_post_thumbnail('medium'); ?>
                <?php else : ?>
                  <img src="https://via.placeholder.com/300x173" alt="<?php the_title(); ?>">
                <?php endif; ?>
                <div class="event-info">
                  <h3><?php the_title(); ?></h3>
                  <?php if ( class_exists( 'WooCommerce' ) ) :
                    global $product;
                    if ( $product && $product->get_price() ) :
                  ?>
                    <p class="price"><?php echo wc_price( $product->get_price() ); ?></p>
                  <?php endif; endif; ?>
                </div>
              </a>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <p class="no-results">Sonuç bulunamadı.</p>
        <?php endif; ?>
      </div><!-- /.event-grid -->

    </div><!-- /.container -->
  </main>

</div><!-- /.page-wrapper -->

<?php get_footer(); ?>
