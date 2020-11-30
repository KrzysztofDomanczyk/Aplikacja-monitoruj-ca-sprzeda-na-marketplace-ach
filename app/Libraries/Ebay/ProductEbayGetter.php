<?php
namespace App\Libraries\Ebay;

use App\EbayProduct;
use App\Exceptions\NoProductsException;
use App\Libraries\ProductsGetter;
use Illuminate\Support\Facades\Log;

class ProductEbayGetter extends ProductsGetter
{
    protected $productsEbay;
    protected $error;


    public function __construct($download_id = null)
    {
        $this->apiConnection = new EbayAPI;
        $this->productsContainer = [];
        $this->download_id = $download_id;
        $this->model = new EbayProduct();
    }

    public function getProducts()
    {
        if ($this->apiConnection->getNewToken()) {
            $products = $this->apiConnection->getItems();
            $this->productsContainer = array_merge($this->productsContainer, $products);
        }
    }

    public function save()
    {
        if (empty($this->productsContainer)) throw new NoProductsException('Ebay');
        $this->download_id = $this->createDownloadId();
        foreach ($this->productsContainer as $productEbay) {
            try {
                if ($productEbay['isAvailable']) {
                    $productEbayModel = new EbayProduct();
                    $productEbayModel->index = $productEbay['Index'];
                    $productEbayModel->download_id = $this->download_id;
                    $productEbayModel->stock = $productEbay['QuantityAvailable'];
                    $productEbayModel->price = $productEbay['Price'];
                    $productEbayModel->can_buy = $productEbay['isAvailable'];
                    $productEbayModel->shipping_cost = $productEbay['ShippingCost'] ;
                    $productEbayModel->uid = $productEbay['UID'];
                    $productEbayModel->product_name = $productEbay['Name'];
                    $productEbayModel->save();
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return false;
            }
        }
    }

}
