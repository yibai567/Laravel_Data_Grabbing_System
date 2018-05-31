<?php
/**
 * ScriptController
 * 模块控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/07
 */

namespace App\Http\Controllers\InternalAPI;

use App\Models\ScriptModel;
use App\Models\Task;
use App\Models\TaskStatistics;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Log;
use App\Models\Script;
use App\Models\ScriptConfig;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ScriptController extends Controller
{
    /**
     * create
     * 创建
     *
     * @param
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'name'                => 'required|string|max:100',
            'description'         => 'nullable|string|max:255',
            'list_url'            => 'nullable|url',
            'languages_type'      => 'required|integer|between:1,3',
            'step'                => 'required|array',
            'cron_type'           => 'nullable|integer',
            'operate_user'        => 'required|string|max:50',
            'next_script_id'      => 'nullable|integer',
            'requirement_pool_id' => 'nullable|integer',
            'is_report'           => 'required|integer|between:1,2',
            'is_download'         => 'required|integer|between:1,2',
            'is_proxy'            => 'required|integer|between:1,2',
            'init'                => 'nullable|array'
        ]);

        //默认值
        if (empty($params['cron_type'])) {
            $params['cron_type'] = Script::CRON_TYPE_KEEP;
        }

        //去name和description空白字符
        if (!empty($params['name'])) {
            $params['name'] = trim($params['name']);
        }

        if (!empty($params['description'])) {
            $params['description'] = trim($params['description']);
        }

        $config = $params['init'];

        try {
            DB::beginTransaction();

            //判断当模板类型等于script的时候需要添加配置
            if ($params['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
                //调用添加配置的方法
                $scriptConfig = $this->__createScriptConfig($config);
            }
            //整理script入库数据
            $scriptData = [
                'list_url'            => $params['list_url'],
                'languages_type'      => $params['languages_type'],
                'step'                => $params['step'],
                'status'              => Script::STATUS_INIT,
                'operate_user'        => $params['operate_user'],
                'next_script_id'      => $params['next_script_id'],
                'requirement_pool_id' => $params['requirement_pool_id'],
                'is_report'           => $params['is_report'],
                'is_download'         => $params['is_download'],
                'is_proxy'            => $params['is_proxy'],
            ];

            if (!empty($scriptConfig)) {
                $scriptData['script_init_id'] = $scriptConfig['id'];
            }

            $script = Script::create($scriptData);

            //整理task数据
            $taskData = [
                'languages_type' => $params['languages_type'],
                'script_id'      => $script->id,
                'name'           => $params['name'],
                'description'    => $params['description'],
                'cron_type'      => $params['cron_type'],
                'status'         => Task::STATUS_INIT,
            ];

            $task = Task::create($taskData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Script create or __createScriptConfig    Exception:'."\t".$e->getCode()."\t".$e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create script or create script config is failed");
        }

        $result = [];
        if (!empty($script)) {
            $result = $script->toArray();
        }

        if (!empty($scriptConfig)) {
            $result['init'] = $scriptConfig;
        }

        if (!empty($task)) {
            $result['task'] = $task->toArray();
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
            'description'         => 'nullable|string|max:255',
            'list_url'            => 'nullable|url',
            'languages_type'      => 'nullable|integer|between:1,3',
            'step'                => 'nullable|array',
            'cron_type'           => 'nullable|integer',
            'operate_user'        => 'nullable|string|max:50',
            'next_script_id'      => 'nullable|integer',
            'requirement_pool_id' => 'nullable|integer',
            'is_report'           => 'nullable|integer|between:1,2',
            'is_download'         => 'nullable|integer|between:1,2',
            'is_proxy'            => 'nullable|integer|between:1,2',
            'init'                => 'nullable|array'
        ]);

        //去name和description空白字符
        if (!empty($params['name'])) {
            $params['name'] = trim($params['name']);
        }

        if (!empty($params['description'])) {
            $params['description'] = trim($params['description']);
        }

        $script = Script::find($params['id']);

        //检测数据是否存在
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        try {
            DB::beginTransaction();

            $script->list_url = $params['list_url'];
            $script->languages_type = $params['languages_type'];
            $script->step = $params['step'];
            $script->status = $params['status'];
            $script->operate_user = $params['operate_user'];
            $script->next_script_id = $params['next_script_id'];
            $script->requirement_pool_id = $params['requirement_pool_id'];
            $script->is_report = $params['is_report'];
            $script->is_download = $params['is_download'];
            $script->is_proxy = $params['is_proxy'];

            $script->save();

            $result = $script->toArray();

            //更改或创建task信息
            $task = $this->__updateTask($params);

            $result['task'] = $task;

            //判断是否修改配置,有数据就修改,无跳过
            if (!empty($params['init']) && $params['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
                $result['init'] = $this->__updateScriptConfig($params['init'], $script);
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
            'id' => 'required|integer',
        ]);

        $script = Script::find($params['id']);

        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        $result = $script->toArray();

        if ($result['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
            //根据一对一关系,查询该script的配置数据
            $scriptConfig = $script->config;
            $result['init'] = [];

            if (empty($scriptConfig)) {
                throw new \Dingo\Api\Exception\ResourceException('$scriptConfig is not found');
            }
            $result['init'] = $scriptConfig->toArray();
        }

        $task = Task::where('script_id',$params['id'])
                    ->whereIn('status',[Task::STATUS_INIT,Task::STATUS_START])
                    ->first();

        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }
        //整理task数据岛返回结果
        $result['task_id'] = $task->id;
        $result['name'] = $task->name;
        $result['description'] = $task->description;
        $result['cron_type'] = $task->cron_type;

        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * all
     * 列表
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($request->all(), [
            'limit'  => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        $items = Script::take($params['limit'])
                    ->skip($params['offset'])
                    ->orderBy('id', 'desc')
                    ->get();

        $result = [];
        if (!empty($items)) {
            $result = $items->toArray();
        }

        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * generateScript
     * 生成脚本
     *
     * @param $id
     * @return array
     */
    public function generateScript(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        //查询script数据
        $script = Script::find($params['id']);

        //判断script数据和配置是否存在
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        $scriptLanguagesType = $script->languages_type;

        //获取script基础模板内容
        $content = $this->__getBaseScriptModel($scriptLanguagesType);

        if ($scriptLanguagesType == Script::LANGUAGES_TYPE_CASPERJS) {

            //获取script配置数据
            $scriptConfig = $script->config;

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

        //替换步骤中的换行符
        $content = str_replace(array("\r\n", "\r", "\n"), PHP_EOL,  $content);

        //命名js名称
        $filename = 'script_' . $script->id . '.js';

        $fileService = new FileService();
        $result = $fileService->create($filename, $content);

        //检查文件是否生成成功
        if (!$result) {
            throw new \Dingo\Api\Exception\ResourceException("file generation is failed");
        }

        //生成脚本之后,做的一系列状态操作
        $result =$this->__afterScriptGenerated($script);

        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * __createScriptConfig
     * 创建script配置
     *
     * @param
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
                'verbose'      => 'nullable|integer',
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
     * __updateTask
     * 更新task信息
     *
     * @param $uploadData
     * @return array
     */
    public function __updateTask($uploadData)
    {
        //更改script对应的task为停止
        $tasks = Task::where('script_id',$uploadData['id'])->where('status',Task::STATUS_START)->get();
        if (empty($tasks)) {
            throw new \Dingo\Api\Exception\ResourceException('$tasks is not found');
        }

        foreach($tasks as $task){
            $task->status = Task::STATUS_STOP;
            $task->save();
        }

        //查询script是否有未启动的task
        $task = Task::where('script_id',$uploadData['id'])->where('status',Task::STATUS_INIT)->first();

        //查看是否有初始化的task,有则在原基础上该,无则创建
        if (empty($task)) {
            //整理task数据
            $taskData = [
                'languages_type' => $uploadData['languages_type'],
                'script_id'      => $uploadData['id'],
                'name'           => $uploadData['name'],
                'description'    => $uploadData['description'],
                'cron_type'      => $uploadData['cron_type'],
                'status'         => Task::STATUS_INIT,
            ];

            $taskInfo = Task::create($taskData);
        } else {
            //整理task数据
            $task->languages_type = $uploadData['languages_type'];
            $task->name = $uploadData['name'];
            $task->description = $uploadData['description'];
            $task->cron_type = $uploadData['cron_type'];
            $task->status = $uploadData['status'];
            $task->save();

            $taskInfo =$task;
        }

        $result = [];

        if (!empty($taskInfo)) {

            $result = $taskInfo->toArray();
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
            'verbose'      => 'nullable|integer',
            'width'        => 'nullable|string|max:10',
            'height'       => 'nullable|string|max:10'
        ]);

        $scriptConfig = $script->config;

        if (empty($scriptConfig)) {
            throw new \Dingo\Api\Exception\ResourceException('$scriptConfig is not found');
        }

        $scriptConfig->load_images = $postScriptConfig['load_images'];
        $scriptConfig->load_plugins = $postScriptConfig['load_plugins'];
        $scriptConfig->log_level = $postScriptConfig['log_level'];
        $scriptConfig->verbose = $postScriptConfig['verbose'];
        $scriptConfig->width = $postScriptConfig['width'];
        $scriptConfig->height = $postScriptConfig['height'];

        $scriptConfig->save();

        return $scriptConfig->toArray();
    }

    /**
     * __getBaseScriptModel
     * 获取基础模板内容
     *
     * @param
     * @return string
     */
    private function __getBaseScriptModel($languagesType)
    {
        //根据脚本类型选择模块
        $scriptModel = ScriptModel::where('system_type',ScriptModel::SYSTEM_TYPE_BASE)
                                    ->where('languages_type',$languagesType)
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
        $steps = $script['step'];

        $structures = '';
        $num = count($steps);
        for ($i = 0; $i < $num; $i++) {

            //获取模块信息
            $scriptModel = ScriptModel::find($steps[$i][0]);
            if (empty($scriptModel)) {
                throw new \Dingo\Api\Exception\ResourceException('$scriptModel is not found');
            }
            $modelInfo = $scriptModel->toArray();

            //获取代码
            $structure = $modelInfo['structure'];

            //如果只有两个参数,第二个参数为null,直接连接字符串,跳出本次循环
            $paramNum = count($steps[$i]);
            if ($paramNum <= 2 && empty($steps[$i][1])) {
                //代码两次换行,生成脚本时代码分块
                $structures .= $structure . PHP_EOL . PHP_EOL;

                continue;
            }

            for ($j = 1; $j < $paramNum; $j++) {
                if (empty($steps[$i][$j])) {
                    $steps[$i][$j] = '""';
                }
                //参数替换
                $structure = str_replace("~" . $j . "~", $steps[$i][$j], $structure);
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

    /**
     * __afterScriptGenerated
     * 生成脚本后,更改script和task状态,生成task_statistics
     *
     * @param Script $script
     * @return boolean
     */
    private function __afterScriptGenerated($script)
    {

        try {
            DB::beginTransaction();

            //更新script状态和最后生成时间
            $script->status = Script::STATUS_GENERATE;
            $script->last_generate_at = time();
            $script->save();

            //查找script对应的初始化的task(详情script不启动任务),修改状态
            $task = Task::where('script_id',$script->id)->where('status',Task::STATUS_INIT)->first();

            if ($task->cron_type !== Script::CRON_TYPE_ONCE) {
                $task->status = Task::STATUS_START;
                $task->save();
            }

            //生成task_statistics
            $taskStatistics = new TaskStatistics;

            $taskStatistics->task_id = $task->id;
            $taskStatistics->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('__afterScriptGenerated    Exception:'."\t".$e->getCode()."\t".$e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("status update is failed");
        }

        return true;
    }

    /**
     * 根据队列名获取队列数据
     * @param $name 队列名称
     */
    public function getByQueueName(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'name' => 'string|required|max:100',
        ]);

        try {
            $data = [];

            if (Redis::connection('queue')->lLen($params['name']) > 0 ) {
                for ($i = 0; $i < 10; $i++) {
                    $value = Redis::connection('queue')->rpop($params['name']);

                    if (is_null($value)) {
                        break;
                    }
                    $data[$i] = json_decode($value, true);
                }
            }
        } catch (\Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($data, 'list', $request->path());
    }
}