<?php
namespace Ghost\Prepayment;

require_once __DIR__ . '/DataAccessLayer.php';

class Injector {
	/**
	 * User ID for saving sale
	 */
	public $user_id;
	
	/**
	 * Array of product ID's or variation ID's and price need to be saved
	 *
	 * Structure:
	 *
	 * Array (
	 *   Array (
	 *     'id' => 123
	 *     'price' => 999
     *   ),
	 *   Array ( ... ),
	 * )
	 */
	public $product_prices;
	
	/**
	 * Injector constructor.
	 *
	 * @param $user_id
	 * @param array $product_prices
	 */
	public function __construct($user_id, array $product_prices) {
		if ( gettype($product_prices) !== "array" ) {
			error_log('Wrong product types in Injector', 0);
			return false;
		}
		
		if ( empty($product_prices) ) {
			error_log('Empty product types in Injector', 0);
			return false;
		}
		
		$ids = [];
		foreach ( $product_prices as $row ) {
			$id = $row['id'];
			if (
				! in_array( gettype($id), ['integer', 'string'])  ||
				! in_array( gettype($row['price']), ['integer', 'string'])
			) {
				error_log('Wrong product ID or price type in Injector', 0);
				return false;
			}
			
			if ( in_array($id, $ids) ) {
				error_log('Duplicate product in Injector', 0);
				return false;
			}
			
			$ids[] = $id;
		}
		
		$this->user_id = $user_id;
		$this->product_prices = $product_prices;
	}
	
	/**
	 * Method add product price to user through DAL
	 */
	public function AddSale() {
		if ( ! $this->user_id || ! $this->product_prices ) {
			return false;
		}
		
		(new DataAccessLayer())->Create($this->user_id, $this->product_prices);
	}
}