<?php
namespace App\Libraries\Comparison\MarketplaceComparison;

use App\IaiProduct;
use App\Libraries\Comparison\MarketplaceComparison\MarketplaceComparison;
use App\Libraries\Comparison\TypesComparison\PriceComparisonAllegro;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;

class AllegroComparison extends MarketplaceComparison
{
    public function __construct()
    {
        $this->marketplaceData = [
            'symbolMarketplace' => 'allegro',
            'marketplaceModel' => new \App\AllegroProduct(),
            'specificPriceColumn' => null
        ];
    }

    public function compare()
    {
        $priceComparison = new PriceComparisonAllegro($this->marketplaceData);
        $this->compareAvailability();
        $this->comparePrice($priceComparison);
    }
}
