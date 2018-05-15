<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;

class ScriptController extends Controller
{
    /**
     * getByQueue
     * 获取指定队列的任务
     *
     * @param item_id
     * @return array
     */
    public function allByQueue(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'driver' => 'required|string|max:10',
            'cron' => 'required|string|max:10',
        ]);

        $queue['name'] = 'script_queue_' . $params['driver'] . '_' . $params['cron'];

        $result = InternalAPIService::get('/script/queue', $queue);
        return $this->resObjectGet($result, 'item', $request->path());
    }
}
