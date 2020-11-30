<?php
namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use App\Libraries\Comparison\AvailablityComparison;
use App\Libraries\Comparison\PriceComparison;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use App\ProductException;
use Illuminate\Support\Facades\Log;

abstract class BaseComparison implements iResults
{
    protected $marketplaceModel;
    protected $symbolMarketplace;
    protected $specificPrice;
    protected $last_download_id_softlab;
    protected $last_download_id_softlab_date;
    protected $last_download_id_market;
    protected $last_download_id_market_date;

    public function __construct()
    {
        $this->last_download_id_market = $this->marketplaceModel::max('download_id');
        $this->last_download_id_softlab = ProductSoftlab::max('download_id');
        $this->last_download_id_market_date = $this->marketplaceModel::max('created_at');
        $this->last_download_id_softlab_date = ProductSoftlab::max('created_at');
    }

    protected function isProductException($product, $nameCompare)
    {

        $productException = ProductException::where('indeks', $product)
                                            ->where('compare_method', $nameCompare)
                                            ->where('marketplace', $this->symbolMarketplace)->exists();
                                        
        return $productException;
    }
}

