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
	'You are signed in as the administrator. Four customers hold access given by hand, bought with a coupon, claimed free and sent by another system. One record has expired and one has been revoked.',
	array(
		array( 'Access', admin_url( 'edit.php?post_type=gatedmedia_access' ), 'every record: who holds what, its status, expiry and source. Filter by holder, item or source.' ),
		array( 'Add Access', admin_url( 'admin.php?page=gatedmedia-add-access' ), 'pick a person, pick an item, give it a duration.' ),
		array( 'Groups', admin_url( 'admin.php?page=gatedmedia-groups' ), 'what each group holds and who holds it.' ),
		array( 'Products', admin_url( 'edit.php?post_type=gatedmedia_product' ), 'three products, one on a 25% sale and one free.' ),
		array( 'Coupons', admin_url( 'edit.php?post_type=gatedmedia_coupon' ), 'FREEPASS takes 100% off, LAUNCH20 takes 20%.' ),
		array( 'Payments', admin_url( 'admin.php?page=gatedmedia-payments' ), 'the two coupon orders, with the access each created.' ),
		array( 'Posts', admin_url( 'edit.php' ), 'the Access column counts who holds each post.' ),
		array( 'Settings', admin_url( 'admin.php?page=gatedmedia-settings' ), 'currency, product address, Stripe keys, what Revoke does and the emails.' ),
	)
);
