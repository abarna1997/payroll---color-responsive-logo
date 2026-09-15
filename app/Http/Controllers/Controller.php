<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Bio-metrics AMS API Documentation",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="admin@bio-metrics.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Main API Server"
 * )
 */
abstract class Controller
{
    use AuthorizesRequests;
}
