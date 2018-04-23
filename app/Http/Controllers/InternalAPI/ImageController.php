<?php

namespace App\Http\Controllers\InternalAPI;

use App\Services\ImageService;
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
        Log::debug('[internal ImageController upload] start!');
        $image = $request->file('image');
        Log::debug('[upload] start!' . json_encode($image));
        if (empty($image)) {
            return response(500, 'image 参数错误');
        }

        try {
            $imageService = new ImageService();
            $imgResult = $imageService->uploadByFile($image);
            if (empty($imgResult['data']['id'])) {
                if (!empty($imgResult['msg'])) {
                    return response(500, $imgResult['msg']);
                }

                return response(500, '上传图片失败');
            }
            $scheme = config('aliyun.oss.scheme');
            $domain = config('aliyun.oss.domain');

            $url = $scheme.$domain.'/'.$imgResult['data']['id'];

            return ['image_url' => $url];
        } catch (\Exception $e) {
            Log::error('AppImageV3Controller upload error' . $e->getMessage());
        }

        return response(500, '上传图片失败:' . $e->getMessage());
    }
}
