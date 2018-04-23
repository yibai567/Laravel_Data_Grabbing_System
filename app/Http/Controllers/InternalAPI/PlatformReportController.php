<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\HttpService;

/**
 * ItemController
 * 新版任务管理接口
 *
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/04/23
 */
class PlatformReportController extends Controller
{
    /**
     * itemResultReport
     * 任务结果上报
     *
     * @param array
     */
    public function itemResultReport(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'is_test' => 'integer|required',
            'result' => 'required|string'
        ]);

        $httpService = new HttpService();
        $params['result'] = json_decode($params['result']);
        $reportParmas = sign($params);
        $res = $httpService->post(config('url.platform_url'), $reportParmas, 'json');
        return $this->resObjectGet($res, 'item', $request->path());
    }
}
