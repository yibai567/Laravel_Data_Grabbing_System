<?php

namespace App\Services;

use App\Models\Image;
use DB;
use Exception;
use GuzzleHttp\Client;
use JohnLui\AliyunOSS\AliyunOSS;
use Log;

class ImageService extends Service
{

    /**
     * uploadByImageUrl
     * 下载指定 url 的图片并上传到阿里云 oss
     *
     */
    public function uploadByImageUrl($url, $isSizeRule = [])
    {
        try {
            $domain = config('aliyun.oss.domain');
            $scheme = config('aliyun.oss.scheme');
            $separator = config('aliyun.oss.separator');
            $host = config('aliyun.oss.internal_host');
            $accessKey = config('aliyun.access_key');
            $secret = config('aliyun.access_secret');
            $path = 'http://'.$host;

            // 判断快高规则是否符合
            if ($isSizeRule) {
                $getSize = getimagesize($url);
                if (!$getSize) {
                    return '';
                }

                $width = $getSize[0];
                $height = $getSize[1];

                if ($width < $isSizeRule['width'] || $height < $isSizeRule['height']) {
                    return '';
                }
            }

            $client = new Client(['verify' => false]);

            $response = $client->request('GET', $url, ['timeout' => 30]);
            $content = $response->getBody();
            $object = (string)$content;

            $ext = $this->getExt($url);

            if (!$ext) {
                Log::error('not found url is url :  '.$url."\t foreach key is : ", -1);

                return null;
            }

            $size = $content->getSize();

            $md5Content = md5($content);
            $image = Image::where('md5_content', $md5Content)->first();
            if ($image) {
                return $image->id;
            }

            $image = new Image();
            $image->name = md5($url);
            $image->ext = $ext;
            $image->mime_type = $ext;
            $image->size = $size;
            $image->md5_content = $md5Content;

            DB::beginTransaction();
            $image->save();
            $objectKey = config('aliyun.oss.base_key').$image->id;

            $oss = AliyunOSS::boot($path, $accessKey, $secret);
            $oss->setBucket(config('aliyun.oss.bucket'));
            $oss->uploadContent($objectKey, $object);
            $image->update(['oss_url' => $scheme.$domain.'/'.$image['id']]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(
                'ImageService uploadByImageUrl $url :'.$url.'    Exception:'."\t".$e->getCode()."\t".$e->getMessage()
            );

            return null;
        }

        return $image->toArray();
    }

    /**
     * getExt
     *
     * @param  mixed $file_name
     * @return array
     */
    public function getExt($url)
    {
        $params = explode('.', $url);
        $count = count($params);

        $ext = '';
        if ($count > 1) {
            $ext = strtolower($params[$count - 1]);
        }

        return $ext;
    }

    /**
     * 获取 oss 私有文件的 url
     * @param $ossKey
     * @return string
     */
    public static function getOssPrivateUrl($ossKey)
    {
        $accessKey = config('aliyun.access_key');
        $secret = config('aliyun.access_secret');

        $scheme = config('aliyun.oss.scheme');
        $host = config('aliyun.oss.production_host');
        $ossPath = $scheme.$host;

        $oss = AliyunOSS::boot($ossPath, $accessKey, $secret);
        $oss->setBucket(config('aliyun.oss.bucket_private'));

        // 访问图片链接永远不过期
        $expires = new \DateTime('2100-01-01');

        return $oss->getUrl($ossKey, $expires);
    }

    /**
     * uploadByFile
     * 上传文件到阿里云 oss
     * @param $file \Illuminate\Http\UploadedFile
     * @param bool $isPrivate
     * @return array
     */
    public function uploadByFile($file, $isPrivate = false)
    {
        try {
            if ($file->getError() != 0) {
                return ["msg" => '上传图片失败，请检查图片并重试', 'error' => -1];
            }
            $path = $file->path();
            $ext = $file->extension();
            $minType = $file->getMimeType();
            $name = $file->hashName();
            $size = $file->getSize();

            $imageConfig = config('image');
            if (!$name || !$ext) {
                return ["msg" => 'Invaild image name', 'error' => -1];
            }

            if (!in_array($ext, $imageConfig['allow_ext'])) {
                return ["msg" => 'Invaild image ext', 'error' => -1];
            }

            if ($size > $imageConfig['allow_size']) {
                return ["msg" => 'Invaild image size', 'error' => -1];
            }

            $md5Content = md5(file_get_contents($path));
            $image = Image::where('md5_content', $md5Content)->first();
            if ($image) {
                return ['data' => $image];
            }

            $image = new Image();
            $image->name = $name;
            $image->ext = $ext;
            $image->minme_type = $minType;
            $image->size = $size;
            $image->md5_content = $md5Content;
            if (!empty($isPrivate)) {
                $image->is_private = 1;
            }

            DB::beginTransaction();
            $image->save();

            $accessKey = config('aliyun.access_key');
            $secret = config('aliyun.access_secret');

            $host = config('aliyun.oss.internal_host');
            $ossPath = 'http://'.$host;

            $oss = AliyunOSS::boot($ossPath, $accessKey, $secret);
            $objectKey = config('aliyun.oss.base_key').$image->id;

            if (!empty($isPrivate)) { // 加密上传
                $oss->setBucket(config('aliyun.oss.bucket_private'));
            } else { // 普通上传
                $oss->setBucket(config('aliyun.oss.bucket'));
            }
            $oss->uploadFile($objectKey, $path);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(
                'ImageService uploadByImageUrl    Exception:'."\t".$e->getCode()."\t".$e->getMessage(
                ).' '.$e->getTraceAsString()
            );

            return ["msg" => '上传图片失败，请重试', 'error' => -1];
        }

        return ['data' => $image];
    }

    /**
     * uploadByFile
     * 上传文件到阿里云 oss
     * @param $imgObj \Intervention\Image\Image
     */
    public function uploadImgObjByFile($imgObj)
    {
        try {
            $md5Content = md5($imgObj->getEncoded());
            $image = Image::where('md5_content', $md5Content)->first();
            if ($image) {
                return ['data' => $image];
            }

            $image = new Image();
            $image->name = $imgObj->filename;
            $image->ext = $imgObj->extension;
            $image->minme_type = $imgObj->mime;
            $image->size = $imgObj->filesize();
            $image->md5_content = $md5Content;

            DB::beginTransaction();
            $image->save();

            $accessKey = config('aliyun.access_key');
            $secret = config('aliyun.access_secret');
            $host = config('aliyun.oss.internal_host');
            $ossPath = 'http://'.$host;

            $oss = AliyunOSS::boot($ossPath, $accessKey, $secret);
            $objectKey = config('aliyun.oss.base_key').$image->id;
            $oss->setBucket(config('aliyun.oss.bucket'));
            $oss->uploadFile($objectKey, $imgObj->basePath());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ImageService uploadByImageUrl    Exception:'."\t".$e->getCode()."\t".$e->getMessage());

            return ["msg" => 'upload exception', 'error' => -1];
        }

        return ['data' => $image];
    }

}
