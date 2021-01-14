<?php
/**
 * Application Contract interface file.
 *
 * @package Mantle
 */

namespace Mantle\Framework\Contracts;

use RuntimeException;
use Mantle\Framework\Contracts\Kernel as Kernel_Contract;
use Mantle\Framework\Service_Provider;

/**
 * Application Contract
 */
interface Application {
	/**
	 * Getter for the base path.
	 *
	 * @return string
	 */
	public function get_base_path(): string;

	/**
	 * Set the base path for a application.
	 *
	 * @param string $path Path to set.
	 */
	public function set_base_path( string $path );

	/**
	 * Get the path to the application "app" directory.
	 *
	 * @param string $path Path to append, optional.
	 * @return string
	 */
	public function get_app_path( string $path = '' ): string;

	/**
	 * Set the application directory.
	 *
	 * @param string $path Path to use.
	 * @return static
	 */
	public function set_app_path( string $path );

	/**
	 * Getter for the bootstrap path.
	 *
	 * @param string $path Path to append.
	 * @return string
	 */
	public function get_bootstrap_path( string $path = '' ): string;

	/**
	 * Set the root URL of the application.
	 *
	 * @param string $url Root URL to set.
	 */
	public function set_root_url( string $url );

	/**
	 * Getter for the root URL.
	 *
	 * @param string $path Path to append.
	 * @return string
	 */
	public function get_root_url( string $path = '' ): string;

	/**
	 * Get the cache folder root
	 *
	 * @return string
	 */
	public function get_cache_path(): string;

	/**
	 * Get the cached Composer packages path.
	 * Folder that stores all compiled server-side assets for the application.
	 *
	 * @return string
	 */
	public function get_cached_packages_path(): string;

	/**
	 * Get the path to the application configuration files.
	 *
	 * @return string
	 */
	public function get_config_path(): string;

	/**
	 * Get the Application's Environment
	 *
	 * @return string
	 */
	public function environment(): string;

	/**
	 * Check if the Application's Environment matches a list.
	 *
	 * @param string|array ...$environments Environments to check.
	 * @return bool
	 */
	public function is_environment( ...$environments ): bool;

	/**
	 * Get the application namespace.
	 *
	 * @return string
	 *
	 * @throws RuntimeException Thrown on error determining namespace.
	 */
	public function get_namespace(): string;

	/**
	 * Check if the application is running in the console.
	 *
	 * @return bool
	 */
	public function is_running_in_console(): bool;

	/**
	 * Determine if the application has booted.
	 *
	 * @return bool
	 */
	public function is_booted(): bool;

	/**
	 * Boot the application's service providers.
	 */
	public function boot();

	/**
	 * Register a new boot listener.
	 *
	 * @param callable $callback Callback for the listener.
	 */
	public function booting( $callback );

	/**
	 * Register a new "booted" listener.
	 *
	 * @param callable $callback Callback for the listener.
	 */
	public function booted( $callback );

	/**
	 * Run the given array of bootstrap classes.
	 *
	 * Bootstrap classes should implement `Mantle\Framework\Contracts\Bootstrapable`.
	 *
	 * @param string[]        $bootstrappers Class names of packages to boot.
	 * @param Kernel_Contract $kernel Kernel instance.
	 */
	public function bootstrap_with( array $bootstrappers, Kernel_Contract $kernel );

	/**
	 * Get an instance of a service provider.
	 *
	 * @param string $name Provider class name.
	 * @return Service_Provider|null
	 */
	public function get_provider( string $name ): ?Service_Provider;

	/**
	 * Get all service providers.
	 *
	 * @return Service_Provider[]
	 */
	public function get_providers(): array;

	/**
	 * Determine if the application is cached.
	 *
	 * @return bool
	 */
	public function is_configuration_cached(): bool;

	/**
	 * Retrieve the cached configuration path.
	 *
	 * @return string
	 */
	public function get_cached_config_path(): string;
}
