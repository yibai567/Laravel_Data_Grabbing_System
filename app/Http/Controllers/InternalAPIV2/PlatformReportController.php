<?php

namespace App\Http\Controllers\InternalAPIV2;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemRunLog;
use App\Services\HttpService;
use App\Services\InternalAPIService;
;

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
            'is_test' => 'integer|required|between:1,2',
            'result' => 'required|string',
        ]);
        Log::debug('[itemResultReport] 接收参数 $params : ', $params);

        $httpService = new HttpService();
        $params['result'] = json_decode($params['result'], true);
        if ($params['is_test'] == ItemRunLog::TYPE_TEST) {
            $params['is_test'] = 1;
        } else {
            $params['is_test'] = 0;
        }
        $reportParmas = sign($params);

        Log::info('[itemResultReport] 请求 ' . config('url.new_platform_url') . '/api/live/put_craw_data $reportParmas : ', $reportParmas);

        $res = $httpService->post(config('url.new_platform_url') . '/api/live/put_craw_data', $reportParmas, 'json');

        Log::debug('[itemResultReport] $res = ' . $res);
        return $this->resObjectGet($res, 'item', $request->path());
    }
}
