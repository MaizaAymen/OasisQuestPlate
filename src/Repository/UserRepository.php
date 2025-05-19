<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find user with all related data (chat messages, orders, etc.)
     */
    public function findWithAllData(int $userId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Get user
        $userSql = '
            SELECT id, email, roles 
            FROM users 
            WHERE id = :userId
        ';
        $user = $conn->executeQuery($userSql, ['userId' => $userId])->fetchAssociative();
        
        if (!$user) {
            return null;
        }
        
        // Get user's chat messages
        $chatSql = '
            SELECT id, role, content, created_at 
            FROM chat_message 
            WHERE user_id = :userId
            ORDER BY created_at DESC
        ';
        $chatMessages = $conn->executeQuery($chatSql, ['userId' => $userId])->fetchAllAssociative();
        
        // Get user's orders
        $ordersSql = '
            SELECT id, created_at, items, address, city, postal_code
            FROM orders
            WHERE user_id = :userId
            ORDER BY created_at DESC
        ';
        $orders = $conn->executeQuery($ordersSql, ['userId' => $userId])->fetchAllAssociative();
        
        // Combine all data
        return [
            'user' => $user,
            'chatMessages' => $chatMessages,
            'orders' => $orders
        ];
    }
}