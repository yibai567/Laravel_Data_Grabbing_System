<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class ValidatorService extends Service
{
    /**
     * check
     * 参数验证
     *
     * @param $params array
     * @param $rule array eg: ['task_id' => 'required|integer']
     * @return array
     */
    public static function check($params, $rule = [])
    {
        $validator = Validator::make($params, $rule);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $value) {
                throw new \Dingo\Api\Exception\ResourceException($value);
            }
        }
        return;
    }
}
