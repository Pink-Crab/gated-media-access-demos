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
 * A heading block.
 *
 * @param string $text  The heading.
 * @param int    $level 2 or 3.
 */
function heading( string $text, int $level = 2 ): string {
	$attributes = 2 === $level ? '' : ' {"level":' . $level . '}';

	return "<!-- wp:heading{$attributes} -->\n<h{$level} class=\"wp-block-heading\">" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->";
}

/**
 * A bulleted list block.
 *
 * @param array<string> $items Each item's HTML.
 */
function bullets( array $items ): string {
	$items = array_map( static fn ( string $item ): string => '<!-- wp:list-item --><li>' . $item . '</li><!-- /wp:list-item -->', $items );

	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( '', $items ) . "</ul>\n<!-- /wp:list -->";
}

/** Where the plugin keeps its documentation screenshots. */
const SCREENSHOTS = 'https://raw.githubusercontent.com/Pink-Crab/PinkCrab-Gated-Media-Access-Plugin/main/docs/images/';

/**
 * A screenshot from the plugin's documentation, as an image block.
 *
 * @param string $file The file in docs/images.
 * @param string $alt  What it shows.
 */
function screenshot( string $file, string $alt ): string {
	return "<!-- wp:image {\"sizeSlug\":\"large\",\"style\":{\"border\":{\"width\":\"1px\",\"color\":\"#cccccc\",\"radius\":\"8px\"}}} -->\n<figure class=\"wp-block-image size-large has-custom-border\"><img src=\"" . esc_url( SCREENSHOTS . $file ) . '" alt="' . esc_attr( $alt ) . "\" class=\"has-border-color\" style=\"border-color:#cccccc;border-width:1px;border-radius:8px\"/><figcaption class=\"wp-element-caption\">The same screen in the plugin's documentation, with different sample data. The link below opens it in this demo.</figcaption></figure>\n<!-- /wp:image -->";
}

/**
 * The same three ideas on every Start here page.
 */
function how_it_works(): string {
	return heading( 'How Gated Media Access works' )
		. "\n\n" . paragraphs( 'Gated Media Access puts posts, pages and files behind access, and decides who gets in. Everything in this demo comes down to three ideas.' )
		. "\n\n" . bullets(
			array(
				'<strong>Groups.</strong> Put a post, page or file in a group and it is restricted. Give someone the group and they can see everything in it, including anything you add to it later. Nobody else can: a restricted post is a 404 for them, and a restricted file is never sent.',
				'<strong>Access records.</strong> Each time someone is given something, one record is written: who, what, for how long, and where it came from. It might come from an administrator, a purchase, a free claim, or another system telling the site someone has paid.',
				'<strong>Products.</strong> A price, a duration and what it grants. Buyers pay through Stripe. Free products, and coupons that take the price to nothing, give access straight away with no payment at all.',
			)
		);
}

/**
 * The page the demo opens on: what this demo is, who is in it, and a guided tour.
 *
 * @param array<string>                                                           $intro  Opening paragraphs.
 * @param array<string>                                                           $people Who is who, one HTML line each.
 * @param array<int, array{title: string, url: string, link: string, text: string}> $tour   One stop per feature: its heading, the screen to open, the link text, and what it is and what to look for (one paragraph per line).
 * @param string                                                                  $note   A closing paragraph, '' for none.
 */
function start_page( array $intro, array $people, array $tour, string $note = '' ): int {
	$stops = array_map(
		static fn ( array $stop, int $index ): string => '<!-- wp:details' . ( 0 === $index ? ' {"showContent":true}' : '' ) . " -->\n"
			. '<details class="wp-block-details"' . ( 0 === $index ? ' open' : '' ) . '><summary>' . esc_html( $stop['title'] ) . "</summary>\n\n"
			. paragraphs( $stop['text'] )
			. ( empty( $stop['image'] ) ? '' : "\n\n" . screenshot( $stop['image'], $stop['title'] ) )
			. ( '' === $stop['url'] ? '' : "\n\n" . paragraphs( '<a href="' . esc_url( $stop['url'] ) . '">' . esc_html( $stop['link'] ) . ' &rarr;</a>' ) )
			. "\n\n</details>\n<!-- /wp:details -->",
		$tour,
		array_keys( $tour )
	);

	$content = paragraphs( implode( "\n", $intro ) )
		. "\n\n" . how_it_works()
		. "\n\n" . heading( 'Who is who' )
		. "\n\n" . bullets( $people )
		. "\n\n" . heading( 'The tour' )
		. "\n\n" . implode( "\n\n", $stops )
		. "\n\n" . heading( 'Signing in' )
		. "\n\n" . paragraphs( 'Every customer in this demo signs in with their first name in lower case and the password <code>' . PASSWORD . '</code>, for example <code>sam</code> / <code>' . PASSWORD . '</code>. The administrator is <code>admin</code> / <code>password</code>. Nothing here is real: the site lives in your browser and is gone when you close the tab.' . ( '' === $note ? '' : "\n" . $note ) );

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
