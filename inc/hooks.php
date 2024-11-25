<?php
/**
 * Hooks
 *
 * @package typesense
 */

namespace Typesense;

/**
 * Get option and fall back to constant
 *
 * @param string $field lowercase name of the field.
 * @param string $default_value default in case of missing value.
 * @return string|null
 */
function get_config( $field, $default_value = null ) {
	$value = get_option( $field );

	if ( $value ) {
		return $value;
	}

	$field = strtoupper( $field );

	if ( defined( $field ) ) {
		return constant( $field );
	}

	return $default_value;
}

/**
 * Index a single post
 *
 * @param int      $post_id The post ID.
 * @param \WP_Post $post The post.
 */
function index_post( int $post_id, \WP_Post $post ) {
	$url        = get_config( 'typesense_url', 'http://localhost' );
	$token      = get_config( 'typesense_api_key' );
	$collection = get_config( 'typesense_collection', 'wp_posts' );

	$body = $post->to_array();

	// handle dates by convention as int64.
	$body['post_date']         = strtotime( $post->post_date );
	$body['post_date_gmt']     = strtotime( $post->post_date_gmt );
	$body['post_modified']     = strtotime( $post->post_modified );
	$body['post_modified_gmt'] = strtotime( $post->post_modified_gmt );

	/**
	 * Fires before Typesense receives a post update.
	 *
	 * This is the opportunity to transform the payload that is going to be sent
	 * to Typesense server.
	 *
	 * @param array $body Array representation of WP_Post.
	 * @param int $post_id The post ID.
	 * @param \WP_Post $post The post object.
	 */
	$body = apply_filters( 'typesense_before_post_update', $body, $post_id, $post );

	$result = wp_remote_post(
		"$url/collections/$collection/documents?action=upsert",
		array(
			'method'      => 'POST',
			'timeout'     => 5,
			'headers'     => array(
				'Content-Type'        => 'application/json',
				'X-TYPESENSE-API-KEY' => $token,
			),
			'data_format' => 'body',
			'body'        => wp_json_encode( $body ),
		)
	);

	if ( ( is_wp_error( $result ) || $result['response']['code'] > 299 ) && wp_is_json_request() ) {
		return wp_send_json_error( $result, 400 );
	}
}

/**
 * Delete a single post
 *
 * @param int      $post_id The post ID.
 * @param \WP_Post $post The post.
 */
function delete_post( int $post_id, \WP_Post $post ) {
	$url        = get_config( 'typesense_url', 'http://localhost' );
	$token      = get_config( 'typesense_api_key' );
	$collection = get_config( 'typesense_collection', 'wp_posts' );

	/**
	 * Fires before Typesense receives a post delete.
	 *
	 * This is the opportunity to transform the post ID if a different reference
	 * or format is used in Typesense server.
	 *
	 * @param int $post_id The post ID.
	 * @param \WP_Post $post The post object.
	 */
	$post_id = apply_filters( 'typesense_before_post_delete', $post_id, $post );

	$result = wp_remote_request(
		"$url/collections/$collection/documents/$post_id?ignore_not_found=true",
		array(
			'method'  => 'DELETE',
			'timeout' => 5,
			'headers' => array(
				'Content-Type'        => 'application/json',
				'X-TYPESENSE-API-KEY' => $token,
			),
		)
	);

	if ( ( is_wp_error( $result ) || $result['response']['code'] > 299 ) && wp_is_json_request() ) {
		return wp_send_json_error( $result, 400 );
	}
}

/**
 * Sends event of post update
 */
add_action(
	'post_updated',
	function ( int $post_id, \WP_Post $post ) {
		$public = is_post_publicly_viewable( $post ) && 'publish' === get_post_status( $post );

		if ( $public ) {
			return index_post( $post_id, $post );
		} else {
			return delete_post( $post_id );
		}
	},
	10,
	3
);

/**
 * Sends event of post delete
 */
add_action(
	'delete_post',
	__NAMESPACE__ . '\\delete_post'
);
