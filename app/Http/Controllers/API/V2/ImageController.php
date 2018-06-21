<?php
/**
 * ImageController
 * 图片控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/24
 */

namespace App\Http\Controllers\API\V2;

use App\Services\ImageService;
use App\Services\InternalAPIV2Service;
use Log;
use Illuminate\Http\Request;
use App\Services\ValidatorService;

class ImageController extends Controller
{
    /**
     * upload
     * 上传
     *
     * @param Request $request
     * @return imageInfo
     */
    public function upload(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'image'         => 'required|image',
            'task_run_log_id' => 'required|integer|max:999999999'
        ]);

        try {
            //调取根据task_run_log_id查询data信息
            $uploadSelectData['task_run_log_id'] = intval($params['task_run_log_id']);
            $datum = InternalAPIV2Service::get('/data/task_run_log_id', $uploadSelectData);

            if (empty($datum)) {
                Log::debug('[v2 ImageController upload] $datum is not found,task_run_log_id : '.$uploadSelectData['task_run_log_id']);

                return $this->resObjectGet(false, 'image', $request->path());
            }

            $image = $request->file('image');

            // 图片上传
            $imageService = new ImageService();
            $imageInfo = $imageService->uploadByFile($image);

            if (!$imageInfo) {
                Log::debug('[v2 ImageController upload] image upload  is fail');

                return $this->resObjectGet(false, 'image', $request->path());
            }

            //调取更新data信息接口
            $uploadUpdateData['task_run_log_id'] = intval($params['task_run_log_id']);
            $uploadUpdateData['screenshot'] = $imageInfo;

            InternalAPIV2Service::post('/data/update/task_run_log_id', $uploadUpdateData);
        } catch (\Exception $e) {
            Log::debug('[v2 ImageController upload] error message = ' . $e->getMessage());

            return $this->resObjectGet(false, 'data', $request->path());
        }


        return $this->resObjectGet(true, 'image', $request->path());
    }
}