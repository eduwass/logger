<?php
namespace AI_Logger\Tests;

use AI_Logger\AI_Logger;
use AI_Logger\Handler\{
    CLI_Handler,
    Post_Handler,
	Post_Meta_Handler,
	Term_Meta_Handler,
	Exception_Handler,
	Handler_Exception
};
use Mantle\Framework\Testing\Framework_Test_Case;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Logger;
use Psr\Log\LogLevel;

/**
 * Test log handlers.
 */
class Test_Class_Handler extends Framework_Test_Case {
	use MockeryPHPUnitIntegration;

	protected function setUp(): void {
		parent::setUp();

		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false' );
	}

	public function test_post_handler() {
		$post_id = $this->factory->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Logger( 'Unit Test', [ new Post_Meta_Handler( Logger::DEBUG, true, $post_id, 'test_key' ) ] );
		$post_logger->info( 'Test message' );

		do_action( 'shutdown' );

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertIsArray( $log, 'Log should have log entries.' );

		$entry = array_shift( $log );
		$this->assertEquals( 'INFO', $entry['level_name'] );
		$this->assertEquals( 'Test message', $entry['message'] );
	}

	public function test_switch_site_writing() {
		$post_id = $this->factory->post->create();

		$this->assertEmpty( get_post_meta( $post_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Logger( 'Unit Test', [ new Post_Meta_Handler( Logger::DEBUG, true, $post_id, 'test_key' ) ] );
		$post_logger->info( 'Test message' );

		$new_blog_id = $this->factory->blog->create();

		switch_to_blog( $new_blog_id );

		// Write to the logger again.
		$post_logger->error( 'Error from another site!' );
		// Even try and process the log here.

		do_action( 'shutdown' );

		// Go back to the original site.
		restore_current_blog();

		// Now process the log.
		do_action( 'shutdown' );

		$log = get_post_meta( $post_id, 'test_key', false );
		$this->assertIsArray( $log, 'Log should have log entries.' );

		$entry = array_shift( $log );
		$this->assertEquals( 'INFO', $entry['level_name'] );
		$this->assertEquals( 'Test message', $entry['message'] );

		// Check that the next log was recorded as well.
		$entry = array_shift( $log );
		$this->assertEquals( 'ERROR', $entry['level_name'] );
		$this->assertEquals( 'Error from another site!', $entry['message'] );
	}

	public function test_term_handler() {
		$term_id = $this->factory->term->create();

		$this->assertEmpty( get_term_meta( $term_id, 'test_key', false ), 'Log should be empty.' );

		// Write the log.
		$post_logger = new Logger( 'Unit Test', [ new Term_Meta_Handler( Logger::DEBUG, true, $term_id, 'test_key' ) ] );
		$post_logger->info( 'Test message' );

		do_action( 'shutdown' );

		$log = get_term_meta( $term_id, 'test_key', false );
		$this->assertIsArray( $log, 'Log should have log entries.' );

		$entry = array_shift( $log );
		$this->assertEquals( 'INFO', $entry['level_name'] );
		$this->assertEquals( 'Test message', $entry['message'] );
	}

	/**
	 * Test calling the logger with `ai_logger()`.
	 */
	public function test_ai_logger() {
		// Ensure all logs are written instantly.
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false', 99 );

		$log_key = 'Log key ' . wp_rand( 1, 1000 );

		// Write to the log.
		ai_logger()->info( $log_key, [ 'context' => 'log-context' ] );

		// Check if the log exists.
		$logs = get_posts(
			[
				'post_type' => 'ai_log',
			]
		);

		$this->assertNotEmpty( $logs );

		$log = array_shift( $logs );
		$this->assertEquals( $log_key, $log->post_title, 'Log post title should match the "' . $log_key . '"' );

		// Verify the context.
		$this->assertEquals( $this->get_log_context( $log->ID ), 'log-context' );
	}

	/**
	 * Test the legacy method to write logs via 'ai_logger_insert'.
	 */
	public function test_legacy_write_logs() {
		// Ensure all logs are written instantly.
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false', 99 );

		$log_key = 'Log key ' . wp_rand( 1, 1000 );

		// Write to the log.
		\do_action( 'ai_logger_insert', $log_key, 'Log message', [ 'context' => 'log-context' ] );

		// Check if the log exists.
		$logs = get_posts(
			[
				'post_type' => 'ai_log',
			]
		);

		$this->assertNotEmpty( $logs );

		$log = array_shift( $logs );
		$this->assertEquals( $log_key, $log->post_title, 'Log post title should match the "' . $log_key . '"' );

		// Verify the context.
		$this->assertEquals( $this->get_log_context( $log->ID ), 'log-context' );
	}

	public function test_post_logger() {
		// Ensure all logs are written instantly.
		add_filter( 'ai_logger_should_write_on_shutdown', '__return_false', 99 );

		$log_message = 'Direct Log key ' . wp_rand( 1, 1000 );

		AI_Logger::instance()->emergency( $log_message, [ 'context' => 'the-context' ] );

		// Check if the log exists.
		$logs = get_posts(
			[
				'post_type' => 'ai_log',
			]
		);

		$this->assertNotEmpty( $logs );

		$log = array_shift( $logs );
		$this->assertEquals( $log_message, $log->post_title, 'Log post title should match the "' . $log_message . '"' );

		// Verify the context.
		$this->assertEquals( $this->get_log_context( $log->ID ), 'the-context' );
	}

	/**
	 * Quick log context getter.
	 *
	 * @param int $log_id Log post ID.
	 * @return string|null
	 */
	protected function get_log_context( int $log_id ): string {
		$terms = \get_the_terms( $log_id, Post_Handler::TAXONOMY_LOG_CONTEXT );
		return ! empty( $terms ) ? $terms[0]->slug : null;
	}

	public function test_wp_cli_handler() {
		$mock = Mockery::mock( 'alias:WP_CLI' );
		$mock->shouldReceive( 'log' )->twice();

		define( 'WP_CLI', true );

		$cli_handler = new CLI_Handler();
		$cli_handler->handle( LogLevel::ALERT, 'An alert to log to the CLI!', [ 1, 2, 3 ] );
		$cli_handler->handle( LogLevel::INFO, 'An info to log to the CLI!', [ 1, 2, 3 ] );
	}

	public function test_exception_handler() {
		$this->expectException( Handler_Exception::class );

		$exception_handler = new Exception_Handler();
		$exception_handler->handle( LogLevel::EMERGENCY, 'A real emergency!', [ 1, 2, 3 ] );
	}
}
