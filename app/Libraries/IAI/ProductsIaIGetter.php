<?php
namespace App\Libraries\IAI;

use App\Exceptions\NoProductsException;
use App\IaiProduct;
use App\Libraries\ProductsGetter;
use App\ProductMarketplace;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductsIaiGetter extends ProductsGetter
{
    protected $resultNumberPage;
    protected $shopKey;
    protected $stockWholesaleKey;
    const STOCK_ID_WHOLESALE = 2;
    public function __construct($download_id = null)
    {
        $this->apiConnection = new ProductsIaiApi;
        $this->productsContainer = [];
        $this->download_id = $download_id;
        $this->model = new IaiProduct();
    }

    public function getProducts()
    {
        $iai_products =  $this->apiConnection->getProductsToBuy();
        $this->shopKey = $this->getShopIdKeyArray($iai_products['results'][0]['productShopsAttributes']);
        $this->resultNumberPage = $iai_products['resultsNumberPage'];
        $this->productsContainer = array_merge($this->productsContainer, $iai_products['results']);

        $i = 1;
        while ($i <= $this->resultNumberPage) {
            $iai_products =  $this->apiConnection->getProductsToBuy($i);
            $this->productsContainer = array_merge($this->productsContainer, $iai_products['results']);
            $i++;
            echo "Downloading.. " . "page: " . $i . " from: " . $this->resultNumberPage . PHP_EOL;
        }
        
    }

    public function save() 
    {
        if (empty($this->productsContainer)) throw new Exception('IAI');
        $this->download_id = $this->createDownloadId();
        foreach ($this->productsContainer as $product) {
            $isSet = substr($product['productDisplayedCode'], 0, 2) == "30";
            if ($isSet) continue;

            try {
                $stock_wholesale = $this->getStockWholesale($product['productStocksData']['productStocksQuantities']);
                $iaiProduct = new IaiProduct();
                $iaiProduct->index = $product['productDisplayedCode'];
                $iaiProduct->download_id = $this->download_id;
                $iaiProduct->uid = $product['productId'];
                $iaiProduct->stock_wholesale = $stock_wholesale;
                $iaiProduct->price = $product['productShopsAttributes'][$this->shopKey]['productRetailPrice'];
                $iaiProduct->product_name = $product['productDescriptionsLangData'][0]['productName'];
                $iaiProduct->save();
            } catch (Exception $e) {
                Log::notice($e->getMessage());
            }
        }

    }

    private function getStockWholesale($quantities)
    {
        foreach ($quantities as $key => $value) {
            if ($value['stockId'] == self::STOCK_ID_WHOLESALE) {
                $stockKey = $key;
            } else {
                $stockKey = null;
            }
        }
        if ($stockKey != null) {
            $stock_wholesale = $quantities[$stockKey]['productSizesData'][0]['productSizeQuantity'];
        } else {
            $stock_wholesale = null;
        }

        return $stock_wholesale;
    }

    private function getStockIdKeyArray($attributes, $id)
    {
        foreach ($attributes as $key => $value) {
            if ($value['stockId'] == $id) {
                return $key;
            }
        }
    }

    public function getDownloadId()
    {
        return $this->download_id;
    }

    public function getResultsNumberPage()
    {
        return $this->resultNumberPage;
    }

    private function getShopIdKeyArray($attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($value['shopId'] == 1) {
                return $key;
            }
        }
    }
}

