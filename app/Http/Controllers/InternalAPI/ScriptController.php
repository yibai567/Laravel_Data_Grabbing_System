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
use Illuminate\Support\Facades\DB;
use Log;
use App\Models\Script;
use App\Models\ScriptInit;
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
        Log::debug('[internal ScriptController create] start!');
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'languages_type' => 'required|integer|between:1,3',
            'step' => 'required|array',
            'cron_type' => 'nullable|integer',
            'operate_user' => 'required|string|max:50',
            'init' => 'nullable|array'
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

        $init = $params['init'];

        DB::beginTransaction();
        try {
            //判断当模板类型等于script的时候需要添加配置
            if ($params['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
                //调用添加配置的方法
                $scriptInit = $this->__createScriptInit($init);
            }
            //整理script入库数据
            $scriptData = [
                'languages_type' => $params['languages_type'],
                'step' => $params['step'],
                'status' => Script::STATUS_INIT,
                'operate_user' => $params['operate_user'],
            ];

            if (!empty($scriptInit)) {
                $scriptData['script_init_id'] = $scriptInit['id'];
            }

            $script = Script::create($scriptData);

            //整理task数据
            $taskData = [
                'languages_type' => $params['languages_type'],
                'script_id' => $script->id,
                'name' => $params['name'],
                'description' => $params['description'],
                'cron_type' => $params['cron_type'],
                'status' => Task::STATUS_INIT,
            ];

            $task = Task::create($taskData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Script create or __createScriptInit    Exception:'."\t".$e->getCode()."\t".$e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create script or create scriptInit is failed");
        }

        $result = [];
        if (!empty($script)) {
            $result = $script->toArray();
        }

        if (!empty($scriptInit)) {
            $result['init'] = $scriptInit;
        }

        if (!empty($task)) {
            $taskInfo = $task->toArray();
            $result['task'] = $taskInfo;
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
        Log::debug('[internal ScriptController update] start!');
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'languages_type' => 'nullable|integer|between:1,3',
            'step' => 'nullable|array',
            'cron_type' => 'nullable|integer',
            'operate_user' => 'nullable|string|max:50',
            'init' => 'nullable|array'
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
            throw new \Dingo\Api\Exception\ResourceException("script is not found");
        }

        DB::beginTransaction();

        try {
            $scriptData = [
                'languages_type' => $params['languages_type'],
                'step' => $params['step'],
                'status' => Script::STATUS_INIT,
                'operate_user' => $params['operate_user'],
            ];

            $script->update($scriptData);

            if (!empty($script)) {
                $result = $script->toArray();
            }

            //更改或创建task信息
            $task = $this->__updateTask($params);

            $result['task'] = $task;

            //判断是否修改配置,有数据就修改,无跳过
            if (!empty($params['init']) && $params['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
                $result['init'] = $this->__updateScriptInit($params['init'], $script);
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollback();

            Log::error('Script update or __updateScriptInit    Exception:'."\t".$e->getCode()."\t".$e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("update script or update scriptInit is failed");
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
        Log::debug('[internal ScriptController retrieve] start!');
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        $script = Script::find($params['id']);
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException("script is not found");
        }
        $result = $script->toArray();

        if ($result['languages_type'] == Script::LANGUAGES_TYPE_CASPERJS) {
            //根据一对一关系,查询该script的配置数据
            $scriptInit = $script->init;
            $result['init'] = [];

            if (empty($scriptInit)) {
                throw new \Dingo\Api\Exception\ResourceException("scriptInit is not found");
            }
            $result['init'] = $scriptInit->toArray();
        }

        $task = Task::where('script_id',$params['id'])
                    ->whereIn('status',[Task::STATUS_INIT,Task::STATUS_START])
                    ->first();

        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }
        //整理task数据岛返回结果
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
        Log::debug('[internal ScriptController all] start!');
        $params = $request->all();

        //验证参数
        ValidatorService::check($request->all(), [
            'limit' => 'nullable|integer|min:1|max:500',
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
        Log::debug('[internal ScriptController generateScript] start!');
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        //查询script数据
        $script = Script::find($params['id']);

        //判断script数据和配置是否存在
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException("script is not found");
        }

        //整理代码数据
        $result = $this->__formatData($script);

        //通过文件类型获取不同模板内容
        switch ($script->languages_type) {
            case  Script::LANGUAGES_TYPE_CASPERJS:
                //获取casperJs基础模板内容
                $content = $this->__getBaseScriptModel(Script::LANGUAGES_TYPE_CASPERJS);

                //获取script配置数据
                $scriptInit = $script->init;
                if (empty($scriptInit)) {
                    throw new \Dingo\Api\Exception\ResourceException("scriptInit is not found");;
                }
                //将配置数据转化为数组处理
                $init = $scriptInit->toArray();

                //替换模板中的配置
                $content = $this->__getScriptInitData($content, $init);

                break;
            case Script::LANGUAGES_TYPE_HTML:
                //获取HTML基础模板内容
                $content = $this->__getBaseScriptModel(Script::LANGUAGES_TYPE_HTML);

                break;
            case Script::LANGUAGES_TYPE_API:
                //获取API基础模板内容
                $content = $this->__getBaseScriptModel(Script::LANGUAGES_TYPE_API);

                break;
            default:
                throw new \Dingo\Api\Exception\ResourceException("template is not found");

                break;
        }
        //替换基础模板内容中的scriptId将script_id传递给脚本
        $content = str_replace("{{script_id}}", $script->id,  $content);

        //连接模板内容和代码
        $content = $content . PHP_EOL . PHP_EOL . $result;

        //替换步骤中的换行符
        $content = str_replace(array("\r\n", "\r", "\n"), PHP_EOL,  $content);

        //命名js名称
        $filename = 'script_' . $script->id . '.js';

        //拼接路径和名称
        $filePath = config('script.casperjs_generate_path');
        $file = $filePath.$filename;

        //写入文件
        file_put_contents($file, $content);

        //检查文件是否生成成功
        if (!file_exists($file)){
            throw new \Dingo\Api\Exception\ResourceException("file generation is failed");
        }

        //生成脚本之后,做的一系列状态操作
        $this->__afterScriptGenerated($script);

        return $this->resObjectGet(true, 'script', $request->path());
    }

    /**
     * __createScriptInit
     * 创建script配置
     *
     * @param
     * @return array
     */
    private function __createScriptInit($scriptInit)
    {
        //判断配置数据存在,存在则验证参数,无则赋予空数组
        if (!empty($scriptInit)) {
            ValidatorService::check($scriptInit, [
                'load_images' => 'nullable|integer|between:1,2',
                'load_plugins' => 'nullable|integer|between:1,2',
                'log_level' => 'nullable|string|max:10',
                'verbose' => 'nullable|integer',
                'width' => 'nullable|string|max:10',
                'height' => 'nullable|string|max:10'
            ]);
        }
        //设置默认值
        if (empty($scriptInit['load_images'])) {
            $scriptInit['load_images'] = ScriptInit::DEFAULT_LOAD_IMAGES;
        }

        if (empty($scriptInit['load_plugins'])) {
            $scriptInit['load_plugins'] = ScriptInit::DEFAULT_LOAD_PLUGINS;
        }

        if (empty($scriptInit['log_level'])) {
            $scriptInit['log_level'] = ScriptInit::DEFAULT_LOG_LEVEL;
        }

        if (empty($scriptInit['verbose'])) {
            $scriptInit['verbose'] = ScriptInit::DEFAULT_VERBOSE;
        }

        $scriptInit = ScriptInit::create($scriptInit);

        $result = [];
        if (!empty($scriptInit)) {
            $result = $scriptInit->toArray();
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
        $startTasks = Task::where('script_id',$uploadData['id'])->where('status',Task::STATUS_START)->get();
        if (empty($startTasks)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        foreach($startTasks as $task){
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
                'script_id' => $uploadData['id'],
                'name' => $uploadData['name'],
                'description' => $uploadData['description'],
                'cron_type' => $uploadData['cron_type'],
                'status' => Task::STATUS_INIT,
            ];

            $taskInfo = Task::create($taskData);
        } else {
            //整理task数据
            $taskData = [
                'languages_type' => $uploadData['languages_type'],
                'name' => $uploadData['name'],
                'description' => $uploadData['description'],
                'cron_type' => $uploadData['cron_type'],
                'status' => Task::STATUS_INIT,
            ];

            $task->update($taskData);

            $taskInfo =$task;
        }

        $result = [];

        if (!empty($taskInfo)) {

            $result = $taskInfo->toArray();
        }

        return $result;
    }

    /**
     * __updateScriptInit
     * 更新
     *
     * @param
     * @return array
     */
    public function __updateScriptInit($postScriptInit, $script)
    {
        //判断配置数据存在,存在则验证参数,无则赋予空数组
        ValidatorService::check($postScriptInit, [
            'load_images' => 'nullable|integer|between:1,2',
            'load_plugins' => 'nullable|integer|between:1,2',
            'log_level' => 'nullable|string|max:10',
            'verbose' => 'nullable|integer',
            'width' => 'nullable|string|max:10',
            'height' => 'nullable|string|max:10'
        ]);

        $scriptInit = $script->init;

        if (empty($scriptInit)) {
            throw new \Dingo\Api\Exception\ResourceException("scriptInit is not found");
        }

        $scriptInit->update($postScriptInit);
        $result = $scriptInit->toArray();

        return $result;
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
        $baseScriptModel = ScriptModel::where('system_type',ScriptModel::SYSTEM_TYPE_BASE)
                                    ->where('languages_type',$languagesType)
                                    ->first();

        if (empty($baseScriptModel)) {
            throw new \Dingo\Api\Exception\ResourceException("scriptModel is not found");
        }

        //获取代码内容
        $structure = $baseScriptModel->structure;

        return $structure;
    }

    /**
     * __formatData
     * 整理代码数据
     *
     * @param $script
     * @return string
     */
    private function __formatData($script)
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
                throw new \Dingo\Api\Exception\ResourceException("ScriptModel is not found");
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
     * __getScriptInitData
     * 获取ScriptInit的配置内容,并替换模板
     *
     * @param $content $init
     * @return string
     */
    private function __getScriptInitData($content, $init)
    {
        //转化参数值,将数据中的1对应true,2对应false
        if ($init['load_images'] == 1){
            $loadImages = 'true';
        } else {
            $loadImages = 'false';
        }

        if ($init['load_plugins'] == 1){
            $loadPlugins = 'true';
        } else {
            $loadPlugins = 'false';
        }

        if ($init['verbose'] == 1){
            $verbose = 'true';
        } else {
            $verbose = 'false';
        }

        //替换模板中的配置参数
        $content = str_replace("{{load_images}}", $loadImages, $content);
        $content = str_replace("{{load_plugins}}", $loadPlugins, $content);
        $content = str_replace("{{log_level}}", $init['log_level'], $content);
        $content = str_replace("{{verbose}}",$verbose, $content);

        if (empty($init['height'])) {
            $content = str_replace("{{height}}", '', $content);
        } else {
            $content = str_replace("{{height}}","height: " . $init['height'] . ",", $content);
        }

        if (empty($init['width'])) {
            $content = str_replace("{{width}}", '', $content);
        } else {
            $content = str_replace("{{width}}","width: " . $init['width'] . ",", $content);
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
        DB::beginTransaction();

        try {

            //更新script状态和最后生成时间
            $script->status = Script::STATUS_GENERATE;
            $script->last_generate_at = time();
            $script->save();

            //查找script对应的初始化的task,修改状态
            $task = Task::where('script_id',$script->id)->where('status',Task::STATUS_INIT)->first();
            $task->status = Task::STATUS_START;
            $task->save();

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