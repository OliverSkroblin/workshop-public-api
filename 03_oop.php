<?php

class Order
{
    const ID = 'id';
    const CUSTOMER = 'customer';
    const AMOUNT = 'amount';
    const STATUS = 'status';
    const ITEMS = 'items';
    const DATE = 'date';

    private $order;

    public function __construct(array $order)
    {
        $this->order = $order;
    }

    public function getId()
    {
        return $this->order[self::ID];
    }

    public function getCustomer()
    {
        return $this->order[self::CUSTOMER];
    }

    public function getAmount()
    {
        return $this->order[self::AMOUNT];
    }

    public function getStatus()
    {
        return $this->order[self::STATUS];
    }

    public function getItems()
    {
        return $this->order[self::ITEMS];
    }

    public function getDate()
    {
        return new DateTime($this->order[self::DATE]);
    }
}

class RevenueCalculator
{
    public function calculateTotalRevenue(array $orders)
    {
        $amounts = array_map(function (Order $order) {
            return $order->getAmount();
        }, $orders);

        return array_sum($amounts);
    }

    public function calculateAverageOrderAmount(array $orders, $totalRevenue)
    {
        $orderCount = count($orders);
        return $orderCount ? $totalRevenue / $orderCount : 0;
    }
}

class OrderFilter
{
    public function getCompletedOrders(array $orders)
    {
        return array_filter($orders, function (Order $order) {
            return $order->getStatus() === 'completed';
        });
    }

    public function getRecentOrders(array $orders, DateTime $currentDate)
    {
        return array_filter($orders, function (Order $order) use ($currentDate) {
            $interval = $currentDate->diff($order->getDate());
            return $interval->days <= 7;
        });
    }
}

class OrderGrouper
{
    public function getOrdersByCustomer(array $orders)
    {
        $ordersByCustomer = [];
        foreach ($orders as $order) {
            $customer = $order->getCustomer();
            if (!isset($ordersByCustomer[$customer])) {
                $ordersByCustomer[$customer] = [];
            }
            $ordersByCustomer[$customer][] = $order;
        }
        return $ordersByCustomer;
    }
}

class ReportFormatter
{
    public function formatReport($totalRevenue, $averageOrderAmount, $completedOrders, $ordersByCustomer, $recentOrders)
    {
        $report = "Order Report:\n";
        $report .= $this->formatTotalRevenue($totalRevenue);
        $report .= $this->formatAverageOrderAmount($averageOrderAmount);
        $report .= $this->formatCompletedOrders($completedOrders);
        $report .= $this->formatOrdersByCustomer($ordersByCustomer);
        $report .= $this->formatRecentOrders($recentOrders);

        return $report;
    }

    private function formatTotalRevenue($totalRevenue)
    {
        return "Total Revenue: $" . $totalRevenue . "\n";
    }

    private function formatAverageOrderAmount($averageOrderAmount)
    {
        return "Average Order Amount: $" . number_format($averageOrderAmount, 2) . "\n";
    }

    private function formatCompletedOrders($completedOrders)
    {
        $report = "Completed Orders:\n";
        foreach ($completedOrders as $order) {
            $report .= $this->formatOrder($order);
        }
        return $report;
    }

    private function formatOrder($order)
    {
        return "  Order ID: " . $order->getId() . " - Customer: " . $order->getCustomer() . " - Amount: $" . $order->getAmount() . "\n";
    }

    private function formatOrdersByCustomer($ordersByCustomer)
    {
        $report = "Orders by Customer:\n";
        foreach ($ordersByCustomer as $customer => $orders) {
            $report .= $this->formatCustomerOrders($customer, $orders);
        }
        return $report;
    }

    private function formatCustomerOrders($customer, $orders)
    {
        $report = "  Customer: " . $customer . "\n";
        foreach ($orders as $order) {
            $report .= "    " . $this->formatOrder($order);
        }
        return $report;
    }

    private function formatRecentOrders($recentOrders)
    {
        $report = "Recent Orders:\n";
        foreach ($recentOrders as $order) {
            $report .= $this->formatOrderWithDate($order);
        }
        return $report;
    }

    private function formatOrderWithDate($order)
    {
        return "  Order ID: " . $order->getId() . " - Date: " . $order->getDate()->format('Y-m-d') . "\n";
    }
}

class OrderProcessor
{
    private $orders;

    public function __construct()
    {
        $this->orders = [
            [Order::ID => 1, Order::CUSTOMER => 'Alice', Order::AMOUNT => 100, Order::STATUS => 'completed', Order::ITEMS => 3, Order::DATE => '2023-01-01'],
            [Order::ID => 2, Order::CUSTOMER => 'Bob', Order::AMOUNT => 50, Order::STATUS => 'pending', Order::ITEMS => 1, Order::DATE => '2023-01-02'],
            [Order::ID => 3, Order::CUSTOMER => 'Charlie', Order::AMOUNT => 150, Order::STATUS => 'completed', Order::ITEMS => 5, Order::DATE => '2023-01-03'],
            [Order::ID => 4, Order::CUSTOMER => 'Dave', Order::AMOUNT => 200, Order::STATUS => 'completed', Order::ITEMS => 7, Order::DATE => '2023-01-04'],
        ];
    }

    public function processOrders()
    {
        $orderObjects = array_map(function ($order) {
            return new Order($order);
        }, $this->orders);

        $revenueCalculator = new RevenueCalculator();
        $totalRevenue = $revenueCalculator->calculateTotalRevenue($orderObjects);
        $averageOrderAmount = $revenueCalculator->calculateAverageOrderAmount($orderObjects, $totalRevenue);

        $orderFilter = new OrderFilter();
        $completedOrders = $orderFilter->getCompletedOrders($orderObjects);
        $recentOrders = $orderFilter->getRecentOrders($orderObjects, new DateTime());

        $orderGrouper = new OrderGrouper();
        $ordersByCustomer = $orderGrouper->getOrdersByCustomer($orderObjects);

        $reportFormatter = new ReportFormatter();
        return $reportFormatter->formatReport($totalRevenue, $averageOrderAmount, $completedOrders, $ordersByCustomer, $recentOrders);
    }
}

$orderProcessor = new OrderProcessor();
echo $orderProcessor->processOrders();
