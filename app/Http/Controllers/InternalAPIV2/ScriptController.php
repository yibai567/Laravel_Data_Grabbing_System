<?php
/**
 * ScriptController
 * Script控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/13
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\Requirement;
use App\Models\V2\ScriptModel;
use App\Models\V2\Task;
use App\Models\V2\TaskStatistics;
use App\Services\FileService;
use App\Services\InternalAPIV2Service;
use Illuminate\Support\Facades\DB;
use Log;
use App\Models\V2\Script;
use App\Models\V2\ScriptConfig;
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
            'description'         => 'nullable|string|max:1000',
            'list_url'            => ['required','url','max:200','regex:/^(http|https):\/\//i'],
            'data_type'           => 'required|integer|between:1,3',
            'modules'             => 'nullable|array|max:2000',
            'content'             => 'nullable|string|max:5000',
            'cron_type'           => 'nullable|integer|between:1,4',
            'ext'                 => 'nullable|integer|between:1,2',
            'created_by'          => 'nullable|string|max:100',
            'next_script_id'      => 'nullable|integer|max:999999999',
            'requirement_pool_id' => 'nullable|integer|max:999999999',
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

        if (!empty($params['requirement_pool_id'])) {
            //查询需求池信息
            $requirement = Requirement::find($params['requirement_pool_id']);
            if (empty($requirement)) {
                throw new \Dingo\Api\Exception\ResourceException('$requirement is not found');
            }
            $params['company_id'] = $requirement->company_id;
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
                'name'                => $params['name'],
                'description'         => $params['description'],
                'list_url'            => $params['list_url'],
                'data_type'           => $params['data_type'],
                'casper_config_id'    => $scriptConfig['id'],
                'modules'             => $params['modules'],
                'content'             => $params['content'],
                'is_proxy'            => $params['is_proxy'],
                'cron_type'           => $params['cron_type'],
                'requirement_pool_id' => $params['requirement_pool_id'],
                'company_id'          => $params['company_id'],
                'ext'                 => $params['ext'],
                'created_by'          => $params['created_by'],
                'status'              => Script::STATUS_INIT,
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
     * update
     * 更新
     *
     * @param
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id'                  => 'required|integer',
            'name'                => 'nullable|string|max:100',
            'description'         => 'nullable|string|max:1000',
            'list_url'            => ['nullable', 'url', 'max:200', 'regex:/^(http|https):\/\//i'],
            'data_type'           => 'nullable|integer|between:1,3',
            'modules'             => 'nullable|array|max:2000',
            'content'             => 'nullable|string|max:5000',
            'cron_type'           => 'nullable|integer|between:1,4',
            'ext'                 => 'nullable|integer|between:1,2',
            'created_by'          => 'nullable|string|max:100',
            'next_script_id'      => 'nullable|integer|max:999999999',
            'requirement_pool_id' => 'nullable|integer|max:999999999',
            'is_proxy'            => 'nullable|integer|between:1,2',
            'projects'            => 'nullable|array|max:100',
            'filters'             => 'nullable|array|max:200',
            'actions'             => 'nullable|array|max:200',
            'casper_config'       => 'nullable|array'
        ]);

        //去name和description空白字符
        if (!empty($params['name'])) {
            $params['name'] = trim($params['name']);
        }

        if (!empty($params['description'])) {
            $params['description'] = trim($params['description']);
        }

        if (!empty($params['requirement_pool_id'])) {
            //查询需求池信息
            $requirement = Requirement::find($params['requirement_pool_id']);
            if (empty($requirement)) {
                throw new \Dingo\Api\Exception\ResourceException('$requirement is not found');
            }
            $params['company_id'] = $requirement->company_id;
        }

        $script = Script::find($params['id']);

        //检测数据是否存在
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        try {
            DB::beginTransaction();
            $params['status'] = Script::STATUS_INIT;
            $script->update($params);
            $result = $script->toArray();

            //判断是否修改配置,有数据就修改,无跳过
            if (!empty($params['casper_config']) && $script->data_type == Script::DATA_TYPE_CASPERJS && !empty($script->modules)) {
                $result['casper_config'] = $this->__updateScriptConfig($params['casper_config'], $script);
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollback();

            Log::error('Script update or __updateScriptConfig    Exception:'."\t".$e->getCode()."\t".$e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("update script or update script config is failed");
        }

        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * retrieve
     * 详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer|max:999999999',
        ]);

        $script = Script::find($params['id']);

        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        $result = $script->toArray();

        if ($result['data_type'] == Script::DATA_TYPE_CASPERJS && !empty($result['modules'])) {
            //根据一对一关系,查询该script的配置数据
            $scriptConfig = $script->casperConfig;
            if (empty($scriptConfig)) {
                throw new \Dingo\Api\Exception\ResourceException('$scriptConfig is not found');
            }
            $result['casper_config'] = $scriptConfig->toArray();
        }


        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * publishScript
     * 发布脚本
     *
     * @param $id
     * @return array
     */
    public function publishScript(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id'        => 'required|integer|max:999999999',
            'publisher' => 'required|string|max:100'
        ]);

        //查询script数据
        $script = Script::find($params['id']);

        //判断script数据是否存在
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }
        try {
            //生成脚本
            $filename = $this->__generateScript($script);

            $script->status = Script::STATUS_GENERATE;
            $script->last_generate_at = time();
            $script->save();

            //调用task创建接口
            $postTaskData = [];
            $postTaskData['script_id'] = $script->id;
            $postTaskData['script_path'] = $filename;
            $postTaskData['publisher'] = $params['publisher'];
            $result = InternalAPIV2Service::post('/task', $postTaskData);

            //调用保存任务与分发器关系接口
            $postTaskProjectMapData = [];
            $postTaskProjectMapData['task_id'] = $result['id'];

            InternalAPIV2Service::post('/task/project_map', $postTaskProjectMapData);

            $postTaskData['id'] = $result['id'];

            if ($script->cron_type !== Script::CRON_TYPE_ONCE) {
                InternalAPIV2Service::post('/task/start', $postTaskData);
            }

            //生成task_statistics
            $taskStatistics = new TaskStatistics;

            $taskStatistics->task_id = $result['id'];

            $taskStatistics->save();
        } catch (\Exception $e) {

            Log::error('publishScript    Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("script publish is failed");
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

    /**
     * __updateScriptConfig
     * 更新
     *
     * @param
     * @return array
     */
    public function __updateScriptConfig($postScriptConfig, $script)
    {
        //判断配置数据存在,存在则验证参数,无则赋予空数组
        ValidatorService::check($postScriptConfig, [
            'load_images'  => 'nullable|integer|between:1,2',
            'load_plugins' => 'nullable|integer|between:1,2',
            'log_level'    => 'nullable|string|max:10',
            'verbose'      => 'nullable|integer|between:1,2',
            'width'        => 'nullable|string|max:10',
            'height'       => 'nullable|string|max:10'
        ]);

        $scriptConfig = $script->casperConfig;

        if (empty($scriptConfig)) {
            throw new \Dingo\Api\Exception\ResourceException('$scriptConfig is not found');
        }

        $scriptConfig->update($postScriptConfig);

        return $scriptConfig->toArray();
    }

    /**
     * __generateScript
     * 生成脚本
     *
     * @param script $script
     * @return boolean
     */
    public function __generateScript($script)
    {
        $content = $script->content;

        if (empty($content)) {
            $dataType = $script->data_type;

            //获取script基础模板内容
            $content = $this->__getBaseScriptModel($dataType);

            if ($dataType == Task::DATA_TYPE_CASPERJS) {

                //获取script配置数据
                $scriptConfig = $script->casperConfig;

                if (empty($scriptConfig)) {
                    throw new \Dingo\Api\Exception\ResourceException('$scriptConfig is not found');;
                }

                //将配置数据转化为数组处理
                $scriptConfig = $scriptConfig->toArray();

                //替换模板中的配置
                $content = $this->__replaceScriptContentByScriptConfig($content, $scriptConfig);
            }

            //整理代码数据
            $result = $this->__arrangeStructureContentByScriptStep($script);

            //连接基础模板内容和script代码
            $content = $content . PHP_EOL . PHP_EOL . $result;
        }

        //替换步骤中的换行符
        $content = str_replace(array("\r\n", "\r", "\n"), PHP_EOL,  $content);

        //命名js名称
        $filename = 'script_' . $script->id . '_' . time() . '.js';

        $fileService = new FileService();
        $result = $fileService->create($filename, $content);

        //检查文件是否生成成功
        if (!$result) {
            throw new \Dingo\Api\Exception\ResourceException("file generation is failed");
        }

        return $filename;
    }


    /**
     * __getBaseScriptModel
     * 获取基础模板内容
     *
     * @param
     * @return string
     */
    private function __getBaseScriptModel($dataType)
    {
        //根据脚本类型选择模块
        $scriptModel = ScriptModel::where('system_type',ScriptModel::SYSTEM_TYPE_BASE)
            ->where('data_type',$dataType)
            ->first();

        if (empty($scriptModel)) {
            throw new \Dingo\Api\Exception\ResourceException('$scriptModel is not found');
        }

        //返回代码内容
        return $scriptModel->structure;
    }

    /**
     * __arrangeStructureContentByScriptStep
     * 整理script代码数据
     *
     * @param $script
     * @return string
     */
    private function __arrangeStructureContentByScriptStep($script)
    {
        //$script转化为数组
        $script = $script->toArray();

        //获取步骤
        $modules = $script['modules'];

        $structures = '';
        $num = count($modules);
        for ($i = 0; $i < $num; $i++) {

            //获取模块信息
            $scriptModel = ScriptModel::find($modules[$i][0]);
            if (empty($scriptModel)) {
                throw new \Dingo\Api\Exception\ResourceException('$scriptModel is not found');
            }
            $modelInfo = $scriptModel->toArray();

            //获取代码
            $structure = $modelInfo['structure'];

            //如果只有两个参数,第二个参数为null,直接连接字符串,跳出本次循环
            $paramNum = count($modules[$i]);
            if ($paramNum <= 2 && empty($modules[$i][1])) {
                //代码两次换行,生成脚本时代码分块
                $structures .= $structure . PHP_EOL . PHP_EOL;

                continue;
            }

            for ($j = 1; $j < $paramNum; $j++) {
                if (empty($modules[$i][$j])) {
                    $modules[$i][$j] = '""';
                }
                //参数替换
                $structure = str_replace("~" . $j . "~", $modules[$i][$j], $structure);
            }

            //代码两次换行,生成脚本时代码分块
            $structures .= $structure . PHP_EOL . PHP_EOL;
        }

        return $structures;
    }

    /**
     * __replaceScriptContentByScriptConfig
     * 替换模板中的配置内容
     *
     * @param $content $scriptConfig
     * @return string
     */
    private function __replaceScriptContentByScriptConfig($content, $scriptConfig)
    {
        //转化参数值,将数据中的1对应true,2对应false
        if ($scriptConfig['load_images'] == 1){
            $loadImages = 'true';
        } else {
            $loadImages = 'false';
        }

        if ($scriptConfig['load_plugins'] == 1){
            $loadPlugins = 'true';
        } else {
            $loadPlugins = 'false';
        }

        if ($scriptConfig['verbose'] == 1){
            $verbose = 'true';
        } else {
            $verbose = 'false';
        }

        //替换模板中的配置参数
        $content = str_replace("{{load_images}}", $loadImages, $content);
        $content = str_replace("{{load_plugins}}", $loadPlugins, $content);
        $content = str_replace("{{log_level}}", $scriptConfig['log_level'], $content);
        $content = str_replace("{{verbose}}",$verbose, $content);

        if (empty($scriptConfig['height'])) {
            $content = str_replace("{{height}}", '', $content);
        } else {
            $content = str_replace("{{height}}","height: " . $scriptConfig['height'] . ",", $content);
        }

        if (empty($scriptConfig['width'])) {
            $content = str_replace("{{width}}", '', $content);
        } else {
            $content = str_replace("{{width}}","width: " . $scriptConfig['width'] . ",", $content);
        }

        return $content;
    }

}