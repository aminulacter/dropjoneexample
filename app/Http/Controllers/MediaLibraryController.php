<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Add after namespace declaration
use App\Models\Media;


class MediaLibraryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function mediaLibrary(Request $request){
        $user_obj = auth()->user();
        $media_obj = $user_obj->media->all();
        return view('medialibrary', ['user_obj' => $user_obj, 'media_obj' => $media_obj ]);
    }

    public function alternate(Request $request)
    {
        $user_obj = auth()->user();
        $media_obj = $user_obj->media->all();
        return view('alternate', ['user_obj' => $user_obj, 'media_obj' => $media_obj ]);
    }
    public function try(Request $request)
    {
        $user_obj = auth()->user();

        return view('try1');
    }

}
