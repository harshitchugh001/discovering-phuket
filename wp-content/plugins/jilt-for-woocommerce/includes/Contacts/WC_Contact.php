<?php
/**
 * WooCommerce Jilt
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@jilt.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Jilt to newer
 * versions in the future. If you wish to customize WooCommerce Jilt for your
 * needs please refer to http://help.jilt.com/jilt-for-woocommerce
 *
 * @package   WC-Jilt
 * @author    Jilt
 * @copyright Copyright (c) 2015-2021, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Jilt\WooCommerce\Contacts;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_0 as Framework;

/**
 * WooCommerce Jilt contact object.
 *
 * {BR 2019-09-28} This should likely implement an interface defining what a local
 *  Jilt contact looks like, and some of these methods could move to a trait.
 *
 * @since 1.7.0
 */
class WC_Contact extends \WC_Customer {


	/** Jilt API interactions *************************************************/


	/**
	 * Opts the contact into marketing emails, and optionally assigns lists and / or tags.
	 *
	 * @since 1.7.0
	 *
	 * @param array $list_ids the Jilt list IDs the contact should be added to
	 * @param array $tags the tags that should be added to the contact
	 * @return array success result and message or object
	 */
	public function subscribe( array $list_ids = [], array $tags = [] ) {

		$api  = wc_jilt()->get_integration()->get_api();
		$data = [ 'accepts_marketing' => true, ];

		if ( ! $api ) {

			$success = [
				'result'  => false,
				'message' => 'API unavailable',
			];

		} else {

			if ( ! empty( $list_ids ) ) {
				$data['list_ids'] = $list_ids;
			}

			if ( ! empty( $tags ) ) {
				$data['tags'] = $tags;
			}

			// see if the contact exists in Jilt first
			if ( ( ! $this->is_guest() && $this->get_jilt_remote_id() ) || $this->fetch_remote_contact() ) {

				try {

					$response = $api->update_customer( $this->get_email(), $data );
					$success  = [
						'result'  => true,
						'message' => $response,
					];

				} catch ( Framework\SV_WC_API_Exception $e ) {

					$success = [
						'result'  => false,
						'message' => $e->getMessage(),
					];
				}

			} else {

				$data = array_merge( $data, [
					'email'          => $this->get_email(),
					'first_name'     => $this->get_first_name(),
					'last_name'      => $this->get_last_name(),
					'contact_source' => 'jilt-for-woocommerce',
				] );

				try {

					$response = $api->create_customer( $data );
					$success  = [
						'result'  => true,
						'message' => $response,
					];

				} catch ( Framework\SV_WC_API_Exception $e ) {

					$success = [
						'result'  => false,
						'message' => $e->getMessage(),
					];
				}
			}
		}

		return $success;
	}


	/** Handle contact details ************************************************/


	/**
	 * Gets the locally stored Jilt remote ID.
	 *
	 * @since 1.7.0
	 *
	 * @return int the Jilt contact ID
	 */
	public function get_jilt_remote_id() {

		return (int) $this->get_meta( '_wc_jilt_contact_id' );
	}


	/**
	 * Stores the Jilt remote ID.
	 *
	 * @since 1.7.0
	 *
	 * @param int $value the Jilt contact ID
	 */
	public function set_jilt_remote_id( $value ) {

		$this->update_meta_data( '_wc_jilt_contact_id', $value );
		$this->save();
	}


	/**
	 * Fetches and saves the Jilt ID from the remote API.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $refresh true if the ID should be overridden
	 */
	public function fetch_jilt_remote_id( $refresh = false ) {

		// proceed if we don't have a local ID or we're forcing refresh
		if ( ! $this->get_jilt_remote_id() || $refresh ) {

			$jilt_contact = $this->fetch_remote_contact();

			if ( $jilt_contact ) {
				$this->set_jilt_remote_id( $jilt_contact->id );
			}
		}
	}


	/**
	 * Gets contact's locally stored Jilt opt in data.
	 *
	 * @since 1.7.0
	 *
	 * @return bool whether the contact is opted in
	 */
	public function get_jilt_opt_in() {

		return 'yes' === $this->get_meta( '_wc_jilt_marketing_email_consent' );
	}


	/**
	 * Sets contact's locally stored Jilt opt in data.
	 *
	 * @since 1.7.0
	 *
	 * @param string|bool|int $accepts_marketing whether the contact accepts marketing or not
	 */
	public function set_jilt_opt_in( $accepts_marketing ) {

		$accepts_marketing = is_string( $accepts_marketing ) ? ( 'true' === strtolower( trim( $accepts_marketing ) ) || 'yes' === strtolower( trim( $accepts_marketing ) ) ) : (bool) $accepts_marketing;

		// we expect a "pretty" value...for now
		$accepts_marketing = $accepts_marketing ? 'yes' : 'no';

		$this->update_meta_data( '_wc_jilt_marketing_email_consent', $accepts_marketing );
		$this->save();
	}


	/**
	 * Sets the Jilt opt in value from the API.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $refresh true to force pulling the remote value
	 */
	public function fetch_jilt_opt_in( $refresh = false ) {

		// proceed if we haven't stored opt in data or we're forcing refresh
		if ( $refresh || ! $this->get_jilt_opt_in() ) {

			$jilt_contact = $this->fetch_remote_contact();

			if ( $jilt_contact ) {
				$this->set_jilt_opt_in( $jilt_contact->accepts_marketing );
			}
		}
	}


	/**
	 * Gets the remote contact data from Jilt.
	 *
	 * @since 1.7.0
	 *
	 * @return bool|\stdClass the contact data
	 */
	public function fetch_remote_contact() {

		$api = wc_jilt()->get_integration()->get_api();

		if ( $api ) {

			try {

				return $api->get_customer( $this->get_email() );

			} catch ( Framework\SV_WC_API_Exception $e ) {

				return false;
			}
		}

		return false;
	}


	/** Set additional contact details ****************************************/


	/**
	 * Stores the GDPR consent data locally. This should be passed to Jilt when
	 * accepted by the API.
	 *
	 * @since 1.7.0
	 *
	 * @param string $context
	 * @param string $consent_text
	 * @param bool $ip_address
	 */
	public function store_opt_in_details( $context = '', $consent_text = 'Subscribe', $ip_address = false ) {

		$this->update_meta_data( '_wc_jilt_marketing_email_consent', 'yes' );
		$this->update_meta_data( '_wc_jilt_consent_context', $context );
		$this->update_meta_data( '_wc_jilt_consent_timestamp', date( 'Y-m-d\TH:i:s\Z', time() ) );
		$this->update_meta_data( '_wc_jilt_consent_notice', $consent_text );

		if ( $ip_address ) {
			$this->update_meta_data( '_wc_jilt_consent_ip_address', $ip_address );
		}

		$this->save();
	}


	/** Contact details helpers ***********************************************/


	/**
	 * Updates the contact email address.
	 *
	 * @since 1.7.0
	 *
	 * @param string $email the email value
	 * @return bool|string success or error message
	 */
	public function set_email( $email ) {

		try {

			parent::set_email( $email );
			return true;

		} catch ( \WC_Data_Exception $e ) {

			return $e->getMessage();
		}
	}


	/**
	 * Updates the contact first name.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name the name value
	 * @return bool|string success or error message
	 */
	public function set_first_name( $name ) {

		try {

			parent::set_first_name( $name );
			return true;

		} catch ( \WC_Data_Exception $e ) {

			return $e->getMessage();
		}
	}


	/**
	 * Updates the contact last name.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name the name value
	 * @return bool|string success or error message
	 */
	public function set_last_name( $name ) {

		try {

			parent::set_last_name( $name );
			return true;

		} catch ( \WC_Data_Exception $e ) {

			return $e->getMessage();
		}
	}


	/**
	 * Whether this is a guest customer or not.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_guest() {

		return 0 === $this->get_id();
	}


}
