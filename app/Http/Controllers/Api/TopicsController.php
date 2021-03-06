<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TopicRequest;
use App\Http\Requests\Request;
use App\Models\Topic;
use App\Models\User;
use App\Transformers\TopicTransformer;

class TopicsController extends Controller
{
    // 话题列表
    public function index(Request $request, Topic $topic)
    {
        $query = $topic->query();

        // 如果指定了分类id
        if($categroy_id = $request->categroy_id){
            $query->where('category_id', $categroy_id);
        }

        switch ($request->order) {
            case 'desc':
                $query->orderBy('id', 'desc');
                break;
            default:
                $query->orderBy('id', 'asc');
                break;
        }
        // 获取分页后的数据
        $topics = $query->paginate(5);
        return $this->response->paginator($topics, new TopicTransformer());
    }

    // 某用户话题列表
    public function userIndex(User $user)
    {
        $topics =  $user->topics()->orderBy('id', 'desc')->paginate(2);
        return $this->response->paginator($topics, new TopicTransformer());
    }

    public function show(Topic $topic)
    {
        return $this->response->item($topic, new TopicTransformer());
    }


    // 创建话题
    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = $this->user()->id;
        $topic->save();

        return $this->response->item($topic, new TopicTransformer())->setStatusCode(201);
    }

    // 更新话题
    public function update(TopicRequest $request, Topic $topic)
    {

        // 安全策略类 检测： 修改的话题是否是当前登录用户的
        $this->authorize('update', $topic);

        $topic->update($request->all());
        return $this->response->item($topic, new TopicTransformer());
    }

    // 删除话题
    public function destroy(Topic $topic)
    {
        // 安全策略类 检测： 修改的话题是否是当前登录用户的
        $this->authorize('update', $topic);

        $topic->delete();
        return $this->response->noContent();
    }
}
