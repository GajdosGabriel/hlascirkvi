<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Services\Extractor\ExtractYoutubeComment;

class CommentsExtractorTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        // $extractor = new ExtractYoutubeComment();
        // $this->assertEquals('jeden', $extractor->skuska('jeden'));
     
        $response = $this->get('/');

        // $response->assertStatus(200);
    }
}
