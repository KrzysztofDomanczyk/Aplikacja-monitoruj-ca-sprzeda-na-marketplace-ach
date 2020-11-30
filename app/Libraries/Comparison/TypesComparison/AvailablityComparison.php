<?php

namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use Carbon\Carbon;

class AvailablityComparison extends BaseComparison
{
    protected $availableProducts = [];
    protected $unAvailableProducts = [];
    protected $incorrectVisibility = [];
    const NAMECOMPARE = "AvailablityComparison";
    const TOSELL = '>';
    const NOTTOSELL = '=';

    public function __construct($data)
    {
        $this->marketplaceModel = $data['marketplaceModel'];
        $this->symbolMarketplace = $data['symbolMarketplace'];
        parent::__construct();
    }

    public function compare()
    {
        $this->compareAvailableProducts();
        $this->compareUnavailableProducts();
    }

    protected function compareAvailableProducts()
    {
        $productsSoftlab = $this->getProductsSoftlab(self::TOSELL);
        foreach ($productsSoftlab as $productSoftlab) {
            if ($this->hasToBeOmmited($productSoftlab)) continue;
            $productMarket = $this->getProductsMarket($productSoftlab->index);
            if ($productMarket->isEmpty()) {
                $inCorrectProduct = [
                    "Index" => $productSoftlab->index,
                    "Nazwa towaru" => $productSoftlab->name,
                    "Stan" => $productSoftlab->stock,
                ];
                array_push($this->availableProducts, $inCorrectProduct);
            }
        }
    }
    protected function compareUnavailableProducts()
    {
        $productsSoftlab = $this->getProductsSoftlab(self::NOTTOSELL);
        foreach ($productsSoftlab as $productSoftlab) {
            if ($this->hasToBeOmmited($productSoftlab)) continue;
            $productsMarket = $this->getProductsMarket($productSoftlab->index);
            if (!$productsMarket->isEmpty()) {
                foreach ($productsMarket as $productMarket) {
                    $inCorrectProduct = [
                        "Index" => $productSoftlab->index,
                        "Stan" => $productSoftlab->stock,
                        "Nazwa towaru na marketplace" => $productMarket->product_name,
                        "UID:" => $productMarket->uid
                    ];
                    array_push($this->unAvailableProducts, $inCorrectProduct);
                }
            }
        }
    }

    protected function getProductsSoftlab($char = ">=")
    {
        return ProductSoftlab::where($this->symbolMarketplace, true)
            ->where('stock', $char, 0)
            ->where('download_id', $this->last_download_id_softlab)
            ->get();
    }

    protected function getProductsMarket($index)
    {

        return $this->marketplaceModel
            ->where('index', $index)
            ->where('download_id', $this->last_download_id_market)
            ->get();
    }

    public function getAvailableProducts()
    {
        return $this->availableProducts;
    }

    public function getUnAvailableProducts()
    {
        return $this->unAvailableProducts;
    }

    private function hasToBeOmmited($productSoftlab)
    {
        if ($this->isProductException($productSoftlab->index, self::NAMECOMPARE)) return true;

        return false;
    }

    public function getResults()
    {
        $results = [
            [
                "NameCompare" => "Towary, które powinny być w sprzedaży",
                "TypeCompare" => "availableProducts",
                "Marketplace" => $this->symbolMarketplace,
                "NumberOfDownloadSoftlab" => $this->last_download_id_softlab,
                "NumberOfDownloadMarket" => $this->last_download_id_market,
                "DownloadDateSoftlab" => $this->last_download_id_softlab_date,
                "DownloadDateMarket" => $this->last_download_id_market_date,
                "Data" => $this->availableProducts
            ],
            [
                "NameCompare" => "Towary, które NIE powinny być w sprzedaży",
                "TypeCompare" => "unAvailableProducts",
                "Marketplace" => $this->symbolMarketplace,
                "NumberOfDownloadSoftlab" => $this->last_download_id_softlab,
                "NumberOfDownloadMarket" => $this->last_download_id_market,
                "DownloadDateSoftlab" => $this->last_download_id_softlab_date,
                "DownloadDateMarket" => $this->last_download_id_market_date,
                "Data" => $this->unAvailableProducts
            ],
            [
                "NameCompare" => "Towary, które NIE powinny być widoczne na sklepie",
                "TypeCompare" => "incorrectVisibilty",
                "Marketplace" => $this->symbolMarketplace,
                "NumberOfDownloadSoftlab" => $this->last_download_id_softlab,
                "NumberOfDownloadMarket" => $this->last_download_id_market,
                "DownloadDateSoftlab" => $this->last_download_id_softlab_date,
                "DownloadDateMarket" => $this->last_download_id_market_date,
                "Data" => $this->incorrectVisibility
            ]
        ];
        return $results;
    }

}
