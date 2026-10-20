<?php
/**
 * Getting access free: Sam holds nothing yet, and can claim the free product and use a 100% coupon on a priced one.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo = build();

start_page(
	'You are signed in as Sam Taylor, a customer who holds nothing yet. Stripe is not needed for either of these: a free product and a coupon that takes the price to nothing both give access on the spot.',
	array(
		array( 'Free sample report', get_permalink( $demo['products']['sample'] ), 'press Join, and the sample post and its PDF are Sam\'s.' ),
		array( 'Onboarding course, 12 months', get_permalink( $demo['products']['course'] ), 'type FREEPASS in the coupon field and apply it: the price drops to nothing. Press Get access, and the order completes without Stripe.' ),
		array( 'My Access', home_url( '/account/' ), 'both products\' contents, with the course running for a year.' ),
		array( 'Orders', home_url( '/account/orders/' ), 'the course order, paid with the coupon.' ),
		array( 'Market reports, lifetime', get_permalink( $demo['products']['reports'] ), 'on a 25% sale. Try LAUNCH20 to see a coupon on top of a sale. Paying for real needs Stripe keys, which this demo does not have.' ),
	)
);
