<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="main-header">
  <div class="header-inner">

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

    <!-- 🔹 MENÜ -->
    <nav class="main-menu">
      <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'menu-items',
        'fallback_cb'    => false,
      ]);
      ?>
    </nav>

    <!-- 🔹 ARAMA + GİRİŞ / SEPET -->
    <div class="header-actions">
      <!-- 🔍 Arama -->
      <form role="search" method="get" class="search" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="search" class="search-field" placeholder="Etkinlik ara..." value="<?php echo get_search_query(); ?>" name="s" />
        <i class="fa fa-search"></i>
      </form>

      <!-- 🔹 Kullanıcı / Giriş Durumu -->
      <?php if (is_user_logged_in()) : ?>
        <a href="<?php echo esc_url(site_url('/my-account/user-dashboard')); ?>" class="login-btn">👤 Panelim</a>
      <?php else : ?>
        <a href="<?php echo esc_url(site_url('/giris')); ?>" class="login-btn">Giriş Yap</a>
        <a href="<?php echo esc_url(site_url('/kayit')); ?>" class="register-btn">Kayıt Ol</a>
      <?php endif; ?>

      <!-- 🛒 Sepet -->
      <?php if (class_exists('WooCommerce')) : ?>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-icon">
          <i class="fa fa-shopping-cart"></i>
          <?php
          $count = WC()->cart->get_cart_contents_count();
          if ($count > 0) {
            echo '<span class="cart-count">' . esc_html($count) . '</span>';
          }
          ?>
        </a>
      <?php endif; ?>
    </div>

    <!-- 🔹 Mobil Menü Butonu -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>

  </div>
</header>
