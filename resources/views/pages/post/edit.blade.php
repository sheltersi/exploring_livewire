<?php

use App\Models\Post;
use Illuminate\Support\Str;

new class extends Component
{
    public Post $post;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public string $category = '';

    public string $featured_image = '';

    public string $status = 'draft';

    public string $meta_title = '';

    public string $meta_description = '';

    public function mount(Post $post)
    {
        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->category = $post->category ?? '';
        $this->featured_image = $post->featured_image ?? '';
        $this->status = $post->status;
        $this->meta_title = $post->meta_title ?? '';
        $this->meta_description = $post->meta_description ?? '';
    }

    protected function rules()
    {
        return [
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug,'.$this->post->id,
            'excerpt' => 'nullable|max:500',
            'content' => 'required',
            'category' => 'nullable|max:100',
            'featured_image' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published',
            'meta_title' => 'nullable|max:100',
            'meta_description' => 'nullable|max:160',
        ];
    }

    public function updatedTitle($value)
    {
        if ($this->slug === Str::slug($this->post->title)) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->status === 'published' && ! $this->post->published_at) {
            $validated['published_at'] = now();
        }

        $this->post->update($validated);

        session()->flash('message', 'Post updated successfully.');

        return redirect()->route('dashboard');
    }
};

?>

<div class="max-w-4xl mx-auto py-10">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Edit Post</h2>
                    <p class="mt-1 text-sm text-gray-500">Update your blog post details.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mx-6 mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-700">{{ session('message') }}</p>
            </div>
        @endif

        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" id="title" wire:model.live.debounce.300ms="title" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('title') border-red-500 @enderror"
                            placeholder="Enter post title">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                        <input type="text" id="slug" wire:model="slug"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('slug') border-red-500 @enderror"
                            placeholder="post-url-slug">
                        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea id="excerpt" wire:model="excerpt" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none @error('excerpt') border-red-500 @enderror"
                            placeholder="A short summary of your post (optional)"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Max 500 characters</p>
                        @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                        <textarea id="content" wire:model="content" rows="12"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none font-mono text-sm @error('content') border-red-500 @enderror"
                            placeholder="Write your post content here..."></textarea>
                        @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-5 space-y-5">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Publishing</h3>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" wire:model="status"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white @error('status') border-red-500 @enderror">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input type="text" id="category" wire:model="category"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('category') border-red-500 @enderror"
                                placeholder="e.g. Technology">
                        </div>

                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                            <input type="url" id="featured_image" wire:model="featured_image"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('featured_image') border-red-500 @enderror"
                                placeholder="https://example.com/image.jpg">
                            @error('featured_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-5 space-y-5">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">SEO</h3>
                        
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text" id="meta_title" wire:model="meta_title"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('meta_title') border-red-500 @enderror"
                                placeholder="SEO title (max 100 chars)">
                            <p class="mt-1 text-xs text-gray-500">Max 100 characters</p>
                        </div>

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea id="meta_description" wire:model="meta_description" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none @error('meta_description') border-red-500 @enderror"
                                placeholder="SEO description for search engines"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Max 160 characters</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('dashboard') }}"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors flex items-center gap-2">
                    <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Update Post</span>
                    <span wire:loading wire:target="save">Updating...</span>
                </button>
            </div>
        </form>
    </div>
</div>
