<?php

/**
 * 1) Event (are just for adding stuff, not to replace full functions)
 *
 *
 *
 * 2) Registry / Factory (Cart processor)
 * 3) Decorator (Product price - add stuff, or replace full functionality)
 * 4) Adapter (Filesystem, Increment storage, Search - have different infrastructure solutions for the same thing)
 * 5) Command patter
 * 6) Observer pattern
 * 7) Builder pattern
 * 8) Composite pattern
 * 9) Facade pattern
 * 10) Proxy pattern
 */
class ReportGenerator
{
    private $products;

    public function __construct(private readonly Reporter $reporter)
    {
        $this->products = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 10, 'category' => 'A', 'stock' => 100, 'sales' => 50],
            ['id' => 2, 'name' => 'Product 2', 'price' => 20, 'category' => 'B', 'stock' => 50, 'sales' => 20],
            ['id' => 3, 'name' => 'Product 3', 'price' => 15, 'category' => 'A', 'stock' => 200, 'sales' => 100],
            ['id' => 4, 'name' => 'Product 4', 'price' => 25, 'category' => 'C', 'stock' => 0, 'sales' => 0],
        ];
    }

    public function load(): ReportData
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

        $data = new ReportData(
            $totalProducts,
            $totalSales,
            $totalRevenue,
            $averagePrice,
            $productsByCategory,
            $outOfStockProducts
        );

//        (new \Symfony\Component\EventDispatcher\EventDispatcher())
//            ->dispatch($data, 'report.load');

        return $data;
    }

    public function report(ReportData $data)
    {
        $this->reporter->report($data);
    }
}

class XmlReporter extends Reporter
{
    public function type(): string
    {
        return 'xml';
    }

    public function report(ReportData $data): void
    {
        $xml = new \SimpleXMLElement('<report/>');
        $xml->addChild('totalProducts', $data->totalProducts);
        $xml->addChild('totalSales', $data->totalSales);
        $xml->addChild('totalRevenue', $data->totalRevenue);
        $xml->addChild('averagePrice', $data->averagePrice);
        $productsByCategory = $xml->addChild('productsByCategory');
        foreach ($data->productsByCategory as $category => $products) {
            $categoryElement = $productsByCategory->addChild('category');
            $categoryElement->addAttribute('name', $category);
            foreach ($products as $product) {
                $productElement = $categoryElement->addChild('product');
                $productElement->addChild('name', $product['name']);
                $productElement->addChild('price', $product['price']);
                $productElement->addChild('sales', $product['sales']);
            }
        }
        $outOfStockProducts = $xml->addChild('outOfStockProducts');
        foreach ($data->outOfStockProducts as $product) {
            $outOfStockProducts->addChild('product', $product['name']);
        }

        echo $xml->asXML();

    }
}

class JsonReporter extends Reporter
{
        public function report(ReportData $data): void
        {
            echo json_encode($data);
        }
}

class Reporter
{
    public function report(ReportData $data): void
    {
        // Format report
        $report = "Report:\n";
        $report .= "Total Products: " . $data->totalProducts . "\n";
        $report .= "Total Sales: " . $data->totalSales . "\n";
        $report .= "Total Revenue: $" . $data->totalRevenue . "\n";
        $report .= "Average Price: $" . number_format($data->averagePrice, 2) . "\n";
        $report .= "Products by Category:\n";
        foreach ($data->productsByCategory as $category => $products) {
            $report .= "  Category " . $category . ":\n";
            foreach ($products as $product) {
                $report .= "    " . $product['name'] . " - $" . $product['price'] . " (Sales: " . $product['sales'] . ")\n";
            }
        }
        $report .= "Out of Stock Products:\n";
        foreach ($data->outOfStockProducts as $product) {
            $report .= "  " . $product['name'] . "\n";
        }

        echo $report;
    }
}

class ReporterFactory
{
    public function create(string $format): Reporter
    {
        switch ($format) {
            case 'xml':
                return new XmlReporter();
            case 'json':
                return new JsonReporter();
            default:
                return new Reporter();
        }
    }
}

class ReportData
{
    public function __construct(
        public int $totalProducts,
        public int $totalSales,
        public float $totalRevenue,
        public float $averagePrice,
        public array $productsByCategory,
        public array $outOfStockProducts
    ) {}
}

$reporter = (new ReporterFactory())->create('idontknow');

$reportGenerator = new ReportGenerator($reporter);

$data = $reportGenerator->load();

$reportGenerator->report($data);


