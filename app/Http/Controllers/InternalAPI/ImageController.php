<?php

namespace App\Http\Controllers\InternalAPI;

use App\Services\ImageService;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use Log;
use DB;
use QL\QueryList;
use App\Services\ValidatorService;
use App\Models\Data;
use App\Models\Task;
use App\Models\Image;
use App\Models\Script;

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

    /**
     * extraction
     * 图片提取
     *
     * @param content (required|string) 内容
     * @param host (nullable|string) 域名
     * @return array
     */
    public function getByResult(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "data_id" => "required|integer",
            "thumbnail" => "nullable|string",
            "content" => "nullable|string",
        ]);
        //获取任务ID （需要拆分成接口）
        $dataRes = Data::find($params['data_id']);

        if (empty($dataRes)) {
            Log::debug('[getByResult] data empty dataId = ' . $params['data_id']);
            throw new \Dingo\Api\Exception\ResourceException("data empty");
        }

        $dataInfo = $dataRes->toArray();

        //获取脚本ID 需要拆分成接口）
        $taskInfo = Task::find($dataInfo['task_id']);

        if (empty($taskInfo)) {
            Log::debug('[getByResult] task empty taskId = ' . $taskInfo['task_id']);
            throw new \Dingo\Api\Exception\ResourceException("task empty");
        }

        $taskInfo = $taskInfo->toArray();

        //获取资源url 需要拆分成接口）
        $scriptInfo = Script::find($taskInfo['script_id']);

        if (empty($scriptInfo)) {
            Log::debug('[getByResult] script empty scriptId = ' . $scriptInfo['script_id']);
            throw new \Dingo\Api\Exception\ResourceException("script empty");
        }

        $scriptInfo = $scriptInfo->toArray();
        $urlFormat = [];
        if (!empty($scriptInfo['list_url'])) {
            $urlFormat = parse_url($scriptInfo['list_url']);
        }

        //提取$params['content'] 中url,并且补全
        if (!empty($params['content'])) {

            $ql = QueryList::html($params['content']);
            //获取所有的图片地址
            $imgArr = $ql->find('img')->attrs('src')->all();
            $imageUrl = [];
            if (!empty($urlFormat)) {
                $formatImage = $this->__foramtUrl($imgArr, $urlFormat, $params['content']);
                $params['content'] = $formatImage['content'];
                $imageUrl = $formatImage['format_url'];
            } else {
                foreach ($imgArr as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    $imageUrl[] = $value;
                }
            }
            $dataRes->content = $params['content'];
        }

        //提取$params['thumbnail'] 中url,并且补全
        if (!empty($params['thumbnail'])) {
            $thumbnail = explode(',', $params['thumbnail']);
            if (!empty($urlFormat)) {
                $formatThumbnail = $this->__foramtUrl($thumbnail, $urlFormat);
                $thumbnail = $formatThumbnail['format_url'];
            }
            $dataRes->thumbnail = json_encode($thumbnail);
        }
        if (!empty($imageUrl) && !empty($thumbnail)){
            $imgUrl = array_unique(array_merge($imageUrl,$thumbnail));
        } else if (!empty($imageUrl)) {
            $imgUrl = array_unique($imageUrl);
        } else if (!empty($thumbnail)) {
            $imgUrl = array_unique($thumbnail);
        }

        //保存数据
        $imgNum = count($imgUrl);
        $dataRes->img_remaining_step = $imgNum;
        $dataRes->save();

        $result = [];
        if (!empty($imgUrl)) {
            $result['img_urls'] = $imgUrl;
            $result['is_proxy'] = $scriptInfo['is_proxy'];
        }
        return $this->resObjectGet($result, 'image', $request->path());
    }



    /**
     * replacement
     * 图片替换
     *
     * @param content (required|string) 内容
     * @param image_url (nullable|array) 图片url
     * @return array
     */
    public function replacement(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "data_id" => "required|integer",
            "original_img_url" => "required|string",
            "img_id" => "required|integer",
        ]);

        //获取结果信息
        $dataRes = Data::find($params['data_id']);

        if (empty($dataRes)) {
            Log::debug('[replacement] data empty dataId = ' . $params['data_id']);
            throw new \Dingo\Api\Exception\ResourceException("data empty");
        }

        $dataInfo = $dataRes->toArray();

        //获取image信息
        $imageRes = Image::find($params['img_id']);
        if (empty($imageRes)) {
            Log::debug('[replacement] image empty imgId = ' . $params['img_id']);
            throw new \Dingo\Api\Exception\ResourceException("image empty");
        }
        $imageInfo = $imageRes->toArray();
        //替换富文本url
        if (!empty($dataInfo['content'])) {
            $content = str_replace($params['original_img_url'], $imageInfo['oss_url'], $dataInfo['content']);
            $dataRes->content = $content;
        }

        //替换缩略图url
        if (!empty($dataInfo['thumbnail'])) {
            $thumbnail = implode(",", json_decode($dataInfo['thumbnail']));
            $thumbnail = str_replace($params['original_img_url'], $imageInfo['oss_url'], $thumbnail);
            $dataRes->thumbnail = json_encode(explode(",", $thumbnail));

        }
        $dataRes->decrement('img_remaining_step', 1);
        $dataRes->save();

        return $this->resObjectGet(true, 'image', $request->path());
    }

    /**
     * download
     * 图片下载
     *
     * @param image_url (nullable|string) 图片url
     * @return array
     */
    public function download(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "image_url" => "required|string",
            "is_proxy" => "nullable|integer",
        ]);

        $isProxy = false;

        if ($params['is_proxy'] == 1) {
            $isProxy = true;
        }

        $imageService = new ImageService();
        $imageInfo = $imageService->uploadByImageUrl($params['image_url'], [], $isProxy);

        if (empty($imageInfo['id'])) {
            throw new \Dingo\Api\Exception\ResourceException(" upload image fail");
        }
        $data['image_url'] = $params['image_url'];
        $data['img_id'] = $imageInfo['id'];
        return $this->resObjectGet($data, 'image', $request->path());
    }

    /**
     * __foramtUrl
     * 格式化Url
     *
     * @param array $urlArr array $urlFormat string $content
     * @return array
     */
    private function __foramtUrl($urlArr, $urlFormat, $content = '')
    {
        $extractUrl = [];
        foreach ($urlArr as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $http = substr($value, 0, 4);
            if ($http != 'http') {
                if (substr($value, 0, 2) == '//') {
                    $newUrl = $urlFormat['scheme'] . ':' . $value;
                } else {
                    if (substr($value, 0, 1) == '/') {
                        $newUrl = $urlFormat['scheme'] . '://' . $urlFormat['host'] . $value;
                    } else {
                        $newUrl = $urlFormat['scheme'] . '://' . $urlFormat['host'] . '/' . $value;
                    }
                }
                if (!empty($content)) {
                    $content = str_replace("src=\"$value\"", "src=\"$newUrl\"", $content);
                }
                $extractUrl[] = $newUrl;
            } else {
                $extractUrl[] = $value;
            }
        }
        $data['format_url'] = $extractUrl;

        if (!empty($content)) {
            $data['content'] = $content;
        }
        return $data;
    }
}
