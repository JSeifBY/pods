<?php

/**
 * Pods_Object_Group class.
 *
 * @since 2.8
 */
class Pods_Object_Group extends Pods_Object {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'group';

	/**
	 * {@inheritdoc}
	 */
	public function get_fields() {
		if ( array() === $this->_fields ) {
			return array();
		}

		$object_collection = Pods_Object_Collection::get_instance();

		$storage_object = $object_collection->get_storage_object( $this->get_arg( 'storage_type' ) );

		if ( ! $storage_object ) {
			return array();
		}

		if ( null === $this->_fields ) {
			$args = array(
				'object_type'      => 'field',
				'group'            => $this->get_identifier(),
				'group_id'         => $this->get_id(),
				'group_name'       => $this->get_name(),
				'group_identifier' => $this->get_identifier(),
			);

			$fields = $storage_object->find( $args );

			$this->_fields = wp_list_pluck( $fields, 'id' );

			return $fields;
		}

		$fields = array_map( array( $object_collection, 'get_object' ), $this->_fields );
		$fields = array_filter( $fields );

		$names = wp_list_pluck( $fields, 'name' );

		$fields = array_combine( $names, $fields );

		return $fields;
	}

}
