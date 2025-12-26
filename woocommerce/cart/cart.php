<?php
/**
 * Template Name: Etkinlik-S - Sepet Sayfası
 * Description: Özel sepet şablonu (WooCommerce içeriği + özel tasarım)
 */

defined('ABSPATH') || exit;

get_header(); // ✅ HEADER
?>

<!-- 🔹 CSS -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/cart.css?v=<?php echo filemtime(get_template_directory() . '/assets/css/cart-style.css'); ?>">

<?php
// 🔸 WooCommerce bildirimlerini göster (örnek: "Ürün sepete eklendi")
if (function_exists('woocommerce_output_all_notices')) {
  woocommerce_output_all_notices();
}
?>
<?php
// 🧪 WooCommerce Cart Test
if ( function_exists( 'WC' ) ) {
    $cart = WC()->cart;
    if ( ! $cart ) {
        echo '<div style="background:#ffdddd;padding:10px;margin:10px 0;">❌ WC()->cart nesnesi yok!</div>';
    } elseif ( $cart->is_empty() ) {
        echo '<div style="background:#fff3cd;padding:10px;margin:10px 0;">⚠️ Sepet mevcut ama boş.</div>';
    } else {
        echo '<div style="background:#d4edda;padding:10px;margin:10px 0;">✅ Sepette ürün var: '.count( $cart->get_cart() ).' adet</div>';
    }
}
?>


<!-- 🔹 ANA İÇERİK -->
 <?php echo '<div style="background:#004aad;color:#fff;padding:15px;text-align:center;">✅ Etkinlik-S cart.php aktif!</div>'; ?>

<main class="etkinliks-cart">
  <div class="container">
    <h1 class="cart-title">🛒 Sepetim</h1>

    <!-- 🔸 WooCommerce'in kendi sepet içeriğini göster -->
    <?php echo do_shortcode('[woocommerce_cart]'); ?>

  </div>
</main>

<?php get_footer(); // ✅ FOOTER ?>
