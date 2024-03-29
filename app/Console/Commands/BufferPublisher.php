<?php

namespace App\Console\Commands;

use App\Services\Buffer;
use Illuminate\Console\Command;


class BufferPublisher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PublisherBufferVideo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish posts twice time per day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Buffer $buffer)
    {
        $buffer->handler();
    }
}
