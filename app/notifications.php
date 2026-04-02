<?php

class Notifications
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Stuur een melding naar alle gebruikers behalve degene die de wedstrijd aanmaakte.
     */
    public function notifyNewWedstrijd($excludeUserId, $wedstrijdId, $titel, $date)
    {
        $excludeUserId = (int) $excludeUserId;
        $wedstrijdId = (int) $wedstrijdId;
        $message = sprintf(
            'Nieuwe wedstrijd: "%s" op %s.',
            $titel,
            $date
        );

        try {
            $stmt = $this->pdo->prepare('SELECT id FROM user WHERE id != ?');
            $stmt->execute([$excludeUserId]);
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if ($userIds === []) {
                return true;
            }

            $ins = $this->pdo->prepare(
                'INSERT INTO notification (user_id, wedstrijd_id, message, is_read)
                 VALUES (?, ?, ?, 0)'
            );

            foreach ($userIds as $uid) {
                $ins->execute([(int) $uid, $wedstrijdId, $message]);
            }

            return true;
        } catch (PDOException $e) {
            error_log('Notifications::notifyNewWedstrijd: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getPanelData($userId, $limit = 15)
    {
        $userId = (int) $userId;
        $empty = ['items' => [], 'unread_count' => 0];

        try {
            $c = $this->pdo->prepare(
                'SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = 0'
            );
            $c->execute([$userId]);
            $unread = (int) $c->fetchColumn();

            $lim = max(1, min(50, (int) $limit));
            $q = $this->pdo->prepare(
                "SELECT id, message, wedstrijd_id, is_read, created_at
                 FROM notification
                 WHERE user_id = ?
                 ORDER BY created_at DESC
                 LIMIT {$lim}"
            );
            $q->execute([$userId]);
            $items = $q->fetchAll(PDO::FETCH_ASSOC);

            return [
                'items'        => $items ? $items : [],
                'unread_count' => $unread,
            ];
        } catch (PDOException $e) {
            error_log('Notifications::getPanelData: ' . $e->getMessage());

            return $empty;
        }
    }

    /**
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead($notificationId, $userId)
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE notification SET is_read = 1 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([(int) $notificationId, (int) $userId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Notifications::markAsRead: ' . $e->getMessage());

            return false;
        }
    }
}
