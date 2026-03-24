<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);

        return view('dashboard', compact('posts'));
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);

        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('pages/post/edit', compact('post'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug,'.$id,
            'excerpt' => 'nullable|max:500',
            'content' => 'required',
            'category' => 'nullable|max:100',
            'featured_image' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published',
            'meta_title' => 'nullable|max:100',
            'meta_description' => 'nullable|max:160',
        ]);

        if ($request->status === 'published' && ! $post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('dashboard')->with('message', 'Post updated successfully.');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return redirect()->route('dashboard')->with('message', 'Post deleted successfully.');
    }
}
