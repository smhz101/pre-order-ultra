# Pre-Order Ultra

**Pre-Order Ultra** is a powerful WooCommerce plugin that enables store owners to manage pre-orders seamlessly. Whether you're launching a new product or managing out-of-stock inventory, Pre-Order Ultra provides the tools you need to capture customer interest and streamline your sales process.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Setting Up Pre-Orders for New Products](#setting-up-pre-orders-for-new-products)
  - [Enabling Pre-Orders for Out-of-Stock Products](#enabling-pre-orders-for-out-of-stock-products)
  - [Notify Me When Available](#notify-me-when-available)
- [Shortcodes](#shortcodes)
- [Templates](#templates)
- [Developer Documentation](#developer-documentation)
  - [Directory Structure](#directory-structure)
  - [Hooks and Filters](#hooks-and-filters)
- [Changelog](#changelog)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Support](#support)
- [License](#license)

## Features

- **Pre-Order for New Product Launches:**
  - Schedule product releases with specific availability dates.
  - Display countdown timers to create urgency and anticipation.
  - Allow customers to place pre-orders in advance of the official launch.

- **Pre-Order for Out-of-Stock Products:**
  - Enable customers to subscribe for notifications when out-of-stock products become available.
  - Manage subscriptions for both logged-in users and guests.
  - Automatically notify subscribers via email/SMS when products are restocked.

- **Notify Me When Available:**
  - Offer a "Notify Me" button on product pages.
  - Collect subscriber information securely.
  - Handle subscriptions with ease through an admin interface.

- **Customizable Notifications:**
  - Send personalized emails or SMS notifications to subscribers.
  - Customize email/SMS templates to match your brand's voice.

- **Admin Management:**
  - View and manage all pre-order and subscription data from the WordPress dashboard.
  - Export subscriber lists for marketing purposes.

- **Flexible Pricing Options:**
  - Apply different pricing strategies for pre-orders, including discounts or fixed pricing.

- **Integration with Popular Plugins:**
  - Seamlessly integrates with WooCommerce, Dokan, and Easy Digital Downloads (EDD).

## Installation

1. **Download the Plugin:**
   - Download the latest version of Pre-Order Ultra from the [WordPress Plugin Repository](#) or your source.

2. **Upload to WordPress:**
   - Log in to your WordPress admin dashboard.
   - Navigate to **Plugins > Add New**.
   - Click on **Upload Plugin** and select the `pre-order-ultra.zip` file.
   - Click **Install Now** and then **Activate** the plugin.

3. **Activate the Plugin:**
   - After activation, you'll be prompted to configure the plugin settings.

## Configuration

1. **Global Settings:**
   - Navigate to **WooCommerce > Settings > Pre-Order Ultra**.
   - **Enable Pre-Orders Globally:** Toggle to enable or disable pre-order functionality across all products.
   - **Auto Pre-Order for Out-of-Stock Products:** Enable automatic pre-order options when products are out of stock.
   - **Notification Settings:** Configure email and SMS settings for subscriber notifications.

2. **Product-Level Settings:**
   - Edit a product in **WooCommerce > Products**.
   - Scroll down to the **Pre-Order** tab.
   - **Enable Pre-Order:** Check this box to enable pre-order for the specific product.
   - **Set Release Date:** Choose between "No Date" or "Set Date" and specify the availability date and time.
   - **Pre-Order Pricing:** Select your desired pricing strategy for pre-orders.
   - **Notify Me When Available:** Customize the "Notify Me" button and form settings.

## Usage

### Setting Up Pre-Orders for New Products

1. **Create or Edit a Product:**
   - Go to **WooCommerce > Products** and select a product to edit or add a new one.

2. **Enable Pre-Order:**
   - In the **Pre-Order** tab, check the **Enable Pre-Order** option.

3. **Set Release Date:**
   - Choose **"Set Date"** and select the desired release date and time using the date picker.
   - A countdown timer will appear on the product page, showing customers the time remaining until the product is available.

4. **Configure Pre-Order Pricing:**
   - Select a pricing strategy (e.g., fixed price, discount percentage) and set the corresponding values.

5. **Save the Product:**
   - Click **Update** or **Publish** to save your changes.

### Enabling Pre-Orders for Out-of-Stock Products

1. **Global Pre-Order Settings:**
   - Ensure that **Auto Pre-Order for Out-of-Stock Products** is enabled in the global settings.

2. **Manage Product Stock:**
   - For products that are out of stock, pre-order options will automatically be available based on the global settings.

3. **Notify Me When Available:**
   - Customers can subscribe to be notified when the product is back in stock.

### Notify Me When Available

1. **Display the Notify Me Button:**
   - On applicable product pages, the **"Notify Me When Available"** button will appear.

2. **Subscription Process:**
   - **Logged-In Users:**
     - Click the button to subscribe using their account information.
   - **Guests:**
     - Click the button to open a form where they can enter their **Name**, **Email Address**, and **Phone Number**.

3. **Data Handling:**
   - Subscriptions are stored securely in a custom database table.
   - Subscribers receive confirmation emails/SMS upon subscribing.

4. **Notifications Upon Availability:**
   - When the product becomes available, a cron job triggers and sends notifications to all subscribers.
   - Subscribers are informed via their preferred contact method that the product is now in stock.

## Shortcodes

*Currently, Pre-Order Ultra does not utilize shortcodes. Future updates may include shortcode support for greater flexibility.*

## Templates

Pre-Order Ultra includes customizable templates for the "Notify Me" form and pre-order notices. You can override these templates by copying them to your theme's WooCommerce folder.

### Overriding Templates

1. **Locate the Template:**
   - Find the template file in `pre-order-ultra/templates/notify-me-form.php`.

2. **Copy to Theme:**
   - Copy the file to your theme's WooCommerce directory: `your-theme/woocommerce/pre-order-ultra/notify-me-form.php`.

3. **Customize:**
   - Modify the copied template as needed to match your site's design and functionality requirements.

## Developer Documentation

### Hooks and Filters

Pre-Order Ultra utilizes several WordPress and WooCommerce hooks and filters to extend functionality. Below are some key hooks you can leverage:

- **Actions:**
  - `pre_order_ultra_init`: Fires on plugin initialization.
  - `pre_order_ultra_notify_me_submission`: Fires when a user submits the "Notify Me" form.
  - `pre_order_ultra_product_restocked`: Fires when a product's stock status changes to "In Stock."

- **Filters:**
  - `pre_order_ultra_add_to_cart_text`: Modify the "Add to Cart" button text for pre-order products.
  - `pre_order_ultra_price_html`: Adjust product price display for pre-orders.

### Extending Functionality

Developers can extend Pre-Order Ultra by hooking into its actions and filters or by utilizing its classes:

- **Adding Custom Notification Methods:**
  - Extend the `Subscription_Manager` class to integrate with additional notification channels.

- **Customizing the Notify Me Form:**
  - Override the template file to add or remove fields based on your requirements.

- **Integrating with Third-Party Services:**
  - Use the API endpoints to connect Pre-Order Ultra with external systems or services.

## Changelog

### [1.0.0] - 2024-04-27
- Initial release of Pre-Order Ultra.
- Features:
  - Pre-order for new product launches with release dates and countdown timers.
  - Pre-order for out-of-stock products with "Notify Me When Available" functionality.
  - Admin interface for managing pre-orders and subscriptions.
  - Email/SMS notifications to subscribers upon product availability.
  - Integration with WooCommerce, Dokan, and Easy Digital Downloads (EDD).

## Frequently Asked Questions

### 1. **Can I customize the notification emails?**
Yes, Pre-Order Ultra allows you to customize email and SMS templates to match your brand's voice and messaging needs.

### 2. **Does the plugin support variable products?**
Yes, you can enable pre-orders for variable products. Each variation can have its own pre-order settings.

### 3. **How are guest subscriptions handled?**
Guests can subscribe by providing their **Name**, **Email Address**, and optionally **Phone Number**. Their information is securely stored and used for notifications.

### 4. **Is there a limit to the number of pre-orders I can receive?**
Pre-Order Ultra does not impose any limits on the number of pre-orders or subscriptions. However, performance may vary based on your hosting environment.

### 5. **How do I export subscriber data?**
Subscribers can be viewed and exported from the admin interface under **WooCommerce > Pre-Order Subscriptions**.

## Support

If you encounter any issues or have questions about Pre-Order Ultra, please reach out to our support team:

- **Email:** support@preorderultra.com
- **Support Forum:** [WordPress.org Support](#)
- **Documentation:** Available within the plugin and on our [official website](#).

## License

Pre-Order Ultra is licensed under the [GNU General Public License v2.0](http://www.gnu.org/licenses/gpl-2.0.html) or later.