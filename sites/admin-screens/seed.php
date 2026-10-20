<?php
/**
 * Administrator screens: four customers with access from every source, one record expired and one revoked.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo  = build();
$users = $demo['users'];

// Given by an administrator.
writer()->grant( $users['sam'], 'group', $demo['groups']['course']['uuid'], 365, 'admin', '', array(), 1 );
writer()->grant( $users['jordan'], 'file', (string) $demo['files']['workbook'], 30, 'admin', '', array(), 1 );

// Bought: a coupon that takes the price to nothing completes on the site, so a payment row is written without Stripe.
buy( $demo['products']['reports'], $users['alex'], 'FREEPASS' );
buy( $demo['products']['course'], $users['priya'], 'FREEPASS' );

// Claimed free.
buy( $demo['products']['sample'], $users['priya'] );
buy( $demo['products']['sample'], $users['jordan'] );

// Sent by another system, as the access endpoint would record it.
writer()->grant( $users['jordan'], 'group', $demo['groups']['reports']['uuid'], 90, 'crm', 'INV-1042' );

// Ran out last week.
$expired = writer()->grant( $users['alex'], 'post', (string) $demo['posts']['week1'], 7, 'admin', '', array(), 1 );
writer()->set_expiry( $expired, gmdate( 'Y-m-d H:i:s', time() - WEEK_IN_SECONDS ) );

// Taken back by an administrator.
$revoked = writer()->grant( $users['sam'], 'file', (string) $demo['files']['q3'], null, 'admin', '', array(), 1 );
writer()->revoke( $revoked );

start_page(
	array(
		'This demo shows the site owner\'s side. Northwind Learning is a made-up company that sells an onboarding course and quarterly market reports, and uses Gated Media Access to decide who can read them.',
		'You are signed in as the administrator. Everything lives under one menu in the dashboard, <strong>Gated Access</strong>. Four customers already hold access that arrived in each of the four ways the plugin supports, so every screen has something to show.',
	),
	array(
		'<strong>Sam Taylor</strong>: given the Onboarding course group for a year by an administrator. Was also given the Q3 report PDF, which an administrator has since revoked.',
		'<strong>Priya Shah</strong>: bought the Onboarding course with the coupon FREEPASS, and claimed the free sample report.',
		'<strong>Alex Morgan</strong>: bought the Market reports with FREEPASS. Was given week one of the course for seven days, which ran out last week.',
		'<strong>Jordan Lee</strong>: given the onboarding workbook PDF for 30 days, claimed the free sample, and given the Market reports for 90 days by another system (a made-up CRM, reference INV-1042).',
	),
	array(
		array(
			'title' => 'Access: every record, in one list',
			'url'   => admin_url( 'edit.php?post_type=gatedmedia_access' ),
			'link'  => 'Open the Access list',
			'image' => 'admin-access-list.png',
			'text'  => "One row per access record: what it is for (a post, a file or a group), its status, when it expires, and its source.\nThe source says how the access arrived: <code>admin</code> was given by hand, <code>stripe</code> came from a completed order (here, orders a coupon paid in full), <code>free</code> was claimed from a free product, and <code>crm</code> was sent by another system.\nLook for the expired row (Alex's week one) and the revoked row (Sam's Q3 PDF). Records are never deleted when they end, so the history stays.",
		),
		array(
			'title' => 'Add Access: give someone access by hand',
			'url'   => admin_url( 'admin.php?page=gatedmedia-add-access' ),
			'link'  => 'Open Add Access',
			'image' => 'admin-add-access.png',
			'text'  => "Pick a person, pick a post, file or group, and give it a number of days. Leave the days empty and it lasts for life.\nUseful for staff, complimentary copies, or anyone who paid outside the website.",
		),
		array(
			'title' => 'Groups: restrict content by putting it in a group',
			'url'   => admin_url( 'admin.php?page=gatedmedia-groups' ),
			'link'  => 'Open Groups',
			'image' => 'admin-groups.png',
			'text'  => "Each group shows what it holds and who holds it. Market reports holds both reports and their PDFs; Onboarding course holds both weeks and the workbook.\nAdding content to a group is all it takes to restrict it, and everyone who holds the group sees the new content straight away. Groups are only made on this screen, so nobody creates one by accident while editing a post.",
		),
		array(
			'title' => 'Products: what you sell',
			'url'   => admin_url( 'edit.php?post_type=gatedmedia_product' ),
			'link'  => 'Open Products',
			'image' => 'admin-product-editor.png',
			'text'  => "A product is a price, a duration and the things it grants. Northwind sells the Onboarding course for &pound;49 for a year, the Market reports for life at &pound;120 (on a 25% sale, so &pound;90), and gives away a free sample.\nEach product has one address, <code>/access/</code> followed by a long random code, and is kept out of search and sitemaps. Customers find it through a link you share.",
		),
		array(
			'title' => 'Coupons',
			'url'   => admin_url( 'edit.php?post_type=gatedmedia_coupon' ),
			'link'  => 'Open Coupons',
			'text'  => 'FREEPASS takes 100% off and LAUNCH20 takes 20% off. A coupon can be a percentage or a fixed amount, and can have an end date and a limit on how many times it is used, in total and per customer.',
		),
		array(
			'title' => 'Payments: every order',
			'url'   => admin_url( 'admin.php?page=gatedmedia-payments' ),
			'link'  => 'Open Payments',
			'image' => 'admin-payments.png',
			'text'  => "A read-only list of orders. Here there are two, Alex's and Priya's, each paid in full by FREEPASS, so the amount is Free and the coupon column shows what it took off.\nOn a live site this is also where each Stripe payment appears, with the access it created.",
		),
		array(
			'title' => 'Posts: who holds what, at a glance',
			'url'   => admin_url( 'edit.php' ),
			'link'  => 'Open Posts',
			'image' => 'admin-posts-access-column.png',
			'text'  => 'The ordinary posts list gains an Access column counting how many people hold each post. Every post, page and file also gets an Access panel on its own edit screen.',
		),
		array(
			'title' => 'Settings',
			'url'   => admin_url( 'admin.php?page=gatedmedia-settings' ),
			'link'  => 'Open Settings',
			'image' => 'admin-settings-general.png',
			'text'  => 'The shop currency, the address products live at, the Stripe keys, what Revoke does (mark it revoked and keep the history, expire it now, or delete it), and the emails. Each email can be switched off and rewritten.',
		),
	)
);
