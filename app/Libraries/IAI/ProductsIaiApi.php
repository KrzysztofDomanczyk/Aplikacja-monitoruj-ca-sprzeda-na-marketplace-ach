<?php
namespace App\Libraries\IAI;

class ProductsIaiApi extends IaiApi
{
    protected $address = "xxxx";
    protected $products;

    public function getProductsByMenuItemText($arr_menu)
    {
        $menuItem = array();
        $i = 0;
        foreach ($arr_menu as $item => $value) {
            $menuItem['menuItemsTextIds'][$i]['menuItemTextId'] = $value;
            $menuItem['menuItemsTextIds'][$i]['shopId'] = 1;
            $menuItem['menuItemsTextIds'][$i]['menuId'] = 1;
            $i++;
        }
        $params = [
            'params' => [
                'returnProducts' => 'active',
                'productIsAvailable ' => "n",
                'productIsVisible ' => "n",
                'returnElements' => [
                    'id', 'code', 'lang_data', 'pictures', 'parameters'
                ],
                'producers' => [
                    0 => [
                        'producerName' => 'REDATS'
                    ]
                ],
                'productParametersParams' => [
                    [
                        'parameterNames' => [
                            'REDATS.PL '
                        ],
                    ]
                ],
                'productMenuItems' => [
                        key($menuItem) => $menuItem[key($menuItem)],
                ],
                'productType' => [
                    'productTypeInItem' => true,
                    'productTypeInFree' => false,
                    'productTypeInBundle' => false,
                    'productTypeInCollection' => false,
                    'productTypeInPackaging' => false,
                    'productTypeInService' => false
                ]
            ]
        ];
        $products = $this->getResponse($params);
        return $products;
    }

    public function getProductById($productId)
    {
        $params = [
            'params' => [
                'returnElements' => [
                    'sizes_attributes', 'code', 'parameters'
                ],
                'productParams' => [
                    0 => [
                        'productId' => $productId
                    ]
                ]
            ]
        ];
        $product =  $this->getResponse($params);

        return  $product ;
    }

    public function getAllProductsByPage($numberPage)
    {
        $params = [
            'params' => [
                'returnElements' => [
                    'id', 'code', 'lang_data', 'menu'
                ],
                'resultsPage' => $numberPage
            ]
        ];
        $products =  $this->getResponse($params);
        return $products ;
    }

    public function getProductsToBuy($numberPage = 0)
    {
        $params = [
            'params' => [
                'productIsAvailable' => "y",
                'productIsVisible' => 'y',
                'productAvailableInStocks' => [
                    'productIsAvailableInStocks' => "y"
                ],
                'returnElements' => [
                    'id', 'code', 'shops_attributes', 'lang_data', 'quantities'
                ],
                'resultsPage' => $numberPage
            ]
        ];
        $products =  $this->getResponse($params);
        // dd($products);
        
        return $products ;
    }
}

