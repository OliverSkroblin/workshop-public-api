<?php

namespace Scripts\Examples;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;

require_once __DIR__ . '/../examples/base-script.php';

$env = 'prod'; // by default, kernel gets booted in dev

$kernel = require __DIR__ . '/../boot/boot.php';

class UserData
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function getUserData($userId)
    {
        $query = "SELECT * FROM customer WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $userId);
        $stmt->execute();
        $user = $stmt->fetchAssociative();

        if ($user) {
            $orders = $this->getUserOrders($userId);
            $totalAmountSpent = 0;
            foreach ($orders as &$order) {
                $orderId = $order['id'];
                $items = $this->getOrderItems($orderId);
                $order['items'] = $items;

                $orderTotal = 0;
                foreach ($items as $item) {
                    $orderTotal += $item['quantity'] * $item['price'];
                }
                $order['order_total'] = $orderTotal;
                $totalAmountSpent += $orderTotal;
            }
            $user['orders'] = $orders;
            $user['total_amount_spent'] = $totalAmountSpent;
            $user['average_order_value'] = count($orders) > 0 ? $totalAmountSpent / count($orders) : 0;
            return $user;
        } else {
            return null;
        }
    }

    private function getUserOrders($userId)
    {
        $query = "SELECT * FROM `order` WHERE customer_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $userId);
        $stmt->execute();
        return $stmt->fetchAllAssociative();
    }

    private function getOrderItems($orderId)
    {
        $query = "SELECT * FROM order_line_item WHERE order_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $orderId);
        $stmt->execute();
        return $stmt->fetchAllAssociative();
    }
}

$userData = new UserData($kernel->getContainer()->get(Connection::class));

$user = $userData->getUserData(
    Uuid::randomHex()
);

dd($user);
