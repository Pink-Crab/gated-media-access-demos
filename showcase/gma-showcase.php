<?php
/**
 * Plugin Name: Gated Media Access Showcase
 * Description: Explains each Gated Media Access screen in the demos: a "What am I looking at?" panel and numbered markers with tooltips.
 *
 * Installed by the demo blueprints into mu-plugins. The explanations live in gma-showcase/guides.php.
 */

declare( strict_types = 1 );

namespace GMA_Showcase;

/**
 * Which guide fits the current request, '' for none.
 */
function current_screen_key(): string {
	global $pagenow;

	if ( is_admin() ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only routing.
		$page      = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
		// phpcs:enable

		$pages = array(
			'gatedmedia-add-access' => 'add-access',
			'gatedmedia-groups'     => 'groups',
			'gatedmedia-payments'   => 'payments',
			'gatedmedia-settings'   => 'settings',
		);

		if ( 'admin.php' === $pagenow ) {
			return $pages[ $page ] ?? '';
		}

		$lists = array(
			'gatedmedia_access'  => 'access-list',
			'gatedmedia_product' => 'products',
			'gatedmedia_coupon'  => 'coupons',
			'post'               => 'posts',
		);

		return 'edit.php' === $pagenow ? ( $lists[ $post_type ] ?? '' ) : '';
	}

	if ( is_404() ) {
		return 'not-found';
	}

	if ( is_singular( 'gatedmedia_product' ) ) {
		return 'product';
	}

	$path     = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Compared, never output.
	$segments = '' === $path ? array() : explode( '/', $path );

	if ( array( 'sign-in' ) === $segments ) {
		return 'sign-in';
	}

	if ( array() !== $segments && 'account' === $segments[0] ) {
		$section = $segments[1] ?? 'my-access';

		if ( 'orders' === $section && isset( $segments[2] ) ) {
			return 'order';
		}

		return in_array( $section, array( 'my-access', 'files', 'orders', 'profile' ), true ) ? $section : '';
	}

	return '';
}

/**
 * Prints the guide for this screen and loads the panel's script and styles.
 */
function render(): void {
	$key    = current_screen_key();
	$guides = require __DIR__ . '/gma-showcase/guides.php';

	if ( '' === $key || ! isset( $guides[ $key ] ) ) {
		return;
	}

	$guide          = $guides[ $key ];
	$guide['start'] = home_url( '/' );
	$base           = content_url( 'mu-plugins/gma-showcase/' );

	printf( '<link rel="stylesheet" href="%s">', esc_url( $base . 'showcase.css' ) );
	printf( '<script>window.GMA_SHOWCASE = %s;</script>', wp_json_encode( $guide ) );
	printf( '<script src="%s" defer></script>', esc_url( $base . 'showcase.js' ) );
}

add_action( 'admin_footer', __NAMESPACE__ . '\render' );
add_action( 'wp_footer', __NAMESPACE__ . '\render' );
