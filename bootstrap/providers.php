<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\MCPServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    MCPServiceProvider::class,
    TelescopeServiceProvider::class,
];
