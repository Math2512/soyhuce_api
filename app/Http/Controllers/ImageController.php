<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Image as AppImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $images = AppImage::orderBy('id', 'desc')->get();
        return $images;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors'=> 'Le nom ne doit pas être vide !']);
        }

        $now = Carbon::now()->timestamp;

        $image_64 = $request->input('picture');
        
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
        $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
        $image = str_replace($replace, '', $image_64); 
        $image = str_replace(' ', '+', $image); 
        $imageName = $request->input('name').'_'.$now.'.'.$extension;
        
        Storage::disk('public')->put($imageName, base64_decode($image));
        
        AppImage::create([
            'name'=>$imageName,
            'url'=> Storage::url($imageName)
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        $image = AppImage::find($id);
        $file = Storage::get('public/'.$image->name);

        return 'data:image/png;base64,'.base64_encode($file);

    }

}
