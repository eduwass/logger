<?php
/**
 * Event_Service_Provider class file.
 *
 * @package Mantle
 */

namespace Mantle\Framework\Providers;

use Mantle\Framework\Events\Dispatcher;
use Mantle\Framework\Service_Provider;

/**
 * Event Service Provider
 */
class Event_Service_Provider extends Service_Provider {
	/**
	 * Register any application services.
	 */
	public function register() {
		$this->app->singleton(
			'events',
			function( $app ) {
				return new Dispatcher( $app );
			}
		);
	}
}