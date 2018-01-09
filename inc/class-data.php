<?php
/**
* Client dashboard parent data class -- extend for objec types
*
*/

namespace VIP\Client_Dashboard;

abstract class Data {
	
	protected $db;	//the db connection
	protected $table_name;	//the name of the table
	protected $fields;	// the fields
		
	public function __construct() {
		$this->db = new \mysqli( constant( 'DB_HOSTNAME_' . ENV ), constant( 'DB_USER_' . ENV ), constant( 'DB_PWD_' . ENV ), constant( 'DB_DATABASE_' . ENV ) );
	}
	
	/** QUERY **/
	
	protected function query( $sql ) {
		$res = $this->db->query( $sql );
		if ( is_bool( $res ) ) {
			return $res;
		}elseif( is_a( $res, 'mysqli_result' ) ) {
			return $res->fetch_all( MYSQLI_ASSOC );
		}else{
			return [];
		}		
	}
	
	/** CRUD **/
	
	//should be overridden by child classes to specify required fields.  one liner, though
	public function create( $args, $required ) {
		if ( !$this->validate( $args, $this->fields, $required ) ) {
			return false;
		}
		$clean_args = $this->sanitize( $args );
		
		$fields_list = implode( ',', array_keys( $clean_args ) );
		$values_list = implode( ',', $this->quoteify( $clean_args ) );
		
		$sql = "INSERT INTO {$this->table_name} ( $fields_list ) VALUES ( $values_list )";
		
		return $this->query( $sql );
	}
	
	public function get( $args ) {	
		if ( !$this->validate( $args, [ 'id' ], [ 'id' ] ) ) {
			return false;
		}
		$clean_args = $this->sanitize( $args );
			
		$sql = "SELECT * FROM {$this->table_name} WHERE `id`={$clean_args['id']} LIMIT 1";
			
		return $this->query( $sql );
	}

	public function update ( $args ) {
		if ( !$this->validate( $args, $this->$fields, ['id'] ) ) {
			return false;
		}
		$clean_args = $this->sanitize( $args );
		$update_string = '';
		foreach ( $args as $key => $value ) {
			if ( $key === 'id' ) {
				continue;
			} 
			$update_string .= "`$key`=" . $this->quoteify( $value ) . ',';
		}
		$update_string = trim( $update_string, ',' );
		
		$sql = "UPDATE {$this->table_name} SET $update_string WHERE `id`={$args['id']}";
		return $this->query( $sql );
	}

	public function delete( $args ) {
		
		if ( !$this->validate( $args, [ 'id' ], [ 'id' ] ) ) {
			return false;
		}
		$clean_args = $this->sanitize( $args );
		
		$sql = "DELETE FROM {$this->table_name} WHERE `id`={$clean_args['id']} LIMIT 1";
		
		return $this->query( $sql );
	}
	
	/** GET MULTIPLE **/
	
	public function get_multiple( $args ) {
		if ( !$this->validate( $args ) ) {
			return false;
		}

		$clean_args = $this->sanitize( $args );
		$args_keys = array_keys( $clean_args );
		
		$offset = ( in_array( 'offset', $args_keys ) ) ? (int) $clean_args['offset'] : 0;
		$limit = ( in_array( 'limit', $args_keys ) && (int) $clean_args[ 'limit' ] <= 200 ) ? (int) $clean_args['limit'] : 20;
		$order_by = ( in_array( 'order_by', $args_keys ) && in_array( $clean_args[ 'order_by' ], $this->fields ) ) ?  $clean_args['order_by'] : 'id';
		$order_by_direction = ( in_array( 'order_by_direction', $args_keys ) && $clean_args[ 'order_by_direction' ] === 'ASC' ) ? 'ASC' : 'DESC';
		
		$where_string = '';
		foreach( $clean_args as $key => $value ) {
			if ( in_array( $key, [ 'offset', 'limit', 'order_by', 'order_by_direction' ] ) ) {
				continue;
			}
			$where_string .= "`$key`=" . $this->quoteify( $value ) . ' AND ';
		}
		$where_string = trim( $where_string, ' AND ' );
		$where_string = ( $where_string ) ? 'WHERE ' . $where_string : '';
				
		$sql = "SELECT * FROM {$this->table_name} $where_string ORDER BY $order_by $order_by_direction LIMIT $limit OFFSET $offset";
		return $this->query($sql);
	}

	/** VALIDATION AND SANITIZATION **/
	
	//universal validation -- make sure no funky fields are present, and that all required ones are
	protected function validate( $args, $fields = null, $required = null ) {
		//check for illegal fields
		foreach( array_keys( $args ) as $key ) { 
			if ( !in_array( $key, $this->fields ) && !in_array( $key, [ 'offset', 'limit', 'order_by', 'order_by_direction' ] ) ) {
				return false;
			}
		}
		//check that all required fields are present, and that everything has the right form
		$fields = ( !$fields ) ? $this->fields : $fields;
		$required = ( !$required ) ? [] : $required;
		foreach ( $fields as $field ) {
			//fail if it's a required field that's not set
			if ( in_array( $field, $required ) && !in_array( $field, array_keys( $args ) ) ) {
				return false;
			}
		}
		return true;
	}
	
	// every model must implement its own sanitication
	abstract protected function sanitize( $args );
	
	/** HELPERS **/
	
	//adds since quotes to the non-numeric items in an array
	protected function quoteify( $thing ) {
		if ( !( is_array( $thing ) || is_string( $thing ) || is_numeric( $thing ) ) ) {
			return $thing;
		}
		if (is_array( $thing ) ) {
			return array_map( function( $item ) { return is_numeric( $item ) ? $item : "'$item'"; }, $thing );
		}else{
			return is_numeric( $thing ) ? $thing : "'$thing'";
		}
	}
}