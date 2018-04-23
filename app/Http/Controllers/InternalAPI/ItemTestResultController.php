<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Models\ItemRunLog;
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
        Log::debug('[updateHtml] start');
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

                $result[] = $item;
            }
        }

        $formatData['counter'] = $counter;
        $itemTestResult->update($formatData);

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
        $params = $request->all();
        Log::debug('[updateImage] 更新任务结果image信息' . json_encode($params, JSON_UNESCAPED_UNICODE));

        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'string|required',
        ]);

        $params['id'] = intval($params['id']);
        $itemTestResult = ItemTestResult::find($params['id']);

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
        if ($itemTestResult->counter > 0) { // 更新计数器
            $itemTestResult->counter -= 1;
        }

        // 判断计数器是否为0 修改状态
        if ($itemTestResult->counter <= 0) {
            $itemTestResult->status = ItemTestResult::STATUS_SUCCESS;
        }

        $itemTestResult->save();

        Log::debug('[updateImage] 更新short_contents：' . $itemTestResult->short_contents);

        $result = $itemTestResult->toArray();
        return $this->resObjectGet($result, 'item_test_result', $request->path());
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

    /**
     * updateCapture
     * 更新任务结果capture信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCapture(Request $request)
    {
        Log::debug('[updateCapture] 更新任务结果capture信息');
        $image = $request->file('image');
        $params = $request->all();
        if (empty($image)) {
            return response(500, 'image 参数错误');
        }

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $params['id'] = intval($params['id']);
        $itemTestResult = ItemTestResult::find($params['id']);

        if (empty($itemTestResult)) {
            Log::debug('[updateCapture] 测试结果不存在');
            throw new ResourceException("test result not exist");
        }

        Log::debug('[updateCapture] 图片上传');
        $imageService = new ImageService();
        $imageInfo = $imageService->uploadByFile($image);

        $itemTestResult->image = $imageInfo['image_url'];
        if ($itemTestResult->counter > 0) { // 更新计数器
            $itemTestResult->counter -= 1;
        }

        // 判断计数器是否为0 修改状态
        if ($itemTestResult->counter <= 0) {
            $itemTestResult->status = ItemTestResult::STATUS_SUCCESS;
        }

        $itemTestResult->save();
        Log::debug('[updateImage] 更新short_contents：' . $itemTestResult->short_contents);

        $result = $itemTestResult->toArray();
        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    private function __formatData($params)
    {
        $item = [
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

        foreach ($item as $key => $value) {
            if (!empty($params[$key])) {
                $data[$key] = $params[$key];
            }
        }

        if (!empty($data['short_contents'])) {
            $data['md5_short_contents'] = md5($data['short_contents']);
            $shortContents = json_decode($data['short_contents'], true);
            if (!empty($shortContents)) {
                foreach ($shortContents as $key => $item) {
                    $item = json_decode($item, true);
                    $shortContents[$key] = $item;
                }
                $data['short_contents'] = json_encode($shortContents, JSON_UNESCAPED_UNICODE);
            }
        }

        return $data;
    }
}
