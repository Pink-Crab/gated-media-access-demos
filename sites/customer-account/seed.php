<?php
/**
 * Customer account area: Sam holds the course, the reports bought with a coupon, and a post about to run out.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo = build();
$sam  = $demo['users']['sam'];

writer()->grant( $sam, 'group', $demo['groups']['course']['uuid'], 365, 'admin', '', array(), 1 );
writer()->grant( $sam, 'post', (string) $demo['posts']['sample'], 3, 'admin', '', array(), 1 );
buy( $demo['products']['reports'], $sam, 'FREEPASS' );

start_page(
	array(
		'This demo shows what your customers see. Northwind Learning is a made-up company that sells an onboarding course and quarterly market reports, and uses Gated Media Access to keep them for the people who have paid.',
		'You are signed in as Sam Taylor, one of their customers. Everything below is what Sam sees in the account area, which sits inside the site\'s own theme at <code>/account/</code>.',
	),
	array(
		'<strong>Sam Taylor</strong> (you): holds the Onboarding course group for a year, given by an administrator. Bought the Market reports product with the coupon FREEPASS, so holds that group for life. Was also given the sample post for three days, which is about to run out.',
	),
	array(
		array(
			'title' => 'My Access',
			'url'   => home_url( '/account/' ),
			'link'  => 'Open My Access',
			'image' => 'account-my-access.png',
			'text'  => "Everything Sam holds, in one place, with how long each lasts.\nLook for the two groups, Market reports (lifetime) and Onboarding course (expires in a year), and the sample post at the bottom marked Expires in 3 days. When something is close to running out, the customer is told here and, a few days before, by email.",
		),
		array(
			'title' => 'Restricted posts open for the people who hold them',
			'url'   => get_permalink( $demo['posts']['week1'] ),
			'link'  => 'Open week one of the course',
			'text'  => "Week one of the course is in the Onboarding course group. Sam holds that group, so the post opens like any other.\nFor anyone who does not hold it, the same address is a 404 page, and the post is missing from the blog list and from search. The Restricted as a visitor demo shows that side.",
		),
		array(
			'title' => 'Groups grow with you',
			'url'   => get_permalink( $demo['posts']['q3'] ),
			'link'  => 'Open the Q3 market report',
			'image' => 'account-group-detail.png',
			'text'  => "Sam bought the Market reports product, which grants the Market reports group rather than a fixed list of reports.\nThat matters when the next report comes out: the site owner adds it to the group, and everyone who holds the group can read it straight away. Nobody has to be given it one by one.",
		),
		array(
			'title' => 'Files',
			'url'   => home_url( '/account/files/' ),
			'link'  => 'Open Files',
			'image' => 'account-files.png',
			'text'  => "Every file Sam can download, gathered from all of Sam's groups, with a search box and a filter by type.\nThe files are protected by Restrict Media File Access, which the plugin works with. A protected file is moved out of the public uploads folder and given its own address, and it is only sent to someone who holds it.\nDownloads cannot be tried in this demo, because the site runs inside your browser. On a real site, Download sends the file.",
		),
		array(
			'title' => 'Orders',
			'url'   => home_url( '/account/orders/' ),
			'link'  => 'Open Orders',
			'image' => 'account-order-detail.png',
			'text'  => "What Sam has bought, and when. The Market reports order shows a coupon taking the price to nothing, so it completed on the site without going to Stripe.\nOpen the order to see what it included on the day it was bought and the access it created.",
		),
		array(
			'title' => 'Profile',
			'url'   => home_url( '/account/profile/' ),
			'link'  => 'Open Profile',
			'image' => 'account-profile.png',
			'text'  => 'Sam\'s name, email address and password, kept up to date by Sam without ever seeing the WordPress dashboard.',
		),
	)
);
