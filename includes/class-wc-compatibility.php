<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

use VSP\Core\Traits\WC_Compatibility\Version;
use VSP\Core\Traits\WC_Compatibility\Product;

if ( ! class_exists( '\VSP\WC_Compatibility' ) ) {
	/**
	 * Class WC_Compatibility
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 */
	final class WC_Compatibility {
		use Version;
		use Product;
	}
}
