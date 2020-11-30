<?php
namespace App\Libraries\Comparison\TypesComparison;

use App\IaiProduct;
use App\ProductMarketplace;
use App\ProductSoftlab;
use Illuminate\Support\Facades\Log;
use App\Libraries\Comparison\MarketplaceComparison\iResults;
use Carbon\Carbon;
use Exception;
use Psy\TabCompletion\Matcher\FunctionsMatcher;

class StockComparison extends BaseComparison
{
    protected $incorrectStock = [];
    
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
            $wholesaleStock = $this->calculatewholesaleStock($productSoftlab);
            $wholesaleStockMarketplace = $this->getStockWholsaleProductMarketplace($productSoftlab->index);
            
          


            if ($wholesaleStock != null) {
                if ($wholesaleStockMarketplace != $wholesaleStock) {
                    $inCorrectProduct = [
                        'Index' => $productSoftlab->index,
                        'Stock' => $productSoftlab->stock,
                        'Factor' => $productSoftlab->factor_hurt,
                        'Export Stop' => $productSoftlab->export_stop,
                        'Wholesale Stock' => $wholesaleStock,
                        'Wholesale Stock Marketplace' => $wholesaleStockMarketplace
                    ];
                    array_push($this->incorrectStock, $inCorrectProduct);
                }
            } else {
                $inCorrectProduct = [
                    'Index' => $productSoftlab->index,
                    'Stock' => $productSoftlab->stock,
                    'Factor' => $productSoftlab->factor_hurt,
                    'Export Stop' => $productSoftlab->export_stop,
                    'Wholesale Stock' => $wholesaleStock,
                    'Wholesale Stock Marketplace' => $wholesaleStockMarketplace,
                    'Description' => 'Exportstop > stock lub towar jest nie dostępny na sklepie'
                ];
                array_push($this->incorrectStock, $inCorrectProduct);
            }
        }
    }

    protected function calculatewholesaleStock($product)
    {
        $wholesaleStock = null;

        if ($product->export_stop == 0) {
            $wholesaleStock = (int) floor($product->stock * $product->factor_hurt) ;
        } elseif ($product->stock > $product->export_stop) {
            $wholesaleStock  = $product->stock - $product->export_stop;
        }
        
        return $wholesaleStock;
    }

    protected function getStockWholsaleProductMarketplace($index)
    {
        $stochWholesaleMarketplace = $this->marketplaceModel
        // ->select('stock_wholesale')
        ->where('index', $index)
        ->where('download_id', $this->last_download_id_market)
        ->get()->first();

        try {
            $stochWholesaleMarketplace = $stochWholesaleMarketplace->stock_wholesale;
        } catch (Exception $e) {
            $stochWholesaleMarketplace = null;
        }
   
        return $stochWholesaleMarketplace;
    }
  
    protected function getProductsSoftlab()
    {
        return ProductSoftlab::where($this->symbolMarketplace, "=", "1")
                ->where('download_id', $this->last_download_id_softlab)
                ->where('stock', '>', '1')
                ->get();
    }

    public function getResults()
    {
        $results = [
            [
                "NameCompare" => "Towary, które mają niepoprawny stan [HURT]",
                "TypeCompare" => "incorrectStockWholesale",
                "Marketplace" => $this->symbolMarketplace,
                "NumberOfDownloadSoftlab" => $this->last_download_id_softlab,
                "NumberOfDownloadMarket" => $this->last_download_id_market,
                "DownloadDateSoftlab" => $this->last_download_id_softlab_date,
                "DownloadDateMarket" => $this->last_download_id_market_date,
                "Data" => $this->incorrectStock
            ],
        ];
        return $results;
    }
    
}
