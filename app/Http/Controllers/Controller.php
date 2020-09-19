<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //responses
    protected function unauthorizedResponse($data = [], $headers = []) {
        return response()->json(array_merge([
            'success' => false,
            'message' => 'Unauthorized',
            'errors'  => ['You\'re prohibited to access this resource']
        ], $data), 401, $headers);
    }

    protected function createdResponse($data = [], $headers = []) {
        return response()->json(array_merge([
            'success' => true,
            'message' => 'Created'
        ], $data), 201, $headers);
    }

    protected function okResponse($data = [], $headers = []) {
        return response()->json(array_merge([
            'success' => true,
            'message' => 'Success'
        ], $data), 200, $headers);
    }

    protected function serverErrorResponse($data = [], $headers = []) {
        return response()->json(array_merge([
            'success' => false,
            'message' => 'Internal Server Error',
            'errors'  => ['Something wen\'t wrong on the server. Please try again.']
        ], $data), 500, $headers);
    }

    protected function unprocessableEntityResponse($data = [], $headers = []) {
        return response()->json(array_merge([
            'success' => false,
            'message' => 'Unprocessable Entity',
            'errors'  => ['Unable to process entity']
        ], $data), 422, $headers);
    }
    //end of response
}
