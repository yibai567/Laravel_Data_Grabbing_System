<?php
/**
 * DataController
 * Data控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/23
 */

namespace App\Http\Controllers\API\V1;

use App\Models\Data;
use App\Models\TaskRunLog;
use App\Services\InternalAPIService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * dataResultReport
     * 数据上报
     *
     * @param
     * @return boolean
     */
    public function dataResultReport(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'ids' => 'required|string',
        ]);

        //调取上报数据信息
        $datas = InternalAPIService::get('/datas/ids', $params);

        $newData = [];
        $postNum = 0;
        //遍历上报数据
        foreach ($datas as $data) {

            if (empty($data['title'])) {
                continue;
            }

            $newData[$postNum]['title'] = $data['title'];

            if (empty($data['task_id'])) {
                $newData[$postNum]['task_id'] = "";
            } else {
                $newData[$postNum]['task_id'] = $data['task_id'];
            }

            if (empty($data['detail_url'])) {
                $newData[$postNum]['url'] = "";
            } else {
                $newData[$postNum]['url'] = $data['detail_url'];
            }

            if (empty($data['show_time'])) {
                $newData[$postNum]['date'] = "";
            } else {
                $newData[$postNum]['date'] = $data['show_time'];
            }

            if (empty($data['images'])) {
                $newData[$postNum]['images'] = [];
            } else {
                $newData[$postNum]['images'] = $data['images'];
            }

            if (empty($data['screenshot'])) {
                $newData[$postNum]['screenshot'] = [];
            } else {
                $newData[$postNum]['screenshot'] = $data['screenshot'];
            }

            $postNum += 1;
        }


        //整理数据
        $params['is_test'] = TaskRunLog::TYPE_TEST;
        $params['result'] = json_encode($newData, JSON_UNESCAPED_UNICODE);

        //调用上传数据接口
        $result = InternalAPIService::post('/item/result/report', $params);

        return $this->resObjectGet($result, 'data', $request->path());
    }
}