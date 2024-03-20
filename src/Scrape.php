<?php

namespace App;

require 'vendor/autoload.php';

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;

class Scrape
{
    private array $products = [];

    const PRODUCTS_API_URL = "https://www.magpiehq.com/developer-challenge/smartphones";

    const MB_GB_CONVERSION = 1024;
    
    /**
     * run
     *
     * @return void
     */
    public function run(): void
    {
        $document = ScrapeHelper::fetchDocument(self::PRODUCTS_API_URL);
        $totalPages = $document->filter('#pages')->filter('a');
        $products = $document->filter('.product');
        $pagesCount = count($totalPages);
        $this->listOfProducts($products);

        for ($page = 2; $page <= $pagesCount; $page++) {
            $document = ScrapeHelper::fetchDocument(self::PRODUCTS_API_URL."?page=".$page);
            $products = $document->filter('.product');
            $this->listOfProducts($products);
        }

        file_put_contents('output.json', json_encode($this->products));
    }
    
    /**
     * listOfProducts
     *
     * @param  mixed $products
     * @return void
     */
    private function listOfProducts(Crawler $products) {
        try {
            /* 
            Check if Products count is greater
            than 0 for loop to trigger
            */
            if (count($products) > 0) {
                foreach($products as $product) {
                    $productCrawler = new Crawler($product);
                    
                    // Scrape color of product as array
                    $colorsOfProduct = $productCrawler->filter('span[data-colour]')->each(function (Crawler $node, $i) {
                        return $node->attr('data-colour');
                    });
    
                    // Scrape name of product
                    $nameOfProduct = $productCrawler->filter('.product-name')->text();
    
                    // Scrape capacity of product
                    $textCapacityOfProduct = $productCrawler->filter('.product-capacity')->text();
                    $intCapacityOfProduct = (int)filter_var($textCapacityOfProduct, FILTER_SANITIZE_NUMBER_INT);
    
                    $titleOfProduct = ucwords($nameOfProduct)." ".$textCapacityOfProduct;
                    
                    // Conversion of product capacity from GB to MB
                    if (strpos($textCapacityOfProduct, 'GB') !== false) {
                        $capacityOfProductInMB = $intCapacityOfProduct * self::MB_GB_CONVERSION;
                    } else {
                        $capacityOfProductInMB = $intCapacityOfProduct;
                    }

                    // Scrape price of product
                    $textPriceOfProduct = $productCrawler->filterXPath("//div[contains(text(), '£')]")->text();
                    $priceOfProduct = (float) filter_var($textPriceOfProduct, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
                    // Scrape image source of product and resolve URI
                    $imageSourceOfProduct = $productCrawler->filter('img')->attr('src');
                    $imageURLOfProduct = UriResolver::resolve($imageSourceOfProduct, self::PRODUCTS_API_URL);
    
                    // Scrape availability of the product
                    $textAvailabilityOfProduct = $productCrawler->filterXPath("//div[contains(text(), 'Availability:')]")->text();
                    $textAvailabilityOfProduct = trim(substr($textAvailabilityOfProduct,strrpos($textAvailabilityOfProduct,':') + 1));
                    $isAvailableProduct = $textAvailabilityOfProduct == "Out of Stock" ? false : true;

                    // Scrape shipping date of product if exist
                    $textShippingOfProduct = null;
                    $shippingDateOfProduct = null;
    
                    $shippingTextNodeOfProduct = $productCrawler->filterXPath("//div[contains(translate(text(), 
                                            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                                            'abcdefghijklmnopqrstuvwxyz'), 'deliver')
                                            or contains(translate(text(), 
                                            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                                            'abcdefghijklmnopqrstuvwxyz'), 'ship')]");
                    
                    if ($shippingTextNodeOfProduct->count()) {
                        $textShippingOfProduct = ucwords($shippingTextNodeOfProduct->text());
                        $shippingDateOfProduct = ScrapeHelper::extractDateFromString($textShippingOfProduct);
                    }
                    
                    /*
                    Looping color array to set different data points
                    */
                    foreach($colorsOfProduct as $color) {
                        $endProduct = new Product();
                        $endProduct->setTitle($titleOfProduct);
                        $endProduct->setPrice($priceOfProduct);
                        $endProduct->setImageUrl($imageURLOfProduct);
                        $endProduct->setCapacity($capacityOfProductInMB);
                        $endProduct->setColor($color);
                        $endProduct->setAvailabilityText($textAvailabilityOfProduct);
                        $endProduct->setIsAvailable($isAvailableProduct);
                        $endProduct->setShippingText($textShippingOfProduct);
                        $endProduct->setShippingDate($shippingDateOfProduct);
    
                        if (!in_array($endProduct, $this->products)) {
                            $this->products[] = $endProduct;
                        }
                    }
                }
            }
        } catch (Exception $error) {
            echo $error->getMessage();
        }
    }
}

$scrape = new Scrape();
$scrape->run();
