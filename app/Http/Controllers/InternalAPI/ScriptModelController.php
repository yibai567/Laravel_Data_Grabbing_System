<?php
/**
 * ScriptModelController
 * Script模块控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/07
 */

namespace App\Http\Controllers\InternalAPI;

use Log;
use App\Models\ScriptModel;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ScriptModelController extends Controller
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

        ValidatorService::check($params, [
            'name'           => 'required|string|max:50',
            'description'    => 'nullable|string',
            'structure'      => 'required|string',
            'languages_type' => 'required|integer|between:1,3',
            'parameters'     => 'required|json',
            'system_type'    => 'required|integer|between:1,2',
            'operate_user'   => 'required|string|max:50',
            'sort'           => 'nullable|integer'
        ]);

        $scriptModel = ScriptModel::create($params);

        $result = [];
        if (!empty($scriptModel)) {
            $result = $scriptModel->toArray();
        }

        return $this->resObjectGet($result, 'script_model', $request->path());
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

        ValidatorService::check($params, [
            'id'             => 'required|integer',
            'name'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'structure'      => 'nullable|string',
            'languages_type' => 'nullable|integer|between:1,3',
            'system_type'    => 'nullable|integer|between:1,2',
            'parameters'     => 'nullable|json',
            'operate_user'   => 'nullable|string|max:50',
            'sort'           => 'nullable|integer'
        ]);

        $scriptModel = ScriptModel::find($params['id']);

        if (empty($scriptModel)) {
            throw new \Dingo\Api\Exception\ResourceException('$scriptModel is not found');
        }

        $scriptModel->name = $params['name'];
        $scriptModel->description = $params['description'];
        $scriptModel->structure = $params['structure'];
        $scriptModel->languages_type = $params['languages_type'];
        $scriptModel->system_type = $params['system_type'];
        $scriptModel->parameters = $params['parameters'];
        $scriptModel->operate_user = $params['operate_user'];
        $scriptModel->sort = $params['sort'];

        $scriptModel->save();
        $result = $scriptModel->toArray();

        return $this->resObjectGet($result, 'script_model', $request->path());
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

        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        $scriptModel = ScriptModel::find($params['id']);

        $result = [];
        if (!empty($scriptModel)) {
            $result = $scriptModel->toArray();
        }

        return $this->resObjectGet($result, 'script_model', $request->path());
    }

    /**
     * listByLanguagesType
     * 根据languages_type查询列表
     *
     * @param
     * @return array
     */
    public function listByLanguagesType(Request $request, $languages_type)
    {
        $params = $request->all();
        $params['languages_type'] = $languages_type;

        //验证参数
        ValidatorService::check($params, [
            'languages_type' => 'nullable|integer',
            'limit'          => 'nullable|integer|min:1|max:500',
            'offset'         => 'nullable|integer|min:0',
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        //拼接where条件
        $where = [];
        if (!empty($params['languages_type'])){
            $where [] = ['languages_type', $params['languages_type']];
        }

        //获取数据
        $items = ScriptModel::where($where)
                            ->whereIn('system_type', [1,2])
                            ->take($params['limit'])
                            ->skip($params['offset'])
                            ->orderBy('sort', 'asc')
                            ->orderBy('id', 'desc')
                            ->get();

        $result = [];
        if (!empty($items)) {
            $result = $items->toArray();
        }


        return $this->resObjectGet($result, 'script_model', $request->path());
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
        ValidatorService::check($params, [
            'limit'  => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        //获取数据
        $items = ScriptModel::whereIn('system_type', [1,2])
                            ->take($params['limit'])
                            ->skip($params['offset'])
                            ->orderBy('sort', 'asc')
                            ->orderBy('id', 'desc')
                            ->get();

        $result = [];
        if (!empty($items)) {
            $result = $items->toArray();
        }

        return $this->resObjectGet($result, 'script_model', $request->path());
    }

    /**
     * listByIds
     * 根据多个id查模块信息
     *
     * @param ids
     * @return array
     */
    public function listByIds(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'ids' => 'required|string|max:100',
        ]);

        $ids = explode(',', $params['ids']);
        $scriptModel = ScriptModel::whereIn('id', $ids)->get();

        $data = [];
        if (!empty($scriptModel)) {
            $data = $scriptModel->toArray();
        }

        return $this->resObjectGet($data, 'script_model', $request->path());
    }
}