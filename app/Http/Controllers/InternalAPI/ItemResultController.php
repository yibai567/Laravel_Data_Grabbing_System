<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Models\ItemRunLog;
use App\Services\ImageService;
use Illuminate\Http\Request;
use App\Services\InternalAPIService;
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
        Log::debug('[internal ItemResultController all] start!');
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
        Log::debug('[internal ItemResultController allByLast] start!');
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
        Log::debug('[internal ItemResultController updateImage] start!');
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'string|required',
        ]);

        $params['id'] = intval($params['id']);
        $itemResult = ItemResult::find($params['id']);

        try {
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

            $itemResult->short_contents = json_encode($shortContents, JSON_UNESCAPED_UNICODE);

            // 判断计数器是否为0 修改状态
            $itemResult->counter -= 1;
            if ($itemResult->counter <= 0) {
                $itemResult->counter = 0;
                $itemResult->status = ItemResult::STATUS_SUCCESS;

                // 标记当前任务为成功状态
                Log::debug('[dispatchJob] 请求 /item_run_log/status/success', ['id' => $itemResult->item_run_log_id]);
                InternalAPIService::post('/item_run_log/status/success', ['id' => $itemResult->item_run_log_id]);

                $result = json_decode($itemResult->short_contents, true);
                $result['task_id'] = $itemResult->item_id;
                $result['screenshot'] = $itemResult->images;

                $data['is_test'] = 2;
                $data['result'][] = json_encode($result, JSON_UNESCAPED_UNICODE);

//TODO                 InternalAPIService::post('/item/result/report', $data);
            }
            $itemResult->save();
        } catch (\Exception $e) {
            $itemResult->status = ItemResult::STATUS_FAIL;
            $itemResult->save();
            // 标记当前任务为失败状态
            Log::debug('[dispatchJob] 请求 /item_run_log/status/fail', ['id' => $itemResult->item_run_log_id]);
            InternalAPIService::post('/item_run_log/status/fail', ['id' => $itemResult->item_run_log_id]);
            throw new ResourceException("item result updateImage fail");
        }

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
        Log::debug('[internal ItemResultController updateCapture] start');
        $image = $request->file('image');
        if (empty($image)) {
            return response(500, 'image 参数错误');
        }

        $params['id'] = intval($request->get('id'));
        $itemResult = ItemTestResult::find($params['id']);

        try {
            if (empty($itemResult)) {
                Log::debug('[updateCapture] 结果不存在');
                throw new ResourceException("result not exist");
            }

            // 图片上传
            $imageService = new ImageService();
            $imageInfo = $imageService->uploadByFile($image);
            $imageInfo['url'] = $imageInfo['oss_url'];
            // 更新结果
            $itemResult->image = json_encode($imageInfo, JSON_UNESCAPED_UNICODE);

            $itemResult->counter -= 1;
            if ($itemResult->counter <= 0) { // 判断计数器是否为0 修改状态
                $itemResult->counter = 0;
                $itemResult->status = ItemResult::STATUS_SUCCESS;

                // 标记当前任务为成功状态
                Log::debug('[dispatchJob] 请求 /item_run_log/status/success', ['id' => $itemResult->item_run_log_id]);
                InternalAPIService::post('/item_run_log/status/success', ['id' => $itemResult->item_run_log_id]);

                $result = json_decode($itemResult->short_contents, true);
                $result['task_id'] = $itemResult->item_id;
                $result['screenshot'] = $itemResult->images;

                $data['is_test'] = 2;
                $data['result'][] = json_encode($result, JSON_UNESCAPED_UNICODE);

//TODO                InternalAPIService::post('/item/result/report', $data);
            }
            $itemResult->save();
        } catch (\Exception $e) {
            $itemResult->status = ItemResult::STATUS_FAIL;
            $itemResult->save();
            // 标记当前任务为失败状态
            Log::debug('[dispatchJob] 请求 /item_run_log/status/fail', ['id' => $itemResult->item_run_log_id]);
            InternalAPIService::post('/item_run_log/status/fail', ['id' => $itemResult->item_run_log_id]);

            throw new ResourceException("item result updateCapture fail");
        }

        return $this->resObjectGet($itemResult->toArray(), 'item_test_result', $request->path());
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
        Log::debug('[internal ItemResultController createHtml] start');
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
            if ($item->is_capture_image == Item::IS_CAPTURE_IMAGE_TRUE) {
                $counter += 1;
            }

            foreach ($shortContentsArr as $shortContents) {
                $params['short_contents'] = $shortContents;
                $formatData = $this->__formatData($params);

                $itemResult = ItemResult::firstOrCreate([
                    'item_id' => $formatData['item_id'],
                    'md5_short_contents' => $formatData['md5_short_contents'],
                ]);

                if ($itemResult->wasRecentlyCreated) {
                    $itemResult->item_run_log_id = $formatData['item_run_log_id'];
                    $itemResult->start_at = $formatData['start_at'];
                    $itemResult->end_at = $formatData['end_at'];
                    $shortContents = json_decode($shortContents, true);
                    $shortContents['url'] = $this->__formatURL($shortContents['url'], $item->pre_detail_url);
                    $itemResult->short_contents = json_encode($shortContents, JSON_UNESCAPED_UNICODE);
                    // 如果有图片信息则计数器增1
                    $newCounter = empty($shortContents['images']) ? $counter : $counter + 1;

                    $itemResult->counter = $newCounter;
                    $itemResult->status = $newCounter > 0 ? ItemResult::STATUS_INIT : ItemResult::STATUS_SUCCESS;
                    $itemResult->save();

                    $shortContents['id'] = $itemResult->id;
                    $result[] = $shortContents;
                }
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
            $data['md5_short_contents'] = md5($data['short_contents']);
        }

        return $data;
    }

    /**
     * __formatURL
     * 格式化地址
     *
     * @param Request $request
     * @return array
     */
    private function __formatURL($url, $preDetailUrl)
    {
        if (empty($preDetailUrl) || $url == 'undefined' || $url == 'javascript:;') {
            return false;
        }

        $httpPre = substr($url, 0, 4);
        $urlArr = parse_url($preDetailUrl);

        if ($httpPre != 'http:' && $httpPre != 'https') {
            if (substr($url, 0, 2) == '//') {
                $url = $urlArr['scheme'] . ':' . $url;
            } else {
                if (substr($url, 0, 1) == '/') {
                    $url = $urlArr['scheme'] . '://' . $urlArr['host'] . $url;
                } else {
                    $url = $urlArr['scheme'] . '://' . $urlArr['host'] . '/' . $url;
                }
            }
        }
        return trim($url);
    }
}
