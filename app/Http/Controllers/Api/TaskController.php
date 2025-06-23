<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    // 这里可以添加任务相关的逻辑
    // 例如获取任务列表、创建任务、更新任务等

    /**
     * 获取任务列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = $request->all();
        // 这里可以添加获取任务列表的逻辑
        $user = auth()->user();
        // 假设我们从数据库中获取任务列表
        $tasks = Task::where('user_id', $user->id);

        if (isset($query['project_id'])) {
            $tasks->where('project_id', $query['project_id']);
        }

        if (isset($query['status'])) {
            $tasks->where('status', $query['status']);
        }

        if (isset($query['priority'])) {
            $tasks->where('priority', $query['priority']);
        }

        if (isset($query['search'])) {
            $tasks->where('title', 'like', '%' . $query['search'] . '%');
        }

        // tag
        if (isset($query['tag'])) {
            $tasks->whereHas('tags', function ($q) use ($query) {
                $q->where('name', $query['tag']);
            });
        }


        $tasks = $tasks->with(['labels' => function ($q) {
                $q->select(['name', 'color', 'labels.id as tag_id'])
                    ->orderBy('tag_id', 'desc');
            }])
//            ->where(function ($q) {
//                $q->where('status', 'pending')
//                    ->orWhere(function ($query) {
//                        $query->where('status', 'completed')
//                            ->whereDate('completed_at', today());
//                    });
//            })
            ->orderBy('status', 'asc')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->select('id', 'title', 'status', 'priority', 'description', 'due_date', 'start_time', 'end_time' , 'completed_at')
            ->get();

        $total = count($tasks);
        $tasks->transform(function ($task) {
            // 格式化任务数据
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'description' => $task->description,
                'due_date' => $task->due_date ? date('Y-m-d', strtotime($task->due_date)) : null,
                'completed_at' => $task->completed_at ? date('Y-m-d ', strtotime($task->completed_at)) : null,
                'tags' => $task->labels->map(function ($label) {
                    return [
                        'id' => $label->tag_id,
                        'name' => $label->name,
                        'color' => $label->color
                    ];
                })->toArray(),
            ];
        });

        $day_total = Task::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere(function ($query) {
                        $query->where('status', 'completed')
                            ->whereDate('completed_at', today());
                    });
            })->count();

        $day_completed = Task::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        return $this->success(
            [
                'tasks' => $tasks,
                'total' => $total,
                'day_total' => $day_total,
                'day_completed' => $day_completed,
            ],
            '任务列表获取成功'
        );
    }

    /**
     * 获取单个任务详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // 这里可以添加获取单个任务详情的逻辑
        return $this->success([], '任务详情获取成功');
    }

    /**
     * 创建新任务
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'due_date' => 'nullable|string',
                'priority' => 'nullable|in:low,medium,high',
                'start_time' => 'nullable|string',
                'end_time' => 'nullable|string',
                'tags' => 'nullable|array',
            ]
        );

        DB::beginTransaction();
        try {

            $due_date = $data['due_date'] ?? '';
            if ($due_date) {
                $due_date = date('Y-m-d', strtotime($due_date));
            }

            $user = auth()->user();
            // 检查用户任务数量是否超过限制
            if ($user->hasExceededLimit('tasks')) {
                return $this->error('任务数量已达上限，请删除一些任务后再试', 422);
            }

            if ($user->id == config('app.demo_id') && $user->tasks()->count() >= config('app.demo_limit')) {
                return $this->error('演示用户不允许创建任务', 422);
            }

            // 创建
            $task = new Task();
            $task->user_id = $user->id;
            $task->project_id = 0;
            $task->title = $data['title'];
            $task->description = $data['description'] ?? '';
            $task->due_date = $due_date;
            $task->priority = $data['priority'] ?? 'medium';
            $task->start_time = $data['start_time'] ?? '';
            $task->end_time = $data['end_time'] ?? '';
            $task->status = 'pending'; // 默认状态为待办

            $task->save();

            // 处理标签
            if (isset($data['tags']) && is_array($data['tags'])) {
                // 保存标签 因为传的name,需要转换成id
                $tags = $this->getTags($data['tags'], $user->id);
                $task->labels()->sync($tags);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('任务创建失败: ' . $e->getMessage(), 422);
        }


        // 这里可以添加创建新任务的逻辑
        return $this->success([], '任务创建成功');
    }

    /**
     * 获取标签ID
     *
     * @param array $tags
     * @return array
     */
    private function getTags(array $tags, $user_id)
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            // 假设标签是通过名称来获取ID的
            $label = \App\Models\Labels::firstOrCreate(['name' => $tag, 'user_id' => $user_id], ['color' => get_random_color()]);
            $tagIds[] = $label->id;
        }
        return $tagIds;
    }

    /**
     * 更新任务
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate(
            [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'due_date' => 'nullable|date',
                'priority' => 'nullable|in:low,medium,high',
                'status' => 'nullable|in:pending,completed,cancelled',
                'project_id' => 'nullable|exists:projects,id',
                'start_time' => 'nullable|string',
                'end_time' => 'nullable|string',
                'tags' => 'nullable|array',
            ]
        );
        $due_date = $data['due_date'] ?? '';
        if ($due_date) {
            $due_date = date('Y-m-d', strtotime($due_date));
        }
        $data['due_date'] = $due_date;

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $task = Task::findOrFail($id);
            // 检查任务是否属于当前用户
            if ($task->user_id !== $user->id ) {
                throw new \Exception('无权更新此任务');
            }

            if ($user->id == config('app.demo_id')) {
                return $this->error('演示用户不允许修改任务', 422);
            }
            $task->update($data);

            // 处理标签
            if (isset($data['tags']) && is_array($data['tags'])) {
                // 保存标签 因为传的name,需要转换成id
                $tags = $this->getTags($data['tags'], $user->id);
                $task->labels()->sync($tags);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('任务更新失败: ' . $e->getMessage(), 422);
        }

        // 这里可以添加更新任务的逻辑
        return $this->success([], '任务更新成功');
    }

    /**
     * 删除任务
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $user = auth()->user();
            // 检查任务是否属于当前用户
            if ($task->user_id !== $user->id ) {
                throw new \Exception('无权删除此任务');
            }
            if ($user->id == config('app.demo_id')) {
                return $this->error('演示用户不允许删除任务', 422);
            }
            $task->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('任务删除失败: ' . $e->getMessage(), 422);
        }

        // 这里可以添加删除任务的逻辑
        return $this->success([], '任务删除成功');
    }

    /**
     * 完成任务
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete($id)
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $user = auth()->user();
            // 检查任务是否属于当前用户
            if ($task->user_id !== $user->id) {
                throw new \Exception('无权完成此任务');
            }
            $task->status = 'completed';
            $task->completed_at = now();
            $task->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('任务完成失败: ' . $e->getMessage(), 422);
        }

        return $this->success([], '任务完成成功');
    }

    /**
     * 恢复任务
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $user = auth()->user();
            // 检查任务是否属于当前用户
            if ($task->user_id !== $user->id) {
                throw new \Exception('无权回滚此任务');
            }
            $task->status = 'pending';
            $task->completed_at = null;
            $task->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('任务回滚失败: ' . $e->getMessage(), 422);
        }

        return $this->success([], '任务回滚成功');
    }

}
