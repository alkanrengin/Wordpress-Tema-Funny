<?php
/**
 * Template Name: Şifre Yenile
 * Description: Kullanıcı şifre yenileme sayfası (Etkinlik-S)
 */

get_header();

$key   = isset($_GET['key'])   ? sanitize_text_field($_GET['key'])   : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

$errors = '';
$success = '';

$user = false;
if ( $key && $login ) {
    $user = check_password_reset_key( $key, $login );
    if ( is_wp_error( $user ) ) {
        $errors = 'Bu şifre yenileme bağlantısı geçersiz veya süresi dolmuş.';
    }
} else {
    $errors = 'Geçersiz bağlantı. Lütfen e-postadaki linki kullanın.';
}

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_pass') {

    if ( ! isset($_POST['reset_pass_nonce']) || ! wp_verify_nonce($_POST['reset_pass_nonce'], 'etkinliks_reset_pass') ) {
        $errors = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {

        $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : '';
        $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : '';
        $login = isset($_POST['login']) ? sanitize_text_field($_POST['login']) : '';
        $key   = isset($_POST['key'])   ? sanitize_text_field($_POST['key'])   : '';

        if ( empty($pass1) || empty($pass2) ) {
            $errors = 'Lütfen her iki şifre alanını da doldurun.';
        } elseif ( $pass1 !== $pass2 ) {
            $errors = 'Girdiğiniz şifreler eşleşmiyor.';
        } else {
            // Kullanıcıyı yeniden doğrula
            $user = check_password_reset_key( $key, $login );
            if ( is_wp_error( $user ) ) {
                $errors = 'Bu şifre yenileme bağlantısı geçersiz veya süresi dolmuş.';
            } else {
                reset_password( $user, $pass1 );
                $success = 'Şifreniz başarıyla güncellendi. Şimdi giriş yapabilirsiniz.';
            }
        }
    }
}
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/sifre.css?v=<?php echo time(); ?>">

<main class="forgot-wrapper">
  <div class="forgot-container">
    <div class="register-logo">
      <?php
      if (function_exists('the_custom_logo') && has_custom_logo()) {
        the_custom_logo();
      } else {
        echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
      }
      ?>
      <h2>Yeni Şifre Belirle</h2>
      <p>Lütfen yeni şifrenizi belirleyin. Güçlü bir şifre kullanmanızı öneririz.</p>
    </div>

    <?php if ( $errors ) : ?>
      <p class="error-msg"><?php echo esc_html($errors); ?></p>
    <?php endif; ?>

    <?php if ( $success ) : ?>
      <p class="success-msg"><?php echo esc_html($success); ?></p>
      <div class="forgot-links">
       
      </div>
    <?php elseif ( ! is_wp_error($user) && $user ) : ?>

      <form method="post" class="forgot-form">
        <div class="input-group">
          <input type="password" name="pass1" placeholder="Yeni Şifre" required>
        </div>
        <div class="input-group">
          <input type="password" name="pass2" placeholder="Yeni Şifre (Tekrar)" required>
        </div>

        <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
        <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
        <input type="hidden" name="action" value="reset_pass">
        <?php wp_nonce_field('etkinliks_reset_pass', 'reset_pass_nonce'); ?>

        <button type="submit" class="btn-register">Şifremi Güncelle</button>
      </form>

    <?php endif; ?>

    <div class="forgot-links">
      <a href="<?php echo esc_url(site_url('/giris')); ?>">← Giriş sayfasına dön</a>
    </div>
  </div>
</main>

<?php get_footer(); ?>
