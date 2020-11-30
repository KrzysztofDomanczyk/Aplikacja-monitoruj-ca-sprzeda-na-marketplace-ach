<?php

namespace App\Jobs;

use App\Libraries\Allegro\AllegroProductsGetter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;

class DownloadAllegroProducts extends BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $allegroSaver = new AllegroProductsGetter('xxx');
        $allegroSaver->insertIntoDataBase();
        $download_id = $allegroSaver->getDownloadId();
 
        $allegroSaver = new AllegroProductsGetter('xx', $download_id);
        $allegroSaver->insertIntoDataBase();

        $allegroSaver = new AllegroProductsGetter('xx', $download_id);
        $allegroSaver->insertIntoDataBase();
    }
  
}
