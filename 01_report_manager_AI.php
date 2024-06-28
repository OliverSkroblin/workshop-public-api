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

    public function load(): array
    {
        $totalProducts = count($this->products);
        $totalSales = 0;
        $totalRevenue = 0;
        $totalPrice = 0;
        $productsByCategory = [];
        $outOfStockProducts = [];

        foreach ($this->products as $product) {
            $totalSales += $product['sales'];
            $totalRevenue += $product['price'] * $product['sales'];
            $totalPrice += $product['price'];
            $productsByCategory[$product['category']][] = $product;
            if ($product['stock'] == 0) {
                $outOfStockProducts[] = $product;
            }
        }

        $averagePrice = $totalPrice / $totalProducts;

        return [
            'totalProducts' => $totalProducts,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'averagePrice' => $averagePrice,
            'productsByCategory' => $productsByCategory,
            'outOfStockProducts' => $outOfStockProducts,
        ];
    }

    public function report(array $data)
    {
        // Format report
        $report = "Report:\n";
        $report .= "Total Products: " . $data['totalProducts'] . "\n";
        $report .= "Total Sales: " . $data['totalSales'] . "\n";
        $report .= "Total Revenue: $" . $data['totalRevenue'] . "\n";
        $report .= "Average Price: $" . number_format($data['averagePrice'], 2) . "\n";
        $report .= "Products by Category:\n";
        foreach ($data['productsByCategory'] as $category => $products) {
            $report .= "  Category " . $category . ":\n";
            foreach ($products as $product) {
                $report .= "    " . $product['name'] . " - $" . $product['price'] . " (Sales: " . $product['sales'] . ")\n";
            }
        }
        $report .= "Out of Stock Products:\n";
        foreach ($data['outOfStockProducts'] as $product) {
            $report .= "  " . $product['name'] . "\n";
        }

        echo $report;
    }
}

$reportGenerator = new ReportGenerator();

$data = $reportGenerator->load();

$reportGenerator->report($data);


