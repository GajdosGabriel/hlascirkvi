<?php


namespace App\Inspect;


use App\Post;

class CleanerParagraphs
{

    // Čistí celý odstavec od frázy

    protected $post;

    // Text po týchto frázach bude odstránenýz post->body.
    protected $phrases = [
        "Podporte náš projekt: https://www.logos.tv/pomoc",
        "Kliknite a prihláste sa na odber"
    ];

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function cleanBody()
    {
        foreach ($this->phrases as $value) {
            if (strpos($this->post->body, $value)) {
                $cleanBody = substr($this->post->body, 0, strpos($this->post->body, $value));

                $this->post->update([
                    'body' => $cleanBody
                ]);
            }
        }
    }

}
