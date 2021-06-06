<?php
namespace Ghost\Prepayment;

class CartDiscountMaker {
	public array $product_prices;
	private $discount;
	
	public function __construct(array $product_prices) {
		$this->product_prices = $product_prices[0];
		add_action('woocommerce_before_calculate_totals', array( $this, 'SetAppliedCoupon' ), 10, 1);
		add_action('woocommerce_get_shop_coupon_data', array($this, 'SetCouponData'), 10, 3);
	}
	
	public function MakeDiscount($cart) {
		$cart_items = $cart->get_cart();
		
		$user_discount_products = [];
		foreach ( $this->product_prices as $row ) {
			$user_discount_products[] = $row['id'];
		}
		
		foreach ( $cart_items as $item ) {
			$product_id = $item['data']->get_id();
			if ( ! in_array($product_id, $user_discount_products) ) {
				continue;
			} else {
				$discount_price = 0;
				foreach ( $this->product_prices as $row ) {
					if ( (int)$product_id !== (int)$row['id'] ) {
						continue;
					} else {
						$discount_price = $row['price'];
						$this->CalcDiscount( $product_id, $discount_price );
						break;
					}
				}
			}
		}
	}
	
	private function CalcDiscount( $product_id, $discount_price ) {
		$product = wc_get_product($product_id);
		$regular_price = (int)$product->get_regular_price();
		$sale_price = (int)$product->get_sale_price();
		$price = ( $sale_price ) ? $sale_price : $regular_price; // Too big discount if sale is on
		$discount_price = (int)$discount_price;
		if ( $discount_price > $regular_price ) {
			return false;
		}
		$this->discount = $price - $discount_price;
	}
	
	public function SetAppliedCoupon(\WC_Cart $cart) {
		$this->MakeDiscount($cart);
		
		$cart->applied_coupons = array_diff($cart->applied_coupons, ['prepayment-discount']);
		
		// add your conditions for applying the virtual coupon
		$cart->applied_coupons[] = 'prepayment-discount';
	}
	
	public function SetCouponData($false, $data, $coupon) {
		switch($data) {
			case 'prepayment-discount':
				$coupon->set_virtual(true);
				$coupon->set_discount_type('fixed_cart');
				$coupon->set_amount($this->discount);
				
				return $coupon;
		}
	}
	
}