<?php
/**
 * Customer account area: Sam holds the course, the reports bought with a coupon, a post about to run out and the sample.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo = build();
$sam  = $demo['users']['sam'];

writer()->grant( $sam, 'group', $demo['groups']['course']['uuid'], 365, 'admin', '', array(), 1 );
writer()->grant( $sam, 'post', (string) $demo['posts']['sample'], 3, 'admin', '', array(), 1 );
buy( $demo['products']['reports'], $sam, 'FREEPASS' );

start_page(
	'You are signed in as Sam Taylor, a customer. Sam holds the onboarding course for a year, bought the market reports with a coupon, and has a sample post that runs out in three days.',
	array(
		array( 'My Access', home_url( '/account/' ), 'everything Sam holds, with how long each lasts.' ),
		array( 'Week one of the course', get_permalink( $demo['posts']['week1'] ), 'opens, because Sam holds the Onboarding course group.' ),
		array( 'The Q3 market report', get_permalink( $demo['posts']['q3'] ), 'opens, because Sam bought the Market reports product.' ),
		array( 'Files', home_url( '/account/files/' ), 'the PDFs Sam can download.' ),
		array( 'Orders', home_url( '/account/orders/' ), 'the reports order, paid with the FREEPASS coupon.' ),
		array( 'Profile', home_url( '/account/profile/' ), 'Sam\'s name, email address and password.' ),
	)
);
