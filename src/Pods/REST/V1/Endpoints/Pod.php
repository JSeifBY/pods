<?php

namespace Pods\REST\V1\Endpoints;

use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
use Tribe__REST__Endpoints__DELETE_Endpoint_Interface as DELETE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use Tribe__REST__Endpoints__UPDATE_Endpoint_Interface as UPDATE_Interface;
use WP_Error;
use WP_REST_Request;

class Pod
	extends Base
	implements READ_Interface,
	UPDATE_Interface,
	DELETE_Interface,
	Swagger_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public $route = '/pods/%1$d';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get_documentation() {
		$GET_defaults = [
			'in'      => 'query',
			'default' => '',
			'type'    => 'string',
		];

		// @todo Handle get/post/delete

		return [
			'get' => [
				'summary'    => __( 'Returns a single ticket data', 'pods' ),
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns the data of the ticket with the specified post ID', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Ticket',
								],
							],
						],
					],
					'400' => [
						'description' => __( 'The ticket post ID is invalid.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'401' => [
						'description' => __( 'The ticket with the specified ID is not accessible.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => __( 'A ticket with the specified ID does not exist.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function READ_args() {
		return [
			'id'             => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'include_fields' => [
				'type'        => 'integer',
				'description' => __( 'Whether to include fields (default: off)', 'pods' ),
				'default'     => 0,
			],
			'include_groups' => [
				'type'        => 'integer',
				'description' => __( 'Whether to include groups (default: off)', 'pods' ),
				'default'     => 0,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		$id = $request['id'];

		return $this->get_by_args( [
			'id' => $id,
		], $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function EDIT_args() {
		return [
			'id'    => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'name'  => [
				'type'        => 'string',
				'description' => __( 'The new name of the pod', 'pods' ),
			],
			'label' => [
				'type'        => 'string',
				'description' => __( 'The singular label of the pod', 'pods' ),
			],
			'args'  => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Pod.', 'pods' ),
				'swagger_type' => 'array',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function update( WP_REST_Request $request ) {
		$id = $request['id'];

		$pod = $this->get_by_args( [
			'id' => $id,
		], $request );

		if ( is_wp_error( $pod ) ) {
			return $pod;
		}

		// Get the pod from the response.
		$pod = $pod['pod'];

		$defaults = [
			'id'            => null,
			'name'          => null,
			'label'         => null,
			'args'          => null,
		];

		$params = wp_parse_args( $request->get_params(), $defaults );
		$params = array_filter( $params, [ $this->validator, 'is_not_null' ] );

		if ( isset( $params['args'] ) ) {
			$args = $params['args'];

			unset( $params['args'] );

			// Attempt to convert from JSON to array if needed.
			if ( is_string( $args ) ) {
				$json = @json_decode( $args, true );

				if ( is_array( $json ) ) {
					$args = $json;
				}
			}

			if ( is_array( $args ) ) {
				$params = array_merge( $params, $args );
			}
		}

		// Pass the pod object.
		$params['pod'] = $pod;

		// Handle update.
		pods_api()->save_pod( $params );

		// Return the refreshed pod data.
		return $this->get_by_args( [
			'id'           => $id,
			'bypass_cache' => true,
		], $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_edit() {
		// @todo Check Pods permissions
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function DELETE_args() {
		return [
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function delete( WP_REST_Request $request ) {
		$id = $request['id'];

		return $this->get_by_args( [
			'id' => $id,
		], $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_delete() {
		// @todo Check Pods permissions
		return true;
	}

	/**
	 * Get the response using PodsAPI::load_pod() arguments.
	 *
	 * @since 2.8
	 *
	 * @param array           $args    List of PodsAPI::load_pod() arguments.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array|WP_Error The response or an error.
	 * @throws \Exception
	 */
	public function get_by_args( array $args, WP_REST_Request $request ) {
		$pod = pods_api()->load_pod( $args );

		if ( empty( $pod ) ) {
			// @todo Fix error messaging.
			return new WP_Error( 'no', 'Pod not found' );
		}

		$data = [
			'pod' => $pod,
		];

		if ( 1 === $request['include_fields'] ) {
			// Setup fields.
			$data['fields'] = $pod->get_fields();
		}

		if ( 1 === $request['include_groups'] ) {
			// Setup fields.
			$data['groups'] = $pod->get_groups();
		}

		return $data;
	}
}
