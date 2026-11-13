<?php

require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = Illuminate\Support\Facades\Route::getRoutes();

$r1 = Illuminate\Support\Facades\Route::get('/admin/login', fn()=>'ok')->name('login');
$routes->refreshNameLookups();

// What if we add route with URI /login or alias?
echo "route('login'): " . route('login') . "\n";
