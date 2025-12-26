<?php
/**
 * Single Product Template - Funny Theme (Etkinlik-S)
 */

defined('ABSPATH') || exit;

get_header();
?>
<?php funny_breadcrumb(); ?>
<!-- 🎨 Özel Sayfa CSS (cache kırma ile) -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/single-product.css?ver=<?php echo time(); ?>">

<main class="single-event">
  <div class="container">

    <!-- 🎤 ETKİNLİK GÖRSELİ -->
    <div class="event-hero">
      <?php
      if (has_post_thumbnail()) {
        the_post_thumbnail('large', ['class' => 'event-image']);
      } else {
        echo '<img src="https://picsum.photos/800/500?blur=2" alt="Etkinlik Görseli" class="event-image">';
      }
      ?>
    </div>

    <!-- 💎 ETKİNLİK BİLGİ KARTI -->
    <div class="event-info-card">
      <div class="glass-wrapper"><!-- 🌫 Cam efekt katmanı -->

        <h1 class="event-title"><?php the_title(); ?></h1>

        <!-- 🗓️ Tarih / Yer / Saat -->
        <div class="event-meta">
          <?php
          $event_date_raw  = get_field('etkinlik_baslangic');
          $event_place = get_field('etkinlik_yeri');
          if ($event_date_raw) {
            $parts = explode('T', $event_date_raw);
            $event_date = isset($parts[0]) ? date('d.m.Y', strtotime($parts[0])) : '';
            $event_time = isset($parts[1]) ? date('H:i', strtotime($parts[1])) : '';
          } else {
          $event_date = 'Belirtilmemiş';
          $event_time = 'Belirtilmemiş';
           }

          ?>
          <div class="meta-box">
            <span>Tarih</span>
            <p><?php echo $event_date ? esc_html($event_date) : 'Belirtilmemiş'; ?></p>
          </div>
          <div class="meta-box">
            <span>Yer</span>
            <p><?php echo $event_place ? esc_html($event_place) : 'Belirtilmemiş'; ?></p>
          </div>
          <div class="meta-box">
            <span>Saat</span>
            <p><?php echo $event_time ? esc_html($event_time) : 'Belirtilmemiş'; ?></p>
          </div>
        </div>

        <!-- 💰 FİYAT -->
        <div class="event-price">
          <?php
          global $product;

          if (!$product || !is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
          }

          if ($product) {
            echo $product->get_price_html();
          } else {
            echo '<span>Fiyat bilgisi bulunamadı</span>';
          }
          ?>
        </div>
        <?php
// 💡 Kullanıcının bu etkinlik için bileti var mı?
$ticket_for_this = function_exists('etkinliks_get_user_ticket_for_product')
    ? etkinliks_get_user_ticket_for_product(get_the_ID())
    : null;
?>


        <!-- 🛒 BUTONLAR -->
        <div class="event-buttons">
            <?php
  // 🎯 Etkinlik tarihi kontrolü
  $event_date_raw = get_field('etkinlik_baslangic');
  $event_timestamp = $event_date_raw ? strtotime($event_date_raw) : 0;
  $now = current_time('timestamp');

  if ($event_timestamp && $event_timestamp < $now) {
    // Etkinlik tarihi geçmişse
    echo '<button class="btn disabled-btn" disabled>Etkinlik Tarihi Geçmiş</button>';
  } else {
    // Geçerli etkinlikler için normal sepete ekle
    woocommerce_template_single_add_to_cart();
  }
  ?>

              <?php
                $organizer_id = get_post_field('post_author', get_the_ID());
                if (is_user_logged_in()) :
                  $panel_url = wc_get_account_endpoint_url('my-account/user-dashboard'); // senin panel URL’in
                  $msg_link = add_query_arg('msg_to', $organizer_id, $panel_url);
                ?>
                  <a href="<?php echo esc_url($msg_link); ?>" class="btn black-btn ask-btn">
                    Organizatöre Soru Sor
                  </a>
                <?php else : ?>
                  <a href="<?php echo esc_url(site_url('/giris'));  ?>" class="btn ask-btn">
                    Giriş Yaparak Soru Sor
                  </a>
                <?php endif; ?>
                
             
                <button type="button"
                  class="btn ticket-btn"
                    id="view-ticket-btn">
                      Biletimi Gör
                </button>

        </div>

      </div><!-- /glass-wrapper -->
    </div><!-- /event-info-card -->

    <!-- 💬 AÇIKLAMA -->
    <section class="event-description">
  <h2>Etkinlik Hakkında</h2>

  <?php
  // Organizator bilgisi
  $organizer_id   = get_post_field('post_author', get_the_ID());
  $first_name     = get_the_author_meta('first_name', $organizer_id);
  $last_name      = get_the_author_meta('last_name', $organizer_id);
  $display_name   = trim($first_name . ' ' . $last_name);
  if (empty($display_name)) {
    $display_name = get_the_author_meta('display_name', $organizer_id);
  }

  // Organizator türü (bireysel / kurumsal)
  $organizer_kind = get_user_meta($organizer_id, 'organizer_kind', true);
  if ($organizer_kind === 'bireysel') {
    $kind_label = 'Bireysel Organizatör';
  } elseif ($organizer_kind === 'kurumsal') {
    $kind_label = 'Kurumsal Organizatör';
  } else {
    $kind_label = '';
  }

  // Organizator profil linki
  $panel_url = wc_get_account_endpoint_url('my-account/user-dashboard');
  $msg_link  = add_query_arg('msg_to', $organizer_id, $panel_url);
  ?>

  <div class="organizer-info">
    <p>
      <strong>Organizatör:</strong>
      <a href="<?php echo esc_url($msg_link); ?>">
        <?php echo esc_html($display_name); ?>
      </a>
      <?php if ($kind_label) : ?>
        <span class="org-kind">(<?php echo esc_html($kind_label); ?>)</span>
      <?php endif; ?>
    </p>
  </div>

  <div class="desc-text">
    <?php the_content(); ?>
  </div>
</section>


<!-- 🗺️ KONUM -->
<?php
$map_iframe = get_field('etkinlik_haritasi');
$event_place = get_post_meta(get_the_ID(), 'etkinlik_yeri', true);

// Eğer ACF boşsa otomatik Google Maps oluştur
if (!$map_iframe && $event_place) {
    $map_iframe = '<iframe 
        src="https://www.google.com/maps?q=' . urlencode($event_place) . '&output=embed"
        width="100%" height="380" style="border:0;" 
        allowfullscreen="" loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade"></iframe>';
}
?>




<section class="event-location">
  <h2>Etkinlik Konumu</h2>
  <div class="map-wrapper">
    <?php echo $map_iframe; ?>
  </div>
</section>


  </div>
</main>
<?php
$qr_data = 'ES-DEMO-' . get_current_user_id() . '-' . get_the_ID();
$qr_url  = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qr_data);
?>

<div class="ticket-modal" id="ticket-modal">
  <div class="ticket-wrapper">
    <button class="ticket-close">×</button>

    <div class="ticket-card-min">
      <h2 class="ticket-title-min"><?php the_title(); ?></h2>

      <div class="ticket-top-min">
        <div class="ticket-qr-wrap-min">
          <img src="<?php echo esc_url($qr_url); ?>" alt="QR Kod">
        </div>

        <div class="ticket-name-main-min">
          <?php echo esc_html(wp_get_current_user()->display_name ?: 'Kullanıcı'); ?>
        </div>
      </div>

      <div class="ticket-info-min">
        <div class="row">
          <span class="label">Sipariş Kodu</span>
          <span class="value">DEMO-1234</span>
        </div>
        <div class="row">
          <span class="label">Tarih</span>
          <span class="value"><?php echo date('d.m.Y H:i'); ?></span>
        </div>
        <div class="row">
          <span class="label">Sıra/Koltuk</span>
          <span class="value">3 / 14</span>
        </div>
      </div>

      <div class="ticket-name-bottom-min">
        <?php echo esc_html(wp_get_current_user()->display_name ?: 'Kullanıcı'); ?>
      </div>
    </div>
  </div>
</div>

<script>var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";</script>
<script>
  document.addEventListener("click", function(e) {
  if (e.target.closest(".ask-organizer")) {
    const organizerId = e.target.closest(".ask-organizer").dataset.organizer;
    const question = prompt("Organizatöre mesajınızı yazın:");

    if (!question) return;

    fetch(ajaxurl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "etkinliks_send_message",
        receiver_id: organizerId,
        message: question,
      }),
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("Mesajınız organizatöre gönderildi 🎉");
      } else {
        alert("Bir hata oluştu: " + data.data.message);
      }
    })
    .catch(() => alert("Bir hata oluştu. Lütfen tekrar deneyin."));
  }
});
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("ticket-modal");
  const openBtn = document.getElementById("view-ticket-btn");
  const closeBtn = modal ? modal.querySelector(".ticket-close") : null;

  if (!modal || !openBtn) return;

  openBtn.addEventListener("click", function () {
    modal.classList.add("is-open");
    document.body.style.overflow = "hidden";
  });

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      modal.classList.remove("is-open");
      document.body.style.overflow = "";
    });
  }

  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("is-open");
      document.body.style.overflow = "";
    }
  });
});


</script>


<?php get_footer(); ?>
