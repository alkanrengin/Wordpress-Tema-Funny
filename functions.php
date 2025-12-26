<?php
/**
 * Funny Theme Functions
 * Yazar: Rengin
 * Tema: Funny
 */
// === IYZICO ÖDEME İŞLEMİ ===

// 🔹 Namespace tanımları en üste taşınmalı (global alanda olmalı)
use Iyzipay\Options;
use Iyzipay\Request\CreatePaymentRequest;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\Currency;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentChannel;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Model\BasketItemType;

// 🔹 Ödeme işlevi
add_action('init', function () {
  if (isset($_POST['card_number'])) {

    require_once get_template_directory() . '/includes/iyzipay-php/IyzipayBootstrap.php';
    \IyzipayBootstrap::init();

    // 🔹 namespace'leri closure içinde böyle kullanamayız, o yüzden başına "\" ekliyoruz.
    $options = new \Iyzipay\Options();
    $options->setBaseUrl('https://sandbox-api.iyzipay.com'); // API KEY'in
    $options->setSecretKey('sandbox-yyy'); // SECRET KEY'in
    $options->setBaseUrl('https://sandbox-api.iyzipay.com');

    $request = new \Iyzipay\Request\CreatePaymentRequest();
    $request->setLocale(\Iyzipay\Model\Locale::TR);
    $request->setConversationId(rand(10000, 99999));
    $request->setPrice("550.00");
    $request->setPaidPrice("550.00");
    $request->setCurrency(\Iyzipay\Model\Currency::TL);
    $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

    // ✅ KART NESNESİNİ TANIMLADIK
    $paymentCard = new \Iyzipay\Model\PaymentCard();
    $paymentCard->setCardHolderName($_POST['card_name']);
    $paymentCard->setCardNumber(str_replace(' ', '', $_POST['card_number']));

    // MM/YY ayrıştır
    if (!empty($_POST['expiry']) && strpos($_POST['expiry'], '/') !== false) {
      [$month, $year] = explode('/', $_POST['expiry']);
      $month = trim($month);
      $year = trim($year);
    } else {
      $month = '01';
      $year = '30';
    }

    // 🔹 HATA BURADAYDI → $paymentCard daha önce tanımlı değilmişti
    $paymentCard->setExpireMonth($month);
    $paymentCard->setExpireYear($year);
    $paymentCard->setCvc($_POST['cvv']);
    $paymentCard->setRegisterCard(0);
    $request->setPaymentCard($paymentCard);

    // 🔹 KULLANICI
    $buyer = new \Iyzipay\Model\Buyer();
    $buyer->setId(get_current_user_id() ?: rand(1000, 9999));
    $buyer->setName($_POST['first_name']);
    $buyer->setSurname($_POST['last_name']);
    $buyer->setEmail($_POST['email']);
    $buyer->setGsmNumber($_POST['phone']);
    $buyer->setIdentityNumber("11111111111");
    $buyer->setRegistrationAddress("Türkiye");
    $buyer->setIp($_SERVER['REMOTE_ADDR']);
    $request->setBuyer($buyer);

    // 🔹 ÜRÜN
    $basketItem = new \Iyzipay\Model\BasketItem();
    $basketItem->setId("ETKINLIK001");
    $basketItem->setName("Etkinlik Bileti");
    $basketItem->setCategory1("Etkinlik");
    $basketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
    $basketItem->setPrice("550.00");
    $request->setBasketItems([$basketItem]);

    // 🔹 ÖDEME
    $payment = \Iyzipay\Model\Payment::create($request, $options);

    if ($payment->getStatus() === 'success') {
      wp_redirect(site_url('/tesekkurler/'));
      exit;
    } else {
      echo '<div style="color:red;padding:20px;">❌ Ödeme Başarısız: ' . esc_html($payment->getErrorMessage()) . '</div>';
    }
  }
});




/* === ÇEVİRİLER === */
add_action('after_setup_theme', function() {
    load_theme_textdomain('funny', get_template_directory() . '/languages');
});

/* === TEMA DESTEKLERİ === */
add_action('after_setup_theme', function() {
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(['primary' => __('Ana Menü', 'funny')]);
    add_theme_support('woocommerce');
});

/* === CSS & JS === */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('funny-style', get_stylesheet_uri(), [], '1.0', 'all');

    wp_enqueue_style(
        'funny-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        [],
        '6.5.0'
    );

    wp_enqueue_style(
        'funny-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap',
        [],
        null
    );

    wp_enqueue_script(
        'funny-script',
        get_template_directory_uri() . '/assets/js/script.js',
        [],
        '1.0',
        true
    );

    // Tekil ürün sayfası
    if (is_product()) {
        wp_enqueue_style(
            'funny-single-product',
            get_template_directory_uri() . '/assets/css/single-product.css',
            ['funny-style'],
            filemtime(get_template_directory() . '/assets/css/single-product.css'),
            'all'
        );
    }

    // Ürün arşiv sayfası
    if (is_shop() || is_product_category() || is_product_taxonomy()) {
        wp_enqueue_style(
            'funny-archive-css',
            get_template_directory_uri() . '/assets/css/archive-product.css',
            ['funny-style'],
            filemtime(get_template_directory() . '/assets/css/archive-product.css'),
            'all'
        );
    }
});

/* === ACF VARSA === */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => 'Tema Ayarları',
        'menu_title' => 'Tema Ayarları',
        'menu_slug'  => 'tema-ayarlar',
        'capability' => 'edit_posts',
        'redirect'   => false,
    ]);
}

/* === RESİM BOYUTLARI === */
add_image_size('funny-event', 400, 300, true);
add_image_size('funny-slider', 1600, 600, true);

/* === HEADER TEMİZLİĞİ === */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'rest_output_link_wp_head');

/* === WOOCOMMERCE SAYFA BAŞLIĞINI GİZLE === */
add_filter('woocommerce_show_page_title', '__return_false');

/* === ACF YOKSA GÜVENLİ KALKAN === */
if (!function_exists('get_field')) {
    function get_field($name, $id = null) {
        return null;
    }
}

/* === BREADCRUMB === */
if (!function_exists('funny_breadcrumb')) {
  function funny_breadcrumb() {
    if (is_front_page()) return;

    echo '<nav class="funny-breadcrumb">';

    // Ana sayfa
    echo '<a href="' . home_url() . '">Ana Sayfa</a>';

    // Ürün Detay Sayfası
    if (is_singular('product')) {

      // Ürün kategorisini al
      $categories = get_the_terms(get_the_ID(), 'product_cat');

      echo ' <span class="divider">›</span> ';

      if (!empty($categories) && !is_wp_error($categories)) {
        // İlk kategoriyi al
        $cat = $categories[0];
        echo '<a href="' . get_term_link($cat) . '">' . esc_html($cat->name) . '</a>';
      } else {
        // Yedek
        echo '<a href="' . get_permalink(wc_get_page_id('shop')) . '">Etkinlikler</a>';
      }

      echo ' <span class="divider">›</span> ';
      echo '<span class="current">' . get_the_title() . '</span>';
    }

    // Sayfa (Page)
    elseif (is_page()) {
      echo ' <span class="divider">›</span> ';
      echo '<span class="current">' . get_the_title() . '</span>';
    }

    echo '</nav>';
  }
}


/* === 🎤 ORGANİZATÖR & KULLANICI PANELİ === */

// 1️⃣ Endpoint oluştur
function funny_register_user_dashboard_endpoint() {
    add_rewrite_endpoint('user-dashboard', EP_PAGES);
}
add_action('init', 'funny_register_user_dashboard_endpoint');

// 2️⃣ Query var ekle
function funny_add_user_dashboard_query_var($vars) {
    $vars[] = 'user-dashboard';
    return $vars;
}
add_filter('query_vars', 'funny_add_user_dashboard_query_var');

// 3️⃣ Menüye ekle
function funny_add_user_dashboard_menu_item($items) {
    $logout = $items['customer-logout'];
    unset($items['customer-logout']);

    $items['user-dashboard'] = __('Profil Paneli', 'funny');
    $items['customer-logout'] = $logout;

    return $items;
}
add_filter('woocommerce_account_menu_items', 'funny_add_user_dashboard_menu_item');

// 4️⃣ İçeriği yükle (DOĞRU DİZİN)
function funny_render_user_dashboard_content() {
    wc_get_template(
        'myaccount/organizer-dashboard.php',
        [],
        '',
        get_template_directory() . '/woocommerce/'
    );
}
add_action('woocommerce_account_user-dashboard_endpoint', 'funny_render_user_dashboard_content');

// 5️⃣ CSS & JS Enqueue
add_action('wp_enqueue_scripts', function() {
    global $wp_query;
    if (is_account_page() && isset($wp_query->query_vars['user-dashboard'])) {

        wp_enqueue_style(
            'funny-dashboard',
            get_template_directory_uri() . '/assets/css/organizer-dashboard.css',
            ['funny-style'],
            filemtime(get_template_directory() . '/assets/css/organizer-dashboard.css')
        );

        wp_enqueue_script(
            'funny-dashboard-js',
            get_template_directory_uri() . '/assets/js/dashboard.js',
            [],
            filemtime(get_template_directory() . '/assets/js/dashboard.js'),
            true
        );
    }
});

// 6️⃣ Manuel yönlendirme (son çare)
add_action('template_redirect', function() {
    if (strpos($_SERVER['REQUEST_URI'], 'my-account/user-dashboard') !== false) {
        if (!is_user_logged_in()) {
            wp_redirect(wc_get_page_permalink('myaccount'));
            exit;
        }
        status_header(200);
        nocache_headers();
        include get_template_directory() . '/woocommerce/myaccount/organizer-dashboard.php';
        exit;
    }
});
/**
 * 👥 Organizatör Rolü — Sadece kendi paneline erişim
 */
add_action('init', function() {
    // Eğer rol yoksa oluştur
    if (!get_role('organizer')) {
        add_role('organizer', 'Organizatör', [
            'read'                   => true,
            'edit_posts'             => true,
            'upload_files'           => true,
            'publish_posts'          => true,
            'delete_posts'           => true,
            'edit_published_posts'   => true,
            'delete_published_posts' => true,
            'edit_products'          => true,
            'publish_products'       => true,
            'delete_products'        => true,
            'read_product'           => true,
            'edit_product'           => true,
            'delete_product'         => true,
            'read_private_products'  => false,
            'edit_others_products'   => false, // sadece kendi ürünleri
        ]);
    }
});

/**
 * 🚫 Organizatörlerin wp-admin erişimini engelle
 */
add_action('admin_init', function() {
    $user = wp_get_current_user();

    if (in_array('organizer', (array) $user->roles) && !defined('DOING_AJAX')) {
        wp_redirect(home_url('/my-account/user-dashboard/'));
        exit;
    }
});
/**
 * 🧱 Admin bar'ı gizle (Organizatörler için)
 */
add_filter('show_admin_bar', function($show) {
    if (current_user_can('organizer')) {
        return false;
    }
    return $show;
});
// 🔐 Özel logout endpoint fix
add_action('init', function() {
    if (isset($_GET['custom-logout'])) {
        wp_logout();

        // İstersen burayı login sayfasına yönlendirebilirsin:
        wp_safe_redirect(home_url());
        exit;
    }
});
// 🔹 Giriş yönlendirmesini özel sayfaya al
add_action('template_redirect', function() {
    // Kullanıcı giriş yapmamışsa ve giriş yap sayfası dışında bir yerdeyse
    if (is_account_page() && !is_user_logged_in()) {
        wp_safe_redirect(site_url('/giris')); // 🔸 burayı kendi giriş sayfa URL’inle değiştir
        exit;
    }
});
// 🛒 WooCommerce Session Fix (Etkinlik-S)
add_action('init', function() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
});

// WooCommerce session handler manual start
add_action('woocommerce_init', function() {
    if (class_exists('WC_Session_Handler') && WC()->session === null) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
});
// === ORGANIZER PANELİNDEN YENİ ETKİNLİK EKLEME ===
add_action('init', function() {
  if (isset($_POST['event_title']) && isset($_POST['agreement'])) {

    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    $title   = sanitize_text_field($_POST['event_title']);
    $desc    = sanitize_textarea_field($_POST['event_description']);
    $price   = floatval($_POST['ticket_price']);
    $cat_id  = intval($_POST['event_category']);
    $start   = sanitize_text_field($_POST['start_date']);
    $end     = sanitize_text_field($_POST['end_date']);
    $location= sanitize_text_field($_POST['event_location']);
    $capacity= sanitize_text_field($_POST['capacity']);
    $age     = sanitize_text_field($_POST['age_limit']);
    $rules   = sanitize_textarea_field($_POST['event_rules']);

    // Ürün oluştur
    $new_event = [
      'post_title'   => $title,
      'post_content' => $desc,
      'post_status'  => 'publish',
      'post_author'  => $user_id,
      'post_type'    => 'product',
    ];
    $post_id = wp_insert_post($new_event);

    if ($post_id) {
      wp_set_post_terms($post_id, [$cat_id], 'product_cat');

      update_post_meta($post_id, '_price', $price);
      update_post_meta($post_id, '_regular_price', $price);
      update_post_meta($post_id, '_stock', $capacity);
      update_post_meta($post_id, 'etkinlik_baslangic', $start);
      update_post_meta($post_id, 'etkinlik_bitis', $end);
      update_post_meta($post_id, 'etkinlik_yeri', $location);
      update_post_meta($post_id, 'yas_siniri', $age);
      update_post_meta($post_id, 'etkinlik_kurallari', $rules);

      // Görsel yükleme
      if (!empty($_FILES['event_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['event_image'], ['test_form' => false]);
        if (isset($upload['file'])) {
          $file_type = wp_check_filetype(basename($upload['file']), null);
          $attachment = [
            'post_mime_type' => $file_type['type'],
            'post_title'     => sanitize_file_name($_FILES['event_image']['name']),
            'post_content'   => '',
            'post_status'    => 'inherit'
          ];
          $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
          $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
          wp_update_attachment_metadata($attach_id, $attach_data);
          set_post_thumbnail($post_id, $attach_id);
        }
      }

      // 📍 Başarılı ekleme sonrası yönlendirme
ob_clean(); // olası header çakışmalarını engeller
wp_safe_redirect('https://etkinlik-s.com/my-account/user-dashboard/?tab=etkinlikler');
exit;

    }
  }
});
// === MESAJ TABLOSU OLUŞTURMA ===
function etkinliks_create_message_table() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'etkinliks_messages';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id BIGINT(20) UNSIGNED NOT NULL,
    receiver_id BIGINT(20) UNSIGNED NOT NULL,
    event_id BIGINT(20) UNSIGNED DEFAULT 0,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
add_action('after_switch_theme', 'etkinliks_create_message_table');
// === MESAJ GÖNDERME ===
add_action('wp_ajax_etkinliks_send_message', 'etkinliks_send_message');
add_action('wp_ajax_nopriv_etkinliks_send_message', 'etkinliks_send_message');

function etkinliks_send_message() {
  global $wpdb;
  $table = $wpdb->prefix . 'etkinliks_messages';

  $sender_id = get_current_user_id();
  $receiver_id = intval($_POST['receiver_id']);
  $event_id = intval($_POST['event_id']);
  $message = sanitize_textarea_field($_POST['message']);

  if (!$sender_id || !$receiver_id || empty($message)) {
    wp_send_json_error(['message' => 'Eksik bilgi gönderildi.']);
  }

  $wpdb->insert($table, [
    'sender_id' => $sender_id,
    'receiver_id' => $receiver_id,
    'event_id' => $event_id,
    'message' => $message,
  ]);

  wp_send_json_success(['message' => 'Mesaj gönderildi.']);
}

// === MESAJLARI ÇEKME ===
add_action('wp_ajax_etkinliks_get_messages', 'etkinliks_get_messages');
add_action('wp_ajax_nopriv_etkinliks_get_messages', 'etkinliks_get_messages');

function etkinliks_get_messages() {
  global $wpdb;
  $table = $wpdb->prefix . 'etkinliks_messages';

  $current_user = get_current_user_id();
  $receiver_id = intval($_POST['receiver_id']);

  if (!$current_user || !$receiver_id) {
    wp_send_json_error(['message' => 'Eksik bilgi.']);
  }

  $messages = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT * FROM $table 
       WHERE (sender_id = %d AND receiver_id = %d)
       OR (sender_id = %d AND receiver_id = %d)
       ORDER BY created_at ASC",
      $current_user, $receiver_id, $receiver_id, $current_user
    )
  );

  wp_send_json_success($messages);
}

add_action('init', function () {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $name = sanitize_text_field($_POST['adsoyad'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $user_type = sanitize_text_field($_POST['user_type'] ?? 'customer');
    $org_type = sanitize_text_field($_POST['orgType'] ?? '');

    // Eğer e-posta zaten varsa
    if (email_exists($email)) {
      echo '<div class="alert error" style="color:red;text-align:center;margin-top:15px;">❌ Bu e-posta zaten kayıtlı.</div>';
      return;
    }

    // Kullanıcı oluştur
    $user_id = wp_create_user($email, $password, $email);

    if (!is_wp_error($user_id)) {
      wp_update_user(['ID' => $user_id, 'display_name' => $name]);
      update_user_meta($user_id, 'phone', $phone);
      update_user_meta($user_id, 'org_type', $org_type);

      // Rol belirle
      $user = new WP_User($user_id);
      if ($user_type === 'organizer') {
        $user->set_role('organizer');
      } else {
        $user->set_role('customer');
      }

      // Oturum aç
      wp_set_current_user($user_id);
      wp_set_auth_cookie($user_id);
      wp_redirect(site_url('my-account/user-dashboard'));
      exit;
    } else {
      echo '<div class="alert error" style="color:red;text-align:center;margin-top:15px;">❌ Kayıt sırasında hata oluştu.</div>';
    }
  }
});

// 🔒 iyzico scriptini tamamen devre dışı bırak
add_action('wp_enqueue_scripts', function() {
  wp_dequeue_script('iyzico-script');
  wp_deregister_script('iyzico-script');
}, 100);
// 🟣 AJAX ile WooCommerce sepet güncelleme
add_action('wp_ajax_update_cart_item', 'funny_update_cart_item');
add_action('wp_ajax_nopriv_update_cart_item', 'funny_update_cart_item');

function funny_update_cart_item() {
    if (empty($_POST['cart_item_key'])) {
        wp_send_json_error('Eksik veri');
    }

    $key = sanitize_text_field($_POST['cart_item_key']);
    $qty = intval($_POST['quantity']);

    if (!WC()->cart) WC()->initialize_cart();

    if ($qty <= 0) {
        WC()->cart->remove_cart_item($key);
    } else {
        WC()->cart->set_quantity($key, $qty, true);
    }

    wp_send_json_success([
        'total' => WC()->cart->get_cart_total(),
    ]);
}
// 🛒 Tüm "Add to cart" yazılarını "Sepete Ekle" olarak değiştir
add_filter( 'woocommerce_product_single_add_to_cart_text', 'etkinliks_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'etkinliks_add_to_cart_text' );

function etkinliks_add_to_cart_text() {
    return 'Sepete Ekle';
}
/* ============================
   PROFİL FORMU İÇİN KULLANICI METALARI
============================ */

/* Telefon */
add_action('show_user_profile', 'funny_extra_user_fields');
add_action('edit_user_profile', 'funny_extra_user_fields');
function funny_extra_user_fields($user) { ?>
    <h3>Ek Bilgiler</h3>
    <table class="form-table">
        <tr>
            <th><label for="phone">Telefon</label></th>
            <td><input type="text" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>" /></td>
        </tr>
        <tr>
            <th><label for="address">Adres</label></th>
            <td><input type="text" name="address" value="<?php echo esc_attr(get_user_meta($user->ID, 'address', true)); ?>" /></td>
        </tr>
    </table>
<?php }

/* Kaydetme */
add_action('personal_options_update', 'funny_save_extra_user_fields');
add_action('edit_user_profile_update', 'funny_save_extra_user_fields');
function funny_save_extra_user_fields($user_id) {
    update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
}
/* ============================================
   PANEL PROFİL FORMUNU KAYDETME
============================================ */
add_action('init', function() {

    if (!isset($_POST['save_profile'])) return;
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();

    // Temel bilgiler
    wp_update_user([
        'ID'           => $user_id,
        'first_name'   => sanitize_text_field($_POST['first_name']),
        'last_name'    => sanitize_text_field($_POST['last_name']),
        'user_email'   => sanitize_email($_POST['email']),
    ]);

    // Telefon ve adres
    update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));

    // Profil fotoğrafı yükleme
    if (!empty($_FILES['avatar']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = wp_handle_upload($_FILES['avatar'], ['test_form' => false]);

        if (!isset($upload['error'])) {
            update_user_meta($user_id, 'simple_local_avatar', ['full' => $upload['url']]);
        }
    }

    // Yönlendirme (başarı mesajı için)
    wp_safe_redirect(add_query_arg('profile_updated', '1', wp_get_referer()));
    exit;
});
/* ============================
   PROFİL DÜZENLEME KAYDETME
=============================== */

add_action('init', function () {

  if (!is_user_logged_in()) return;
  if (!isset($_POST['save_profile'])) return;

  $user_id = get_current_user_id();

  // Temel bilgiler
  wp_update_user([
    'ID'         => $user_id,
    'first_name' => sanitize_text_field($_POST['first_name']),
    'last_name'  => sanitize_text_field($_POST['last_name']),
    'user_email' => sanitize_email($_POST['email']),
  ]);

  // Meta alanları
  update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
  update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));

  // PROFİL FOTOĞRAFI YÜKLEME
  if (!empty($_FILES['avatar']['name'])) {

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload = wp_handle_upload($_FILES['avatar'], ['test_form' => false]);

    if (isset($upload['file'])) {

      // eski foto varsa sil
      $old = get_user_meta($user_id, 'profile_picture', true);
      if ($old) wp_delete_attachment($old, true);

      $filetype = wp_check_filetype(basename($upload['file']), null);

      $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($_FILES['avatar']['name']),
        'post_status'    => 'inherit'
      ];

      $attach_id = wp_insert_attachment($attachment, $upload['file']);
      $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
      wp_update_attachment_metadata($attach_id, $attach_data);

      update_user_meta($user_id, 'profile_picture', $attach_id);
    }
  }

  // başarılı bildirim
  wp_safe_redirect(add_query_arg('profile_updated', '1', wp_get_referer()));
  exit;
});

function etkinliks_get_user_tickets() {
    // Kullanıcı login değilse boş dön
    if ( ! is_user_logged_in() ) {
        return [];
    }

    // 🔹 Önce WooCommerce siparişlerinden gerçekten bilet var mı ona bakalım
    if ( class_exists( 'WooCommerce' ) ) {
        $user_id = get_current_user_id();

        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'status'      => ['pending', 'on-hold', 'processing', 'completed'],
            'limit'       => 50,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);

        $tickets = [];

        if ( ! empty( $orders ) ) {
            foreach ( $orders as $order ) {
                $order_id      = $order->get_id();
                $order_code    = $order->get_order_number();
                $customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );

                foreach ( $order->get_items() as $item ) {
                    $product_id = $item->get_product_id();
                    $product    = wc_get_product( $product_id );
                    if ( ! $product ) continue;

                    $event_title = $item->get_name();

                    // ACF alanların varsa:
                    $event_date  = function_exists('get_field') ? get_field('etkinlik_tarihi', $product_id) : '';
                    $event_time  = function_exists('get_field') ? get_field('etkinlik_saati', $product_id) : '';

                    $seat_row = $item->get_meta('seat_row'); // kendi meta key'lerinle değiştirirsin
                    $seat_no  = $item->get_meta('seat_no');

                    $qr_data = 'ES-' . $order_id . '-' . $product_id . '-' . $order_code;

                    $tickets[] = [
                        'order_id'      => $order_id,
                        'order_code'    => $order_code,
                        'event_title'   => $event_title,
                        'event_date'    => $event_date,
                        'event_time'    => $event_time,
                        'customer_name' => $customer_name ?: wp_get_current_user()->display_name,
                        'seat_row'      => $seat_row ?: '',
                        'seat_no'       => $seat_no ?: '',
                        'product_id'    => $product_id,
                        'qr_data'       => $qr_data,
                    ];
                }
            }

            // 🔹 Eğer gerçekten sipariş bulduysa, onları döndür
            if ( ! empty( $tickets ) ) {
                return $tickets;
            }
        }
    }

    // ⬇️ BURASI EN ÖNEMLİ KISIM: HİÇ SİPARİŞ YOKSA TEST İÇİN DEMO BİLET DÖNDÜR
    $current_user = wp_get_current_user();

    return [[
        'order_id'      => 999,
        'order_code'    => 'DEMO-12345',
        'event_title'   => 'Demo Etkinliği',
        'event_date'    => date('d.m.Y'),
        'event_time'    => '20:00',
        'customer_name' => $current_user->display_name ?: 'Demo Kullanıcı',
        'seat_row'      => '3',
        'seat_no'       => '14',
        'product_id'    => 0,
        'qr_data'       => 'DEMO-QR-' . $current_user->ID,
    ]];
}
// Şifre sıfırlama mailindeki linki özel sayfaya yönlendir
add_filter('retrieve_password_message', function( $message, $key, $user_login, $user_data ) {

    // Özel şifre yenile sayfanın URL'si
    $reset_url = add_query_arg(
        [
            'key'   => $key,
            'login' => rawurlencode( $user_login ),
        ],
        site_url( '/sifre-yenile/' )
    );

    $message  = "Merhaba " . $user_login . ",\n\n";
    $message .= "Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:\n\n";
    $message .= $reset_url . "\n\n";
    $message .= "Eğer bu isteği siz göndermediyseniz, bu e-postayı dikkate almayabilirsiniz.\n\n";
    $message .= "Etkinlik-S";

    return $message;
}, 10, 4);

/**
 * My Account > Ayarlar formundan gelen organizatör alanlarını kaydet
 */
function etkinliks_save_organizer_profile_fields() {

  // Sadece giriş yapmış kullanıcı
  if ( ! is_user_logged_in() ) {
    return;
  }

  // Sadece POST isteğinde ve bizim formumuz gönderildiyse
  if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    return;
  }

  if ( ! isset( $_POST['save_profile'] ) ) {
    // Formdaki submit butonunun name'i "save_profile"
    return;
  }

  $user_id = get_current_user_id();

  // Kullanıcı organizer değilse bu alanlara dokunma
  if ( ! user_can( $user_id, 'organizer' ) ) {
    return;
  }

  // Güvenlik için basitçe sanitize et
  $organizer_kind = isset( $_POST['organizer_kind'] ) ? sanitize_text_field( $_POST['organizer_kind'] ) : '';
  $tc_kimlik_no   = isset( $_POST['tc_kimlik_no'] ) ? sanitize_text_field( $_POST['tc_kimlik_no'] ) : '';
  $vergi_no       = isset( $_POST['vergi_no'] ) ? sanitize_text_field( $_POST['vergi_no'] ) : '';
  $company_name   = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
  $iban           = isset( $_POST['organizer_iban'] ) ? sanitize_text_field( $_POST['organizer_iban'] ) : '';

  update_user_meta( $user_id, 'organizer_kind', $organizer_kind );
  update_user_meta( $user_id, 'tc_kimlik_no', $tc_kimlik_no );
  update_user_meta( $user_id, 'vergi_no', $vergi_no );
  update_user_meta( $user_id, 'company_name', $company_name );
  update_user_meta( $user_id, 'organizer_iban', $iban );

  // Buraya istersen ileride: organizer_docs_completed true yapma mantığını ekleriz
}
add_action( 'template_redirect', 'etkinliks_save_organizer_profile_fields' );

