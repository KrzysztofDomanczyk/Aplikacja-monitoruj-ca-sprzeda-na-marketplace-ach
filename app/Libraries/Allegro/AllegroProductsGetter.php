<?php

namespace App\Libraries\Allegro;

use App\AllegroProduct;
use App\Exceptions\NoProductsException;
use App\Libraries\ProductsGetter;

class AllegroProductsGetter extends ProductsGetter
{
    private $offers;
    const MAXLIMITCOUNT = 1000;

    public function __construct($account_name, $download_id = null)
    {
        $this->apiConnection = new AllegroOffers($account_name);
        $this->offers = $this->apiConnection->getAllOffers();
        $this->productsContainer = [];
        $this->download_id = $download_id;
        $this->model = new AllegroProduct();
    }


    public function getProducts()
    {
        if ($this->offers->totalCount < self::MAXLIMITCOUNT) {
            $response = $this->apiConnection->getAllOffers();
            $this->productsContainer = $response->offers;
        } else {
            $howMany = floor($this->offers->totalCount / self::MAXLIMITCOUNT);
            $i = 0;
            while ($i <= $howMany) {
                $offset = self::MAXLIMITCOUNT * $i;
                $offers = $this->apiConnection->getAllOffers($offset);
                $this->productsContainer = array_merge($this->productsContainer, $offers->offers);
                echo "Downloading.. " . PHP_EOL;
                $i++;
            }
        }
    }

    public function save()
    {
        if (empty($this->productsContainer)) throw new NoProductsException('Allegro');
        $this->download_id = $this->createDownloadId();
        foreach ($this->productsContainer as $product) {
            try {
                $allegrooffers = new AllegroProduct();
                $allegrooffers->index = $product->external->id;
                $allegrooffers->download_id = $this->download_id;
                $allegrooffers->stock = $product->stock->available;
                $allegrooffers->price = $product->sellingMode->price->amount;
                $allegrooffers->can_buy = $product->publication->status == "ACTIVE" ? 1 : 0 ;
                $allegrooffers->uid = $product->id;
                $allegrooffers->product_name = $product->name;
                $allegrooffers->save();
            } catch (\Exception $e) {
                dump($e->getMessage());
                continue;
                return false;
            }
        }
    
    }

    public function getDownloadId()
    {
        return $this->download_id;
    }

}
