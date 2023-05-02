<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;

/**
 * @method static createProcessor(\Illuminate\Http\Request $request, $id, mixed $any)
 */
class ImageServiceFacade extends Facade
{
    protected static function getFacadeAccessor(){
        return "ImageService";
    }
}
