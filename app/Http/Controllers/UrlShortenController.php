<?php

namespace App\Http\Controllers;

use App\Models\UrlShorten;
use Illuminate\Http\Request;

class UrlShortenController extends Controller
{
    //
    public function store( Request $request )
    {
        try {
            if (auth()->user()->id){
               $longUrl = $request->get('url');
               $newGeneratedUrl = $request->get('shortlink');

               if($longUrl != '' || $newGeneratedUrl != ''){
                   $url = UrlShorten::where('old_url', $longUrl)->get(['id', 'generated_url'])->toArray();

                   if(!empty($url)){
                       return $url[0]['generated_url'];
                   }

                   $saveUrl = new UrlShorten();
                   $saveUrl->old_url = $longUrl;
                   $saveUrl->generated_url = $newGeneratedUrl;
                   $saveUrl->user_id = auth()->user()->id;

                   if($saveUrl->save()){
                       return $saveUrl->generated_url;
                   }

               }

            }

        } catch (\Exception $e){
            dd($e);
        }

    }

    public function handle(Request $request, $url)
    {
        try {
            $the_url = $_SERVER['REQUEST_URI'];

            if($the_url == ''){
                return abort(404);
            }

            $url = UrlShorten::where('generated_url', 'like', '%'.$the_url.'%')->get('old_url');

            if($url == '' || count($url) == 0 ){
                return abort(404);
            }

            return redirect($url[0]['old_url']);


        } catch (\Exception $e) {
            dd($e);
        }
    }
}
