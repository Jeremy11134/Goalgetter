<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/speler.php';
require_once __DIR__ . '/../app/wedstrijden.php';
require_once __DIR__ . '/../app/trainer.php';

$connect = new Connect();
$pdo = $connect->pdo();

$isSpeler = isset($_SESSION['role']) && $_SESSION['role'] === 'speler';
$isTrainer = isset($_SESSION['role']) && $_SESSION['role'] === 'trainer';
$speler_id = null;
$trainer_id = null;
$club_id = null;

if ($isSpeler) {
    $stmt = $pdo->prepare(
        "SELECT id FROM speler WHERE user_id = :user_id LIMIT 1"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $speler = $stmt->fetch();
    $speler_id = $speler['id'] ?? null;
}

if ($isTrainer) {
    $stmt = $pdo->prepare(
        "SELECT id, club_id FROM trainer WHERE user_id = :user_id LIMIT 1"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $trainer = $stmt->fetch();
    $trainer_id = $trainer['id'] ?? null;
    $club_id = $trainer['club_id'] ?? null;
}

/* Wedstrijd toevoegen (trainer) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_wedstrijd']) && $isTrainer && $club_id) {
    $titel = $_POST['titel'] ?? '';
    $date = $_POST['date'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $description = $_POST['description'] ?? null;
    $status = $_POST['status'] ?? 'gepland';

    if ($titel && $date && $start && $end) {
        $wedstrijdenClass = new Wedstrijden($pdo);
        $wedstrijdenClass->create(
            0, // wedstrijd_aanwezigen_id (kan later worden toegevoegd)
            $club_id,
            $start,
            $end,
            $titel,
            $date,
            $description,
            $status
        );
        header("Location: wedstrijden.php");
        exit;
    }
}

/* Wedstrijd verwijderen (trainer) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_wedstrijd']) && $isTrainer) {
    $wedstrijd_id = $_POST['wedstrijd_id'] ?? null;
    
    if ($wedstrijd_id) {
        $wedstrijdenClass = new Wedstrijden($pdo);
        $wedstrijdenClass->delete($wedstrijd_id);
        header("Location: wedstrijden.php");
        exit;
    }
}

/* Tab bepalen */
$tab = $_GET['tab'] ?? 'upcoming';
$today = date('Y-m-d');

/* Wedstrijden ophalen */
if ($tab === 'old') {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM wedstrijden
         WHERE date < :today
         ORDER BY date DESC"
    );
    $title = "Oude wedstrijden";
} else {
    $stmt = $pdo->prepare(
        "SELECT id, date, titel 
         FROM wedstrijden
         WHERE date >= :today
         ORDER BY date ASC"
    );
    $title = "Aankomende wedstrijden";
}

$stmt->execute(['today' => $today]);
$wedstrijden = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wedstrijden</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="wedstrijden.php" class="active">Wedstrijden</a>
        <a href="trainingen.php">Trainingen</a>
        <a href="leden.php">Leden</a>
    </div>

    <div class="content">

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=upcoming" class="<?= $tab === 'upcoming' ? 'active' : '' ?>">
                Aankomende
            </a>

            <a href="?tab=old" class="<?= $tab === 'old' ? 'active' : '' ?>">
                Oude
            </a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;"><?= $title ?></h2>
            <?php if ($isTrainer): ?>
                <button onclick="document.getElementById('addWedstrijdModal').style.display='block'" class="btn-icon btn-plus" title="Wedstrijd toevoegen">
                    <span style="font-size: 20px;">+</span>
                </button>
            <?php endif; ?>
        </div>

        <table>
            <tr>
                <th>Datum</th>
                <th>Wedstrijd</th>
                <th>Status</th>
                <?php if ($isSpeler): ?>
                    <th>Actie</th>
                <?php elseif ($isTrainer): ?>
                    <th>Actie</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($wedstrijden as $wedstrijd): ?>

                <?php
                $status = 'geen status';

                if ($isSpeler && $speler_id) {
                    $stmt = $pdo->prepare(
                        "SELECT status 
                         FROM wedstrijd_aanwezigen
                         WHERE wedstrijd_id = :wedstrijd_id
                         AND speler_id = :speler_id
                         LIMIT 1"
                    );

                    $stmt->execute([
                        'wedstrijd_id' => $wedstrijd['id'],
                        'speler_id'    => $speler_id
                    ]);

                    $row = $stmt->fetch();
                    if ($row) {
                        $status = $row['status'];
                    }
                }
                ?>

                <tr>
                    <td><?= htmlspecialchars($wedstrijd['date']) ?></td>
                    <td><?= htmlspecialchars($wedstrijd['titel']) ?></td>
                    <td class="status-<?= $status ?>">
                        <?= ucfirst($status) ?>
                    </td>

                    <?php if ($isSpeler): ?>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="wedstrijd_id" value="<?= $wedstrijd['id'] ?>">
                                <input type="hidden" name="status" value="aanwezig">
                                <button class="btn-green">Aanwezig</button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="wedstrijd_id" value="<?= $wedstrijd['id'] ?>">
                                <input type="hidden" name="status" value="afwezig">
                                <button class="btn-red">Afwezig</button>
                            </form>
                        </td>
                    <?php elseif ($isTrainer): ?>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                                <input type="hidden" name="delete_wedstrijd" value="1">
                                <input type="hidden" name="wedstrijd_id" value="<?= $wedstrijd['id'] ?>">
                                <button type="submit" class="btn-icon btn-minus" title="Wedstrijd verwijderen">
                                    <span style="font-size: 18px;">−</span>
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        </table>

    </div>
</div>

<!-- Modal voor wedstrijd toevoegen -->
<?php if ($isTrainer): ?>
<div id="addWedstrijdModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 500px; max-width: 90%;">
        <h2>Wedstrijd Toevoegen</h2>
        <form method="POST">
            <input type="hidden" name="add_wedstrijd" value="1">
            <div style="margin-bottom: 15px;">
                <label>Titel:</label><br>
                <input type="text" name="titel" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Datum:</label><br>
                <input type="date" name="date" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Start tijd:</label><br>
                <input type="time" name="start" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Eind tijd:</label><br>
                <input type="time" name="end" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Beschrijving (optioneel):</label><br>
                <textarea name="description" style="width: 100%; padding: 8px; margin-top: 5px; min-height: 80px;"></textarea>
            </div>
            <div style="margin-bottom: 15px;">
                <label>Status:</label><br>
                <select name="status" style="width: 100%; padding: 8px; margin-top: 5px;">
                    <option value="gepland">Gepland</option>
                    <option value="afgelast">Afgelast</option>
                    <option value="voltooid">Voltooid</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addWedstrijdModal').style.display='none'" class="btn-red" style="padding: 8px 15px;">Annuleren</button>
                <button type="submit" class="btn-green" style="padding: 8px 15px;">Toevoegen</button>
            </div>
        </form>
    </div>
</div>

<script>
// Sluit modal bij klik buiten modal
window.onclick = function(event) {
    const modal = document.getElementById('addWedstrijdModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
<?php endif; ?>

</body>
</html>
