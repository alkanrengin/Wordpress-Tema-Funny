<?php
/**
 * Product Card (Funny Theme)
 */
defined('ABSPATH') || exit;

global $product;

$event_date = get_field('etkinlik_tarihi');
$event_date_formatted = $event_date ? date_i18n('d F Y', strtotime($event_date)) : 'Tarih Belirtilmemiş';
?>

<div class="event-card">
  <div class="event-thumb">
    <a href="<?php the_permalink(); ?>">
      <?php if (has_post_thumbnail()) {
        the_post_thumbnail('medium_large');
      } else {
        echo '<img src="https://picsum.photos/400/250?blur=2" alt="Etkinlik Görseli">';
      } ?>
    </a>

    <!-- 🟣 Tarih Etiketi -->
    <div class="date-tag"><?php echo esc_html($event_date_formatted); ?></div>
  </div>

  <div class="event-details">
    <h3 class="event-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

    <!-- 💰 Fiyat -->
    <div class="event-price">
      <?php echo $product->get_price() ? '₺' . esc_html($product->get_price()) : 'Ücretsiz'; ?>
    </div>

    <a href="<?php the_permalink(); ?>" class="view-btn">Etkinliği Gör</a>
  </div>
</div>
