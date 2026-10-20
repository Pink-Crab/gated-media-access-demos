<?php
/**
 * The content every demo shares: customers, groups, posts, files, products, coupons and the start page.
 *
 * Each site's seed.php requires this after wp-load.php, calls build(), then adds its own access records.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

use Dice\Dice;
use PinkCrab\Gated_Access\Access\Access_Writer;
use PinkCrab\Gated_Access\Admin\Coupon_Metabox;
use PinkCrab\Gated_Access\Payments\Checkout;
use PinkCrab\Gated_Access\Products\Product_Meta;
use PinkCrab\Gated_Access\Registration\Access_Taxonomy;
use PinkCrab\Gated_Access\Registration\Post_Types;

/** Every demo account uses this password. */
const PASSWORD = 'demo';

/**
 * One of the plugin's own classes, built the way the plugin builds them.
 *
 * @param string $class_name The class.
 */
function service( string $class_name ): object {
	static $container = null;
	$container      ??= ( new Dice() )->addRule( '*', array( 'shared' => true ) );

	return $container->create( $class_name );
}

/**
 * The plugin's one writer of access records.
 */
function writer(): Access_Writer {
	return service( Access_Writer::class );
}

/**
 * Buys a product through the plugin's own checkout, as the buy button would.
 *
 * @param int    $product_id The product.
 * @param int    $user_id    The buyer.
 * @param string $coupon     A coupon code, '' for none.
 */
function buy( int $product_id, int $user_id, string $coupon = '' ): void {
	service( Checkout::class )->purchase( $product_id, $user_id, $coupon );
}

/**
 * A customer account.
 *
 * @param string $login The username.
 * @param string $name  First and last name.
 */
function customer( string $login, string $name ): int {
	list( $first, $last ) = explode( ' ', $name, 2 );

	return (int) wp_insert_user(
		array(
			'user_login'   => $login,
			'user_pass'    => PASSWORD,
			'user_email'   => $login . '@example.com',
			'display_name' => $name,
			'first_name'   => $first,
			'last_name'    => $last,
			'role'         => 'subscriber',
		)
	);
}

/**
 * A group, with its UUID.
 *
 * @param string $name        The group's name.
 * @param string $description What it holds.
 * @return array{id: int, uuid: string}
 */
function group( string $name, string $description ): array {
	$term = wp_insert_term( $name, Access_Taxonomy::TAXONOMY, array( 'description' => $description ) );
	$id   = (int) $term['term_id'];

	return array(
		'id'   => $id,
		'uuid' => service( Access_Taxonomy::class )->uuid_for( $id ),
	);
}

/**
 * Puts a post or file in a group, which restricts it.
 *
 * @param int                          $object_id The post or attachment.
 * @param array{id: int, uuid: string} $group     The group.
 */
function add_to_group( int $object_id, array $group ): void {
	wp_set_object_terms( $object_id, array( $group['id'] ), Access_Taxonomy::TAXONOMY, true );
}

/**
 * A published post.
 *
 * @param string $title      The title.
 * @param string $paragraphs The body, one paragraph per line.
 */
function post( string $title, string $paragraphs ): int {
	return (int) wp_insert_post(
		array(
			'post_title'   => $title,
			'post_content' => paragraphs( $paragraphs ),
			'post_status'  => 'publish',
		)
	);
}

/**
 * A PDF in the media library.
 *
 * @param string $filename The file's name.
 * @param string $title    Its title, also printed on the page.
 * @param string $body     One line of text under the title.
 */
function pdf_file( string $filename, string $title, string $body ): int {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload = wp_upload_bits( $filename, null, pdf( $title, $body ) );
	$id     = (int) wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_mime_type' => 'application/pdf',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	// Restrict Media File Access will not move a file that has no metadata.
	$metadata               = wp_generate_attachment_metadata( $id, $upload['file'] );
	$metadata['filesize'] ??= filesize( $upload['file'] );
	wp_update_attachment_metadata( $id, $metadata );

	return $id;
}

/**
 * A one-page PDF with a title and a line of text.
 *
 * @param string $title The heading.
 * @param string $body  The line under it.
 */
function pdf( string $title, string $body ): string {
	$escape  = static fn ( string $text ): string => str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $text );
	$text    = 'BT /F1 20 Tf 72 720 Td (' . $escape( $title ) . ') Tj /F1 12 Tf 0 -32 Td (' . $escape( $body ) . ') Tj ET';
	$objects = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
		'<< /Length ' . strlen( $text ) . " >>\nstream\n" . $text . "\nendstream",
		'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
	);

	$out     = "%PDF-1.4\n";
	$offsets = array();

	foreach ( $objects as $index => $object ) {
		$offsets[] = strlen( $out );
		$out      .= ( $index + 1 ) . " 0 obj\n" . $object . "\nendobj\n";
	}

	$xref = strlen( $out );
	$out .= 'xref' . "\n0 " . ( count( $objects ) + 1 ) . "\n0000000000 65535 f \n";

	foreach ( $offsets as $offset ) {
		$out .= sprintf( "%010d 00000 n \n", $offset );
	}

	return $out . 'trailer' . "\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
}

/**
 * A published product.
 *
 * @param string        $title       The product's name.
 * @param string        $description A line about it.
 * @param int           $price       Price in pence.
 * @param int|null      $days        Duration in days, null for lifetime.
 * @param array<string> $items       What it grants, as `type:identifier`.
 */
function product( string $title, string $description, int $price, ?int $days, array $items ): int {
	$id = (int) wp_insert_post(
		array(
			'post_type'    => Post_Types::PRODUCT,
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:gated-media-access/product-details {"lock":{"move":true,"remove":true}} /-->' . "\n\n" . paragraphs( $description ),
		)
	);

	update_post_meta( $id, Product_Meta::META_PRICE, $price );
	update_post_meta( $id, Product_Meta::META_DURATION, null === $days ? Product_Meta::DURATION_LIFETIME : (string) $days );

	foreach ( $items as $item ) {
		add_post_meta( $id, Product_Meta::META_ITEMS, $item );
	}

	return $id;
}

/**
 * A published coupon. The code is the title.
 *
 * @param string $code  The code customers type.
 * @param string $type  percent or fixed.
 * @param int    $value Percent, or pence off.
 */
function coupon( string $code, string $type, int $value ): int {
	$id = (int) wp_insert_post(
		array(
			'post_type'   => Post_Types::COUPON,
			'post_title'  => $code,
			'post_status' => 'publish',
		)
	);

	update_post_meta( $id, Coupon_Metabox::META_TYPE, $type );
	update_post_meta( $id, Coupon_Metabox::META_VALUE, $value );

	return $id;
}

/**
 * Paragraph blocks, one per line.
 *
 * @param string $text Lines of text.
 */
function paragraphs( string $text ): string {
	$blocks = array_map(
		static fn ( string $line ): string => "<!-- wp:paragraph -->\n<p>" . $line . "</p>\n<!-- /wp:paragraph -->",
		array_filter( array_map( 'trim', explode( "\n", $text ) ) )
	);

	return implode( "\n\n", $blocks );
}

/**
 * The page the demo opens on: what this demo shows and links to try.
 *
 * @param string                       $intro A line or two about the demo.
 * @param array<int, array{0: string, 1: string, 2: string}> $steps Link text, URL and what to expect, per step.
 */
function start_page( string $intro, array $steps ): int {
	$items = array_map(
		static fn ( array $step ): string => '<!-- wp:list-item --><li><a href="' . esc_url( $step[1] ) . '">' . esc_html( $step[0] ) . '</a>: ' . esc_html( $step[2] ) . '</li><!-- /wp:list-item -->',
		$steps
	);

	$content = paragraphs( esc_html( $intro ) )
		. "\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Try this</h2>\n<!-- /wp:heading -->\n\n"
		. "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">" . implode( '', $items ) . "</ol>\n<!-- /wp:list -->\n\n"
		. paragraphs( 'Every demo account uses the password <code>' . PASSWORD . '</code>. Administrator: <code>admin</code> / <code>password</code>.' );

	$page = (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => 'Start here',
			'post_content' => $content,
			'post_status'  => 'publish',
		)
	);

	$news = (int) wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_title'  => 'News',
			'post_status' => 'publish',
		)
	);

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page );
	update_option( 'page_for_posts', $news );

	return $page;
}

/**
 * The shared site: an online training company selling its course notes and market reports.
 *
 * @return array<string, array<string, mixed>>
 */
function build(): array {
	// The administrator, so content keeps its blocks and the plugin's capability checks pass.
	wp_set_current_user( 1 );

	update_option( 'blogname', 'Northwind Learning' );
	update_option( 'blogdescription', 'Training notes and market reports' );

	$users = array(
		'sam'    => customer( 'sam', 'Sam Taylor' ),
		'priya'  => customer( 'priya', 'Priya Shah' ),
		'alex'   => customer( 'alex', 'Alex Morgan' ),
		'jordan' => customer( 'jordan', 'Jordan Lee' ),
	);

	$groups = array(
		'course'  => group( 'Onboarding course', 'Every week of the onboarding course.' ),
		'reports' => group( 'Market reports', 'The quarterly market reports and their PDFs.' ),
		'samples' => group( 'Free samples', 'Given away to anyone who signs up.' ),
	);

	$posts = array(
		'welcome' => post( 'Welcome to Northwind Learning', "Course notes and market reports for teams that sell.\nSome posts are open to everyone. The rest sit behind access." ),
		'week1'   => post( 'Onboarding course: week one', "Week one covers the product range and who buys it.\nRead the notes, then try the quiz at the end." ),
		'week2'   => post( 'Onboarding course: week two', "Week two covers pricing, discounts and the first call.\nThe worked examples are at the bottom." ),
		'q2'      => post( 'Market report: Q2', "Demand held steady through the second quarter.\nThe full figures are in the PDF." ),
		'q3'      => post( 'Market report: Q3', "The third quarter brought the first rise in a year.\nThe full figures are in the PDF." ),
		'sample'  => post( 'Sample report: how we research', "How each market report is put together, from survey to summary.\nFree to anyone who signs up." ),
	);

	$files = array(
		'q2'       => pdf_file( 'market-report-q2.pdf', 'Market report: Q2', 'Demand held steady through the second quarter.' ),
		'q3'       => pdf_file( 'market-report-q3.pdf', 'Market report: Q3', 'The third quarter brought the first rise in a year.' ),
		'workbook' => pdf_file( 'onboarding-workbook.pdf', 'Onboarding workbook', 'Exercises for every week of the onboarding course.' ),
		'sample'   => pdf_file( 'research-method.pdf', 'How we research', 'From survey to summary, step by step.' ),
	);

	add_to_group( $posts['week1'], $groups['course'] );
	add_to_group( $posts['week2'], $groups['course'] );
	add_to_group( $files['workbook'], $groups['course'] );
	add_to_group( $posts['q2'], $groups['reports'] );
	add_to_group( $posts['q3'], $groups['reports'] );
	add_to_group( $files['q2'], $groups['reports'] );
	add_to_group( $files['q3'], $groups['reports'] );

	add_to_group( $posts['sample'], $groups['samples'] );
	add_to_group( $files['sample'], $groups['samples'] );

	$products = array(
		'course'  => product( 'Onboarding course, 12 months', 'Every week of the onboarding course and its workbook, for a year.', 4900, 365, array( 'group:' . $groups['course']['uuid'] ) ),
		'reports' => product( 'Market reports, lifetime', 'Every quarterly market report, now and to come.', 12000, null, array( 'group:' . $groups['reports']['uuid'] ) ),
		'sample'  => product( 'Free sample report', 'See how a report is put together, free.', 0, null, array( 'post:' . $posts['sample'], 'file:' . $files['sample'] ) ),
	);

	// A sale, so the products list and the product page show one.
	update_post_meta( $products['reports'], Product_Meta::META_SALE_TYPE, Product_Meta::SALE_PERCENT );
	update_post_meta( $products['reports'], Product_Meta::META_SALE_VALUE, 25 );

	$coupons = array(
		'FREEPASS' => coupon( 'FREEPASS', 'percent', 100 ),
		'LAUNCH20' => coupon( 'LAUNCH20', 'percent', 20 ),
	);

	return array(
		'users'    => $users,
		'groups'   => $groups,
		'posts'    => $posts,
		'files'    => $files,
		'products' => $products,
		'coupons'  => $coupons,
	);
}
