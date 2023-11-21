<?php
namespace Doorons\DoUI; //Change namespace here

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ResourceServiceProvider extends ServiceProvider //Change class name here
{
    public function boot(): void
    {
       dd('test');


    }

    public function register()
    {
dd('tets');
    }
}
