<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private function findPostBySlug($slug) {
        $post = Post::where("slug", $slug)->first();
        if(!$post) {
            abort(404);
        }
        return $post;
    }

    private function generateSlug($text) {
        $counter = 0;
        do{
            $slug = Str::slug($text);
            if($counter > 0) {
                $slug .= "-" . $counter;
            }
            $slug_esiste = Post::where("slug", $slug)->first();
            if($slug_esiste) {
                $counter++;
            }else {
                return $slug;
            }
        }while($slug_esiste);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::orderBy("created_at", "desc")->get();
        return view("admin.posts.index", compact("posts"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("admin.posts.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "title" => "required | min:10",
            "content" => "required | min:10"
        ]);

        $post = new Post();
        $post->fill($validated);

        $post->slug = $this->generateSlug($post->title);

        $post->save();

        return redirect()->route("admin.posts.show", $post->slug);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $post = $this->findPostBySlug($slug);
        return view("admin.posts.show", compact("post"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $post = $this->findPostBySlug($slug);
        return view("admin.posts.edit", compact("post"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validated = $request->validate([
            "title" => "required | min:10",
            "content" => "required | min:10"
        ]);
        $post = $this->findPostBySlug($slug);
        if($validated["title"] !== $post->title) {
            $post->slug = $this->generateSlug($validated["title"]);
        }
        $post->update($validated);
        return redirect()->route("admin.posts.show", $post->slug);
    }

    /** 
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $post = $this->findPostBySlug($slug);
        $post->delete();
        return redirect()->route("admin.posts.index");
    }
}
