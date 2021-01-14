<?php
/**
 * Queue_Service_Provider class file.
 *
 * @package Mantle
 */

namespace Mantle\Framework\Providers;

use Mantle\Framework\Contracts\Queue\Dispatcher as Dispatcher_Contract;
use Mantle\Framework\Contracts\Queue\Queue_Manager as Queue_Manager_Contract;
use Mantle\Framework\Queue\Console\Run_Command;
use Mantle\Framework\Queue\Dispatcher;
use Mantle\Framework\Queue\Events\Job_Processed;
use Mantle\Framework\Queue\Events\Run_Complete;
use Mantle\Framework\Queue\Queue_Manager;
use Mantle\Framework\Queue\Worker;
use Mantle\Framework\Queue\Wp_Cron_Job;
use Mantle\Framework\Queue\Wp_Cron_Provider;
use Mantle\Framework\Queue\Wp_Cron_Scheduler;
use Mantle\Framework\Service_Provider;

/**
 * Queue Service Provider
 */
class Queue_Service_Provider extends Service_Provider {

	/**
	 * Register the service provider.
	 */
	public function register() {
		$this->app->singleton(
			'queue',
			function ( $app ) {
				// Register the Queue Manager with the supported providers when invoked.
				$manager = new Queue_Manager( $app );
				$this->register_providers( $manager );
				return $manager;
			}
		);

		$this->app->singleton(
			'queue.worker',
			function ( $app ) {
				return new Worker( $app['queue'], $app['events'] );
			}
		);

		$this->app->singleton_if(
			Dispatcher_Contract::class,
			function( $app ) {
				return new Dispatcher( $app );
			}
		);

		$this->add_command( Run_Command::class );
	}

	/**
	 * Boot the service provider.
	 */
	public function boot() {
		$this->app->make( Queue_Manager_Contract::class );
	}

	/**
	 * Register Queue Providers
	 *
	 * @param Queue_Manager_Contract $manager Queue Manager.
	 */
	protected function register_providers( Queue_Manager_Contract $manager ) {
		$this->register_wp_cron_provider( $manager );
	}

	/**
	 * Register the WordPress Cron Queue Provider
	 *
	 * @param Queue_Manager_Contract $manager Queue Manager.
	 */
	protected function register_wp_cron_provider( Queue_Manager_Contract $manager ) {
		$manager->add_provider( 'wordpress', Wp_Cron_Provider::class );

		// Setup the WordPress cron scheduler.
		\add_action( 'init', [ Wp_Cron_Provider::class, 'on_init' ] );
		\add_action( Wp_Cron_Scheduler::EVENT, [ Wp_Cron_Scheduler::class, 'on_queue_run' ] );

		// Add the event listener to remove the queue item.
		$this->app['events']->listen(
			Job_Processed::class,
			function( Job_Processed $event ) {
				if ( $event->job instanceof Wp_Cron_Job ) {
					$queue_post_id = $event->job->get_post_id();

					if ( $queue_post_id ) {
						wp_delete_post( $queue_post_id, true );
					}
				}
			}
		);

		// Add the event listener to schedule the next cron run.
		$this->app['events']->listen(
			Run_Complete::class,
			function( Run_Complete $event ) {
				if ( $event->provider instanceof Wp_Cron_Provider ) {
					Wp_Cron_Scheduler::schedule_next_run( $event->queue );
				}
			}
		);
	}
}
