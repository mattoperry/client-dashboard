<?php
/**
* Client dashboard events data class
*/

namespace VIP\Client_Dashboard;

class Data_Objects extends Data {
		
	public function __construct() {
		parent::__construct();
		$this->table_name = constant( 'TABLE_PREFIX_' . ENV ) . 'objects';
		$this->fields = [ 'id', 'client_slug', 'type', 'platform', 'name', 'value' ];
	}
	
	public function create( $args, $required = null ) {
		return parent::create( $args, [ 'name', 'type', 'client_slug' ] );
	}

	// validate stuff specific to this model
	protected function validate( $args, $fields = null, $required = null ) {
		
		if ( !parent::validate( $args, $fields, $required ) ) {
			return false;
		}
		
		$field = ( $fields ) ? $fields : $this->fields;
		
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
					if ( !in_array( $args[ $field ], [ 'site', 'repo', 'meta' ] ) ) {
						return false;
					}
					break;
				case 'platform':
					if ( !in_array( $args[ $field ], [ 'go', 'wpcom' ] ) ) {
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
					$args[ $name ] = (int) $value;
					break;
				case 'name':
				case 'type':
				case 'platform':
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