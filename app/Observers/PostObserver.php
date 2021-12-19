<?php

namespace App\Observers;


use App\Inspect\CleanBodyText;
use App\Inspect\CleanerParagraphs;
use App\Models\Post;




class PostObserver
{

    /**
     * Handle the post "created" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function created(Post $post)
    {
        session()->flash('flash', 'Príspevok '. $post->title .', bol uložený!');

        // Clean spam in body post
        (new CleanBodyText($post))->handle();

        // Add name to title, Only some of the canals
        if( $post->organization->mod_title != '' ) {
            $post->update([
                'title' => $post->organization->mod_title . ' | ' . $post->title
            ]);
        }
    }


    public function creating(Post $post)
    {

    }

    /**
     * Handle the post "updated" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        // Clean spam in body post celý odstavec
        (new CleanerParagraphs($post))->cleanBody();
    }

    /**
     * Handle the post "deleted" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function deleted(Post $post)
    {
        $post->images()->delete();
    }

    /**
     * Handle the post "restored" event.
     *
     * @param \App\Models\Post  $post
     * @return void
     */
    public function restored(Post $post)
    {
        //
    }

    /**
     * Handle the post "force deleted" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function forceDeleted(Post $post)
    {
        $post->destroyImages();
    }


}
