<?php
namespace App\API\V1\Controllers;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

class BaseController extends Controller {

    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="CDN API Documentation",
     *      description="This documentation you can use to comunicate with the backend part of the project",
     *      @OA\Contact(
     *          email="nikolay.baldziev@scalewest.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     *
     * @OAS\SecurityScheme(
     *      securityScheme="passport",
     *      type="oauth2",
     *      scheme="bearer"
     * )
     *
     */
}
