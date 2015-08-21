# Dota-Market-API

###A quick example

```php
$API = new Dota_Tools_MarketAPI($id, $salt);

$from = 10;
print_r($API->getTrades($from));

print_r($API->getMyInventory());

print_r($API->getMyLots());
```
