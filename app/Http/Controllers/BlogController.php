<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        return view('public.blog.index', [
            'posts' => BlogPost::published()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return view('public.blog.show', [
            'post' => $post,
            'related' => BlogPost::published()
                ->where('id', '!=', $post->id)
                ->where('category', $post->category)
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
