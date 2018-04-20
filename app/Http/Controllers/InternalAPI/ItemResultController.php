<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Models\ItemRunLog;
use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemResult;
use Dingo\Api\Exception\ResourceException;


/**
 * ItemResultController
 * 新版任务结果管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemResultController extends Controller
{

    /**
     * all
     * 获取任务结果列表
     *
     * @param item_id
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/results', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * all
     * 获取任务最后执行结果列表
     *
     * @param item_id
     * @return array
     */
    public function allByLast(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'item_id' => 'required|integer'
        ]);

        $type = ItemRunLog::TYPE_PRO;

        $itemRunLog = InternalAPIService::get('/item_run_log/item/' . $params['item_id'] . '?type=' . $type);

        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException("item_run_log_id not exist");
        }

        $res = ItemTestResult::where('item_run_log_id', $itemRunLog['id'])
                            ->get();
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'item_result', $request->path());
    }

    /**
     * updateImage
     * 更新任务结果image信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'string|required',
        ]);

        $params['id'] = intval($params['id']);
        $itemResult = ItemResult::find($params['id']);

        if (empty($itemResult)) {
            throw new ResourceException("test result not exist");
        }

        $shortContents = json_decode($itemResult->short_contents, true);

        if (empty($shortContents)) {
            throw new ResourceException("result short_contents is empty");
        }

        if (!empty($shortContents['images'])) {
            $shortContents['images'] = $params['images'];
        }

        $itemResult->short_contents = json_encode($shortContents);
        if ($itemResult->counter > 0) { // 更新计数器
            $itemResult->counter -= 1;
        }
        $itemResult->save();

        $result = $itemResult->toArray();
        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateCapture
     * 更新任务结果截图信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCapture(Request $request)
    {
        $params = $request->all();
        Log::debug('[updateCapture] 更新任务结果截图信息' . json_encode($params));

        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'string|required',
        ]);

        $params['id'] = intval($params['id']);
        $itemResult = ItemTestResult::find($params['id']);

        if (empty($itemResult)) {
            Log::debug('[updateImage] 结果不存在');
            throw new ResourceException("result not exist");
        }

        $itemResult->images = json_encode($params['images']);
        if ($itemResult->counter > 0) { // 更新计数器
            $itemResult->counter -= 1;
        }
        $itemResult->save();

        $result = $itemResult->toArray();
        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateHtml
     * 更新html任务结果
     *
     * @param Request $request
     * @return array
     */
    public function createHtml(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'integer|required',
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'nullable',
            'long_contents' => 'nullable',
            'images' => 'nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
        ]);

        $result = [];
        $counter = 0; // 计数器

        if (!empty($params['short_contents'])) {
            $shortContentsArr = json_decode($params['short_contents'], true);

            // 判断任务是否需要截图
            $item = Item::find($params['item_id']);
            if ($item->is_capture) {
                $counter += 1;
            }

            foreach ($shortContentsArr as $shortContents) {
                $params['short_contents'] = $shortContents;
                if (!empty($shortContents['images'])) {
                    $counter += 1;
                }
                $formatData = $this->__formatData($params);

                $itemResult = ItemResult::firstOrCreate([
                    'item_id' => $formatData['item_id'],
                    'item_run_log_id' => $formatData['item_run_log_id'],
                    'md5_short_contents' => $formatData['md5_short_contents'],
                ]);

                if ($itemResult->wasRecentlyCreated) {
                    $itemResult->start_at = $formatData['start_at'];
                    $itemResult->end_at = $formatData['end_at'];
                    $itemResult->short_contents = $formatData['short_contents'];
                }

                if ($counter > 0) {
                    $itemResult->counter = $counter;
                }
                $itemResult->save();

                $shortContents['id'] = $itemResult->id;
                $result = array_merge($result, $shortContents);
            }
        }

        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * __formatData
     * 格式化数据
     * @param $params
     * @return array
     */
    private function __formatData($params)
    {
        $item = [
            'item_id' => '',
            'item_run_log_id' => '',
            'short_contents'  => '',
            'md5_short_contents' => '',
            'long_content0' => '',
            'long_content1' => '',
            'images' => '',
            'start_at' => '',
            'end_at' => '',
        ];

        $data = [];
        foreach ($item as $key => $value) {
            if (!empty($params[$key])) {
                $data[$key] = $params[$key];
            }
        }

        if (!empty($data['short_contents'])) {
            $data['short_contents'] = json_encode($data['short_contents']);
            $data['md5_short_contents'] = md5($data['short_contents']);
        }

        return $data;
    }
}
