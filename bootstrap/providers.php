<?php

use App\Providers\AppServiceProvider;
use App\Providers\Core\Catalog\Product\ProductServiceProvider;
use App\Providers\Core\Home\House\HouseServiceProvider;
use App\Providers\Core\Home\Item\ItemServiceProvider;
use App\Providers\Core\Home\Profile\ProfileServiceProvider;
use App\Providers\Core\Home\Storage\StorageProvider;
use App\Providers\Core\Home\Treatment\TreatmentServiceProvider;

return [
    AppServiceProvider::class,
    HouseServiceProvider::class,
    ProductServiceProvider::class,
    ItemServiceProvider::class,
    StorageProvider::class,
    ProfileServiceProvider::class,
    TreatmentServiceProvider::class,
];
