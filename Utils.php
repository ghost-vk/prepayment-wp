<?php
namespace Ghost\Prepayment;

class Utils {
	/**
	 * Method check product cat `prepayment`
	 * returns prepayment product ID if match, else false
	 *
	 * @param $order_id
	 *
	 * @return boolean
	 */
	public function IsPrepaymentCat($order_id) {
		$order = wc_get_order($order_id);
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$id = $item->get_product_id();
			$product = $item->get_product();
			
			$categories = $product->get_category_ids();
			if ( ! empty($categories) ) {
				$term = get_term_by( 'id', $categories[0], 'product_cat', 'ARRAY_A' );
				$slug = $term['slug'];
				if ( $slug === 'prepayment' ) {
					return $id;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Method get user by email from order
	 *
	 * @param $order_id
	 *
	 * @return false
	 */
	public function GetUserIDByEmailInOrder($order_id) {
		$order = wc_get_order($order_id);
		$data = $order->get_data();
		$email = $data['billing']['email'];
		$user = get_user_by('email', $email);
		if ( ! $user ) {
			return false;
		}
		return $user->ID;
	}
	
	/**
	 * Method get products from order
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	public function GetFromOrderProductIDs($order_id) {
		$product_ids = [];
		$order = wc_get_order($order_id);
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$product_ids[] = (string)$product_id;
			if ( $variation_id ) {
				$product_ids[] = (string)$variation_id;
			}
		}
		
		return $product_ids;
	}
}