<?php
namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use Carbon\Carbon;
use Psy\TabCompletion\Matcher\FunctionsMatcher;

class PriceComparison extends BaseComparison
{
    protected $incorrectPrice = [];
    const NAMECOMPARE = "PriceComparison";
    
    public function __construct($data)
    {
        $this->marketplaceModel = $data['marketplaceModel'];
        $this->symbolMarketplace = $data['symbolMarketplace'];
        $this->specificPrice = $data['specificPriceColumn'];
        parent::__construct();
    }

    public function compare()
    {
        $productsSoftlab = $this->getProductsSoftlab();
        foreach ($productsSoftlab as $productSoftlab) {
            if ($this->hasToBeOmmited($productSoftlab)) continue;
            $productsMarketplace = $this->marketplaceModel
                ->where('index', $productSoftlab->index)
                ->where('download_id', $this->last_download_id_market)
                ->get();

            foreach ($productsMarketplace as $productMarketplace) {
                $this->checkPrice($productSoftlab, $productMarketplace);
            }
        }
    }

    public function priceIsNotSending($productSoftlab)
    {
        return $productSoftlab->{ "send_price_" . $this->symbolMarketplace} == false;
    }

    protected function checkPrice($productSoftlab, $productMarketplace)
    {
        $productPrice = $this->getAppropriatePrice($productSoftlab);
        
        if ($productPrice !=  $productMarketplace->price) {
            $inCorrectProduct = [
                "Index" => $productSoftlab->index,
                "Cena towaru" => $productPrice,
                "Nazwa towaru na marketplace" => $productMarketplace->product_name,
                "Cena towaru na marketplace" => $productMarketplace->price,
                "UID:" => $productMarketplace->uid
            ];
            array_push($this->incorrectPrice, $inCorrectProduct);
        }
    }

    protected function getAppropriatePrice($productSoftlab)
    {
        if ($this->specificPrice == null) {
            return $productSoftlab->promotional_price == "0" ?  $productSoftlab->retail_price : $productSoftlab->promotional_price;
        } else {
            return $productSoftlab->{$this->specificPrice};
        }
    }

    protected function getProductsSoftlab()
    {
        return ProductSoftlab::where($this->symbolMarketplace, "=", "1")
                ->where('download_id', $this->last_download_id_softlab)
                ->get();
    }

    public function getResults()
    {
        $results = [
            [
                "NameCompare" => "Towary, które mają niepoprawną cene",
                "TypeCompare" => "incorrectPrice",
                "Marketplace" => $this->symbolMarketplace,
                "NumberOfDownloadSoftlab" => $this->last_download_id_softlab,
                "NumberOfDownloadMarket" => $this->last_download_id_market,
                "DownloadDateSoftlab" => $this->last_download_id_softlab_date,
                "DownloadDateMarket" => $this->last_download_id_market_date,
                "Data" => $this->incorrectPrice
            ],
        ];
        return $results;
    }

    private function hasToBeOmmited($productSoftlab)
    {
        if ($this->isProductException($productSoftlab->index, self::NAMECOMPARE)) return true;
        if ($this->priceIsNotSending($productSoftlab)) return true;

        return false;
    }
}
