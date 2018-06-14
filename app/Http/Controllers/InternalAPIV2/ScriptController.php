<?php
/**
 * ScriptController
 * Script控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/13
 */

namespace App\Http\Controllers\InternalAPIV2;

use Illuminate\Support\Facades\DB;
use Log;
use App\Models\Script;
use App\Models\ScriptConfig;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ScriptController extends Controller
{
    /**
     * create
     * 创建
     *
     * @param
     *
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'name'                => 'required|string|max:100',
            'description'         => 'nullable|string|max:65535',
            'list_url'            => ['required','url','max:65535','regex:/^(http|https):\/\//i'],
            'data_type'           => 'required|integer|between:1,3',
            'modules'             => 'nullable|array|max:65535',
            'content'             => 'nullable|string|max:65535',
            'cron_type'           => 'nullable|integer|between:1,4',
            'ext'                 => 'nullable|integer|between:1,2',
            'created_by'          => 'nullable|string|max:100',
            'next_script_id'      => 'nullable|integer|max:10',
            'requirement_pool_id' => 'nullable|integer|max:10',
            'is_proxy'            => 'required|integer|between:1,2',
            'casper_config'       => 'nullable|array'
        ]);

        if (empty($params['modules']) && empty($params['content'])) {
            throw new \Dingo\Api\Exception\ResourceException('modules and content can not be empty at the same time');
        }

        if (!empty($params['content']) && empty($params['ext'])) {
            throw new \Dingo\Api\Exception\ResourceException('ext can not be empty');
        }

        //默认值
        if (empty($params['cron_type'])) {
            $params['cron_type'] = Script::CRON_TYPE_KEEP;
        }

        if (empty($params['ext'])) {
            $params['ext'] = Script::EXT_TYPE_JS;
        }

        //去name和description空白字符
        if (!empty($params['name'])) {
            $params['name'] = trim($params['name']);
        }

        if (!empty($params['description'])) {
            $params['description'] = trim($params['description']);
        }

        try {
            DB::beginTransaction();

            $scriptConfig = [];
            //判断当模板类型等于script的时候需要添加配置
            if ($params['data_type'] == Script::DATA_TYPE_CASPERJS && !empty($params['modules'])) {
                //调用添加配置的方法
                $scriptConfig = $this->__createScriptConfig($params['casper_config']);
            }

            //整理script入库数据
            $scriptData = [
                'name'             => $params['name'],
                'description'      => $params['description'],
                'list_url'         => $params['list_url'],
                'data_type'        => $params['data_type'],
                'casper_config_id' => $scriptConfig['id'],
                'modules'          => $params['modules'],
                'content'          => $params['content'],
                'is_proxy'         => $params['is_proxy'],
                'cron_type'        => $params['cron_type'],
                'ext'              => $params['ext'],
                'created_by'       => $params['created_by'],
                'status'           => Script::STATUS_INIT,
            ];

            $script = Script::create($scriptData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Script create or __createScriptConfig    Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create script or create script config is failed");
        }


        $result = $script->toArray();

        if (!empty($scriptConfig)) {
            $result['casper_config'] = $scriptConfig;
        }

        return $this->resObjectGet($result, 'script', $request->path());
    }


    /**
     * __createScriptConfig
     * 创建script配置
     *
     * @param
     *
     * @return array
     */
    private function __createScriptConfig($config)
    {
        //判断配置数据存在,存在则验证参数,无则赋予空数组
        if (!empty($config)) {
            ValidatorService::check($config, [
                'load_images'  => 'nullable|integer|between:1,2',
                'load_plugins' => 'nullable|integer|between:1,2',
                'log_level'    => 'nullable|string|max:10',
                'verbose'      => 'nullable|integer|between:1,2',
                'width'        => 'nullable|string|max:10',
                'height'       => 'nullable|string|max:10'
            ]);
        }
        //设置默认值
        if (empty($config['load_images'])) {
            $config['load_images'] = ScriptConfig::DEFAULT_LOAD_IMAGES;
        }

        if (empty($config['load_plugins'])) {
            $config['load_plugins'] = ScriptConfig::DEFAULT_LOAD_PLUGINS;
        }

        if (empty($config['log_level'])) {
            $config['log_level'] = ScriptConfig::DEFAULT_LOG_LEVEL;
        }

        if (empty($config['verbose'])) {
            $config['verbose'] = ScriptConfig::DEFAULT_VERBOSE;
        }

        $scriptConfig = ScriptConfig::create($config);

        $result = [];
        if (!empty($scriptConfig)) {
            $result = $scriptConfig->toArray();
        }

        return $result;
    }

}