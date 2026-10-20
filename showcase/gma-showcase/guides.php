<?php
/**
 * What each screen shows, for the showcase panel and its markers.
 *
 * Each guide: a title, paragraphs for the panel, and tips. A tip is a CSS selector, a short label and the text its tooltip shows. A tip whose element is not on the page is left out.
 */

declare( strict_types = 1 );

$no_downloads = 'Downloads cannot be tried in this demo, because the site runs inside your browser. On a real site, Download sends the file to the person holding it.';

return array(

	'access-list' => array(
		'title' => 'The Access list',
		'intro' => array(
			'Every time someone is given something, the plugin writes one access record. This screen lists them all: one row per person per thing.',
			'Nothing here is ever lost. When access runs out or is taken back, the record stays with its new status, so you can always see who had what and when.',
		),
		'tips'  => array(
			array( '#gatedmedia_holder', 'Holder', 'The person who holds this access.' ),
			array( '#gatedmedia_item', 'Item', 'What they hold: a single post, a single file, or a whole group. Holding a group means holding everything in it, including anything added later.' ),
			array( '#gatedmedia_status', 'Status', 'Active means they can see it now. Expired means its time ran out. Revoked means an administrator took it back.' ),
			array( '#gatedmedia_expiry', 'Expiry', 'When the access ends, or Lifetime if it never does. Open a record to change its date: a past date ends it now, a future date brings it back.' ),
			array( '#gatedmedia_source', 'Source', 'How the access arrived. admin: given by hand. stripe: from a completed order. free: claimed from a free product. Anything else, like crm here, was sent by another system through the plugin\'s access endpoint.' ),
			array( '#gatedmedia_filter_source', 'Filters', 'Narrow the list by holder, by item, by type of item or by source.' ),
		),
	),

	'add-access' => array(
		'title' => 'Add Access',
		'intro' => array(
			'Give someone access by hand. Useful for staff, complimentary copies, or anyone who paid outside the website.',
			'The result is an ordinary access record, the same as a purchase makes, so it shows in the customer\'s account straight away.',
		),
		'tips'  => array(
			array( '#gatedmedia_user_search', 'Person', 'Start typing a name or email address to find the customer.' ),
			array( '#gatedmedia_item_type', 'What', 'A single post, a single file, or a group. Choose a group to give everything in it, now and later.' ),
			array( '#gatedmedia_duration', 'Days', 'How long it lasts. Leave it empty for lifetime. Giving more days to someone whose access is still running adds them on to the end.' ),
		),
	),

	'groups' => array(
		'title' => 'Groups',
		'intro' => array(
			'A group is how content gets restricted. Put a post, page or file in a group and only people holding the group can see it. Everybody else gets a 404 for the post and nothing at all for the file.',
			'Groups are live. Add a new report to Market reports and everyone who holds Market reports can read it at once, without being given it one by one.',
			'Groups are only created here, never from the post editor, so nobody makes one by accident with a typo.',
		),
		'tips'  => array(
			array( '#gatedmedia_group_name', 'New group', 'Name a group to create it. Then add content to it from any post, page or file\'s edit screen.' ),
			array( '.gatedmedia-admin-panel', 'A group', 'What the group holds and who holds it.' ),
		),
	),

	'products' => array(
		'title' => 'Products',
		'intro' => array(
			'A product is what you sell: a price, how long it lasts, and the posts, files or groups it gives. Open one to edit all of that in a single block.',
			'Each product lives at its own address, /access/ followed by a long random code, and is kept out of search and sitemaps. People reach it through a link you share, an email or your own page.',
		),
		'tips'  => array(
			array( '#title', 'Product', 'Northwind sells three: the onboarding course for a year, the market reports for life, and a free sample.' ),
			array( '#gatedmedia_price', 'Price', 'Price and any sale. Market reports are on a 25% sale, so &pound;120 becomes &pound;90. A price of nothing makes a free product, claimed with a Join button.' ),
			array( 'select[name="gatedmedia_bulk_sale"]', 'Sales', 'Put several products on sale at once: tick them, then choose a sale here.' ),
		),
	),

	'coupons' => array(
		'title' => 'Coupons',
		'intro' => array(
			'A coupon takes a percentage or a fixed amount off. It can have an end date and a limit on how many times it is used, in total and per customer.',
			'A coupon that takes the price to nothing completes the order on the site, with no payment. The Payments screen shows two orders made that way.',
		),
		'tips'  => array(
			array( '#title', 'Code', 'The code customers type. FREEPASS takes 100% off; LAUNCH20 takes 20%.' ),
		),
	),

	'payments' => array(
		'title' => 'Payments',
		'intro' => array(
			'Every order, read only. On a live site this is where each Stripe payment appears, with the access it created.',
			'The two orders here were paid in full by the FREEPASS coupon, so they completed on the site without Stripe.',
		),
		'tips'  => array(
			array( '#holder', 'User', 'Who bought it.' ),
			array( '#amount', 'Amount', 'What they paid. Free here, because the coupon covered it all.' ),
			array( '#coupon', 'Coupon', 'Which coupon was used and how much it took off.' ),
			array( '#status', 'Status', 'Complete once paid. Access is only given when a payment completes, never before.' ),
			array( '#reference', 'Reference', 'The order\'s own code. Open an order to see what it included and the access it created.' ),
		),
	),

	'posts' => array(
		'title' => 'Posts',
		'intro' => array(
			'Your ordinary posts list, with two additions from the plugin.',
		),
		'tips'  => array(
			array( '#taxonomy-gatedmedia_access', 'Groups', 'The groups each post is in. A post in a group is restricted.' ),
			array( '#gatedmedia_access', 'Access', 'How many people hold this post, directly or through a group. Quick edit can give someone access from here.' ),
		),
	),

	'settings' => array(
		'title' => 'Settings',
		'intro' => array(
			'Everything about how the plugin behaves on this site, in tabs. The Notifications tab holds the emails: each one can be switched off, and its subject and body rewritten.',
		),
		'tips'  => array(
			array( '#gatedmedia_account_creation', 'Accounts', 'How customers get an account: by signing up, made by an administrator, or made when they buy.' ),
			array( '#gatedmedia_currency', 'Currency', 'The shop currency for every price.' ),
			array( '#gatedmedia_product_path', 'Product address', 'The word in front of every product\'s address. access gives /access/ followed by the product\'s code.' ),
			array( '#gatedmedia_stripe_mode', 'Stripe', 'Test or live, with a set of keys for each. A saved secret is never shown again.' ),
			array( '#gatedmedia_revoke_behaviour', 'Revoke', 'What Revoke does: mark it revoked and keep the history, end it now, or delete it.' ),
		),
	),

	'my-access' => array(
		'title' => 'My Access',
		'intro' => array(
			'The customer\'s own page, at /account/. It sits inside the site\'s theme, so it looks like the rest of the site, and customers never see the WordPress dashboard.',
			'Everything they hold is listed here, grouped into groups, posts and files, with how long each lasts.',
		),
		'tips'  => array(
			array( '.gatedmedia-account-nav', 'Account menu', 'My Access, Files, Orders and Profile. On a phone this becomes a row of tabs.' ),
			array( '.gatedmedia-view--my-access .gatedmedia-row', 'An item', 'Something they hold. A group shows how many items it holds; open it to see them.' ),
			array( '.gatedmedia-expiry', 'Expiry', 'How long is left. Close to the end it says so here, and the customer is also sent a reminder email.' ),
		),
	),

	'files' => array(
		'title' => 'Files',
		'intro' => array(
			'Every file the customer can download, gathered from everything they hold.',
			'The files are protected by Restrict Media File Access, which the plugin works with: each one is moved out of the public uploads folder and only sent to someone holding it.',
			$no_downloads,
		),
		'tips'  => array(
			array( '.gatedmedia-filter', 'Search and filter', 'Find a file by name, or show only PDFs, videos, ZIPs or audio.' ),
			array( '.gatedmedia-row__action', 'Download', $no_downloads ),
		),
	),

	'orders' => array(
		'title' => 'Orders',
		'intro' => array(
			'What the customer has bought, and when. Open an order to see what it included on the day it was bought and the access it created.',
			'An order paid in full by a coupon shows as Free.',
		),
		'tips'  => array(
			array( '.gatedmedia-view--orders .gatedmedia-row', 'An order', 'The product, the date, what was paid and whether it is complete.' ),
		),
	),

	'order' => array(
		'title' => 'An order',
		'intro' => array(
			'One order in full: what was paid, any coupon, what the product included on the day it was bought, and the access it created.',
			'Coming back from Stripe before the payment is confirmed, this page says it is confirming and updates itself, so nobody has to reload.',
		),
		'tips'  => array(),
	),

	'profile' => array(
		'title' => 'Profile',
		'intro' => array(
			'The customer keeps their own name, email address and password up to date here, without the WordPress dashboard.',
		),
		'tips'  => array(
			array( '.gatedmedia-profile-group--main', 'Details', 'Name and contact details.' ),
			array( '.gatedmedia-profile-group--password', 'Password', 'Change the password, after typing the current one.' ),
		),
	),

	'product' => array(
		'title' => 'A product page',
		'intro' => array(
			'What a customer sees before buying: the price, any sale, what is included, a coupon field and the buy button.',
			'Paying needs Stripe, which cannot work in this demo: Stripe confirms a payment by calling the website, and this website exists only in your browser. Free products, and coupons that take the price to nothing, work here because no payment is needed.',
		),
		'tips'  => array(
			array( '.gatedmedia-price-block', 'Price', 'The price, any sale, and how long the access lasts.' ),
			array( '.gatedmedia-contents', 'What is included', 'The posts, files and groups this product gives.' ),
			array( '.gatedmedia-coupon', 'Coupon', 'Type a code and press Apply to see what it takes off before buying. Try FREEPASS or LAUNCH20.' ),
			array( '.gatedmedia-buy', 'Buy', 'Join for a free product, Get access for a priced one. A visitor is asked to sign in first.' ),
			array( '.gatedmedia-notice', 'Already held', 'Shown when the customer already holds this product.' ),
		),
	),

	'sign-in' => array(
		'title' => 'Sign in',
		'intro' => array(
			'Signing in, signing up and resetting a password all happen on the site\'s own pages, in its own theme. wp-login.php is left exactly as it was.',
			'Every customer in this demo signs in with their first name in lower case and the password demo, for example sam / demo.',
		),
		'tips'  => array(),
	),

	'not-found' => array(
		'title' => 'Page not found',
		'intro' => array(
			'If you followed a link to a restricted post, this is the point: someone without access gets the site\'s ordinary 404 page.',
			'There is no "buy this to read it" page that would confirm the post exists. Restricted posts are also left out of the blog, search, feeds and sitemaps.',
		),
		'tips'  => array(),
	),
);
