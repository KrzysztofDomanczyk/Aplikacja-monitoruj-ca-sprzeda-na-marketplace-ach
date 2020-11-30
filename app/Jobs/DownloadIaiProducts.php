<?php

namespace App\Jobs;

use App\Libraries\IAI\ProductsIaiApi;
use App\Libraries\IAI\ProductsIaiGetter;
use App\ProductMarketplace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;

class DownloadIaiProducts extends BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
   

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productsGetter = new ProductsIaiGetter;
        $productsGetter->insertIntoDataBase();
    }
}
