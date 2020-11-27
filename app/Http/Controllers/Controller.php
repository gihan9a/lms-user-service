<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

class Controller extends BaseController
{

    /**
     * Send response
     *
     * @param mixed $data
     * @param mixed $errors
     * @param int   $code
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    protected function respond($data = null, $errors = null, $code = Response::HTTP_OK): \Illuminate\Http\JsonResponse
    {
        $json = [
            'code' => $code,
            'data' => $data,
            'errors' => $errors,
        ];
        return response()->json($json);
    }

    /**
     * Find model by id or fail
     *
     * @param string  $class
     * @param string $id
     * 
     * @return Model
     * 
     * @author Gihan S <gihanshp@gmail.com>
     */
    public function findModelOrFail(string $class, string $id): Model
    {
        $model = $class::find((int)$id);
        if ($model === null) {
            $name = strtolower((new \ReflectionClass($class))->getShortName());
            throw new ModelNotFoundException('Unable to find ' . $name . ' ' . $id, 404);
        }
        return $model;
    }
}
