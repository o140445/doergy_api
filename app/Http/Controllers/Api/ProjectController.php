<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * 获取项目列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // 这里可以添加获取项目列表的逻辑
        $user = auth()->user();
        $data = Project::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'created_at']);
        return $this->success($data, '项目列表获取成功');
    }

    /**
     * 获取单个项目详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // 这里可以添加获取单个项目详情的逻辑
        $user = auth()->user();
        $project = Project::where('user_id', $user->id)->findOrFail($id, ['id', 'name']);
        return $this->success($project, '项目详情获取成功');
    }

    /**
     * add a new project
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // 检查name 是否已存在
        if (Project::where('user_id', $user->id)->where('name', $data['name'])->exists()) {
            return $this->error('项目名称已存在', 422);
        }

        $project = new Project($data);
        $project->user_id = $user->id;
        $project->clor =  get_random_color();
        $project->save();

        return $this->success($project, '项目创建成功');
    }

    /**
     * 更新项目
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $project = Project::where('user_id', $user->id)->findOrFail($id);

        // 检查name 是否已存在
        if (Project::where('user_id', $user->id)->where('name', $data['name'])->where('id', '!=', $id)->exists()) {
            return $this->error('项目名称已存在', 422);
        }

        $project->update($data);

        return $this->success($project, '项目更新成功');
    }

    /**
     * 删除项目
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $project = Project::where('user_id', $user->id)->findOrFail($id);

        // 删除项目
        $project->delete();

        return $this->success([], '项目删除成功');
    }

}
