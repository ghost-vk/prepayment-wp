<?php
namespace Ghost\Prepayment;

require_once __DIR__ . '/DataAccessLayer.php';

/**
 * Class Implementor
 *
 * Used for implement saved data contained products and prices
 *
 * @package Ghost\Prepayment
 */
class Implementor {
	/**
	 * @param $user_id
	 * @param string $implement_type
	 *
	 * Structure of returned data:
	 *     Array (
	 *       Array (
	 *         'id' => 123
	 *         'price' => 999
	 *       ),
	 *       Array ( ... ),
	 *     )
	 *
	 * @return array|false|mixed
	 */
	public function Implement($user_id, $implement_type = 'raw') {
		if ( ! in_array($implement_type, ['raw', 'with-links']) ){
			return false;
		}
		
		$discount_products = [];
		
		$dal = new DataAccessLayer();
		$raw = $dal->Read($user_id);
		
		switch ($implement_type) {
			case ('raw') : {
				$discount_products = $raw;
				break;
			}
			case ('with-links') : {
				break;
			}
			default : {
				return false;
			}
		}
		
		return $discount_products;
	}
}