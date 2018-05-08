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
            'step' => 'required|array',
            'cron_type' => 'nullable|integer',
            'operate_user' => 'required|string|max:50',
            'init' => 'nullable|json'
        ]);

        //默认值
        if (empty($params['cron_type'])) {
            $params['cron_type'] = Script::IS_INIT;
        }

        $init = $params['init'];
        //开启事务
        try {
            //调用添加配置的方法
            $scriptInit = $this->__createInit($init);
            $params['script_init_id'] = $scriptInit['id'];

            unset($params['init']);
            //给定默认值
            $params['status'] = Script::STATUS_INIT;
            //数组转json
            $params['step'] = json_encode($params['step']);
            //添加script
            $script = Script::create($params);
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }

        $result = [];
        if (!empty($script)) {
            //整理数据
            $result = $script->toArray();
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
            'step' => 'nullable|array',
            'cron_type' => 'nullable|integer',
            'operate_user' => 'nullable|string|max:50',
            'init' => 'nullable|json'
        ]);

        $script = Script::find($params['id']);

        //检测数据是否存在
        if (empty($script)) {
            return $this->resError(405, 'script is not exists!');
        }

        $init = $params['init'];
        unset($params['init']);

        //将step数组转化为json存入数据库
        $params['step'] = json_encode($params['step']);
        //开启事务
        try {
            //修改script数据
            $script->update($params);
            $result = $script->toArray();

            //判断是否修改配置,有数据就修改,无跳过
            if (!empty($init)) {
                $result['init'] = $this->__updateInit($init, $script);
            }
        } catch (Exception $e){
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
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
        //根据一对一关系,查询该script的配置数据
        $scriptInit = $script->init;

        $result = [];
        //整理数据
        if (!empty($script)) {
            $result = $script->toArray();
        }

        $result['init'] = [];
        if (!empty($scriptInit)) {
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
        ValidatorService::check($params, [
            'page' => 'nullable|integer',
            'num' => 'nullable|integer',
        ]);

        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['num'])) {
            $params['num'] = 20;
        }
        //求出跳过数据个数
        $offset = $params['num'] * ($params['page'] - 1);

        //获取数据
        $items = Script::take($params['num'])
            ->skip($offset)
            ->orderBy('id', 'desc')
            ->get();

        $result = [];
        if (!empty($items)) {
            $result = $items->toArray();
        }


        return $this->resObjectGet($result, 'script', $request->path());
    }

    /**
     * generateScript()
     * 生成脚本
     *
     * @param
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

        //查询配置数据和script数据
        $script = Script::find($params['id']);
        $scriptInit = $script->init;

        //判断script数据和配置是否存在
        if (empty($script)) {
            return $this->resError(405, 'script is not exists!');
        }

        if (empty($scriptInit)) {
            return $this->resError(405, 'scriptInit is not exists!');
        }
        //将script数据和配置转为数组
        $scriptInfo = $script->toArray();
        $init = $scriptInit->toArray();

        //整理代码数据
        $result = $this->__arrangeData($scriptInfo);

        //转化参数值,将数据中的1对应true,2对应false
        $loadImages = $init['load_images'] == 1 ? "true" : "false";
        $loadPlugins = $init['load_plugins'] == 1 ? "true" : "false";
        $verbose = $init['verbose'] == 1 ? "true" : "false";
        //获取模板
        $content = file_get_contents(public_path().'/js/alphacj_index-news.js');
        //替换模板中的配置参数
        $content = str_replace("{{load_images}}", $loadImages, $content);
        $content = str_replace("{{load_plugins}}", $loadPlugins, $content);
        $content = str_replace("{{log_level}}", $init['log_level'], $content);
        $content = str_replace("{{verbose}}",$verbose, $content);
        $content = str_replace("{{width}}", $init['width'], $content);
        $content = str_replace("{{height}}",$init['height'], $content);
        //将代码数据和原来模板内容合并
        $content = $content.$result;

        $lastGenerateAt = time();
        //命名js名称
        $filename = public_path().'/js/script_' . $scriptInfo['id'] . '_'.$lastGenerateAt.'.js';

        //写入文件
        file_put_contents($filename, $content);

        //检查文件是否生成成功
        if (!file_exists($filename)){
            return $this->resError(404, 'file generation fail!');
        }

        //更新状态和时
        $script->status = Script::STATUS_GENERATE;
        $script->last_generate_at = $lastGenerateAt;
        $script->save();

        return $this->resObjectGet(true, 'script', $request->path());
    }

    /**
     * __creteInit()
     * 创建script配置
     * @param
     * @return array
     */
    private function __createInit($init)
    {
        //解析json转数组
        $init = json_decode($init,true);
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
        $init['load_images'] = $init['load_images'] ?? ScriptInit::DEFAULT_LOAD_IMAGES;
        $init['load_plugins'] = $init['load_plugins'] ?? ScriptInit::DEFAULT_LOAD_PLUGINS;
        $init['log_level'] = $init['log_level'] ?? ScriptInit::DEFAULT_LOG_LEVEL;
        $init['verbose'] = $init['verbose'] ?? ScriptInit::DEFAULT_VERBOSE;

        $scriptInit = ScriptInit::create($init);

        $result = [];
        if (!empty($scriptInit)) {
            $result = $scriptInit->toArray();
        }

        return $result;
    }

    /**
     * __updateInit
     * 更新
     *
     * @param
     * @return array
     */
    public function __updateInit($init, $script)
    {
        //解析json转数组
        $init = json_decode($init,true);
        //判断配置数据存在,存在则验证参数,无则赋予空数组
        ValidatorService::check($init, [
            'load_images' => 'nullable|integer|between:1,2',
            'load_plugins' => 'nullable|integer|between:1,2',
            'log_level' => 'nullable|string|max:10',
            'verbose' => 'nullable|integer',
            'width' => 'nullable|string|max:10',
            'height' => 'nullable|string|max:10'
        ]);

        $scriptInit = $script->init;

        if (empty($scriptInit)) {
            return $this->resError(405, 'scriptInit is not exists!');
        }

        $scriptInit->update($init);
        $result = $scriptInit->toArray();

        return $result;
    }

    /**
     * __arrangeData
     * 更新
     *
     * @param $id
     * @return array
     */
    private function __arrangeData($script)
    {
        $steps = $script['step'];
        //json转数组
        $stepArr = json_decode($steps,true);
        $num = count($stepArr);
        for ($i = 0; $i < $num; $i++) {
            //获取模块信息
            $scriptModel = ScriptModel::find($stepArr[$i][0]);

            if (empty($scriptModel)) {
                return $this->resError(405, 'scriptModel is not exists!');
            }
            $modelInfo = $scriptModel->toArray();
            //获取代码
            $structure = $modelInfo['structure'];
            $paramNum = count($stepArr[$i]);
            if($paramNum > 1){
                for ($j = 1; $j < $paramNum; $j++) {
                    //参数替换
                    $structure = str_replace("$".$j, '"'.$stepArr[$i][$j].'"', $structure);
                }
            }

            $structures .= $structure."\n"."\n";

        }

        return $structures;
    }

}