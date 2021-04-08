<?php

class Current_Password_Change extends WP_UnitTestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once __DIR__ . '/../../001-core/password.php';
	}

	public function setUp() {
		parent::setUp();

		$this->factory->user->create( [
			'user_login' => 'john',
			'user_email' => 'john@example.com',
			'user_pass'  => 'secret1',
		] );
	}

	public function test__should_return_if_no_pass_update() {
		$errors               = new WP_Error();
		$user                 = get_user_by( 'login', 'john' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'update-user_' . $user->ID );
		do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, $user ) );

		$this->assertFalse( $errors->has_errors() );
	}

	public function test__should_return_error_if_no_current_pass() {
		$_POST                = [
			'pass1' => 'somepassword',
		];
		$errors               = new WP_Error();
		$user                 = get_user_by( 'login', 'john' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'update-user_' . $user->ID );
		do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, $user ) );

		$expected_error = '<strong>ERROR</strong>: Please enter your current password.';

		$this->assertTrue( $errors->has_errors() );
		$this->assertEquals( $expected_error, $errors->get_error_message( 0 ) );
	}

	public function test__should_return_error_if_current_pass_incorrect() {
		$_POST                = [
			'pass1'        => 'somepassword',
			'current_pass' => 'incorrect',
		];
		$errors               = new WP_Error();
		$user                 = get_user_by( 'login', 'john' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'update-user_' . $user->ID );
		do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, $user ) );

		$expected_error = '<strong>ERROR</strong>: The entered current password is not correct.';

		$this->assertTrue( $errors->has_errors() );
		$this->assertEquals( $expected_error, $errors->get_error_message( 0 ) );
	}

	public function test__should_succeed_with_correct_password() {
		$_POST                = [
			'pass1'        => 'somepassword',
			'current_pass' => 'secret1',
		];
		$errors               = new WP_Error();
		$user                 = get_user_by( 'login', 'john' );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'update-user_' . $user->ID );
		do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, $user ) );

		$this->assertFalse( $errors->has_errors() );
	}
}
