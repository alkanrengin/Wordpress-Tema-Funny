<?php
/**
 * Template Name: Şifremi Unuttum
 * Description: Kullanıcı şifre sıfırlama ekranı (Etkinlik-S)
 */
get_header();
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
      <h2>Şifremi Unuttum</h2>
      <p>Lütfen kayıtlı e-posta adresinizi girin. Şifre sıfırlama bağlantısı e-postanıza gönderilecektir.</p>
    </div>

    <?php
    $msg = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_email'])) {

      $email = sanitize_email($_POST['user_email']);
      $user  = get_user_by('email', $email);

      if ($user) {
        // WordPress'in kendi şifre sıfırlama sistemini çalıştır
        $_POST['user_login'] = $user->user_login;

        $result = retrieve_password();

        if ($result === true) {
          $msg = '<p class="success-msg">✅ Şifre sıfırlama bağlantısı e-postanıza gönderildi.</p>';
        } else {
          $msg = '<p class="error-msg">⚠️ Mail gönderilemedi. Lütfen daha sonra tekrar deneyin.</p>';
        }

      } else {
        $msg = '<p class="error-msg">❌ Bu e-posta adresiyle kayıtlı bir kullanıcı bulunamadı.</p>';
      }
    }
    ?>

    <form method="post" class="forgot-form">
      <div class="input-group">
        <input type="email" name="user_email" placeholder="E-posta Adresi" required>
      </div>
      <button type="submit" class="btn-register">Bağlantı Gönder</button>
    </form>

    <?php echo $msg; ?>

    <div class="forgot-links">
      <a href="<?php echo esc_url(site_url('/giris')); ?>">← Giriş sayfasına dön</a>
    </div>
  </div>
</main>

<?php get_footer(); ?>
