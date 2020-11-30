<?php
namespace App\Libraries\Raport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Raport
{
    private $itemsOfRaport = [];
    private $nameMarketplaces;
    
    public function __construct($name)
    {
        $this->nameMarketplaces = $name;
    }

    public function addItem($item)
    {
        $this->itemsOfRaport = array_merge($this->itemsOfRaport, $item);
        return $this;
    }

    public function getItemsOfRaport()
    {
        return $this->itemsOfRaport;
    }

    public function sendEmailWithRaport()
    {
        $filesNames = $this->saveCsvFileWithCompareTypes();
        Mail::send('raport', ['itemsOfRaport' => $this->itemsOfRaport], function ($message) use ($filesNames) {
            $message->from('braki@phu-szczepan.pl', 'Monitor dostępności');
            $message->subject('Monitor dostępności ' . Carbon::now()->format('Y-m-d H:i:s'));
            $message->to('it@phu-szczepan.pl');
            foreach ($filesNames as $fileName) {
                $message->attach($this->getFilePath($fileName));
            }
         });
    }


    public function saveHtmlFileRaport()
    {
        $raportHtml = view('raport')->with(["itemsOfRaport" => $this->itemsOfRaport])
        ->render();
        Storage::disk('public')->put(Carbon::now()->format('Y-m-d H:i:s') . ".html", $raportHtml);
    }

    public function saveCsvFileWithCompareTypes()
    {
        $availabilityComparisonFile = '';
        $availabilityComparisonFile .= PHP_EOL;
        $availabilityComparisonFile .= 'Towary, które powinny być w sprzedaży' . ';'  . PHP_EOL;
        $availabilityComparisonFile .= "Index" . ';' ."Nazwa towaru". ';' ."Stan". ';' . "iai" . ';' ."allegro" . ';' ."ebay" . PHP_EOL;

        $unavailabilityComparisonFile = '';
        $unavailabilityComparisonFile .= PHP_EOL;
        $unavailabilityComparisonFile .= 'Towary, które NIE powinny być w sprzedaży' . ';'  . PHP_EOL;
        $unavailabilityComparisonFile .= "Index" . ';' ."Stan". ';' ."Nazwa towaru na marketplace". ";". "UID" . ';' . "iai" . ';' ."allegro" . ';' ."ebay" . PHP_EOL;
       
        $priceComparisonFile = '';
        $priceComparisonFile .= PHP_EOL;
        $priceComparisonFile .= 'Towary, które mają niepoprawną cene' . ';'  . PHP_EOL;
        $priceComparisonFile .= "Index" . ';' ."Nazwa towaru na marketplace". ';' ."Cena towaru". ";". "Cena towaru na marketplace" . ';' . "UID" . ';' . "iai" . ';' ."allegro" . ';' ."ebay" . PHP_EOL;
        
        $visibilityComaprisonFile = '';
        $visibilityComaprisonFile .= PHP_EOL;
        $visibilityComaprisonFile .= 'Towary, które NIE powinny być widoczne na sklepie' . ';'  . PHP_EOL;
        $visibilityComaprisonFile .= "Index" . ';' ."Stan". ';' ."Nazwa towaru na marketplace". ";". "UID" . PHP_EOL;
        
        foreach ($this->itemsOfRaport as $item) {
            if (empty($item['Data'])) continue;
            switch ($item['TypeCompare']) {
                case 'availableProducts':
                    foreach ($item['Data'] as $data) {
                        $iai = $item['Marketplace'] == "iai" ? 1 : 0;
                        $allegro = $item['Marketplace'] == "allegro" ? 1 : 0;
                        $ebay = $item['Marketplace'] == "ebay" ? 1 : 0;
                        $availabilityComparisonFile .= $data['Index']. ";"
                            .  $data["Nazwa towaru"]. ";"
                            . $data["Stan"] . ";"
                            . $iai . ";"
                            . $allegro . ";"
                            . $ebay . ";"
                            . PHP_EOL;
                    }
                    break;
                case 'unAvailableProducts':
                    foreach ($item['Data'] as $data) {
                        $iai = $item['Marketplace'] == "iai" ? 1 : 0;
                        $allegro = $item['Marketplace'] == "allegro" ? 1 : 0;
                        $ebay = $item['Marketplace'] == "ebay" ? 1 : 0;
                        $unavailabilityComparisonFile .= $data['Index']. ";"
                            . $data["Stan"]. ";"
                            . $data["Nazwa towaru na marketplace"] . ";"
                            . $data["UID:"] . ";"
                            . $iai . ";"
                            . $allegro . ";"
                            . $ebay . ";"
                            . PHP_EOL;
                    }
                    break;
                case 'incorrectPrice':
                    foreach ($item['Data'] as $data) {
                        $iai = $item['Marketplace'] == "iai" ? 1 : 0;
                        $allegro = $item['Marketplace'] == "allegro" ? 1 : 0;
                        $ebay = $item['Marketplace'] == "ebay" ? 1 : 0;
                        $priceComparisonFile .= $data['Index']. ";"
                            . $data["Nazwa towaru na marketplace"] . ";"
                            . $data["Cena towaru"]. ";"
                            . $data["Cena towaru na marketplace"] . ";"
                            . $data["UID:"] . ";"
                            . $iai . ";"
                            . $allegro . ";"
                            . $ebay . ";"
                            . PHP_EOL;
                    }
                    break;
                case 'incorrectVisibilty':
                    foreach ($item['Data'] as $data) {
                   
                        $visibilityComaprisonFile .= $data['Index']. ";"
                            . $data["Stan"]. ";"
                            . $data["Nazwa towaru na marketplace"] . ";"
                            . $data["UID:"] . ";"
                            . PHP_EOL;
                    }
                    break;
            }
        }

        $availabilityComparisonFileName =  Carbon::now()->format('Y-m-d H-i-s') . "-dostepne-produkty". ".csv";
        $unavailabilityComparisonFileName = Carbon::now()->format('Y-m-d H-i-s') . "-niedostepne-produkty". ".csv";
        $priceComparisonFileName = Carbon::now()->format('Y-m-d H-i-s') . "-nieprawidlowa-cena". ".csv";
        $visibiltyComparisonFileName = Carbon::now()->format('Y-m-d H-i-s') . "-widoczne-produkty". ".csv";

        Storage::disk('public')->put($availabilityComparisonFileName, $availabilityComparisonFile);
        Storage::disk('public')->put($unavailabilityComparisonFileName, $unavailabilityComparisonFile);
        Storage::disk('public')->put($priceComparisonFileName, $priceComparisonFile);
        Storage::disk('public')->put($visibiltyComparisonFileName, $visibilityComaprisonFile);

        return [$availabilityComparisonFileName, $unavailabilityComparisonFileName, $priceComparisonFileName, $visibiltyComparisonFileName];
    }

    private function getFilePath($fileName)
    {
        return Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . $fileName;
    }
}
