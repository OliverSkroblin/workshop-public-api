<?php

class ReportGenerator
{
    private $products;

    public function __construct()
    {
        $this->products = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 10, 'category' => 'A', 'stock' => 100, 'sales' => 50],
            ['id' => 2, 'name' => 'Product 2', 'price' => 20, 'category' => 'B', 'stock' => 50, 'sales' => 20],
            ['id' => 3, 'name' => 'Product 3', 'price' => 15, 'category' => 'A', 'stock' => 200, 'sales' => 100],
            ['id' => 4, 'name' => 'Product 4', 'price' => 25, 'category' => 'C', 'stock' => 0, 'sales' => 0],
        ];
    }

    public function generateReport()
    {
        // Get the total number of products
        $totalProducts = count($this->products);

        // Calculate total sales
        $totalSales = 0;
        foreach ($this->products as $product) {
            $totalSales += $product['sales'];
        }

        // Calculate total revenue
        $totalRevenue = 0;
        foreach ($this->products as $product) {
            $totalRevenue += $product['price'] * $product['sales'];
        }

        // Get products by category
        $productsByCategory = [];
        foreach ($this->products as $product) {
            $category = $product['category'];
            if (!isset($productsByCategory[$category])) {
                $productsByCategory[$category] = [];
            }
            $productsByCategory[$category][] = $product;
        }

        // Calculate average price
        $totalPrice = 0;
        foreach ($this->products as $product) {
            $totalPrice += $product['price'];
        }
        $averagePrice = $totalPrice / $totalProducts;

        // Get out of stock products
        $outOfStockProducts = [];
        foreach ($this->products as $product) {
            if ($product['stock'] == 0) {
                $outOfStockProducts[] = $product;
            }
        }

        // Format report
        $report = "Report:\n";
        $report .= "Total Products: " . $totalProducts . "\n";
        $report .= "Total Sales: " . $totalSales . "\n";
        $report .= "Total Revenue: $" . $totalRevenue . "\n";
        $report .= "Average Price: $" . number_format($averagePrice, 2) . "\n";
        $report .= "Products by Category:\n";
        foreach ($productsByCategory as $category => $products) {
            $report .= "  Category " . $category . ":\n";
            foreach ($products as $product) {
                $report .= "    " . $product['name'] . " - $" . $product['price'] . " (Sales: " . $product['sales'] . ")\n";
            }
        }
        $report .= "Out of Stock Products:\n";
        foreach ($outOfStockProducts as $product) {
            $report .= "  " . $product['name'] . "\n";
        }

        echo $report;
    }
}

$reportGenerator = new ReportGenerator();

$reportGenerator->generateReport();


