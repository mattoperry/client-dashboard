<?php
/**
* Client dashboard base class
*
*/

namespace VIP\Client_Dashboard;

class Dashboard {
	
	/** data objects 
	* @todo will need to be private, but we'll live with public for testing now
	**/
	public $current_endpoint;
		
	public function __construct() {
	}
	
	// configuration for endpoints -- note: all are assumed to be GET
	public function endpoints() {
		return [
			'events' => [
				'handler' => 'events',
				'allowed_args' => [ 'id', 'name', 'client_slug', 'type', 'open_time', 'close_time', ],
			],
		];
	}
	
	/** HANDLERS **/
	
	protected function events( $dirty_args ) {
		$model = new Data_Events;
		/** sanitization and validation within **/
		$data = $model->get_multiple( $dirty_args );
		$data = (!$data) ? [] : $data;
		if ( is_array( $data) ) {
			$this->send_response( $data );
		}else{
			$this->send_response( [ 'error' => 'invalid response' ], false );
		}
	}
	
	/** PLUMBING **/
	
	public function do_request() {
		//only work from the configured URI
		$uri = trim( parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH ), '/' );
		if ( $uri !== constant( 'BASE_API_URI_' . ENV ) ) {
			$this->send_response( [ 'error' => 'incorrect base URI' ], false );
		}
		
		/** set and validate the current endpoint **/
		$this->current_endpoint = ( in_array( $_GET[ 'action' ], array_keys( $this->endpoints(), true ) ) ) ? $_GET[ 'action' ] : '';
		if ( !$this->current_endpoint ) {
			$this->send_response( [ 'error' => 'incorrect endpoint' ], false );
		}
		
		/** check the args **/
		$dirty_args = $_GET;
		$always_ok_args = [ 'offset', 'limit', 'order_by', 'order_by_direction' ];
		unset( $dirty_args[ 'action' ] );
		if ( array_diff( array_keys( $dirty_args ), array_merge( $this->endpoints()[ $this->current_endpoint ][ 'allowed_args' ], $always_ok_args ) ) ) {
			$this->send_response( [ 'error' => 'invalid arg for endpoint ' . $this->current_endpoint ], false );
		}
		
		$handler = $this->endpoints()[ $this->current_endpoint ]['handler'];
		
		if ( !$handler ) {
			$this->send_response( [ 'error' => 'invalid handler, check configuration ' . $this->current_endpoint ], false );
		}
		
		/** note: handlers will delegate sanitization and further validation to the model **/
		$this->$handler( $dirty_args );
	}
	
	protected function send_response( $data, $success = true ) {
		@header( 'Content-Type: application/json;' );
		$response = [ 'success' => $success , 'data' => $data ];
		echo json_encode( $response );
		die;
	}
}