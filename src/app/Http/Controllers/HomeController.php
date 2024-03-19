<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();
        $posts = Post::query()
            ->withCount('reactions')
            ->withCount('comments')
            ->with([
                'comments' => function ($query) use ($userId) {
                    $query->withCount('reactions')
                        ->with([
                            'reactions' => function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            }]);
                },
                'reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
            ->latest()
            ->paginate(20);

        return Inertia::render('Home', [
            'posts' => PostResource::collection($posts)
        ]);
    }
}
