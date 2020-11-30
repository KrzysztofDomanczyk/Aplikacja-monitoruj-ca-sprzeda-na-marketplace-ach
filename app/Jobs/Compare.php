<?php

namespace App\Jobs;

use App\Libraries\Comparison\Comparison;
use App\Libraries\Comparison\MarketplaceComparison\AllegroComparison;
use App\Libraries\Comparison\MarketplaceComparison\EbayComparison;
use App\Libraries\Comparison\MarketplaceComparison\IaiComparison;
use App\Libraries\Raport\Raport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;

class Compare extends BaseJob implements ShouldQueue
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
        $iaiCompare = new IaiComparison;
        $iaiCompare->compare();

        $allegroCompare = new AllegroComparison;
        $allegroCompare->compare();

        $ebayCompare = new EbayComparison;
        $ebayCompare->compare();

        $raport = new Raport($iaiCompare->getSymbolMarketplace());
        $raport->addItem($iaiCompare->getResults())
            ->addItem($allegroCompare->getResults())
            ->addItem($ebayCompare->getResults());

            $raport->sendEmailWithRaport();
    }
}
