<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{
    /**
     * {@inheritdoc}
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected function formatValidationErrors(Validator $validator)
    {
        // @TODO If we can set an errorFormatter that's the best way
        // if (isset(static::$errorFormatter)) {
        //     return (static::$errorFormatter)($validator);
        // }

        return [
            'data' => null,
            'error' => [
                'type' => 'validation',
                'messages' => $validator->errors()->getMessages(),
            ],
        ];
    }

    /**
     * Send response
     *
     * @param mixed $data
     * @param mixed $error
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected function respond($data = null, $error = null): \Illuminate\Http\JsonResponse
    {
        $json = [
            'data' => $data,
            'error' => $error,
        ];
        return response()->json($json);
    }
}
