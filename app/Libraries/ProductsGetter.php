<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;

abstract class ProductsGetter implements iAdapterProducts
{
    protected $apiConnection;
    protected $productsContainer;
    protected $model;
    protected $download_id;

    public function insertIntoDatabase()
    {
        $this->clearDatabase();
        $this->getProducts();
        $this->save();
    }

    private function clearDatabase()
    {
        if ($this->model->getTable() != "allegro_products") {
            $downloadIds = DB::table($this->model->getTable())
            ->select(DB::raw('count(id) as count, download_id'))
            ->groupBy('download_id')
            ->get();
            if (count($downloadIds) > 2) {
                DB::table($this->model->getTable())->truncate();
            }
        }
    }

    protected function createDownloadId()
    {
        if ($this->download_id === null) {
            $downloadId = $this->model->max('download_id');
            return $downloadId === null ? 0 : (int) $downloadId + 1 ;
        } else {
            return $this->download_id;
        }
    }


}
