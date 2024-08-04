<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Config;
use http\Message;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\Event;
use App\Models\Prayer;
use App\Models\Comment;
use App\Services\Buffer;
use App\Mail\EventInvite;
use App\Models\FirstName;
use App\Models\Messenger;
use App\Mail\PostNewsletter;
use App\Models\Organization;
use App\Services\Newsletter;
use Illuminate\Http\Request;
use App\Services\VideoUpload;
use App\Models\EventSubscribe;
use App\Events\User\NotifyBell;
use App\Services\VideoUploadFilter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\Extractor\ExtractEcav;
use App\Services\Extractor\ExtractTkkbs;
use App\Services\PostService\PostService;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Extractor\ExtractVyveska;
use App\Notifications\User\NewRegistration;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Subscribe\NewSubscribe;
use App\Repositories\Contracts\UserRepository;
use App\Services\Extractor\ExtractMojaKomunita;
use App\Repositories\Contracts\PrayerRepository;
use App\Services\Extractor\ExtractZdruzenieMedaily;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentPrayerRepository;
use App\Services\Extractor\ExtractSluzobniceDuchaSvateho;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Services\Prayers\UnansweredPrayers;

class TestController extends Controller
{

    public function __construct(UserRepository $user)
    {
        $this->middleware('guest');
        $this->user = $user;
    }

    public function index(PrayerRepository $prayers)
    {
        $event = Event::find(4236);

          $event = (new ExtractTkkbs())->parseEvent('https://www.tkkbs.sk/view.php?cisloclanku=20230213030', $event);

        // $xx =   new ExtractEcav();
        dd($event);


// dd(date("Y"));

               $event = Event::find(4236);
        // $href = 'http://www.vyveska.sk/pozvanka-na-prazdniny-u-minoritov.html';
         $href = 'https://www.ecav.sk/aktuality/pozvanky/ciel-na-obzore-modlitebny-tyzden';
        //  $href = 'https://www.ecav.sk/aktuality/pozvanky/kurz-zaklady-vyucovania-deti-a-dorastu';
        // $href = 'https://www.vyveska.sk/mozaika-lasky.html';
        // $event = Event::first();


        //    $events = (new ExtractVyveska())->parseListUrl();
        $event = (new ExtractEcav())->parseEvent($href, $event);
        // $event = (new ExtractTkkbs())->parseEvent($href, $event);
        // $events = (new ExtractVyveska())->parseEvent($href, $event);

        dd();


  


        $event = Event::find(3677);
        $subscribe = EventSubscribe::first();

     
        
        Notification::send( [$subscribe->organization->user, $event->organization->user] , new NewSubscribe($subscribe));
        dd($subscribe->event->organization->user);

        
        return new EventInvite($event);
        $user = User::first();
        //  Send notification new User registration to admin
        Notification::send(User::role('admin')->get(), new NewRegistration($user));


        dd(Post::whereVideoAvailable(0)->get()->count());



      


        // $href = 'http://www.vyveska.sk/pozvanka-na-prazdniny-u-minoritov.html';
        $href = 'https://www.tkkbs.sk/view.php?cisloclanku=20211202023';
        $event = Event::first();
        // dd($event);

        //    $events = (new ExtractVyveska())->parseListUrl();
        $events = (new ExtractTkkbs())->parseListUrl();

        dd($events);



        $name = FirstName::whereId(1)->first();
        dd(FirstName::orderBy('count', 'asc')->whereId(rand(1, 1000))->first()->name);

        $organizations = (new ExtractSluzobniceDuchaSvateho())->parseListUrl();
        dd($organizations);




        //         $this->organizations = new EloquentOrganizationRepository;

        // dd($this->organizations->getYoutubeVideos());

        $organization = Organization::find(257);


        $filter = new VideoUploadFilter($organization, 'Bohoslužba Banská Bystrica');


        if ($filter->wordsChecker()) {
            echo "som tu";
        };



        dd($filter->wordsChecker());

        // $comments = \Youtube::getCommentThreadsByVideoId($post->video_id);
        // $video = \Youtube::getVideoInfo('zOpsGQ_2MbY'); // Nie
        $video = \Youtube::getVideoInfo('lSKaDNTAN2Y'); // Ano




        // dd($comments);
        dd($video);




        $text = "<a href=cc";

        if (!str_contains($text, '<a href=')) {
            echo 'Elephants are intelligent.';
        }

        dd(77);

        // $comments = \Youtube::getCommentThreadsByVideoId('984SFBcc-eQ');
        // $comments = \Youtube::getVideoInfo('1nFJV_NU8LQ');

        // dd($comments);

        $posts =  (new EloquentPostRepository)->postsByUpdater(15)->where('video_id', '<>', null)
            // ->where('created_at', '<=', Carbon::now()->subWeek(1) )
            ->whereVideoDuration(null)
            ->take(10)
            ->latest()->get();

        dd($posts);

        foreach ($posts as $post) {

            // Chceck if video is commentAble
            $video = \Youtube::getVideoInfo($post->video_id);

            // If video dont exist any more
            if (!$video) {
                $post->update(['video_available' => $video]);
                continue;
            };

            // If video is dont available // private
            if (!$video->status->embeddable) {
                $post->update(['video_available' => false]);
                continue;
            };

            // Put video duration
            if (!$post->duration) {
                $post->update(['video_duration' => $video->contentDetails->duration]);
                continue;
            };

            // If video doest have any comments
            if (!$video->statistics->commentCount > 0) {
                continue;
            };



            // dd($comments->statistics->commentCount);

            // dd($post->video_id);



            foreach ($comments as $comment) {
                $bodyComment = $comment->snippet->topLevelComment->snippet->textDisplay;
                if (strlen($bodyComment) > 10) {

                    $post->comments()->create([
                        'organization_id' => 100,
                        'body' => $comment->snippet->topLevelComment->snippet->textDisplay,
                        'user_avatar' => $comment->snippet->topLevelComment->snippet->authorProfileImageUrl,
                        'user_name' => $comment->snippet->topLevelComment->snippet->authorDisplayName,
                        // 'created_at' => \Carbon\Carbon::parse($comment->snippet->topLevelComment->snippet->publishedAt)->format('Y-m-d h:i:s'),
                    ]);
                }
            }
        }
        dd('Koniec');
        dd($comments[0]->snippet->topLevelComment->snippet);


        dd('Koniec');

        return;

        dd($comments->snippet);
        dd($video->snippet->description);
        dd($video->snippet->title);

        // User::create([
        //     'first_name' => 'vabriel',
        // 'last_name' => 'Gajdoš',
        // 'email' => 'dfddsdf@email.comdfds',
        // 'password' => Hash::make('password')
        // ]);
        //    $this->user->createUserRegisterForm([
        //     'first_name' => 'Gabriel',
        //     'last_name' => 'Gajdoš',
        //     'email' => 'dfasdf@email.com',
        //     'password' => Hash::make('password')
        //    ]);



        // dd('nie je');



        $posts =
            \DB::table('posts')
            ->join('post_updater', function ($join) {
                $join->on('posts.id', '=', 'post_updater.post_id')
                    ->whereUpdaterId('16');
            })
            ->where('created_at', '>',  Carbon::now()->subDays(20))->get();
        dd($posts);



        $vysledok =  \DB::table('organizations')
            ->select([
                \DB::raw('count(*) as user_posts'),
                \DB::raw('DATE(created_at) as den')
            ])
            ->groupBy('den')
            ->orderBy('user_posts', 'asc')
            ->get();

        dd($vysledok);










        $url = "http://www.youtube.com/watch?v=C4kxS1ksqtw&feature=relate";
        parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
        echo $my_array_of_vars['v'];

        //    $url = "http://www.youtube.com/watch?v=C4kxS1ksqtw&feature=related";
        //     $parse = parse_url($url, PHP_URL_QUERY);
        //     parse_str($parse, $output);
        //     echo $output['watch'];

        // $posts= Post::whereHas('updaters', function(){

        // })->whereNull('published')->where('youtube_blocked', 0)->get();

        // $sum = 0;

        // foreach($posts as $post){

        //     $post->update([
        //         'published' => $post->created_at
        //     ]);
        //     $sum ++;
        // };

        // print_r($sum);

        //     $class = "App\Models\Post";
        //     $class = new $class;

        //     dd($class);


        //    $posts = (new EloquentPostRepository())->postsByUpdater(15)
        //             ->where('title', 'like', '%duch sv%')
        //             ->OrWhere('title', 'like', '%ducha sv%')
        //             ->orWhere('title', 'like', '%turic%')
        //             ->whereNotIn('id', [682]) // Zdvojené video
        //             ->get();


        //             dd($posts);

        // $user = User::first();
        // event(new NotifyBell($user));



        // $prayer = Prayer::whereId(367)->first();

        //    dd( $prayer );

        // foreach($prayer->favorites as $favorite)
        // {
        //   echo ($favorite->user) . '</br>';
        // }

        // dd();
        // dd(  ( new Newsletter)->prayerFulfilledOrNotYet() );


        // $posts = (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get();
        // $events = (new EloquentEventRepository)->orderByStarting()->take(5)->get();
        // $prayers = Prayer::latest()->take(5)->get();

        // Mail::to(User::first())->send(new PostNewsletter($posts, $events, $prayers));

        //    return new PostNewsletter($posts, $events, $prayers);
    }


    public function test()
    {


        // (new ExtractTkkbs())->parseListUrl();
        // $event = Event::findOrFail(100);
        // (new ExtractTkkbs())->parseEvent( 'https://www.tkkbs.sk/view.php?cisloclanku=20210414030', $event);

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
