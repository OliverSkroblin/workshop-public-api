<?php

class OrderProcessor
{
    private $orders;

    public function __construct()
    {
        $this->orders = [
            ['id' => 1, 'customer' => 'Alice', 'amount' => 100, 'status' => 'completed', 'items' => 3, 'date' => '2023-01-01'],
            ['id' => 2, 'customer' => 'Bob', 'amount' => 50, 'status' => 'pending', 'items' => 1, 'date' => '2023-01-02'],
            ['id' => 3, 'customer' => 'Charlie', 'amount' => 150, 'status' => 'completed', 'items' => 5, 'date' => '2023-01-03'],
            ['id' => 4, 'customer' => 'Dave', 'amount' => 200, 'status' => 'completed', 'items' => 7, 'date' => '2023-01-04'],
        ];
    }

    public function processOrders()
    {
        // Calculate total revenue
        $totalRevenue = 0;
        foreach ($this->orders as $order) {
            $totalRevenue += $order['amount'];
        }

        // Get completed orders
        $completedOrders = [];
        foreach ($this->orders as $order) {
            if ($order['status'] === 'completed') {
                $completedOrders[] = $order;
            }
        }

        // Get orders by customer
        $ordersByCustomer = [];
        foreach ($this->orders as $order) {
            $customer = $order['customer'];
            if (!isset($ordersByCustomer[$customer])) {
                $ordersByCustomer[$customer] = [];
            }
            $ordersByCustomer[$customer][] = $order;
        }

        // Calculate average order amount
        $averageOrderAmount = $totalRevenue / count($this->orders);

        // Get orders in the last 7 days
        $recentOrders = [];
        $currentDate = new DateTime();
        foreach ($this->orders as $order) {
            $orderDate = new DateTime($order['date']);
            $interval = $currentDate->diff($orderDate);
            if ($interval->days <= 7) {
                $recentOrders[] = $order;
            }
        }

        // Format the processed orders
        $report = "Order Report:\n";
        $report .= "Total Revenue: $" . $totalRevenue . "\n";
        $report .= "Average Order Amount: $" . number_format($averageOrderAmount, 2) . "\n";
        $report .= "Completed Orders:\n";
        foreach ($completedOrders as $order) {
            $report .= "  Order ID: " . $order['id'] . " - Customer: " . $order['customer'] . " - Amount: $" . $order['amount'] . "\n";
        }
        $report .= "Orders by Customer:\n";
        foreach ($ordersByCustomer as $customer => $orders) {
            $report .= "  Customer: " . $customer . "\n";
            foreach ($orders as $order) {
                $report .= "    Order ID: " . $order['id'] . " - Amount: $" . $order['amount'] . "\n";
            }
        }
        $report .= "Recent Orders:\n";
        foreach ($recentOrders as $order) {
            $report .= "  Order ID: " . $order['id'] . " - Date: " . $order['date'] . "\n";
        }

        return $report;
    }
}

$orderProcessor = new OrderProcessor();
echo $orderProcessor->processOrders();
