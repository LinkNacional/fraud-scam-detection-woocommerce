=== Fraud and Scam Detection For WooCommerce ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/wordpress/
Tags: woocommerce, antifraud, recaptcha, security, cloudflare
Requires at least: 5.7
Tested up to: 6.9
Stable tag: 1.2.0
Requires PHP: 7.2
Requires Plugins: woocommerce
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Add Google reCAPTCHA or Cloudflare Turnstile verification to WooCommerce checkout to prevent fraudulent transactions.

== Description ==

The **Fraud and Scam Detection For WooCommerce** plugin helps protect your online store by adding a verification layer to the WooCommerce checkout.  
Using **Google reCAPTCHA** or **Cloudflare Turnstile**, the plugin automatically analyzes user interactions and blocks suspicious checkout attempts, reducing fraudulent transactions and ensuring safer payments.

**Main Features:**
- Integration with **Google reCAPTCHA v3**;
- Integration with **Cloudflare Turnstile**;
- Protects WooCommerce checkout against automated bots and fraudulent activity;
- Configurable minimum score threshold for human-like behavior detection (reCAPTCHA);
- **IP banning** — block specific IP addresses from completing checkout directly from the order detail page or the Anti-Fraud settings;
- Lightweight and optimized for performance.

**Dependencies**

This plugin requires [WooCommerce](https://woocommerce.com/) to be installed and active.  
For Google reCAPTCHA, you also need valid [Google reCAPTCHA API keys](https://www.google.com/recaptcha/admin/create).  
For Cloudflare Turnstile, you need valid [Cloudflare Turnstile site and secret keys](https://dash.cloudflare.com/?to=/:account/turnstile).

**User instructions**

1. Go to WordPress admin panel > WooCommerce > Settings > Anti-Fraud;

2. Enable the antifraud option and choose between **Google reCAPTCHA** or **Cloudflare Turnstile**;

3. Enter the corresponding **Site Key** and **Secret Key** for the chosen service;

4. For reCAPTCHA: set the **minimum score threshold** (higher values = stricter validation);

5. Optionally enable **IP check** to ban specific IP addresses from checkout;

6. Optionally enable **debug mode** to log requests and responses;

7. Save the settings. From now on, the WooCommerce checkout will require security validation.

== External services ==

This plugin integrates with Google reCAPTCHA v3 and Cloudflare Turnstile to provide fraud and bot protection for WooCommerce checkout processes.

**Google reCAPTCHA v3**

What the service is and what it is used for:  
Google reCAPTCHA v3 is a security service that analyzes user behavior to determine if a user is likely human or bot. It's used to protect the WooCommerce checkout process from automated fraud attempts and malicious activities.

What data is sent and when:  
When a customer attempts to complete a checkout, the plugin sends the following data to Google reCAPTCHA servers:
- User's IP address
- Browser and device information
- User interaction patterns during checkout
- reCAPTCHA response token

- Google reCAPTCHA Terms of Service: https://developers.google.com/recaptcha/docs/terms
- Google Privacy Policy: https://policies.google.com/privacy

**Cloudflare Turnstile**

What the service is and what it is used for:  
Cloudflare Turnstile is a privacy-friendly CAPTCHA alternative that verifies users without tracking or invasive data collection. It's used to protect the WooCommerce checkout from bots and fraudulent activity.

What data is sent and when:  
When a customer attempts to complete a checkout, the plugin sends the Turnstile response token to Cloudflare servers for validation:
- Turnstile response token
- User's IP address (handled by Cloudflare)

- Cloudflare Turnstile Terms of Service: https://www.cloudflare.com/terms/
- Cloudflare Privacy Policy: https://www.cloudflare.com/privacypolicy/

== Installation ==

1. Look in the sidebar for the WordPress plugins area;

2. In installed plugins look for the option 'add new';

3. Click on the 'send plugin' option in the page title and upload the fraud-scam-detection-woocommerce.zip plugin;

4. Click on the 'install now' button and then activate the installed plugin;

5. Now go to WooCommerce settings > Anti-Fraud;

6. Enter your Google reCAPTCHA credentials, configure the minimum score, and save.

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* WooCommerce installed and active;
* Google reCAPTCHA API keys (if using reCAPTCHA);
* Cloudflare Turnstile site and secret keys (if using Turnstile).

= How does the minimum score work? =

* Google reCAPTCHA v3 assigns a score between 0.0 (likely a bot) and 1.0 (likely human).  
  You can configure the threshold in plugin settings to determine how strict the validation should be.

= How does the IP banning system work? =

* When the **Enable IP Check** option is active, a lookup/ban panel appears on each order detail page.  
  You can also manage the full list of banned IPs (add or remove) directly in **WooCommerce > Settings > Anti-Fraud > Banned IPs**.  
  Any customer attempting to checkout from a banned IP will be blocked and the order will be flagged as fraud.


== Changelog ==
= 1.2.0 =
* New security verification system with Cloudflare Turnstile.
* New IP banning system.

= 1.1.9/1.1.10 =
* New banners according to country.

= 1.1.8 =
* New layout for the plugin images.

= 1.1.7 =
* Fix the plugin URL.

= 1.1.6 =
* Change actions.

= 1.1.5 =
* Fix Wordpress issues.

= 1.1.4 =
* Fix Wordpress issues.

= 1.1.3 =
* Remove plugin updater.

= 1.1.2 =
* Change plugin title.

= 1.1.1 =
* Fix GitHub actions.

= 1.1.0 =
* Add compatibility with shortcode form.

= 1.0.0 =
* Plugin launch with Google reCAPTCHA integration for WooCommerce checkout.

== Upgrade Notice ==
= 1.2.0 =
* New security verification system with Cloudflare Turnstile and IP banning feature.
= 1.1.9/1.1.10 =
* New banners according to country.

= 1.1.8 =
* New layout for the plugin images.

= 1.1.7 =
* Fix the plugin URL.

= 1.1.6 =
* Change actions.

= 1.1.5 =
* Fix Wordpress issues.

= 1.1.4 =
* Fix Wordpress issues.

= 1.1.3 =
* Remove plugin updater.

= 1.1.2 =
* Change plugin title.

= 1.1.1 =
* Fix GitHub actions.

= 1.1.0 =
* Add compatibility with shortcode form.

= 1.0.0 =
* Plugin launch.
