<?php
/**
 * affiliates-referred-coupons-only.php
 *
 * Copyright (c) www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author kento
 * @package affiliates-tests
 * @since affiliates-tests 1.0.0
 *
 * Plugin Name: Affiliates Referred Coupons Only
 * Plugin URI: http://www.itthinx.com/
 * Description: Restrict use of coupons assigned to affiliates, so they can only be applied when a customer is referred by the assigned affiliate and while the affiliate link referral is valid.
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class Affiliates_Referred_Coupons_Only {

	/**
	 * Add filters.
	 */
	public static function init() {
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'woocommerce_coupon_is_valid' ), 10, 2 );
	}

	/**
	 * Determine coupon validity based on whether the customer trying to apply it was referred by the related affiliate.
	 *
	 * @param boolean$valid
	 * @param WC_Coupon $coupon
	 */
	public static function woocommerce_coupon_is_valid( $valid, $coupon ) {
		if ( $valid && $coupon instanceof WC_Coupon ) {
			if (
				class_exists( 'Affiliates_Attributes_WordPress' ) &&
				method_exists( 'Affiliates_Attributes_WordPress', 'get_affiliate_for_coupon' )
			) {
				$code = $coupon->get_code();
				if ( $affiliate_id = Affiliates_Attributes_WordPress::get_affiliate_for_coupon( $code ) ) {
					if ( $affiliate_id !== null && $affiliate_id > 0 ) {
						include_once AFFILIATES_CORE_LIB . '/class-affiliates-service.php';
						if (
							class_exists( 'Affiliates_Service' ) &&
							method_exists( 'Affiliates_Service', 'get_referrer_id' )
						) {
							$referrer_id = Affiliates_Service::get_referrer_id();
							if ( $referrer_id === false || $referrer_id === null || $referrer_id < 1 ) {
								$valid = false;
							} else {
								if ( $affiliate_id !== $referrer_id ) {
									$valid = false;
								}
							}
						}
					}
				}
			}
		}
		return $valid;
	}
}

Affiliates_Referred_Coupons_Only::init();
