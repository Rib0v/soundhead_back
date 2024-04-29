<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *   title="API документашка",
 *   version="1.0.0"   
 * ),
 * @OA\Components(
 *   @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="http",
 *     scheme="bearer"
 *   )
 * )
 */
abstract class Controller
{
    //
}
