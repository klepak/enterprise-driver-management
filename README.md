# Enterprise Driver Management

## HP

### Downloading catalog

#### Console
```
php artisan hpcat:update
```

Only driver packs:  
```
php artisan hpcat:update --dpc
```

Only product catalog:
```
php artisan hpcat:update --pc
```

#### Code

```php
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

HpDriverPackCatalogController::checkForCatalogUpdates();
HpProductCatalogController::checkForCatalogUpdates();
```

### Processing catalog
Catalog needs to be downloaded using above steps before processing.

#### Console
```bash
php artisan hpcat:process
```

Only driver packs:  
```
php artisan hpcat:process --dpc
```

Only product catalog:
```
php artisan hpcat:process --pc
```

#### Code

```php
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

(new HpDriverPackCatalogController)->processCatalog();
(new HpProductCatalogController)->processCatalog();
```
