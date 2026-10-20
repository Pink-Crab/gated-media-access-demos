# Gated Media Access demos

WordPress Playground demos for the [Gated Media Access](https://github.com/Pink-Crab/PinkCrab-Gated-Media-Access-Plugin) plugin. Each demo opens a complete WordPress site in the browser, with the plugin, its dependency [Restrict Media File Access](https://github.com/a8cteam51/restrict-media-file-access) and sample content in place.

**Open them from the [launcher](https://pink-crab.github.io/gated-media-access-demos/)**, which can also run a demo against an older plugin release: `?demo=<demo>&version=<release>`.

Every site has a **Start here** page listing what to try. Customer accounts use the password `demo`; the administrator is `admin` / `password`.

## Demos

| Demo | Signed in as | Open |
|---|---|---|
| Customer account area: My Access, Files, Orders and Profile | Sam, holding a course, bought reports and a post about to run out | [Open in Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Pink-Crab/gated-media-access-demos/main/sites/customer-account/blueprint.json) |
| Administrator screens: Access, Add Access, Groups, Products, Coupons, Payments, Settings | The administrator | [Open in Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Pink-Crab/gated-media-access-demos/main/sites/admin-screens/blueprint.json) |
| Getting access free: a free product and a 100% coupon | Sam, holding nothing yet | [Open in Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Pink-Crab/gated-media-access-demos/main/sites/free-access/blueprint.json) |
| Restricted as a visitor: 404s, listings, search and refused files | Nobody | [Open in Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Pink-Crab/gated-media-access-demos/main/sites/visitor/blueprint.json) |

Paying through Stripe cannot be shown: Stripe confirms a payment by calling the site, and a Playground site exists only in your browser. Free products and coupons that take the price to nothing never involve Stripe, so those work.

## Layout

```
core/demo.php            the shared content: customers, groups, posts, PDFs, products, coupons, the Start here page
sites/<demo>/
  blueprint.json         installs both plugins, copies the two PHP files in, runs them, signs in
  seed.php               the access records this demo starts with, and its Start here steps
index.html               the launcher, served by GitHub Pages
```

The blueprints install the latest release of each plugin. Seeding goes through the plugin's own classes (`Access_Writer` for access records, `Checkout` for purchases), so the records are the ones the plugin itself would write.

To add a demo: copy a folder under `sites/`, change its `seed.php` and the `seed.php` address in its `blueprint.json`, and add a card to `index.html`.
