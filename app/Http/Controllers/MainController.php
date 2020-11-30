<?php

namespace App\Http\Controllers;

use App\Jobs\Compare;
use App\Jobs\DownloadAllegroProducts;
use App\Jobs\DownloadEbayProducts;
use App\Jobs\DownloadIaiProducts;
use App\Jobs\DownloadSoftlabProducts;
use App\Libraries\Allegro\AllegroOffers;
use App\Libraries\Allegro\AllegroProductsGetter;
use App\Libraries\Comparison\Comparison;
use App\Libraries\Comparison\MarketplaceComparison\AllegroComparison;
use App\Libraries\Comparison\MarketplaceComparison\EbayComparison;
use App\Libraries\IAI\ProductsIaI;
use App\Libraries\IAI\ProductsIaiGetter;
use App\Libraries\Comparison\MarketplaceComparison\IaiComparison;
use App\Libraries\Ebay\EbayAPI;
use App\Libraries\Ebay\ProductEbayGetter;
use App\Libraries\Raport\Raport;
use App\Libraries\Softlab\ProductsSoftlabGetter;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MainController extends Controller
{
    public function prepareCompareView()
    {
        echo '<strong>Pamiętaj o wrzuceniu aktualnego raportu z SL na FTP</strong><br><br>';
        echo '<a href="/startCompare">Utwórz raport</a>';
    }

    public function startCompare()
    {
     
        if (!$this->osQueueProcessIsRunning()) {
            DownloadIaiProducts::withChain([
                new DownloadAllegroProducts(),
                new DownloadSoftlabProducts(),
                new DownloadEbayProducts(),
                new Compare,
            ])->dispatch();
            exec('/opt/alt/php74/usr/bin/php /home/login888/domains/monitor-dostepnosci.hekko24.pl/public_html/artisan queue:work --stop-when-empty --timeout=6200 --memory=512 > /dev/null 2>/dev/null &', $return);
            dump("Rozpoczęto proces");
            exec('ps aux -ww', $process_status);
            dump($process_status);
        } else {
            dump("Proces wykonujący zadania z kolejki już działa.");
            dump('Jeżeli chcesz zastopować, kliknij przycisk poniżej');
            echo '<a href="/stop-queue">stop</a>';
        }
    }

    protected function osQueueProcessIsRunning()
    {
        $needle = 'queue:work';
        exec('ps aux -ww', $process_status);
        $result = array_filter($process_status, function ($var) use ($needle) {
            return strpos($var, $needle);
        });

        if (!empty($result)) {
            dump($result);
            return $result;
        }
        return false;
    }

    public function stopOsQueueProcess()
    {
        $pidsProcess = $this->osQueueProcessIsRunning();
        if ($pidsProcess) {
            foreach ($pidsProcess as $proces) {
                $proces = explode(' ', $proces);
                $cmd = "kill -9 $proces[1]";
                exec($cmd, $res);
            }
            DB::table('jobs')->truncate();
            dump("Przerwano proces");
        } else {
            dump("Proces nie działa");
        }
    }

    public function showOsQueueProcess()
    {
        $pidsProcess = $this->osQueueProcessIsRunning();
    }

    public function test()
    {

        $product = new ProductsIaiGetter;
        $product->insertIntoDataBase();

        $iaiCompare = new IaiComparison;
        $iaiCompare->compare();

    }

    

    public function getAccesToken()
    {
        $allegro = new AllegroOffers('xxx');
        $allegro->getAccessToken();
        echo '<a href="' . $allegro->getVerificationUriComplete() . '">xx </a>';
        $allegro = new AllegroOffers('xxx');
        $allegro->getAccessToken();
        echo '<a href="' . $allegro->getVerificationUriComplete() . '">xxxx </a>';
        $allegro = new AllegroOffers('xx');
        $allegro->getAccessToken();
        echo '<a href="' . $allegro->getVerificationUriComplete() . '">xxxx </a>';
    }

    public function checkUserAcceptance()
    {
       ///
    }

    public function refreshTokens()
    {
        ///
    }

}
