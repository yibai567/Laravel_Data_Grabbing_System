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
use Illuminate\Support\Facades\DB;
use Log;
use App\Models\Script;
use App\Models\ScriptInit;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

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
            'init' => 'nullable|json'
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
            //整理入库数据
            $data = [
                'name' => $params['name'],
                'description' => $params['description'],
                'languages_type' => $params['languages_type'],
                'step' => $params['step'],
                'status' => Script::STATUS_INIT,
                'operate_user' => $params['operate_user'],
                'cron_type' => $params['cron_type']
            ];

            if (!empty($scriptInit)) {
                $data['script_init_id'] = $scriptInit['id'];
            }

            $script = Script::create($data);

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
            'init' => 'nullable|json'
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
            $script->update($params);

            $result = $script->toArray();

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
                //获取基础模板内容
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
                $content = $this->__getBaseScriptModel(Script::LANGUAGES_TYPE_HTML);

                break;
            case Script::LANGUAGES_TYPE_API:
                $content = $this->__getBaseScriptModel(Script::LANGUAGES_TYPE_API);

                break;
            default:
                throw new \Dingo\Api\Exception\ResourceException("template is not found");

                break;
        }

        //连接模板内容和代码
        $content = $content . PHP_EOL . PHP_EOL . $result;

        //命名js名称
        $filename = 'script_' . $script->id . '.js';

        //拼接路径和名称
        $filePath = config('script.casperjs_generate_path');
        $file = $filePath.$filename;

        //写入文件
        file_put_contents($file, $content);

        //检查文件是否生成成功
        if (!file_exists($file)){
            throw new \Dingo\Api\Exception\ResourceException("file generation failed");
        }

        //更新状态和时
        $script->status = Script::STATUS_GENERATE;
        $script->last_generate_at = time();
        $script->save();

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
        //解析json转数组
        $init = json_decode($scriptInit,true);

        //判断配置数据存在,存在则验证参数,无则赋予空数组
        if (!empty($init)) {
            ValidatorService::check($init, [
                'load_images' => 'nullable|integer|between:1,2',
                'load_plugins' => 'nullable|integer|between:1,2',
                'log_level' => 'nullable|string|max:10',
                'verbose' => 'nullable|integer',
                'width' => 'nullable|string|max:10',
                'height' => 'nullable|string|max:10'
            ]);
        }
        //设置默认值
        if (empty($init['load_images'])) {
            $init['load_images'] = ScriptInit::DEFAULT_LOAD_IMAGES;
        }

        if (empty($init['load_plugins'])) {
            $init['load_plugins'] = ScriptInit::DEFAULT_LOAD_PLUGINS;
        }

        if (empty($init['log_level'])) {
            $init['log_level'] = ScriptInit::DEFAULT_LOG_LEVEL;
        }

        if (empty($init['verbose'])) {
            $init['verbose'] = ScriptInit::DEFAULT_VERBOSE;
        }
        $scriptInit = ScriptInit::create($init);

        $result = [];
        if (!empty($scriptInit)) {
            $result = $scriptInit->toArray();
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
        //解析json转数组
        $postScriptInit = json_decode($postScriptInit,true);
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
        $stepArr = json_decode($steps,true);
        $structures = '';
        $num = count($stepArr);
        for ($i = 0; $i < $num; $i++) {

            //获取模块信息
            $scriptModel = ScriptModel::find($stepArr[$i][0]);
            if (empty($scriptModel)) {
                throw new \Dingo\Api\Exception\ResourceException("ScriptModel is not found");
            }
            $modelInfo = $scriptModel->toArray();

            //获取代码
            $structure = $modelInfo['structure'];

            //如果只有一个参数,跳出本次循环
            $paramNum = count($stepArr[$i]);
            if($paramNum <= 1){
                continue;
            }

            for ($j = 1; $j < $paramNum; $j++) {
                if (empty($stepArr[$i][$j])) {
                    $stepArr[$i][$j] = '""';
                }

                //替换步骤中的换行符
                $stepArr[$i][$j] = str_replace(array("\r\n", "\r", "\n"), PHP_EOL,  $stepArr[$i][$j]);

                //参数替换
                $structure = str_replace("~" . $j . "~", $stepArr[$i][$j], $structure);
            }
            //代码两次换行,可以生成脚本时隔开一行
            $structures .= $structure . PHP_EOL . PHP_EOL;
        }

        return $structures;
    }

    /**
     * __getScriptInitData
     * 获取casperjs的模板内容
     *
     * @param $script
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

}