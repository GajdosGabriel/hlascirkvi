<?php

namespace App\Http\Controllers;

use App\Messenger;
use App\Organization;
use App\Post;
use App\Prayer;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Services\Buffer;
use App\Services\Extractor\ExtractZdruzenieMedaily;
use App\Services\Extractor\ExtractMojaKomunita;
use App\Services\Extractor\ExtractTkkbs;
use App\Event;
use App\User;
use Carbon\Carbon;
use Config;
use DB;
use http\Message;
use Illuminate\Http\Request;
use Auth;

class TestController extends Controller
{


    public function test()
    {
//        $users = User::all();
//
//        foreach ($users as $user){
//            $user->update([
//                'api_token' => bin2hex(openssl_random_pseudo_bytes(30))
//                ]);
//        }

//        bin2hex(openssl_random_pseudo_bytes(16))
//        $message = Messenger::whereId(11)->first();

//        dd($message->requestedUser->fullname);

//        (new Buffer())->handler();
//       $prayers =  Prayer::all();
//
//       foreach ($prayers as $prayer){
//           $prayer->update([
//               'created_at' =>  Carbon::createFromFormat('Y-m-d H:i:s',$prayer->created_at)->addMinute(rand(3,49))->toDateTimeString()
//           ]);
//       }
//       $posts = (new ExtractMojaKomunita())->parseListUrl();

    }



    public function cleanTitle()
    {

        $posts = \DB::table('posts')->get();
        $count = 0;
        $frase = "&amp;";

        foreach ($posts as $post) {

            if (strpos($post->title, $frase)) {

                $post = \DB::table('posts')->whereId($post->id)->first();

               $xxx = \DB::table('posts')->whereId($post->id)->update([
                    'title' => cleanTitle($post->title)
                ]);
                $count++;
            }

        }
        echo $count;
    }



    public function textClean()
    {
        $posts = \DB::table('posts')->get();
        $count = 0;
        $frase = "================";

        foreach ($posts as $post) {

            if (strpos($post->body, $frase)) {

                $cleanBody = substr($post->body, 0, strpos($post->body, $frase));

                $post = \DB::table('posts')->whereId($post->id)->first();

                \DB::table('posts')->whereId($post->id)->update([
                    'body' => $cleanBody
                ]);
                $count++;
            }

        }

        echo $count;

    }

    public function greckyMagazin()
    {
        $posts = \DB::table('posts')->get();
        $count = 0;
        $frase = "Podporte náš projekt: https://www.logos.tv/pomoc";

        foreach ($posts as $post) {

            if (strpos($post->body, $frase)) {

                $cleanBody = substr($post->body, 0, strpos($post->body, $frase));
                $post = \DB::table('posts')->whereId($post->id)->first();

                \DB::table('posts')->whereId($post->id)->update([
                    'body' => $cleanBody
                ]);
                $count++;
            }

        }

        echo $count;

    }

    public function paragraphGenerator($text, $length = 400, $maxLength = 550)
    {

        //Text length
        $textLength = strlen($text);

        //initialize empty array to store split text
        $splitText = array();

        //return without breaking if text is already short
        if (!($textLength > $maxLength)) {
            $splitText[] = $text;
            return $splitText;
        }

        //Guess sentence completion
        $needle = '.';

        /*iterate over $text length
          as substr_replace deleting it*/
        while (strlen($text) > $length) {

            $end = strpos($text, $needle, $length);

            if ($end === false) {

                //Returns FALSE if the needle (in this case ".") was not found.
                $splitText[] = substr($text, 0);
                $text = '';
                break;

            }

            $end++;
            $splitText[] = substr($text, 0, $end);
            $text = substr_replace($text, '', 0, $end);

        }

        if ($text) {
            $splitText[] = substr($text, 0);
        }

//        foreach ($splitText as $text) {
//            $text->update([
//                'body' => $post->body . '<p>' . $text . '</p>'
//            ]);
//
//        }


        return $splitText;

    }




//   public function test() {
//
//       $organizations = \DB::table('views')
////           ->take(1000)
//               ->whereMonth('viewed_at', date('m'))
//
//           ->whereMonth('viewed_at', date('m'))
//           ->join('posts', 'posts.id', '=', 'views.viewable_id')
//           ->select('viewable_id', DB::raw('count(*) as total_view , posts.title as post_title, posts.id as post_id'))
//           ->groupBy('viewable_id', 'post_title', 'post_id')
//           ->orderBy('total_view', 'desc')
//           ->get();
//
////       ->groupBy('viewable_id', 'desc');
////           ->groupBy('visitor', 'desc')->count();
//
//
//
//
//      dd($organizations);
//
//   }


//   public function test() {
//
//      $districts = DB::table('events')
//          ->where('end_at', '>', Carbon::now())->wherePublished(1)
//          ->where('deleted_at', null)
//          ->join('villages', 'events.village_id', '=', 'villages.id')
//          ->join('districts','districts.id','=','villages.district_id')
//          ->select('districts.name', 'districts.id' )
//          ->get()
//       ->groupBy('name');
//
//      dd($districts);
//
//   }


//   public function test()
//   {
//      $posts = Post::take(200)->get();
//      foreach($posts as $post) {
//
//         // Remove spacial characters
//         $cleanTitle = str_replace([':', ',', '?','.'], ' ' , $post->title);
//
//         // if title is too long
////         if(strlen($post->title) > 50) continue;
//
//         // Only org. who is person
//         if($post->organization->person !== 1) continue;
//
//         // I. If full name is not already in the post's title
//         if(stripos($cleanTitle, $post->organization->title)) continue;
//
//         // II. If post title contain one of the fullname
//         $orgName = explode(" ", $post->organization->title);
//         $postTitle = explode(" ", $cleanTitle);
//
//         // Find only match
//         $arracount = array_diff($orgName,$postTitle);
//
//         // If same match first name or last name
//         if( count($arracount) == 0 || count($arracount) == 1) continue;
//
//
//
//         print_r(count($arracount).'/'. $post->title .' '. strlen($post->title) . ' '.$post->organization->title .'<br>') ;
//      }


    // Check if name not exist in title


//   }


//   public function test() {
//
//      $even = new ExtractTkkbs();
//
//      $getEvent = Event::whereId(1)->get();
//
//
//     $evenx = $even->parseEvent('https://www.tkkbs.sk/view.php?cisloclanku=20200107034', $getEvent);
//
//
////      if (getimagesize('https://www.tkkbs.sk/galeria/images/1235725406/1499930279.jpg') !== false) {
////         echo 'exist img';
////      }
//   }
}
