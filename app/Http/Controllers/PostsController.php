<?php

namespace App\Http\Controllers;
use App\Post;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use DB;

class PostsController extends Controller
{
   
   public function __construct(){
       $this->middleware('auth');
   }

   public function index(){
       $users = auth()->user()->following()->pluck('profiles.user_id');
       $posts = Post::whereIn('user_id', $users)->latest()->paginate(5);
       
        return view('posts.index', compact('posts'));
   }
   
    public function create() {
        return view('posts.create');
    }

    public function store() {
        $data = request()->validate([
            'caption' => 'required',
            'image' => ['required', 'image'],
        ]);

       $imagePath = request('image')->store('uploads', 'public');

       $image = Image::make(public_path("storage/{$imagePath}"))->fit(1200, 1200);
       $image->save();
       
       auth()->user()->posts()->create([
           'caption' => $data['caption'],
           'image' => $imagePath,
       ]);
    
        return redirect('/profile/'. auth()->user()->id);
    }

    public function show($id) {

        $post = DB::table('users')
        ->join('posts', 'users.id', '=', 'posts.user_id')
        ->join('profiles', 'users.id', '=', 'profiles.user_id')
        ->select('posts.image as post_image', 'posts.caption as caption','users.username as username','profiles.image as profile_image','users.id as user_id','posts.id')
       ->where('posts.id','=',$id)
        ->first();
       
        return view('posts.show',compact('post'));
        //dd($post);
    }
}
