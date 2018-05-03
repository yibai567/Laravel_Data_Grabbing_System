<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlockNewsController extends Controller
{
    /**
     * result
     * 存储结果
     *
     * @param Request $request
     * @return array
     */
    public function result(Request $request)
    {
        return $request->all();
    }

    /**
     * results
     * 批量存储结果
     *
     * @param Request $request
     * @return array
     */
    public function results(Request $request)
    {
        return $request->all();
    }
}
