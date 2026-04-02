<?php

/** @var PDO $pdo */

if (!isset($pdo) || !isset($_SESSION['user_id'])) {
    return;
}

require_once __DIR__ . '/../../app/notifications.php';
$panel = (new Notifications($pdo))->getPanelData((int) $_SESSION['user_id']);
?>

<section class="notification-panel" aria-labelledby="notifications-heading">
    <div class="notification-panel-header">
        <h2 id="notifications-heading">Meldingen</h2>
        <?php if ($panel['unread_count'] > 0): ?>
            <span class="notification-badge"><?= (int) $panel['unread_count'] ?> nieuw</span>
        <?php endif; ?>
    </div>
    <?php if ($panel['items'] === []): ?>
        <p class="notification-empty">Geen meldingen.</p>
    <?php else: ?>
        <ul class="notification-list">
            <?php foreach ($panel['items'] as $row): ?>
                <?php
                $unread = !((int) ($row['is_read'] ?? 0));
                $ts     = !empty($row['created_at'])
                    ? date('d-m-Y H:i', strtotime((string) $row['created_at']))
                    : '';
                ?>
                <li class="notification-item<?= $unread ? ' notification-item--unread' : '' ?>">
                    <div class="notification-body">
                        <p class="notification-text"><?= htmlspecialchars((string) $row['message']) ?></p>
                        <?php if ($ts !== ''): ?>
                            <time class="notification-time" datetime="<?= htmlspecialchars((string) $row['created_at']) ?>"><?= htmlspecialchars($ts) ?></time>
                        <?php endif; ?>
                    </div>
                    <?php if ($unread): ?>
                        <a class="notification-dismiss"
                           href="/Goalgetter/view/mark_notification_read.php?id=<?= (int) $row['id'] ?>">Markeer gelezen</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
