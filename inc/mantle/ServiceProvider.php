<?php
namespace AI_Logger\Mantle;

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../bootstrap.php';

class Service_Provider extends \Mantle\Framework\Service_Provider {

	/**
	 * Register any application services.
	 */
	public function register() {
		var_dump( 'logger register', $this->app ?? null );
	}

	/**
	 * Bootstrap services.
	 */
	public function boot() {
		var_dump( 'logger boot', $this->app ?? null );
	}
}
