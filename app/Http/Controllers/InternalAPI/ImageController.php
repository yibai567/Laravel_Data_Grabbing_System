<?php

namespace App\Http\Controllers\InternalAPI;

use App\Service\ImageService;
use Illuminate\Http\Request;
use Log;

/**
 * ImageController
 * 新版图片管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
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
        $image = $request->file('image');

        if (empty($image)) {
            $this->response()->error('image 参数错误', 500);
        }

        try {
            $imageService = new ImageService();
            $imgResult = $imageService->uploadByFile($image);

            if (empty($imgResult['data']['id'])) {
                if (!empty($imgResult['msg'])) {
                    $this->response()->error($imgResult['msg'], 500);
                }

                $this->response()->error('上传图片失败', 500);
            }
            $scheme = config('aliyun.oss.scheme');
            $domain = config('aliyun.oss.domain');

            $url = $scheme.$domain.'/'.$imgResult['data']['id'];

            return ['image_url' => $url];
        } catch (\Exception $e) {
            Log::error('AppImageV3Controller upload error' . $e->getMessage());
        }

        $this->response()->error('上传图片失败:' . $e->getMessage(), 500);
    }
}
