<?php
/**
 * Template: Organizer & Kullanıcı Paneli
 * Konum: woocommerce/myaccount/organizer-dashboard.php
 */

$current_user  = wp_get_current_user();
$is_organizer  = current_user_can('organizer');
// Kullanıcı ID
$user_id = $current_user->ID;

// Sadece organizatörler için gerekli alanları kontrol et
$organizer_kind = get_user_meta( $user_id, 'organizer_kind', true ); // bireysel / kurumsal
$tc_kimlik_no   = get_user_meta( $user_id, 'tc_kimlik_no', true );
$vergi_no       = get_user_meta( $user_id, 'vergi_no', true );
$company_name   = get_user_meta( $user_id, 'company_name', true );
$iban           = get_user_meta( $user_id, 'organizer_iban', true );

$has_required_info = false;

if ( $is_organizer ) {
  if ( $organizer_kind === 'bireysel' ) {
    // bireysel organizatör için zorunlu alanlar: tc + iban
    $has_required_info = ! empty( $tc_kimlik_no ) && ! empty( $iban );
  } elseif ( $organizer_kind === 'kurumsal' ) {
    // kurumsal organizatör için zorunlu alanlar: firma adı + vergi no + iban
    $has_required_info = ! empty( $company_name ) && ! empty( $vergi_no ) && ! empty( $iban );
  }
}

// Etkinlik oluşturabilir mi?
$can_create_events = ( $is_organizer && $has_required_info );


/**
 * Organizator satış / katılımcı / gelir verileri
 * - WooCommerce siparişlerinden, ürün yazarı = organizatör olacak şekilde toplar
 */
if ( ! function_exists( 'etkinliks_get_organizer_sales' ) && function_exists( 'wc_get_orders' ) ) {

  function etkinliks_get_organizer_sales( $organizer_id ) {

    $data = [
      'sales'          => [],
      'total_earnings' => 0,
      'attendees'      => [],
    ];

    if ( ! $organizer_id || ! class_exists( 'WooCommerce' ) ) {
      return $data;
    }

    // Tüm sipariş statülerini al (pending, processing, completed vs.)
    $order_statuses = array_keys( wc_get_order_statuses() );

    $orders = wc_get_orders( [
      'limit'  => 100,
      'status' => $order_statuses,
      'orderby'=> 'date',
      'order'  => 'DESC',
    ] );

    if ( empty( $orders ) ) {
      return $data;
    }

    $sales     = [];
    $attendees = [];
    $total     = 0;

    foreach ( $orders as $order ) {
      if ( ! $order instanceof WC_Order ) {
        continue;
      }

      foreach ( $order->get_items() as $item ) {
        /** @var WC_Order_Item_Product $item */
        $product_id = $item->get_product_id();
        if ( ! $product_id ) {
          continue;
        }

        // Ürünün yazarı bu organizatör mü?
        $author_id = (int) get_post_field( 'post_author', $product_id );
        if ( $author_id !== (int) $organizer_id ) {
          continue;
        }

        $event_title = get_the_title( $product_id );
        $qty         = $item->get_quantity();
        $line_total  = (float) $item->get_total();

        $order_id    = $order->get_id();
        $order_date  = $order->get_date_created();
        $date_str    = $order_date ? $order_date->date_i18n( 'd.m.Y H:i' ) : '';
        $status      = wc_get_order_status_name( $order->get_status() );
        $cust_name   = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        if ( ! $cust_name ) {
          $cust_name = $order->get_formatted_billing_full_name();
        }
        $cust_email  = $order->get_billing_email();

        $total += $line_total;

        $sales[] = [
          'order_id'      => $order_id,
          'order_number'  => $order->get_order_number(),
          'order_date'    => $date_str,
          'status'        => $status,
          'customer_name' => $cust_name,
          'customer_email'=> $cust_email,
          'product_id'    => $product_id,
          'event_title'   => $event_title,
          'qty'           => $qty,
          'line_total'    => $line_total,
        ];

        // Katılımcılar yapısı
        if ( ! isset( $attendees[ $product_id ] ) ) {
          $attendees[ $product_id ] = [
            'event_title' => $event_title,
            'buyers'      => [],
          ];
        }

        $attendees[ $product_id ]['buyers'][] = [
          'order_number'  => $order->get_order_number(),
          'customer_name' => $cust_name,
          'customer_email'=> $cust_email,
          'qty'           => $qty,
          'order_date'    => $date_str,
        ];
      }
    }

    $data['sales']          = $sales;
    $data['total_earnings'] = $total;
    $data['attendees']      = $attendees;

    return $data;
  }
}

$organizer_sales_data = [
  'sales'          => [],
  'total_earnings' => 0,
  'attendees'      => [],
];

if ( $is_organizer && function_exists( 'etkinliks_get_organizer_sales' ) ) {
  $organizer_sales_data = etkinliks_get_organizer_sales( $current_user->ID );
}

?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/organizer-dashboard.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/organizer-dashboard.css'); ?>">

<div class="dashboard-wrapper">

  <!-- 🔹 SIDEBAR SADE MENÜ -->
  <aside class="dashboard-sidebar">
    <ul class="dashboard-menu">
      <li class="active" data-tab="profil">👤 Profil</li>
      <li data-tab="etkinlikler">🎫 Etkinliklerim</li>
      <li data-tab="biletlerim">🎟️ Biletlerim</li>

      <?php if ( $is_organizer ) : ?>
        <li data-tab="satislarim">🧾 Satışlarım</li>
        <li data-tab="katilimcilar">👥 Katılımcılar</li>
        <li data-tab="gelirlerim">💸 Gelirlerim</li>
        <li data-tab="yeni-etkinlik" class="<?php echo $can_create_events ? '' : 'locked'; ?>">
    ➕ Yeni Etkinlik Ekle
    <?php if ( ! $can_create_events ) : ?>
      <span class="lock-tag">Kilitli</span>
    <?php endif; ?>
  </li>
  </li>
      <?php endif; ?>

      <li data-tab="mesajlar">💬 Mesajlar</li>
      <li data-tab="ayarlar">⚙️ Ayarlar</li>
      <li class="logout-item">
        <a href="<?php echo esc_url( site_url( '?custom-logout=1' ) ); ?>">🚪 Çıkış Yap</a>
      </li>
      <li class="logout-item">
        <a href="<?php echo esc_url( site_url() ); ?>"> Ana Sayfa</a>
      </li>
    </ul>
  </aside>

  <!-- 🔹 ANA İÇERİK -->
  <section class="dashboard-content">

    <!-- 👤 PROFİL -->
    <div class="tab-content active" id="profil">
      <div class="profile-card">

        <!-- Profil Fotoğrafı -->
        <?php
        $pp = get_user_meta( $current_user->ID, 'profile_picture', true );
        if ( $pp ) {
          echo wp_get_attachment_image( $pp, 'thumbnail', false, [ 'class' => 'custom-avatar' ] );
        } else {
          echo get_avatar( $current_user->ID, 120 );
        }
        ?>

        <!-- İsim Soyisim -->
        <h2>
          <?php
          echo esc_html( trim( $current_user->first_name . ' ' . $current_user->last_name ) ) ?: esc_html( $current_user->display_name );
          ?>
        </h2>

        <!-- Email -->
        <p class="email"><?php echo esc_html( $current_user->user_email ); ?></p>

        <!-- 📌 Hakkımda -->
        <div class="info-box">
          <h3>Hakkımda</h3>
          <p>
            <?php
            $bio = get_user_meta( $current_user->ID, 'description', true );
            echo $bio ? esc_html( $bio ) : 'Hakkımda bilgisi eklenmemiş.';
            ?>
          </p>
        </div>

        <!-- 📌 İletişim Bilgileri -->
        <div class="info-box">
          <h3>İletişim</h3>

          <p><strong>E-posta:</strong> <?php echo esc_html( $current_user->user_email ); ?></p>

          <p><strong>Telefon:</strong>
            <?php
            $phone = get_user_meta( $current_user->ID, 'phone', true );
            echo $phone ? esc_html( $phone ) : 'Telefon bilgisi eklenmemiş.';
            ?>
          </p>

          <p><strong>Adres:</strong>
            <?php
            $address = get_user_meta( $current_user->ID, 'address', true );
            echo $address ? esc_html( $address ) : 'Adres bilgisi eklenmemiş.';
            ?>
          </p>
        </div>

        <!-- Düzenleme Butonu -->
        <a href="#" data-tab="ayarlar" class="btn">Bilgilerimi Düzenle</a>

      </div>
    </div>

    <!-- 🎫 ETKİNLİKLER -->
    <div class="tab-content" id="etkinlikler">
      <h2>Etkinliklerim</h2>
      <?php
      $args   = [
        'post_type'      => 'product',
        'posts_per_page' => 6,
        'author'         => $current_user->ID,
      ];
      $events = new WP_Query( $args );
      if ( $events->have_posts() ) :
        echo '<div class="event-grid">';
        while ( $events->have_posts() ) :
          $events->the_post();
          global $product;
          ?>
          <div class="event-card">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'funny-event' ); ?>
              </a>
            <?php endif; ?>
            <div class="event-info">
              <h4><?php the_title(); ?></h4>
              <p><?php echo $product ? $product->get_price_html() : ''; ?></p>
              <a href="<?php the_permalink(); ?>" class="btn-small">Görüntüle</a>
            </div>
          </div>
          <?php
        endwhile;
        echo '</div>';
      else :
        echo '<p>Henüz etkinlik oluşturmadınız.</p>';
      endif;
      wp_reset_postdata();
      ?>
    </div>

    <!-- 🎟️ BİLETLERİM -->
    <div class="tab-content" id="biletlerim">
      <h2>Biletlerim</h2>
      <?php
      $tickets = function_exists( 'etkinliks_get_user_tickets' ) ? etkinliks_get_user_tickets() : [];

      if ( empty( $tickets ) ) :
        echo '<p>Henüz bir biletiniz bulunmuyor.</p>';
      else :
        echo '<div class="my-tickets-grid">';
        foreach ( $tickets as $ticket ) :
          $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode( $ticket['qr_data'] );
          ?>
          <div class="ticket-card-min">
            <h2 class="ticket-title-min"><?php echo esc_html( $ticket['event_title'] ); ?></h2>

            <div class="ticket-top-min">
              <div class="ticket-qr-wrap-min">
                <img src="<?php echo esc_url( $qr_url ); ?>" alt="QR Kod">
              </div>
              <div class="ticket-name-main-min"><?php echo esc_html( $ticket['customer_name'] ); ?></div>
            </div>

            <div class="ticket-info-min">
              <div class="row">
                <span class="label">Sipariş Kodu</span>
                <span class="value"><?php echo esc_html( $ticket['order_code'] ); ?></span>
              </div>
              <div class="row">
                <span class="label">Tarih</span>
                <span class="value"><?php echo esc_html( trim( $ticket['event_date'] . ' ' . $ticket['event_time'] ) ); ?></span>
              </div>
              <div class="row">
                <span class="label">Sıra/Koltuk</span>
                <span class="value"><?php echo esc_html( $ticket['seat_row'] . ' / ' . $ticket['seat_no'] ); ?></span>
              </div>
            </div>

            <div class="ticket-name-bottom-min"><?php echo esc_html( $ticket['customer_name'] ); ?></div>
          </div>
          <?php
        endforeach;
        echo '</div>';
      endif;
      ?>
    </div>

    <!-- 🧾 SATIŞLARIM (ORGANİZATÖR) -->
    <?php if ( $is_organizer ) : ?>
      <div class="tab-content" id="satislarim">
        <h2>Satışlarım</h2>

        <?php if ( empty( $organizer_sales_data['sales'] ) ) : ?>
          <p>Henüz herhangi bir satışınız bulunmuyor.</p>
        <?php else : ?>
          <div class="event-grid">
            <?php foreach ( $organizer_sales_data['sales'] as $sale ) : ?>
              <div class="event-card">
                <div class="event-info">
                  <h4><?php echo esc_html( $sale['event_title'] ); ?></h4>
                  <p><strong>Alıcı:</strong> <?php echo esc_html( $sale['customer_name'] ); ?></p>
                  <p><strong>E-posta:</strong> <?php echo esc_html( $sale['customer_email'] ); ?></p>
                  <p><strong>Adet:</strong> <?php echo esc_html( $sale['qty'] ); ?></p>
                  <p><strong>Tutar:</strong> <?php echo wc_price( $sale['line_total'] ); ?></p>
                  <p><strong>Tarih:</strong> <?php echo esc_html( $sale['order_date'] ); ?></p>
                  <p><strong>Durum:</strong> <?php echo esc_html( $sale['status'] ); ?></p>
                  <p><strong>Sipariş No:</strong> #<?php echo esc_html( $sale['order_number'] ); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 👥 KATILIMCILAR -->
      <div class="tab-content" id="katilimcilar">
        <h2>Katılımcılar</h2>

        <?php if ( empty( $organizer_sales_data['attendees'] ) ) : ?>
          <p>Henüz etkinlikleriniz için kayıtlı katılımcı bulunmuyor.</p>
        <?php else : ?>
          <?php foreach ( $organizer_sales_data['attendees'] as $product_id => $event_data ) : ?>
            <div class="info-box">
              <h3><?php echo esc_html( $event_data['event_title'] ); ?></h3>

              <?php if ( empty( $event_data['buyers'] ) ) : ?>
                <p>Bu etkinlik için henüz katılımcı yok.</p>
              <?php else : ?>
                <div class="participants-list">
                  <?php foreach ( $event_data['buyers'] as $buyer ) : ?>
                    <div class="row">
                      <span class="label">
                        <?php echo esc_html( $buyer['customer_name'] ); ?>
                        (<?php echo esc_html( $buyer['customer_email'] ); ?>)
                      </span>
                      <span class="value">
                        Adet: <?php echo esc_html( $buyer['qty'] ); ?> -
                        <?php echo esc_html( $buyer['order_date'] ); ?> -
                        #<?php echo esc_html( $buyer['order_number'] ?? '' ); ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- 💸 GELİRLERİM -->
      <div class="tab-content" id="gelirlerim">
        <h2>Gelirlerim</h2>

        <div class="info-box">
          <h3>Toplam Satış Geliri</h3>
          <p><strong><?php echo wc_price( $organizer_sales_data['total_earnings'] ); ?></strong></p>
          <p>Bu tutar, oluşturduğunuz etkinlikler üzerinden gerçekleşen bilet satışlarının toplamıdır.</p>
        </div>

        <?php if ( ! empty( $organizer_sales_data['sales'] ) ) : ?>
          <div class="info-box">
            <h3>Son Satışlar</h3>
            <?php
            $counter = 0;
            foreach ( $organizer_sales_data['sales'] as $sale ) :
              if ( $counter >= 5 ) {
                break;
              }
              $counter++;
              ?>
              <div class="row">
                <span class="label">
                  <?php echo esc_html( $sale['event_title'] ); ?> -
                  <?php echo esc_html( $sale['customer_name'] ); ?>
                </span>
                <span class="value">
                  <?php echo wc_price( $sale['line_total'] ); ?> /
                  <?php echo esc_html( $sale['order_date'] ); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <p class="note">
          İleride bu alana, Dokan üzerinden çekim talepleri (withdraw) ve detaylı ödeme geçmişi de eklenebilir.
        </p>
      </div>
    <?php endif; ?>

    <!-- ➕ YENİ ETKİNLİK -->
    <?php if ( isset( $_GET['success'] ) && $_GET['success'] == '1' ) : ?>
  <div class="alert-success">
    🎉 Etkinliğiniz başarıyla oluşturuldu!
  </div>
<?php endif; ?>

<?php if ( $is_organizer ) : ?>
  <div class="tab-content" id="yeni-etkinlik">
    <h2>Yeni Etkinlik Ekle</h2>

    <?php if ( ! $can_create_events ) : ?>

      <div class="alert-warning">
        ⚠ Etkinlik yükleyebilmek için önce organizatör bilgilerinizi tamamlamanız gerekiyor.
      </div>

      <ul class="locked-reasons">
        <?php if ( empty( $organizer_kind ) ) : ?>
          <li>• Organizatör türü (bireysel / kurumsal) seçin.</li>
        <?php endif; ?>

        <?php if ( $organizer_kind === 'bireysel' ) : ?>
          <?php if ( empty( $tc_kimlik_no ) ) : ?>
            <li>• TC kimlik numaranızı girin.</li>
          <?php endif; ?>
          <?php if ( empty( $iban ) ) : ?>
            <li>• Ödeme alacağınız IBAN bilgisini girin.</li>
          <?php endif; ?>
        <?php elseif ( $organizer_kind === 'kurumsal' ) : ?>
          <?php if ( empty( $company_name ) ) : ?>
            <li>• Firma adını girin.</li>
          <?php endif; ?>
          <?php if ( empty( $vergi_no ) ) : ?>
            <li>• Vergi kimlik numarasını girin.</li>
          <?php endif; ?>
          <?php if ( empty( $iban ) ) : ?>
            <li>• Ödeme alacağınız IBAN bilgisini girin.</li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <p>
        <strong>Not:</strong> Bu bilgileri <span data-tab="ayarlar" class="link-to-settings">Ayarlar</span> sekmesinden doldurabilirsiniz.
      </p>

    <?php else : ?>

      <p>Lütfen aşağıdaki formu doldurarak yeni etkinliğinizi oluşturun.</p>

      <form id="new-event-form" class="event-form" method="post" enctype="multipart/form-data">

        <div class="form-group full">
          <label>Etkinlik Adı</label>
          <input type="text" name="event_title" placeholder="Etkinlik adını giriniz" required>
        </div>

        <div class="form-group">
          <label>Etkinlik Kategorisi</label>
          <?php
          $dropdown = wp_dropdown_categories( [
            'taxonomy'         => 'product_cat',
            'hide_empty'       => false,
            'name'             => 'event_category',
            'show_option_none' => 'Kategori seçiniz',
            'option_none_value'=> '',
            'echo'             => false,
          ] );
          echo str_replace( '<select', '<select required', $dropdown );
          ?>
        </div>

        <div class="form-group">
          <label>Bilet Fiyatı (₺)</label>
          <input type="number" name="ticket_price" step="0.01" min="0" placeholder="0.00">
        </div>

        <div class="form-group">
          <label>Başlangıç Tarihi & Saati</label>
          <input type="datetime-local" name="start_date" min="<?php echo date( 'Y-m-d\TH:i' ); ?>" required>
        </div>

        <div class="form-group">
          <label>Bitiş Tarihi & Saati</label>
          <input type="datetime-local" name="end_date" min="<?php echo date( 'Y-m-d\TH:i' ); ?>" required>
        </div>

        <div class="form-group full">
          <label>Mekan / Adres</label>
          <input type="text" name="event_location" placeholder="Etkinliğin yapılacağı yer">
        </div>

        <div class="form-group full">
          <label>Açıklama</label>
          <textarea name="event_description" rows="4" placeholder="Etkinliğiniz hakkında bilgi verin..." required></textarea>
        </div>

        <div class="form-group">
          <label>Etkinlik Görseli / Afişi</label>
          <input type="file" name="event_image" accept="image/*">
        </div>

        <div class="form-group">
          <label>Kapasite</label>
          <input type="number" name="capacity" min="1" placeholder="Katılımcı sayısı">
        </div>

        <div class="form-group full">
          <label>Yaş Sınırı</label>
          <select name="age_limit">
            <option value="">Seçiniz</option>
            <option value="Yok">Yok</option>
            <option value="7+">7+</option>
            <option value="13+">13+</option>
            <option value="18+">18+</option>
          </select>
        </div>

        <div class="form-group full">
          <label>Etkinlik Kuralları</label>
          <textarea name="event_rules" rows="3" placeholder="Katılımcı kurallarını yazınız"></textarea>
        </div>

        <div class="form-group full agreement">
          <label>
            <input type="checkbox" name="agreement" required>
            <span>
              Etkinliği yükleyerek
              <a href="<?php echo site_url( '/sozlesme' ); ?>" target="_blank">Etkinlik-S Yayın Politikası ve Organizator Sözleşmesi</a>
              'ni kabul ediyorum.
            </span>
          </label>
        </div>

        <button type="submit" class="btn">Etkinliği Yayınla</button>
      </form>

    <?php endif; ?>
  </div>
<?php endif; ?>


    <!-- ⚙️ MESAJLAR -->
    <div class="tab-content" id="mesajlar">
      <h2>Mesajlar</h2>

      <div class="messages-wrapper">
        <!-- 🔹 SOL KONU LİSTESİ -->
        <aside class="conversation-list">

          <?php
          global $wpdb;
          $current_id = get_current_user_id();

          $conversations = $wpdb->get_results("
            SELECT 
              CASE WHEN sender_id = $current_id THEN receiver_id ELSE sender_id END AS other_user,
              MAX(created_at) as last_time,
              (SELECT message FROM wp_etkinliks_messages WHERE 
                  (sender_id = $current_id AND receiver_id = other_user)
                  OR (receiver_id = $current_id AND sender_id = other_user)
                  ORDER BY created_at DESC LIMIT 1) as last_message
            FROM wp_etkinliks_messages
            WHERE sender_id = $current_id OR receiver_id = $current_id
            GROUP BY other_user
            ORDER BY last_time DESC
          ");

          if ( $conversations ) :
            foreach ( $conversations as $conv ) :
              $user = get_user_by( 'id', $conv->other_user );
              if ( $user ) :
                ?>
                <div class="conversation-item" data-receiver="<?php echo esc_attr( $user->ID ); ?>">
                  <div class="avatar"><?php echo get_avatar( $user->ID, 40 ); ?></div>
                  <div class="conv-info">
                    <h4><?php echo esc_html( $user->display_name ); ?></h4>
                    <p><?php echo esc_html( wp_trim_words( $conv->last_message, 10 ) ); ?></p>
                  </div>
                  <span class="time"><?php echo date( 'H:i', strtotime( $conv->last_time ) ); ?></span>
                </div>
                <?php
              endif;
            endforeach;
          else :
            echo '<p>Henüz bir konuşmanız yok.</p>';
          endif;
          ?>
        </aside>

        <!-- 🔹 SAĞ MESAJ ALANI -->
        <section class="chat-area">
          <div class="chat-header">
            <h3 class="chat-title">Bir konuşma seçin</h3>
            <p class="chat-subtitle">Sağ tarafta mesajları görüntüleyebilirsiniz</p>
          </div>
          <div class="chat-body"></div>
          <div class="chat-footer">
            <input type="text" placeholder="Mesajınızı yazın..." />
            <button type="button" class="send-btn">📨</button>
          </div>
        </section>
      </div>
    </div>

    <!-- ⚙️ AYARLAR -->
    <?php if ( isset( $_GET['profile_updated'] ) ) : ?>
      <div class="alert-success">✔ Profil başarıyla güncellendi.</div>
    <?php endif; ?>

    <div class="tab-content" id="ayarlar">
      <h2>Hesap Bilgilerim</h2>
      <p>Aşağıdaki formdan kişisel bilgilerinizi güncelleyebilirsiniz.</p>

      <?php
      $user_id = get_current_user_id();
      $user    = get_userdata( $user_id );
      ?>

      <form method="post" enctype="multipart/form-data" class="profile-edit-form">

        <!-- Profil Fotoğrafı -->
        <div class="form-group full">
          <label>Profil Fotoğrafı</label>
          <img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" class="avatar-preview">
          <input type="file" name="avatar" accept="image/*">
        </div>

        <div class="form-group">
          <label>İsim</label>
          <input type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>">
        </div>

        <div class="form-group">
          <label>Soyisim</label>
          <input type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>">
        </div>

        <div class="form-group full">
          <label>Telefon</label>
          <input type="text" name="phone" value="<?php echo esc_attr( get_user_meta( $user_id, 'phone', true ) ); ?>">
        </div>

        <div class="form-group full">
          <label>Adres</label>
          <input type="text" name="address" value="<?php echo esc_attr( get_user_meta( $user_id, 'address', true ) ); ?>">
        </div>

        <div class="form-group full">
          <label>E-posta</label>
          <input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>">
        </div>
        <?php if ( $is_organizer ) : ?>
  <hr>
  <h3>Organizatör Bilgileri</h3>

  <?php
  // Ana rol zaten "organizer"
  $organizer_kind = get_user_meta( $user_id, 'organizer_kind', true ); // bireysel / kurumsal
  $tc_kimlik_no   = get_user_meta( $user_id, 'tc_kimlik_no', true );
  $iban           = get_user_meta( $user_id, 'organizer_iban', true );
  $vergi_no       = get_user_meta( $user_id, 'vergi_no', true );
  $company_name   = get_user_meta( $user_id, 'company_name', true );
  ?>

  <!-- 2. seviye: Organizatör tipi -->
  <div class="form-group full">
    <label>Organizatör Türü</label>
    <select name="organizer_kind" id="organizer_kind" required>
      <option value="">Seçiniz</option>
      <option value="bireysel" <?php selected( $organizer_kind, 'bireysel' ); ?>>Bireysel Organizatör</option>
      <option value="kurumsal" <?php selected( $organizer_kind, 'kurumsal' ); ?>>Kurumsal Organizatör</option>
    </select>
  </div>

  <!-- BİREYSEL ALANLAR (sadece bireysel organizatörler için) -->
  <div class="organizer-fields organizer-bireysel" style="<?php echo ( $organizer_kind === 'kurumsal' ) ? 'display:none;' : ''; ?>">
    <div class="form-group full">
      <label>TC Kimlik No</label>
      <input type="text" name="tc_kimlik_no" value="<?php echo esc_attr( $tc_kimlik_no ); ?>" placeholder="11 haneli T.C. kimlik numaranız">
    </div>

    <div class="form-group">
      <label>Kimlik Ön Yüz Fotoğrafı</label>
      <input type="file" name="id_front">
    </div>

    <div class="form-group">
      <label>Kimlik Arka Yüz Fotoğrafı</label>
      <input type="file" name="id_back">
    </div>
  </div>

  <!-- KURUMSAL ALANLAR (sadece kurumsal organizatörler için) -->
  <div class="organizer-fields organizer-kurumsal" style="<?php echo ( $organizer_kind === 'kurumsal' ) ? '' : 'display:none;'; ?>">
    <div class="form-group full">
      <label>Firma Adı</label>
      <input type="text" name="company_name" value="<?php echo esc_attr( $company_name ); ?>" placeholder="Şirket / Kurum adı">
    </div>

    <div class="form-group full">
      <label>Vergi Kimlik No</label>
      <input type="text" name="vergi_no" value="<?php echo esc_attr( $vergi_no ); ?>" placeholder="Vergi kimlik numarası">
    </div>

    <div class="form-group">
      <label>Vergi Levhası Ön Yüz Fotoğrafı</label>
      <input type="file" name="tax_doc_front">
    </div>

    <div class="form-group">
      <label>Vergi Levhası Arka Yüz Fotoğrafı</label>
      <input type="file" name="tax_doc_back">
    </div>
  </div>

  <!-- Ortak alan: IBAN (hem bireysel hem kurumsal için zorunlu) -->
  <div class="form-group full">
    <label>Ödeme Hesabı / IBAN</label>
    <input type="text" name="organizer_iban" value="<?php echo esc_attr( $iban ); ?>" placeholder="TR...">
  </div>

  <p class="note">
    Bu alanlar sadece <strong>organizatör</strong> hesapları için geçerlidir.
    Normal kullanıcı (müşteri) giriş yaptığında görünmez.
  </p>
<?php endif; ?>


        <button type="submit" name="save_profile" class="btn">Bilgileri Kaydet</button>
      </form>
    </div>

  </section>
</div>

<?php if ( isset( $_GET['msg_to'] ) ) : ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const msgTab = document.querySelector('[data-tab="mesajlar"]');
      const allTabs = document.querySelectorAll(".dashboard-menu li");
      const allContents = document.querySelectorAll(".tab-content");

      allTabs.forEach(t => t.classList.remove("active"));
      msgTab.classList.add("active");

      allContents.forEach(c => c.classList.remove("active"));
      document.getElementById("mesajlar").classList.add("active");

      const receiverId = <?php echo intval( $_GET['msg_to'] ); ?>;
      if (typeof loadMessages === "function") {
        loadMessages(receiverId);
      }
      window.selectedReceiver = receiverId;
    });
  </script>
<?php endif; ?>

<?php if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], ['etkinlikler','biletlerim','satislarim','katilimcilar','gelirlerim'], true ) ) : ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const targetTabSlug = "<?php echo esc_js( $_GET['tab'] ); ?>";
      const targetTab = document.querySelector('[data-tab="' + targetTabSlug + '"]');
      const allTabs = document.querySelectorAll(".dashboard-menu li");
      const allContents = document.querySelectorAll(".tab-content");

      if (targetTab) {
        allTabs.forEach(t => t.classList.remove("active"));
        targetTab.classList.add("active");
        allContents.forEach(c => c.classList.remove("active"));
        const content = document.getElementById(targetTabSlug);
        if (content) content.classList.add("active");
      }
    });
  </script>
<?php endif; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const kindSelect  = document.getElementById("organizer_kind");
  if (!kindSelect) return; // müşteri ise zaten yok

  const bireyselBox = document.querySelector(".organizer-bireysel");
  const kurumsalBox = document.querySelector(".organizer-kurumsal");

  function toggleOrganizerKind() {
    const val = kindSelect.value;
    if (val === "bireysel") {
      if (bireyselBox) bireyselBox.style.display = "block";
      if (kurumsalBox) kurumsalBox.style.display = "none";
    } else if (val === "kurumsal") {
      if (bireyselBox) bireyselBox.style.display = "none";
      if (kurumsalBox) kurumsalBox.style.display = "block";
    } else {
      if (bireyselBox) bireyselBox.style.display = "none";
      if (kurumsalBox) kurumsalBox.style.display = "none";
    }
  }

  kindSelect.addEventListener("change", toggleOrganizerKind);
  toggleOrganizerKind(); // sayfa açılışında mevcut değere göre set et
});
</script>


<script>
  var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
  var currentUserId = <?php echo get_current_user_id(); ?>;
</script>

<script src="<?php echo get_template_directory_uri(); ?>/assets/js/dashboard.js?v=<?php echo filemtime( get_template_directory() . '/assets/js/dashboard.js' ); ?>" defer></script>
