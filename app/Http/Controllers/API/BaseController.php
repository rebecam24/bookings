<?php

 
namespace App\Http\Controllers\API;
 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
 
/**
 * @OA\Info(
 *     title="Documentación de la API de Reserva de Espacios",
 *     version="1.0.0",
 *     description="API para la gestión de reservas de espacios como salas de reuniones, auditorios, y más.",
 *     @OA\Contact(
 *         email="rmartinez4298@gmail.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *  
 *  @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
 
        return response()->json($response, $code);
    }
 
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];
 
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
 
        return response()->json($response, $code);
    }
}