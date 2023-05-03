# PackBot - A Telegram Bot for Website Monitoring and Analysis

PackBot is a powerful and easy-to-use Telegram bot designed for website monitoring, domain and SEO analysis. Developed in PHP and leveraging the [php-telegram-bot](https://github.com/php-telegram-bot/core) library, PackBot aims to provide a range of essential features to help you keep track of your website's performance.

You can test it here: https://t.me/packhelperbot

## Getting Started

### Prerequisites

- PHP 8.0 or later
- Composer (PHP dependency manager)
- A Telegram bot token obtained from @BotFather
- (Optional) A Google PageSpeed API key

### Installation

1. Create a new bot on Telegram using @BotFather and obtain your unique bot token.
2. Clone the PackBot repository to your server.
3. Navigate to the repository's root directory and run `composer install` to install required dependencies.
4. Copy the `config.sample.php` file and rename it to `config.php`. Update the file with your bot token and (optionally) the Google PageSpeed API key.
5. Create a new MySQL databese and import the `structure.sql`.
6. Open your `config.php` file and update the database connection settings with your database credentials.
7. Set up the webhook by opening the `set.php` file and modifying the webhook URL to match your bot's address (e.g., `https://example.com/bot/hook.php`).

PackBot is now ready to use! Send the `/start` command to your bot on Telegram to begin.

To enable website monitoring, configure your server to execute the `cronjob.php` file every few minutes, passing the "key" parameter from `config.php` as a GET request. Additionally, schedule the `cronjob_daily.php` file to run once daily for the bot to perform routine system tasks.

## Key Features

- **Multilingual Support:** English and Russian language options are available.
- **Website Monitoring:**
  - Receive real-time notifications about your website's status, including response codes and response times.
  - Access detailed failure statistics presented in an easy-to-understand format.
- **Domain Analysis:**
  - Obtain domain age, WHOIS information, and DNS records for any domain.
- **SEO Analysis:**
  - Assess the indexability of URLs and gain insights into search engine visibility.
- **Additional Diagnostics:**
  - Perform CMS detection, server response analysis (with screenshots), and track redirects.
  - *Note:* A Google PageSpeed API key is required for server response analysis. Obtain one [here](https://developers.google.com/speed/docs/insights/v5/get-started).
- **Utility Scripts:**
  - Format URL lists, with file support, and parse sitemaps for convenient data extraction.

With its extensive feature set, PackBot serves as an invaluable tool for monitoring and analyzing your website's performance, allowing you to make data-driven decisions for improvement.
