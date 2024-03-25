<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserStatus;
use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $user = $request->user();
        $posts = Post::postsForTimeline($userId)
            ->select('posts.*')
            ->leftJoin('followers AS f', function ($join) use ($userId) {
                $join->on('f.user_id', '=', 'posts.user_id')
                    ->where('f.follower_id', '=', $userId);
            })
            ->leftJoin('group_users AS gu', function ($join) use ($userId) {
                $join->on('gu.group_id', '=', 'posts.group_id')
                    ->where('gu.user_id', '=', $userId)
                    ->where('gu.status', GroupUserStatus::APPROVED->value);
            })
            ->where(function (Builder $query) use ($userId) {
                $query->whereNotNull('f.follower_id')
                    ->orWhereNotNull('gu.group_id')
                   // ->orWhere('posts.user_id', '=', $userId)
                ;
            })
            ->whereNot('posts.user_id', '=', $userId)
            ->paginate(10);

        $posts = PostResource::collection($posts);
        if ($request->wantsJson()) {
            return $posts;
        }

        $groups = Group::query()
            ->with('currentUserGroup')
            ->select(['groups.*'])
            ->join('group_users AS gu', 'gu.group_id', 'groups.id')
            ->where('gu.user_id', Auth::id())
            ->orderBy('gu.role')
            ->orderBy('name', 'desc')
            ->get();

        return Inertia::render('Home', [
            'posts' => $posts,
            'followings' => UserResource::collection($user->followings),
            'groups' => GroupResource::collection($groups)
        ]);
    }
}
