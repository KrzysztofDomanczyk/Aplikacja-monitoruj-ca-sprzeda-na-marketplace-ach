<?php
namespace App\Libraries\Comparison\MarketplaceComparison;

use App\IaiProduct;
use App\Libraries\Comparison\MarketplaceComparison\MarketplaceComparison;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;

class EbayComparison extends MarketplaceComparison
{
    public function __construct()
    {
        $this->marketplaceData = [
            'symbolMarketplace' => 'ebay',
            'marketplaceModel' => new \App\EbayProduct(),
            'specificPriceColumn' => 'ebay_price'
        ];
    }

    public function compare()
    {
        $this->compareAvailability();
        $this->comparePrice();
    }
}

