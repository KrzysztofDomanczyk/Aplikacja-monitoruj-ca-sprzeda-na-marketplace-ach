<?php
namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;
use App\Libraries\Comparison\TypesComparison\PriceComparison;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use Carbon\Carbon;
use Psy\TabCompletion\Matcher\FunctionsMatcher;

class PriceComparisonAllegro extends PriceComparison
{
    const NAMECOMPARE = "PriceComparisonAllegro";

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

    protected function priceIsLessThanOne($productSoftlab)
    {
        return (float) $this->getAppropriatePrice($productSoftlab) < 1;
    }

    private function hasToBeOmmited($productSoftlab)
    {
        if ($this->isProductException($productSoftlab->index, self::NAMECOMPARE)) return true;
        if ($this->priceIsLessThanOne($productSoftlab)) return true;
        if ($this->priceIsNotSending($productSoftlab)) return true;

        return false;
    }
}
