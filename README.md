# Partially prepayment plugin for WordPress - WooCommerce
___
It's not exactly plugin, you can't install this from tab Plugins.
However, you can set up it flexible. Plugin contains data access layer (DAL),
so you can make changes  inside.
---
## Dependencies
- WordpPress
- WooCommerce
- ACF Plugin (used for DAL, you can use another system)
## How to use it?
1) You should register user (or user should be registered) on your website while get payment.
2) Create product category and name it "__prepayment__".
3) Add prepayment product, regular price will be saved for user. After that user will pay
difference between price (or sale price - so you can save sale for user) and full price
   <br /><br />
    _For example: regular price for product is 1000, sale is 900, prepayment product regular price
   is 100, user can buy that product with price 800 (800 + 100 = 900), so discount will be saved
   for that user when you already disable discount._
   <br /><br />
4) For start plugin you should include prepayment-plugin.php in your functions.php
```PHP
require_once __DIR__ . '/ghost-plugins/prepayment-plugin.php';
```
It will call method of Prepayment class:
```php
$prepayment_plugin = new \Ghost\Prepayment\Prepayment();
$prepayment_plugin->Run();
```
---
### Show prepayment for users
You can use method IsPluginOn of Prepayment class for check plugin status.
```php
$prepayment_plugin->IsPluginOn();
```