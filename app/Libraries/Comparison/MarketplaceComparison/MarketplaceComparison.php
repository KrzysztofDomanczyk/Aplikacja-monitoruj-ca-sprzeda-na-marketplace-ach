<?php
namespace App\Libraries\Comparison\MarketplaceComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use App\Libraries\Comparison\TypesComparison\AvailablityComparison;
use App\Libraries\Comparison\TypesComparison\PriceComparison;
use App\Libraries\Comparison\TypesComparison\StockComparison;
use Illuminate\Support\Facades\Log;

abstract class MarketplaceComparison
{
    protected $marketplaceModel;
    protected $symbolMarketplace;
    protected $marketplaceData;
    protected $results = [];

    protected function compareAvailability($specificComparisionAvailability = null)
    {
        if ($specificComparisionAvailability == null) {
            $compareAvailabilty = new AvailablityComparison($this->marketplaceData);
            $compareAvailabilty->compare();
            $this->results = array_merge($this->results, $compareAvailabilty->getResults());
        } else {
            $specificComparisionAvailability->compare();
            $this->results = array_merge($this->results, $specificComparisionAvailability->getResults());
        }
    }

    protected function comparePrice($specificComparisionPrice = null)
    {
        if ($specificComparisionPrice == null) {
            $comparePrice = new PriceComparison($this->marketplaceData);
            $comparePrice->compare();
            $this->results = array_merge($this->results, $comparePrice->getResults());
        } else {
            $specificComparisionPrice->compare();
            $this->results = array_merge($this->results, $specificComparisionPrice->getResults());
        }
    }

    protected function compareStockWholesale()
    {
        $compareStock = new StockComparison($this->marketplaceData);
        $compareStock->compare();
        $res = $compareStock->getResults();
        $this->results = array_merge($this->results, $compareStock->getResults()); 
    }
   
    public function getResults()
    {
        return $this->results;
    }

    public function getSymbolMarketplace()
    {
        return $this->symbolMarketplace;
    }
}

