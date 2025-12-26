<?php
/**
 * Template Name: Kayıt Sayfası
 * Description: Müşteri ve Organizatör kayıt ekranı (Etkinlik-S)
 */

add_filter('body_class', function ($classes) {
  $classes[] = 'register-page';
  return $classes;
});

// 🧩 Kayıt işlemi
$register_errors  = [];
$register_success = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_type']) ) {

  // Nonce kontrolü
  if (
    ! isset($_POST['etkinliks_register_nonce']) ||
    ! wp_verify_nonce( $_POST['etkinliks_register_nonce'], 'etkinliks_register' )
  ) {
    $register_errors[] = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
  } else {

    $user_type = sanitize_text_field( $_POST['user_type'] ); // customer / organizer

    // Ortak alanlar
    $email    = isset($_POST['email'])    ? sanitize_email($_POST['email']) : '';
    $phone    = isset($_POST['phone'])    ? sanitize_text_field($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

    if ( empty($email) || empty($phone) || empty($password) || empty($password2) ) {
      $register_errors[] = 'Lütfen tüm zorunlu alanları doldurun.';
    }

    if ( $password !== $password2 ) {
      $register_errors[] = 'Girdiğiniz şifreler eşleşmiyor.';
    }

    if ( email_exists( $email ) ) {
      $register_errors[] = 'Bu e-posta adresiyle zaten bir hesap bulunuyor.';
    }

    // Sözleşme onayları
    if ( $user_type === 'customer' ) {
      if ( empty($_POST['accept_terms_customer']) ) {
        $register_errors[] = 'Üyelik ve KVKK metinlerini onaylamanız gerekiyor.';
      }
    }

    if ( $user_type === 'organizer' ) {
      if ( empty($_POST['accept_terms_organizer']) ) {
        $register_errors[] = 'Organizatör sözleşmesi ve KVKK metinlerini onaylamanız gerekiyor.';
      }
    }

    // Kullanıcı tipi özel alanlar
    $first_name = '';
    $last_name  = '';
    $display_name = '';

    $organizer_type = '';

    if ( $user_type === 'customer' ) {

      $full_name = isset($_POST['adsoyad']) ? sanitize_text_field($_POST['adsoyad']) : '';
      if ( empty($full_name) ) {
        $register_errors[] = 'Ad Soyad alanı zorunludur.';
      } else {
        $parts = preg_split('/\s+/', $full_name);
        $first_name = array_shift($parts);
        $last_name  = implode(' ', $parts);
        $display_name = $full_name;
      }

    } elseif ( $user_type === 'organizer' ) {

      $organizer_type = isset($_POST['orgType']) ? sanitize_text_field($_POST['orgType']) : '';
      if ( empty($organizer_type) ) {
        $register_errors[] = 'Lütfen organizatör türünü seçiniz.';
      }

      if ( $organizer_type === 'bireysel' ) {

        $full_name = isset($_POST['adsoyad']) ? sanitize_text_field($_POST['adsoyad']) : '';
        if ( empty($full_name) ) {
          $register_errors[] = 'Ad Soyad alanı zorunludur.';
        } else {
          $parts = preg_split('/\s+/', $full_name);
          $first_name = array_shift($parts);
          $last_name  = implode(' ', $parts);
          $display_name = $full_name;
        }

      } elseif ( $organizer_type === 'kurumsal' ) {

        $company_name   = isset($_POST['company_name'])   ? sanitize_text_field($_POST['company_name'])   : '';
        $contact_person = isset($_POST['contact_person']) ? sanitize_text_field($_POST['contact_person']) : '';

        if ( empty($company_name) ) {
          $register_errors[] = 'Firma adı zorunludur.';
        }
        if ( empty($contact_person) ) {
          $register_errors[] = 'İletişim kurulacak kişi alanı zorunludur.';
        }

        $first_name   = $contact_person;
        $last_name    = '';
        $display_name = $company_name;
      }
    }

    // Hata yoksa kullanıcı oluştur
    if ( empty($register_errors) ) {

      // Rol belirleme
      $role = 'customer';
      if ( $user_type === 'organizer' ) {
        if ( get_role('seller') ) {
          $role = 'seller';          // Dokan vendor rolü
        } elseif ( get_role('organizer') ) {
          $role = 'organizer';       // Senin özel rolün varsa
        }
      }

      $userdata = [
        'user_login' => $email,
        'user_email' => $email,
        'user_pass'  => $password,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'display_name' => $display_name ?: $email,
        'role'       => $role,
      ];

      $user_id = wp_insert_user( $userdata );

      if ( is_wp_error($user_id) ) {
        $register_errors[] = 'Kayıt sırasında bir hata oluştu: ' . $user_id->get_error_message();
      } else {

        // Ortak meta
        update_user_meta($user_id, 'phone', $phone);

        if ( $user_type === 'customer' ) {
          update_user_meta($user_id, 'user_sms_verified', 'no');
        }

        if ( $user_type === 'organizer' ) {
          update_user_meta($user_id, 'organizer_type', $organizer_type);
          update_user_meta($user_id, 'organizer_sms_verified', 'no');
          update_user_meta($user_id, 'organizer_kyc_completed', 'no');
          update_user_meta($user_id, 'organizer_approved', 'no');

          if ( isset($company_name) ) {
            update_user_meta($user_id, 'organizer_company_name', $company_name);
          }
          if ( isset($contact_person) ) {
            update_user_meta($user_id, 'organizer_contact_person', $contact_person);
          }
        }

        $register_success = 'Kayıt işleminiz başarıyla tamamlandı. Giriş yapabilirsiniz.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
  <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/register.css?v=<?php echo time(); ?>">
</head>

<body <?php body_class(); ?>>

  <div class="register-container">
    <div class="register-logo">
      <?php
      if (function_exists('the_custom_logo') && has_custom_logo()) {
        the_custom_logo();
      } else {
        echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
      }
      ?>
      <h2>Kayıt Ol</h2>
    </div>

    <?php if ( ! empty($register_errors) ) : ?>
      <div class="register-alert error">
        <?php foreach ( $register_errors as $err ) : ?>
          <p><?php echo esc_html($err); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ( $register_success ) : ?>
      <div class="register-alert success">
        <p><?php echo esc_html($register_success); ?></p>
        <p><a href="<?php echo esc_url( site_url('/giris') ); ?>">Giriş yap</a></p>
      </div>
    <?php endif; ?>

    <!-- 🔹 Sekmeler -->
    <div class="register-tabs">
      <button class="tab-btn active" data-tab="musteri">👤 Kullanıcı Kaydı</button>
      <button class="tab-btn" data-tab="organizer">🎪 Organizatör Kaydı</button>
    </div>

    <!-- 🔹 Müşteri Kaydı -->
    <div class="tab-content active" id="musteri">
      <form class="register-form" method="post">
        <input type="hidden" name="user_type" value="customer">
        <?php wp_nonce_field('etkinliks_register', 'etkinliks_register_nonce'); ?>

        <div class="input-group">
          <input type="text" name="adsoyad" placeholder="Ad Soyad" required>
        </div>
        <div class="input-group">
          <input type="email" name="email" placeholder="E-posta Adresi" required>
        </div>
        <div class="input-group">
          <input type="tel" name="phone" placeholder="Telefon Numarası" required>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Şifre" required>
        </div>
        <div class="input-group">
          <input type="password" name="password2" placeholder="Şifre Tekrarı" required>
        </div>

        <label class="checkbox">
          <input type="checkbox" name="accept_terms_customer" required>
          <span>
            <a href="<?php echo esc_url( site_url('/uyelik-ve-kullanim-kosullari') ); ?>" target="_blank">
              Üyelik ve Kullanım Koşulları
            </a> ile
            <a href="<?php echo esc_url( site_url('/gizlilik-politikasi') ); ?>" target="_blank">
              Gizlilik Politikası ve KVKK Aydınlatma Metni
            </a>'ni okudum, kabul ediyorum.
          </span>
        </label>

        <button type="submit" class="btn-register">Kayıt Ol</button>
      </form>
    </div>

    <!-- 🔹 Organizatör Kaydı -->
    <div class="tab-content" id="organizer">
      <form class="register-form" method="post">
        <input type="hidden" name="user_type" value="organizer">
        <?php wp_nonce_field('etkinliks_register', 'etkinliks_register_nonce'); ?>

        <div class="input-group">
          <select id="orgType" name="orgType" required>
            <option value="">Organizatör Türü Seçin</option>
            <option value="bireysel">Bireysel</option>
            <option value="kurumsal">Kurumsal</option>
          </select>
        </div>

        <!-- Dinamik içerik alanı -->
        <div id="dynamicFields"></div>

        <label class="checkbox">
          <input type="checkbox" name="accept_terms_organizer" required>
          <span>
            <a href="<?php echo esc_url( site_url('/organizator-sozlesmesi') ); ?>" target="_blank">
              Organizatör Sözleşmesi
            </a> ile
            <a href="<?php echo esc_url( site_url('/gizlilik-politikasi') ); ?>" target="_blank">
              Gizlilik Politikası ve KVKK Aydınlatma Metni
            </a>'ni okudum ve onaylıyorum.
          </span>
        </label>

        <button type="submit" class="btn-register">Kaydı Tamamla</button>
      </form>
    </div>

  </div><!-- .register-container -->

  <script src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/register.js?v=<?php echo time(); ?>" defer></script>
  <?php wp_footer(); ?>
</body>
</html>
