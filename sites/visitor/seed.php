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
	'You are not signed in. Some posts are open to everyone; the rest belong to a group, and a group\'s posts and files are refused to anyone who does not hold it.',
	array(
		array( 'News', home_url( '/news/' ), 'only the welcome post is listed. The course weeks and market reports are left out.' ),
		array( 'Search for "report"', home_url( '/?s=report' ), 'no market reports in the results.' ),
		array( 'Week one of the course', get_permalink( $demo['posts']['week1'] ), 'a 404, as if it did not exist.' ),
		array( 'The Q3 report PDF', wp_get_attachment_url( $demo['files']['q3'] ), 'refused: instead of the PDF you get a blank placeholder image, because the file is served only to people who hold it.' ),
		array( 'Market reports, lifetime', get_permalink( $demo['products']['reports'] ), 'the product page, with its price and sale. Buying asks you to sign in first.' ),
		array( 'Sign in', home_url( '/sign-in/' ), 'sign in as sam / demo. Sam holds the market reports, so the Q3 PDF now opens, while the course stays a 404.' ),
	)
);
