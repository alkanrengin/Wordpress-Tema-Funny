<?php
/* Template Name: Giriş Sayfası (Tam Ekran) */

if (is_user_logged_in()) {
  wp_redirect(home_url());
  exit;
}

// 🔐 Giriş işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['funny_login_nonce']) && wp_verify_nonce($_POST['funny_login_nonce'], 'funny_login_action')) {
    $creds = [
        'user_login'    => sanitize_text_field($_POST['log']),
        'user_password' => $_POST['pwd'],
        'remember'      => true
    ];

    $user = wp_signon($creds, false);

    if (!is_wp_error($user)) {
        // 🎯 Rol bazlı yönlendirme
        if (in_array('organizer', (array)$user->roles)) {
            wp_redirect(site_url('/my-account/user-dashboard'));
        } else {
            wp_redirect(home_url());
        }
        exit;
    } else {
        $error_message = 'E-posta veya şifre hatalı.';
    }
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giriş Yap - <?php bloginfo('name'); ?></title>
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/login.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/login.css'); ?>">
  <script src="https://kit.fontawesome.com/a2e0e6b6f4.js" crossorigin="anonymous"></script>
  <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login.js?v=<?php echo filemtime(get_template_directory() . '/assets/js/login.js'); ?>" defer></script>
  <?php wp_head(); ?>
</head>

<body <?php body_class('login-page'); ?>>

<section class="login-wrapper">
  <div class="login-container">
     <!-- 🔹 LOGO -->
    <div class="logo">
      
        <?php
        if (function_exists('the_custom_logo') && has_custom_logo()) {
          the_custom_logo();
        } else {
          echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
        }
        ?>
      
    </div>

    <?php if (!empty($error_message)) : ?>
      <div class="login-error"><?php echo esc_html($error_message); ?></div>
    <?php endif; ?>

    <div class="login-tabs">
      <button class="tab-btn active" data-target="#musteri">Kullanıcı Girişi</button>
      <button class="tab-btn" data-target="#organizer">Organizatör Girişi</button>
    </div>

    <!-- 🔹 MÜŞTERİ GİRİŞİ -->
    <div id="musteri" class="tab-content active">
      <form method="post" class="login-form">
        <?php wp_nonce_field('funny_login_action', 'funny_login_nonce'); ?>
        <div class="input-group">
          <i class="fa fa-envelope"></i>
          <input type="email" name="log" placeholder="E-posta adresiniz" required>
        </div>
        <div class="input-group">
          <i class="fa fa-lock"></i>
          <input type="password" name="pwd" placeholder="Şifreniz" required>
        </div>
        <a href="<?php echo esc_url(site_url('/sifremi-unuttum')); ?>" class="forgot">Şifremi unuttum</a>
        <button type="submit" class="btn-login">Giriş Yap</button>
      </form>
    </div>

    <!-- 🔹 ORGANİZATÖR GİRİŞİ -->
    <div id="organizer" class="tab-content">
      <form method="post" class="login-form">
        <?php wp_nonce_field('funny_login_action', 'funny_login_nonce'); ?>
        <div class="input-group">
          <i class="fa fa-envelope"></i>
          <input type="email" name="log" placeholder="Organizatör e-postası" required>
        </div>
        <div class="input-group">
          <i class="fa fa-lock"></i>
          <input type="password" name="pwd" placeholder="Şifreniz" required>
        </div>
        <a href="<?php echo esc_url(site_url('/sifremi-unuttum')); ?>" class="forgot">Şifremi unuttum</a>
        <button type="submit" class="btn-login">Giriş Yap</button>
      </form>
    </div>
  </div>
</section>

<?php wp_footer(); ?>
</body>
</html>
