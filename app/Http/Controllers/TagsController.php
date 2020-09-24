<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Post;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    public function explodeWords() {
        $posts = Post::where('id', '>', 10)->get();

        foreach($posts as $post)
        {



        $words = explode(" ", $post->body);

        foreach($words as $text) {

            // replace non letter or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);

//            // transliterate
//            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
//
//            // remove interpunkcie characters
//            $text = preg_replace('~[^-\w]+~', '', $text);

            // trim
            $text = trim($text, '-');

            // remove duplicate -
            $text = preg_replace('~-+~', '-', $text);

            // lowercase
            $text = strtolower($text);

//            if (empty($text)) {
//                return 'n-a';
//            }



//           $cleanDot = rtrim($word, '.');
//           $cleanDouble = rtrim( $cleanDot, ':');
//           $cleanzatvorka = rtrim( $cleanDouble, ')');
//           $cleanWord = rtrim( $cleanzatvorka, '(');
//          $slug =  strtolower($cleanWord);

            if( Tag::whereSlug(trim($text))->exists() ) continue;

                Tag::create([
                    'title' => $text,
                    'slug' => trim($text)
                ]);
            }
        }
        return 'done';


    }

}
