<?php

namespace VSP;

use VSP\Core\Traits\WC_Compatibility\Version;
use VSP\Core\Traits\WC_Compatibility\Product;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( '\VSP\WC_Compatibility' ) ) {
	/**
	 * Class WC_Compatibility
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	final class WC_Compatibility {
		use Version;
		use Product;
	}
}
