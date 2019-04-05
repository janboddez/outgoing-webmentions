<?php
/**
 * Plugin Name: Outgoing Webmentions
 * Description: Attempts to send webmentions to web pages linked to in a post.
 * GitHub Plugin URI: https://github.com/janboddez/outgoing-webmentions
 * Author: Jan Boddez
 * Author URI: https://janboddez.tech/
 * License: GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Version: 0.1
 */

// Prevent this script from being loaded directly.
defined( 'ABSPATH' ) or exit;

// Load Composer's autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class.
 *
 * @since 0.1
 */
class Outgoing_Webmentions {
	/**
	 * Class constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'publish_post', array( $this, 'send_webmention' ), 10, 2 );
	}

	/**
	 * Attempts to send webmentions to all URLs mentioned in a post.
	 *
	 * @param int $post_id Unique post ID.
	 * @param WP_Post $post Corresponding WP_Post object.
	 *
	 * @since 0.1
	 */
	public function send_webmention( $post_id, $post ) {
		// Prevent double posting.
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Init Webmention Client.
		$client = new IndieWeb\MentionClient();

		// Grab the post's HTML and list outgoing links.
		$html = apply_filters( 'the_content', $post->post_content );
		$urls = $client->findOutgoingLinks( $html );

		if ( empty( $urls ) || ! is_array( $urls ) ) {
			// Nothing to do. Bail.
			return;
		}

		foreach ( $urls as $url ) {
			// Try to find a Webmention endpoint.
			$endpoint = $client->discoverWebmentionEndpoint( $url );

			if ( $endpoint ) {
				// Send the webmention.
				$response = wp_safe_remote_post( esc_url_raw( $endpoint ), array(
					'body'=> array(
						'source' => rawurlencode( get_permalink( $post_id ) ),
						'target' => rawurlencode( $url ),
					),
				) );

				if ( is_wp_error( $response ) ) {
					// Something went wrong.
					error_log( print_r( $response->get_error_messages(), true ) );
				}
			}
		}
	}
}

new Outgoing_Webmentions();
