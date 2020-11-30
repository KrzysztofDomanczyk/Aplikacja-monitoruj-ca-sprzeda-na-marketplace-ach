<?php

namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use Carbon\Carbon;
use App\Libraries\Comparison\TypesComparison\AvailablityComparison;

class AvailabilityComparisonIai extends AvailablityComparison
{
    protected $incorrectVisibility = [];
    const NAMECOMPARE = "AvailabilityComparisonIai";

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
        $this->isVisibleOnShop();
    }

    private function isVisibleOnShop()
    {
        $productsSoftlab = $this->getProductsSoftlab();
        foreach ($productsSoftlab as $productSoftlab) {
            if ($this->hasToBeOmmited($productSoftlab)) continue;
            $productsMarket = $this->getProductsMarket($productSoftlab->index);
            if ($productSoftlab->visible_in_shop == false and $productsMarket->isEmpty() == false) {
                foreach ($productsMarket as $productMarket) {
                    $inCorrectProduct = [
                        "Index" => $productSoftlab->index,
                        "Stan" => $productSoftlab->stock,
                        "Nazwa towaru na marketplace" => $productMarket->product_name,
                        "UID:" => $productMarket->uid
                    ];
                    array_push($this->incorrectVisibility, $inCorrectProduct);
                }
            }
        }

    }

    private function hasToBeOmmited($productSoftlab)
    {
        if ($this->isProductException($productSoftlab->index, self::NAMECOMPARE)) return true;
        if ($this->isManualAvailability($productSoftlab)) return true;

        return false;
    }

    private function isManualAvailability($product)
    {
        return $product->manual_available == 1;
    }
}
