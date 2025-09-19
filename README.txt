=== Fraud and Scam Detection For WooCommerce ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/wordpress/
Tags: woocommerce, antifraud, recaptcha, security, fraud
Requires at least: 5.7
Tested up to: 6.8
Stable tag: 1.1.6
Requires PHP: 7.2
Requires Plugins: woocommerce
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Add Google reCAPTCHA verification to WooCommerce checkout to prevent fraudulent transactions.

== Description ==

The **Fraud and Scam Detection For WooCommerce** plugin helps protect your online store by adding a verification layer to the WooCommerce checkout.  
Using **Google reCAPTCHA**, the plugin automatically analyzes user interactions and blocks suspicious checkout attempts, reducing fraudulent transactions and ensuring safer payments.

**Main Features:**
- Integration with **Google reCAPTCHA**;
- Protects WooCommerce checkout against automated bots and fraudulent activity;
- Configurable minimum score threshold for human-like behavior detection;
- Lightweight and optimized for performance.

**Dependencies**

This plugin requires [WooCommerce](https://woocommerce.com/) to be installed and active.  
You also need valid [Google reCAPTCHA API keys](https://www.google.com/recaptcha/admin/create).

**User instructions**

1. Go to WordPress admin panel > WooCommerce > Settings > Anti-Fraud;

2. Enable the reCAPTCHA option;

3. Enter your Google reCAPTCHA **Site Key** and **Secret Key**;

4. Set the **minimum score threshold** (higher values = stricter validation);

5. Optionally enable **debug mode** to log requests and responses;

6. Save the settings. From now on, the WooCommerce checkout will require reCAPTCHA validation.

== External services ==

This plugin integrates with Google reCAPTCHA v3 service to provide fraud and bot protection for WooCommerce checkout processes.

**What the service is and what it is used for:**
Google reCAPTCHA v3 is a security service that analyzes user behavior to determine if a user is likely human or bot. It's used to protect the WooCommerce checkout process from automated fraud attempts and malicious activities.

**What data is sent and when:**
When a customer attempts to complete a checkout on your WooCommerce store, the plugin sends the following data to Google reCAPTCHA servers:
- User's IP address
- Browser and device information
- User interaction patterns during checkout
- reCAPTCHA response token

This data is sent every time a customer loads the checkout page and attempts to place an order.

**Service terms and privacy policy:**
- Google reCAPTCHA Terms of Service: https://developers.google.com/recaptcha/docs/terms
- Google Privacy Policy: https://policies.google.com/privacy

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
* Google reCAPTCHA API keys.

= How does the minimum score work? =

* Google reCAPTCHA v3 assigns a score between 0.0 (likely a bot) and 1.0 (likely human).  
  You can configure the threshold in plugin settings to determine how strict the validation should be.


== Changelog ==
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
