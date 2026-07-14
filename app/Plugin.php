<?php

declare(strict_types=1);

namespace BoltShare;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BoltShare\Frontend\Assets;
use BoltShare\Frontend\ShareShortcode;
use BoltShare\Support\SvgIcons;

final class Plugin {

	public function __construct() {
		$assets = new Assets();

		new ShareShortcode( $assets, new SvgIcons() );
	}
}
