<?php

namespace TeguhRijanandi\LaravelSelect2Ajax;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSelect2AjaxServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-select2-ajax')
            ->hasConfigFile('select2-ajax')
            ->hasRoute('select2-api');
    }
}
