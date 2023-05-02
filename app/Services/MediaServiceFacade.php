<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;

/**
 * @method static createProcessor(\Illuminate\Http\Request $request, $id)
 */
class MediaServiceFacade extends Facade
{
    protected static function getFacadeAccessor(){
        return "MediaService";
    }
}
