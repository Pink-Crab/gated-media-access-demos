<?php
/**
 * Getting access free: Sam holds nothing yet, and can claim the free product and use a 100% coupon on a priced one.
 */

declare( strict_types = 1 );

namespace GMA_Demo;

$demo = build();

start_page(
	array(
		'This demo shows a customer getting access without paying. Northwind Learning is a made-up company that sells an onboarding course and quarterly market reports, and gives away a free sample.',
		'You are signed in as Sam Taylor, a customer who holds nothing yet. Follow the tour in order: claim the free sample, then use a coupon to get the course for nothing, then see both in Sam\'s account.',
	),
	array(
		'<strong>Sam Taylor</strong> (you): a new customer with no access at all.',
	),
	array(
		array(
			'title' => '1. Claim a free product',
			'url'   => get_permalink( $demo['products']['sample'] ),
			'link'  => 'Open the Free sample report',
			'image' => 'product-free.png',
			'text'  => "A product with no price shows a Join button instead of a price. Press it and the sample post and its PDF are Sam's for life, straight away, with no order and no payment.\nFree products are a way to give something away in exchange for an account, such as a sample report or a taster lesson.",
		),
		array(
			'title' => '2. Use a coupon that makes it free',
			'url'   => get_permalink( $demo['products']['course'] ),
			'link'  => 'Open the Onboarding course',
			'image' => 'product-coupon-applied.png',
			'text'  => "The course costs &pound;49 for a year. Type <code>FREEPASS</code> in the coupon field and press Apply: the page shows what the coupon takes off and the new price, which is nothing.\nPress Get access. Because there is nothing to pay, the order completes on the site without going to Stripe, and you land on the order itself.",
		),
		array(
			'title' => '3. See it in the account area',
			'url'   => home_url( '/account/' ),
			'link'  => 'Open My Access',
			'image' => 'account-my-access.png',
			'text'  => 'My Access now lists the Onboarding course group (for a year) and the sample post (for life). Files lists the PDFs that came with them (downloads cannot be tried in this demo, because the site runs inside your browser), and Orders lists the course order with the coupon.',
		),
		array(
			'title' => '4. A sale and a coupon together',
			'url'   => get_permalink( $demo['products']['reports'] ),
			'link'  => 'Open the Market reports',
			'image' => 'product-for-sale.png',
			'text'  => "The reports are &pound;120 on a 25% sale, so &pound;90. Apply <code>LAUNCH20</code> to see a 20% coupon come off the sale price.\nPaying the rest needs Stripe, which cannot work here: Stripe confirms a payment by calling the website, and this website exists only in your browser. On a real site, the buyer pays on Stripe's own page and the access is given the moment Stripe confirms it.",
		),
	)
);
