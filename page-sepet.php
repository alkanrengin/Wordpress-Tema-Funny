<?php
/**
 * Template Name: Sepet Sayfası
 * Description: Etkinlik-S modern sepet görünümü
 */

get_header();
?>
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/css/cart.css?v=<?php echo time(); ?>">

<main class="cart-page container">
  <h1 class="cart-title">Sepetim</h1>

  <?php if ( function_exists('WC') && !WC()->cart->is_empty() ) : ?>
    <div class="cart-wrapper">
      <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
        $_product   = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $product_name = $_product->get_name();
        $product_price = WC()->cart->get_product_price($_product);
        $product_qty = $cart_item['quantity'];
        $product_subtotal = WC()->cart->get_product_subtotal($_product, $product_qty);
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
        $product_image_url = $product_image ? esc_url($product_image[0]) : 'https://via.placeholder.com/100';
      ?>
        <div class="cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
          <div class="cart-item-left">
            <img src="<?php echo $product_image_url; ?>" alt="<?php echo esc_attr($product_name); ?>">
            <div class="cart-item-info">
              <h3><?php echo esc_html($product_name); ?></h3>
            </div>
          </div>

          <div class="cart-item-right">
            <p class="cart-price"><?php echo $product_price; ?></p>
            <div class="cart-quantity">
              <div class="qty-box">
                <button type="button" class="qty-btn minus">−</button>
                <input type="number" class="qty" value="<?php echo esc_attr($product_qty); ?>" min="1">
                <button type="button" class="qty-btn plus">+</button>
              </div>
              <button type="button" class="remove-btn">🗑</button>
            </div>
            <p class="cart-total"><?php echo $product_subtotal; ?></p>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="cart-summary">
        <h3>Toplam: <span id="cart-total"><?php echo WC()->cart->get_cart_total(); ?></span></h3>
        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">Satın Al</a>
      </div>
    </div>
  <?php else : ?>
    <p class="empty-cart">Sepetinizde ürün bulunmamaktadır.</p>
  <?php endif; ?>
</main>

<script>
window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".cart-item").forEach(item => {
    const key = item.dataset.key;
    const qtyInput = item.querySelector(".qty");
    const plus = item.querySelector(".qty-btn.plus");
    const minus = item.querySelector(".qty-btn.minus");
    const remove = item.querySelector(".remove-btn");

    const updateCart = (quantity) => {
      fetch(window.ajaxurl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "update_cart_item",
          cart_item_key: key,
          quantity: quantity
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload(); // sayfayı yenile, fiyatlar güncelsin
        }
      })
      .catch(err => console.error("AJAX hata:", err));
    };

    plus?.addEventListener("click", () => updateCart(parseInt(qtyInput.value) + 1));
    minus?.addEventListener("click", () => {
      const newQty = parseInt(qtyInput.value) - 1;
      if (newQty > 0) updateCart(newQty);
    });
    remove?.addEventListener("click", () => updateCart(0));
  });
});
</script>


<?php get_footer(); ?>
