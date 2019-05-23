# Enterprise Driver Management

Automatically generate and maintain driver sets for computer models from HP. Dell, Lenovo and Microsoft coming soon.

## Getting started

### Install package
```
composer require klepak/enterprise-driver-management
```

### Publish config
```
php artisan vendor:publish --provider="Klepak\DriverManagement\DriverManagementServiceProvider" --force
```

### Migrate
```
php artisan migrate
```

## Usage

### Downloading catalog

#### Console
```
php artisan catalog:update hp,dell,lenovo
```

Only driver packs:  
```
php artisan catalog:update hp,dell,lenovo --dpc
```

Only product catalog:
```
php artisan catalog:update hp,dell,lenovo --pc
```

#### Code

```php
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController;

HpDriverPackCatalogController::checkForCatalogUpdates();
HpProductCatalogController::checkForCatalogUpdates();

DellDriverPackCatalogController::checkForCatalogUpdates();
DellCatalogPcController::checkForCatalogUpdates();
```

### Processing catalog
Catalog needs to be downloaded using above steps before processing.

#### Console
```bash
php artisan catalog:process hp,dell,lenovo
```

Only driver packs:  
```
php artisan catalog:process hp,dell,lenovo --dpc
```

Only product catalog:
```
php artisan catalog:process hp,dell,lenovo --pc
```

#### Code

```php
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController;

(new HpDriverPackCatalogController)->processCatalog();
(new HpProductCatalogController)->processCatalog();

(new DellDriverPackCatalogController)->processCatalog();
(new DellCatalogPcController)->processCatalog();
```
