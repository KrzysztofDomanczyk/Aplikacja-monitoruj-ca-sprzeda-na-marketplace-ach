<?php
namespace App\Libraries\Comparison\MarketplaceComparison;

use App\IaiProduct;
use App\Libraries\Comparison\MarketplaceComparison\MarketplaceComparison;
use App\Libraries\Comparison\TypesComparison\AvailabilityComparisonIai;
use App\Libraries\Comparison\TypesComparison\PriceComparison;
use App\Libraries\Comparison\TypesComparison\PriceComparisonIai;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;

class IaiComparison extends MarketplaceComparison
{
    public function __construct()
    {
        $this->marketplaceData = [
            'symbolMarketplace' => 'iai',
            'marketplaceModel' => new \App\IaiProduct(),
            'specificPriceColumn' => null
        ];
    }

    public function compare()
    {
        // $this->compareStockWholesale();
        $availabilityComparison = new AvailabilityComparisonIai($this->marketplaceData);
        $this->compareAvailability($availabilityComparison);
        $this->comparePrice();
    }
}

