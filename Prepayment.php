<?php
namespace Ghost\Prepayment;

require_once __DIR__ . '/Injector.php';
require_once __DIR__ . '/DataAccessLayer.php';
require_once __DIR__ . '/Implementor.php';
require_once __DIR__ . '/CartDiscountMaker.php';
require_once __DIR__ . '/Utils.php';

/**
 * Class Prepayment
 *
 * Handler class for interact with API
 *
 * @package Ghost\Prepayment
 */
class Prepayment {
	public $user_id;
	
	public function __construct( $user_id = 0 ) {
		$this->user_id = $user_id;
	}
	
	/**
	 * Method starts plugin
	 */
	public function Run() {
		/**
		 * Inject data on purchase prepayment product
		 * Delete discount data from user
		 */
		add_action( 'woocommerce_order_status_completed', array( $this, 'ListenPurchase' ), 99, 1 );
		
		/**
		 * Make discount in cart
		 */
		$this->ListenCart();
	}
	
	/**
	 * Check sale availability
	 *
	 * You can do something to show discount availability
	 * for users
	 * @return bool
	 */
	public function IsPluginOn() {
		return (new DataAccessLayer())->IsPluginOn();
	}
	
	/**
	 * Method save discount to user
	 * @param array $product_price
	 */
	public function AddSale(array $product_price) {
		if ( ! $this->user_id ) {
			return;
		}
		(new Injector($this->user_id, $product_price))->AddSale();
	}
	
	/**
	 * Method removes discount from user
	 *
	 * @param null $order_id (Successful payment order)
	 */
	public function RemoveDiscount($order_id = null) {
		if ( ! $this->user_id ) {
			$this->SetUserID();
		}
		
		if ( ! $this->user_id || ! $order_id ) {
			return;
		}
		
		$discount_products = $this->GetDiscountProducts();
		if ( empty($discount_products) ) {
			return;
		}
		
		$discount_products = $discount_products[0];
		$current_order_products = (new Utils())->GetFromOrderProductIDs($order_id);
		log_array($current_order_products);
		foreach ( $discount_products as $discount_product ) {
			if ( ! in_array($discount_product['id'], $current_order_products) ) {
				continue;
			} else {
				(new DataAccessLayer())->RemoveDiscount($this->user_id);
				return;
			}
		}
	}
	
	/**
	 * Method returns product ids and prices with discount
	 */
	public function GetDiscountProducts() {
		if ( ! $this->user_id ) {
			$this->SetUserID();
		}
		return (new Implementor())->Implement($this->user_id);
	}
	
	/**
	 * Method calculate discount amount in cart
	 */
	public function ListenCart() {
		$data = $this->GetDiscountProducts();
		if ( ! empty($data) && $this->user_id ) { // Check discount for user
			$discounter = new CartDiscountMaker($data);
		}
	}
	
	/**
	 * Method add or delete discount for user
	 * (depends on products in order)
	 */
	public function ListenPurchase($order_id) {
		$this->SetUserID($order_id);
		$utils = new Utils();
		$dal = new DataAccessLayer();
		
		$is_prepayment_cat = $utils->IsPrepaymentCat($order_id); // Returns product container ID
		$is_sale_on = $dal->IsPluginOn();
		if ( $is_prepayment_cat && $this->user_id && $is_sale_on ) {
			$product_id = $is_prepayment_cat;
			$data_to_save = (new DataAccessLayer())->GetPartiallyPaidProducts($product_id);
			$this->AddSale($data_to_save);
			return;
		}
		
		$this->RemoveDiscount($order_id);
	}
	
	/**
	 * Method set user to interact with
	 */
	public function SetUserID($order_id = null) {
		if ( is_user_logged_in() ) {
			$this->user_id = get_current_user_id();
		} else {
			if ( $order_id ) {
				$this->user_id = (new Utils())->GetUserIDByEmailInOrder($order_id);
			}
		}
	}
}