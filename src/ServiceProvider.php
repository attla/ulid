<?php

namespace Attla\Ulid;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events
     *
     * @return void
     */
    public function boot()
    {
        Blueprint::macro('ulid', function ($length = 26) {
            $this->char('id', $length)->primary();
        });

        Blueprint::macro('foreignUlid', function ($name, $length = 26, $reference = 'id', $onTable = null) {
            $this->char($name, $length)->primary();
            $this->foreign($name)->references($reference)->on($onTable ?? Str::plural(Str::beforeLast($name, '_' . $reference)));
        });
    }
}
