<?php
/**
 * Restricted as a visitor: nobody is signed in, so every restricted post and file is refused.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo = build();

// So signing in as Sam opens what a visitor was refused.
writer()->grant( $demo['users']['sam'], 'group', $demo['groups']['reports']['uuid'], null, 'admin', '', array(), 1 );

start_page(
	array(
		'This demo shows what someone without access sees. Northwind Learning is a made-up company that sells an onboarding course and quarterly market reports, and keeps them for paying customers.',
		'You are not signed in. The course weeks and the market reports are each in a group, so to a visitor they do not exist. Go through the tour, then sign in as Sam and see what changes.',
	),
	array(
		'<strong>You</strong>: a visitor, not signed in, holding nothing.',
		'<strong>Sam Taylor</strong>: holds the Market reports group. Sign in as <code>sam</code> / <code>demo</code> at the end of the tour.',
	),
	array(
		array(
			'title' => '1. Restricted posts are left out of the blog',
			'url'   => home_url( '/news/' ),
			'link'  => 'Open News',
			'text'  => "Only the welcome post is listed. The two course weeks and the two market reports are not there at all, rather than listed with a lock on them, so the site gives nothing away. The same goes for feeds, the REST API and sitemaps.",
		),
		array(
			'title' => '2. And out of search',
			'url'   => home_url( '/?s=report' ),
			'link'  => 'Search for "report"',
			'text'  => 'No market reports in the results, even though two posts have "report" in their titles.',
		),
		array(
			'title' => '3. A restricted post is a 404',
			'url'   => get_permalink( $demo['posts']['week1'] ),
			'link'  => 'Open week one of the course',
			'text'  => 'Even with the exact address, a visitor gets the site\'s ordinary "page not found". There is no "you need to buy this" page that confirms the post exists.',
		),
		array(
			'title' => '4. A restricted file is never sent',
			'url'   => '',
			'link'  => '',
			'text'  => "The Q3 report PDF is in the Market reports group. Files in a group are moved out of the public uploads folder by Restrict Media File Access, which the plugin works with, and are only sent to someone who holds them. Anyone else following the file's link gets a blank placeholder image instead of the PDF.\nThat holds even if the link is shared: the address alone is not enough.\nDownloads cannot be tried in this demo, because the site runs inside your browser.",
		),
		array(
			'title' => '5. Products are how people get in',
			'url'   => get_permalink( $demo['products']['reports'] ),
			'link'  => 'Open the Market reports product',
			'image' => 'product-for-sale.png',
			'text'  => 'The product page shows the price, the 25% sale and what is included. A visitor who wants to buy is asked to sign in or create an account first.',
		),
		array(
			'title' => '6. Sign in as Sam',
			'url'   => home_url( '/sign-in/' ),
			'link'  => 'Open Sign in',
			'image' => 'auth-sign-in.png',
			'text'  => "Sign in as <code>sam</code> / <code>demo</code>. Sign in, sign up and password reset all happen on the site's own pages, in its own theme.\nSam holds the Market reports, so go back to steps 1 and 2: the reports now appear in News and search, and the Q3 PDF is listed in Sam's Files. Week one of the course is still a 404, because Sam does not hold the course.",
		),
	)
);
