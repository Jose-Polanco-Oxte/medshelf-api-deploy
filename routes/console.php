<?php

use App\Providers\Core\Home\Item\Service\CheckExpiringItems;
use App\Providers\Core\Home\Treatment\Service\CheckCompletedTreatments;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(CheckExpiringItems::class)->execute();
})->daily()->name('check-expiring-items');
Schedule::call(function () {
    app(CheckCompletedTreatments::class)->execute();
})->daily()->name('check-completed-treatments');
