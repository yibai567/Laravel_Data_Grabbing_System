<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemTestResult;
use App\Services\InternalAPIService;
use Dingo\Api\Exception\ResourceException;

/**
 * ItemTestResultController
 * 新版任务测试结果管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemTestResultController extends Controller
{
    /**
     * getTestResult
     * 获取任务最后测试结果
     *
     * @param item_id
     * @return array
     */
    public function getTestResult(Request $request)
    {
        Log::debug('[internal ItemTestResultController getTestResult] start');
        $params = $request->all();
        ValidatorService::check($params, [
            'item_run_log_id' => 'required|integer'
        ]);

        $res = ItemTestResult::where('item_run_log_id', $params['item_run_log_id'])
                            ->first();
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'item_result', $request->path());
    }

    /**
     * updateHtml
     * 更新html类型任务结果数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHtml(Request $request)
    {
        Log::debug('[internal ItemTestResultController updateHtml] start');
        $params = $request->all();
        ValidatorService::check($params, [
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'nullable',
            'long_contents' => 'nullable',
            'images' => 'nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
            'is_proxy' => 'required|integer',
        ]);

        //获取测试结果
        $itemTestResult = InternalAPIService::get('/item/test_result', ['item_run_log_id' => $params['item_run_log_id']]);
        Log::debug('[updateHtml] get test result info ' . json_encode($itemTestResult, JSON_UNESCAPED_UNICODE));

        if (empty($itemTestResult)) {
            Log::debug('[updateHtml] item test result is empty ');
            throw new ResourceException(" test result not exist");
        }

        //判断错误信息
        if (!empty($params['error_message'])) {
            //判断当前状态
            if ($itemTestResult['status'] == ItemTestResult::STATUS_NO_PROXY_TEST_FAIL || $itemTestResult['status'] == ItemTestResult::STATUS_PROXY_TEST_FAIL) {
                $params['status'] = ItemTestResult::STATUS_FAIL;
            } else {
                if ($params['is_proxy'] == Item::IS_PROXY_YES) {
                    $params['status'] = ItemTestResult::STATUS_PROXY_TEST_FAIL;
                } else {
                    $params['status'] = ItemTestResult::STATUS_NO_PROXY_TEST_FAIL;
                }
            }
        } else {
            $params['status'] = ItemTestResult::STATUS_SUCCESS;
            if (empty($params['short_contents']) && empty($params['long_contents'])) {
                $params['status'] = ItemTestResult::STATUS_FAIL;
            }
        }

        $formatData = $this->__formatData($params);

        if (empty($formatData['error_message'])) {
            $formatData['error_message'] = '';
        }

        $itemTestResult = ItemTestResult::find($itemTestResult['id']);
        if (empty($itemTestResult)) {
            throw new ResourceException("result not exist");
        }

        $counter = 0; // 计数器

        // 判断任务是否需要截图
        $item = Item::find($itemTestResult->item_id);
        if ($item->is_capture_image == Item::IS_CAPTURE_IMAGE_TRUE) {
            $counter += 1;
        }

        $result = [];
        if (empty($params['error_message'])) {// 判断如果没有错误信息则返回short_contents数组，否则返回空数组
            if (!empty($params['short_contents'])) {
                $shortContent = json_decode($params['short_contents'], true);
                $item = json_decode($shortContent[0], true);
                $item['id'] = $itemTestResult->id;

                if ($item['images']) {
                    $counter += 1;
                }

                // 测试结束返回状态及计数器
                $result[] = $item;
            }
        }

        $formatData['counter'] = $counter;
        $itemTestResult->update($formatData);
        $result[0]['counter'] = $counter;
        $result[0]['status'] = $itemTestResult->status;

        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateImage
     * 更新任务测试结果image信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage(Request $request)
    {
        Log::debug('[internal ItemTestResultController updateImage] start');
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'string|required',
        ]);
        $params['id'] = intval($params['id']);
        $itemTestResult = ItemTestResult::find($params['id']);

        try {

            if (empty($itemTestResult)) {
                Log::debug('[updateImage] 测试结果不存在');
                throw new ResourceException("test result not exist");
            }

            $shortContents = json_decode($itemTestResult->short_contents, true);
            Log::debug('[updateImage] 测试结果short_contents' . $shortContents);

            if (empty($shortContents)) {
                Log::debug('[updateImage] short_contents为空');
                throw new ResourceException("test result short_contents is empty");
            }

            if (!empty($shortContents[0]['images'])) {
                Log::debug('[updateImage] short_contents第一个数组的images属性为：' . $shortContents[0]['images']);
                $shortContents[0]['images'] = $params['images'];
            }

            $itemTestResult->short_contents = json_encode($shortContents, JSON_UNESCAPED_UNICODE);

            // 判断计数器是否为0 修改状态
            $itemTestResult->counter -= 1;
            if ($itemTestResult->counter <= 0) {
                $itemTestResult->counter = 0;
                $itemTestResult->status = ItemTestResult::STATUS_SUCCESS;
                // 标记当前任务为成功状态
                Log::debug('[updateImage] 请求 /item_run_log/status/success', ['id' => $itemTestResult->item_run_log_id]);
                InternalAPIService::post('/item_run_log/status/success', ['id' => $itemTestResult->item_run_log_id]);
                // 标记任务状态为成功
                Log::debug('[updateImage] 请求 /item/status/test_success', ['id' => $itemTestResult->item_id]);
                InternalAPIService::post('/item/status/test_success', ['id' => $itemTestResult->item_id]);

                $result = json_decode($itemTestResult->short_contents, true);
                $result['task_id'] = $itemTestResult->item_id;
                $result['screenshot'] = $itemTestResult->images;

                $data['is_test'] = 1;
                $data['result'][] = $result;
                $data['result'] = json_encode($data['result'], JSON_UNESCAPED_UNICODE);

                // TODO 上报
                // InternalAPIService::post('/item/result/report', $data);
            }

            $itemTestResult->save();

        } catch (\Exception $e) {
            $itemTestResult->update(['status' => ItemTestResult::STATUS_FAIL]);
            // 标记当前任务为失败状态
            Log::debug('[updateImage] 请求 /item_run_log/status/fail', ['id' => $itemTestResult->item_run_log_id]);
            InternalAPIService::post('/item_run_log/status/fail', ['id' => $itemTestResult->item_run_log_id]);
            // 标记任务状态为失败
            Log::debug('[updateImage] 请求 /item/status/test_fail', ['id' => $itemTestResult->item_id]);
            InternalAPIService::post('/item/status/test_fail', ['id' => $itemTestResult->item_id]);
            throw new ResourceException("item test result updateImage fail");
        }

        $result = $itemTestResult->toArray();
        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateCapture
     * 更新任务结果capture信息
     *
     * @param Request $request
     * @return array
     */
    public function updateCapture(Request $request)
    {
        Log::debug('[internal ItemTestResultController updateCapture] start');
        $image = $request->file('image');
        if (empty($image)) {
            return response(500, 'image 参数错误');
        }

        $params['id'] = intval($request->get('id'));
        $itemTestResult = ItemTestResult::find($params['id']);

        try {
            if (empty($itemTestResult)) {
                Log::debug('[updateCapture] 测试结果不存在');
                throw new ResourceException("test result not exist");
            }

            Log::debug('[updateCapture] 图片上传');
            $imageService = new ImageService();
            $imageInfo = $imageService->uploadByFile($image);

            // 更新测试结果
            $itemTestResult->image = json_encode($imageInfo, JSON_UNESCAPED_UNICODE);

            $itemTestResult->counter -= 1;
            if ($itemTestResult->counter <= 0) { // 判断计数器是否为0 修改状态
                $itemTestResult->counter = 0;
                $itemTestResult->status = ItemTestResult::STATUS_SUCCESS;

                // 标记当前任务为成功状态
                Log::debug('[dispatchJob] 请求 /item_run_log/status/success', ['id' => $itemTestResult->item_run_log_id]);
                InternalAPIService::post('/item_run_log/status/success', ['id' => $itemTestResult->item_run_log_id]);
                // 标记任务状态为成功
                Log::debug('[dispatchJob] 请求 /item/status/test_success', ['id' => $itemTestResult->item_id]);
                InternalAPIService::post('/item/status/test_success', ['id' => $itemTestResult->item_id]);

                $result = json_decode($itemTestResult->short_contents, true);
                $result['task_id'] = $itemTestResult->item_id;
                $result['screenshot'] = $itemTestResult->images;

                $data['is_test'] = 1;
                $data['result'][] = $result;
                $data['result'] = json_encode($data['result'], JSON_UNESCAPED_UNICODE);
                // TODO 上报
                // InternalAPIService::post('/item/result/report', $data);
            }

            $itemTestResult->save();
        } catch (\Exception $e) {
            $itemTestResult->update(['status' => ItemTestResult::STATUS_FAIL]);
            // 标记当前任务为失败状态
            Log::debug('[dispatchJob] 请求 /item_run_log/status/fail', ['id' => $itemTestResult->item_run_log_id]);
            InternalAPIService::post('/item_run_log/status/fail', ['id' => $itemTestResult->item_run_log_id]);
            // 标记任务状态为失败
            Log::debug('[dispatchJob] 请求 /item/status/test_fail', ['id' => $itemTestResult->item_id]);
            InternalAPIService::post('/item/status/test_fail', ['id' => $itemTestResult->item_id]);
            throw new ResourceException("item test result updateCapture fail");
        }

        return $this->resObjectGet($itemTestResult->toArray(), 'item_test_result', $request->path());
    }

    /**
     * create
     * 插入数据
     *
     * @param Request $request
     * @return array
    */
    public function create(Request $request)
    {
        Log::debug('[internal ItemTestResultController create] start');
        $params = $request->all();

        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'nullable',
            'long_content' => 'nullable',
            'images' => 'nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
        ]);

        $params['status'] = ItemTestResult::STATUS_INIT;

        $itemTestResult = ItemTestResult::create($params);

        $result = [];

        if (!empty($itemTestResult)) {
            $result = $itemTestResult->toArray();
        }

        return $this->resObjectGet($result, 'item_result', $request->path());
    }

    private function __formatData($params)
    {
        $fields = [
            'id' => '',
            'item_id' => '',
            'short_contents'  => '',
            'md5_short_contents' => '',
            'long_content0' => '',
            'long_content1' => '',
            'images' => '',
            'error_message' => '',
            'start_at' => '',
            'end_at' => '',
            'status' => ''
        ];

        $data = [];

        foreach ($fields as $key => $value) {
            if (!empty($params[$key])) {
                $data[$key] = $params[$key];
            }
        }

        $item = Item::find($data['item_id']);
        if (!empty($data['short_contents'])) {
            $data['md5_short_contents'] = md5($data['short_contents']);
            $shortContents = json_decode($data['short_contents'], true);
            if (!empty($shortContents)) {
                foreach ($shortContents as $key => $val) {
                    $val = json_decode($val, true);
                    $val['url'] = $this->__formatUrl($val['url'], $item->pre_detail_url);
                    $shortContents[$key] = $val;
                }
                $data['short_contents'] = json_encode($shortContents, JSON_UNESCAPED_UNICODE);
            }
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
        if ($httpPre != 'http:') {
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
