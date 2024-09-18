# WZ NLB Payment Gateway for WooCommerce

First and foremost, this project was completed in September 2023. Please provide feedback for additional improvements or bug reports.

**WZ NLB Payment Gateway** is a custom WooCommerce payment gateway that integrates the NLB (National Bank) payment system with your WooCommerce store. This plugin allows seamless transactions using NLB's secure payment methods, ensuring a smooth and reliable experience for both customers and merchants.

## Features

- Easy integration with WooCommerce
- Supports NLB payment gateway for credit/debit card transactions
- Customizable settings from the WordPress admin panel
- Secure and reliable transaction handling
- Compatible with the latest WooCommerce versions
- Multilingual support (optional)

## Requirements

- WordPress 5.0 or higher (tested to work seamlessly on WordPress 5)
- WooCommerce 4.0 or higher
- PHP 7.2 or higher
- NLB Merchant account

## Installation

1. **Download the Plugin:**
   - Clone the repository or download it as a zip file.

2. **Upload the Plugin:**
   - Log into your WordPress dashboard.
   - Navigate to **Plugins > Add New**.
   - Click on **Upload Plugin**, then select the zip file you downloaded.

3. **Activate the Plugin:**
   - After uploading, click **Activate** to enable the plugin.
   - Additionally, it can be enabled from the **WooCommerce Payments** tab.

4. **Configure the Plugin:**
   - Go to **WooCommerce > Settings > Payments**.
   - Click on **NLB Payment Gateway** and enter your NLB API credentials and any other necessary settings.

## Configuration

1. **Merchant Account Information:**
   - Obtain API credentials from your NLB merchant account.
   - Enter the Merchant ID and Secret Key in the WooCommerce NLB Payment Gateway settings.

2. **Payment Settings:**
   - Set payment methods, currency, and transaction options according to your preference.

3. **Testing:**
   - Enable test mode to ensure that everything is working as expected before going live.

## Usage

Once the plugin is configured, customers will be able to select the **NLB Payment Gateway** option during the checkout process. After entering their payment information, they will be redirected to NLB’s secure payment page to complete the transaction.

## Development

### Cloning the Repository

```bash
git clone https://github.com/yilmazca/wz-nlb-payment-gateway-for-woocommerce.git
cd wz-nlb-payment-gateway-for-woocommerce
Contributions
We welcome contributions from the community. To contribute:

Fork the repository.
Create a new branch (git checkout -b feature-name).
Commit your changes (git commit -am 'Add new feature').
Push to the branch (git push origin feature-name).
Create a pull request.
Issues
If you encounter any problems or have suggestions for improvement, please open an issue on GitHub.

License
This project is licensed under the MIT License - see the LICENSE file for details.

Attribution and Respect for Effort
Please respect the work that has gone into this project. If you copy or clone this repository, kindly give proper attribution. Your recognition and acknowledgment of the effort put into developing this project is greatly appreciated.