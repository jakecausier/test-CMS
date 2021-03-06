<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Auth;

class PostsController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with(['content', 'author'])
                     ->orderBy('id', 'desc')
                     ->paginate('5');
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Post $post)
    {
        $this->validatePost($request);

        $live = $request->live ? now()->toDateTimeString() : null;

        $post = $post->create([
            'name' => $request->name,
            'id_author' => Auth::id(),
            'live_at' => $live,
        ]);

        if ($request->has('content')) {
            $post->syncContent($request->content);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $this->validatePost($request);

        $live = $request->live ? now()->toDateTimeString() : null;

        $post->update([
            'name' => $request->name,
            'live_at' => $live,
        ]);

        if ($request->has('content')) {
            $post->syncContent($request->content);
        }

        return redirect()->route('posts.show', $post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->destroy();
    }

    /**
     * Ensure the post is validated
     *
     * @var Request $request
     * @return mixed
     */
    protected function validatePost(Request $request) {
        return $this->validate($request, [
                    'name' => 'required',
                    'content' => 'array',
                ], [
                    'name.required' => 'The post title is required.',
                    'content.array' => 'The content is in the wrong format (found: array).'
                ]);
    }
}
