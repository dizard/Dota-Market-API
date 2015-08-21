# Dota-Market-API

###A quick example

```php
require_once("DizardMarketAPI.php");

$API = new DizardMarketAPI($id, $salt);

$from = 10;
print_r($API->getTrades($from));

print_r($API->getMyInventory());

print_r($API->getMyLots());
```
