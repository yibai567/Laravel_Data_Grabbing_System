<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlockNewsController extends Controller
{
    /**
     * result
     * 结果入库
     * @param Request $request
     */
    public function create(Request $request)
    {
        dd($request->all());
    }

    /**
     * @param Request $request
     */
    public function results(Request $request)
    {
        dd($request->all());
    }
}
