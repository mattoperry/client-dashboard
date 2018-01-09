<?php
/**
* Client dashboard events data class
*/

namespace VIP\Client_Dashboard;

class Data_Events extends Data {
		
	public function __construct() {
		parent::__construct();
		$this->table_name = constant( 'TABLE_PREFIX_' . ENV ) . 'events';
		$this->fields = [ 'id', 'name', 'client_slug', 'type', 'open_time', 'close_time', 'value' ];
	}
	
	public function create( $args, $required = null ) {
		return parent::create( $args, [ 'name', 'type', 'open_time', 'client_slug' ] );
	}
	
	public function get_multiple( $args ) {
		// can't search by value
		unset( $args[ 'value' ] );
		return parent::get_multiple( $args );
	}

	// validate stuff specific to this model
	protected function validate( $args, $fields = null, $required = null ) {
		if ( !parent::validate( $args, $fields, $required ) ) {
			return false;
		}
		
		$fields = ( $fields ) ? $fields : $this->fields;
				
		foreach ( $fields as $field ) {
			//move on if it's a not-required field that's not set
			if ( !in_array( $field, array_keys( $args )  ) ) {
				continue;
			}
			switch ( $field ) {
				case 'id':
					if ( !is_numeric( $args[ $field ] ) || $args[ $field ] <= 0 ) {
						return false;
					}
					break;
				case 'name':
				case 'client_slug':
					if ( strlen( $args[ $field ] > 255 ) ) {
						return false;
					}
					break;
				case 'type':
					if ( !in_array( $args[ $field ], [ 'pr', 'commit', 'ticket' ] ) ) {
						return false;
					}
					break;
				case 'open_time':
				case 'close_time':
				case 'offset':
				case 'limit':
					if ( !is_numeric( $args[ $field ] ) || $args[ $field ] <= 0 ) {
						return false;
					}
					break;
 				default:
					break;
			}
		}
		return true;
	}
	
	protected function sanitize( $args ) {
		foreach ( $args as $name => $value ) {
			switch ( $name ) {
				case 'id':
				case 'open_time':
				case 'close_time':
				case 'offset';
				case 'limit':
					$args[ $name ] = (int) $value;
					break;
				case 'name':
				case 'type':
				case 'value':
				case 'client_slug':
					$args[ $name ] = $this->db->real_escape_string( $value );
					break;
				default:
					break;
			}
		}
		return $args;
	}
}