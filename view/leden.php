<?php
session_start();

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../app/speler.php';
require_once __DIR__ . '/../app/statistieken.php';
require_once __DIR__ . '/../app/trainer.php';
require_once __DIR__ . '/../app/person.php';
require_once __DIR__ . '/../app/user.php';

$connect = new Connect();
$pdo = $connect->pdo();

$isTrainer = isset($_SESSION['role']) && $_SESSION['role'] === 'trainer';
$trainer_id = null;
$club_id = null;

if ($isTrainer) {
    $stmt = $pdo->prepare(
        "SELECT id, club_id FROM trainer WHERE user_id = :user_id LIMIT 1"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $trainer = $stmt->fetch();
    $trainer_id = $trainer['id'] ?? null;
    $club_id = $trainer['club_id'] ?? null;
}

$spelerClass = new Speler($pdo);
$statistiekenClass = new Statistieken($pdo);

/* Statistieken bijwerken (trainer) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stats']) && $isTrainer) {
    $speler_id = $_POST['speler_id'] ?? null;
    $goals = $_POST['goals'] ?? 0;
    $win = $_POST['win'] ?? 0;
    $draw = $_POST['draw'] ?? 0;
    $loses = $_POST['loses'] ?? 0;

    if ($speler_id) {
        $speler = $spelerClass->read($speler_id);
        if ($speler) {
            $statistieken_id = $speler['statistieken_id'];
            $statistiekenClass->update($statistieken_id, $goals, $win, $draw, $loses);
            header("Location: leden.php");
            exit;
        }
    }
}

/* Speler verwijderen (trainer) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_speler']) && $isTrainer) {
    $speler_id = $_POST['speler_id'] ?? null;
    
    if ($speler_id) {
        $speler = $spelerClass->read($speler_id);
        if ($speler) {
            // Verwijder statistieken
            $statistiekenClass->delete($speler['statistieken_id']);
            // Verwijder speler
            $spelerClass->delete($speler_id);
            header("Location: leden.php");
            exit;
        }
    }
}

/* Speler toevoegen (trainer) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_speler']) && $isTrainer && $club_id) {
    $voornaam = $_POST['voornaam'] ?? '';
    $tussenvoegsels = $_POST['tussenvoegsels'] ?? '';
    $achternaam = $_POST['achternaam'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $lidnummer = $_POST['lidnummer'] ?? '';

    if ($voornaam && $achternaam && $email && $password && $lidnummer) {
        $spelerClass->registerspeler(
            $voornaam,
            $tussenvoegsels,
            $achternaam,
            $email,
            $password,
            $lidnummer,
            $club_id
        );
        header("Location: leden.php");
        exit;
    }
}

/* Spelers ophalen */
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.statistieken_id,
        p.voornaam,
        p.tussenvoegsels,
        p.achternaam,
        st.goals,
        st.win,
        st.draw,
        st.loses
    FROM speler s
    JOIN person p ON s.person_id = p.id
    JOIN statistieken st ON s.statistieken_id = st.id
    ORDER BY st.goals DESC
");

$spelers = $stmt->fetchAll();

/* Trainers ophalen */
$stmt = $pdo->query("
    SELECT p.voornaam, p.tussenvoegsels, p.achternaam
    FROM trainer t
    JOIN person p ON t.person_id = p.id
    ORDER BY p.achternaam ASC
");
$trainers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leden</title>
    <link rel="stylesheet" href="/Goalgetter/view/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="wedstrijden.php">Wedstrijden</a>
        <a href="trainingen.php">Trainingen</a>
        <a href="leden.php" class="active">Leden</a>
    </div>

    <div class="content">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Spelers</h2>
            <?php if ($isTrainer): ?>
                <button onclick="document.getElementById('addSpelerModal').style.display='block'" class="btn-icon btn-plus" title="Speler toevoegen">
                    <span style="font-size: 20px;">+</span>
                </button>
            <?php endif; ?>
        </div>

<table class="leden-table">
    <tr>
        <th>Naam</th>
        <th>Goals</th>
        <th>W</th>
        <th>D</th>
        <th>L</th>
        <th>Gemiddelde</th>
        <?php if ($isTrainer): ?>
            <th>Actie</th>
        <?php endif; ?>
    </tr>

    <?php foreach ($spelers as $speler): ?>

        <?php
        $gemiddelde = $spelerClass->getGemiddeldeGoals($speler['id']);
        ?>

        <tr>
            <td>
                <?= trim(
                    htmlspecialchars($speler['voornaam'] . ' ' .
                    $speler['tussenvoegsels'] . ' ' .
                    $speler['achternaam'])
                ) ?>
            </td>

            <td>
                <?php if ($isTrainer): ?>
                    <span id="goals_<?= $speler['id'] ?>"><?= $speler['goals'] ?></span>
                <?php else: ?>
                    <?= $speler['goals'] ?>
                <?php endif; ?>
            </td>

            <td>
                <?php if ($isTrainer): ?>
                    <span id="win_<?= $speler['id'] ?>"><?= $speler['win'] ?></span>
                <?php else: ?>
                    <?= $speler['win'] ?>
                <?php endif; ?>
            </td>

            <td>
                <?php if ($isTrainer): ?>
                    <span id="draw_<?= $speler['id'] ?>"><?= $speler['draw'] ?></span>
                <?php else: ?>
                    <?= $speler['draw'] ?>
                <?php endif; ?>
            </td>

            <td>
                <?php if ($isTrainer): ?>
                    <span id="loses_<?= $speler['id'] ?>"><?= $speler['loses'] ?></span>
                <?php else: ?>
                    <?= $speler['loses'] ?>
                <?php endif; ?>
            </td>

            <td>
                 <?= number_format($gemiddelde, 2) ?>
            </td>

            <?php if ($isTrainer): ?>
                <td>
                    <button onclick="openEditModal(<?= $speler['id'] ?>, <?= $speler['goals'] ?>, <?= $speler['win'] ?>, <?= $speler['draw'] ?>, <?= $speler['loses'] ?>)" class="btn-green" style="padding: 4px 8px; font-size: 12px;">Bewerken</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Weet je zeker dat je deze speler wilt verwijderen?');">
                        <input type="hidden" name="delete_speler" value="1">
                        <input type="hidden" name="speler_id" value="<?= $speler['id'] ?>">
                        <button type="submit" class="btn-icon btn-minus" title="Speler verwijderen" style="margin-left: 5px;">
                            <span style="font-size: 18px;">−</span>
                        </button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>

    <?php endforeach; ?>
</table>

        <h2>Trainers</h2>

        <ul class="leden-lijst">
            <?php foreach ($trainers as $trainer): ?>
                <li>
                    <?= htmlspecialchars($trainer['voornaam']) ?>
                    <?= htmlspecialchars($trainer['tussenvoegsels']) ?>
                    <?= htmlspecialchars($trainer['achternaam']) ?>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

</div>

<!-- Modal voor statistieken bewerken -->
<?php if ($isTrainer): ?>
<div id="editStatsModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: white; margin: 10% auto; padding: 20px; border-radius: 10px; width: 400px; max-width: 90%;">
        <h2>Statistieken Bewerken</h2>
        <form method="POST">
            <input type="hidden" name="update_stats" value="1">
            <input type="hidden" name="speler_id" id="edit_speler_id">
            <div style="margin-bottom: 15px;">
                <label>Goals:</label><br>
                <input type="number" name="goals" id="edit_goals" required min="0" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Winsten:</label><br>
                <input type="number" name="win" id="edit_win" required min="0" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Gelijk:</label><br>
                <input type="number" name="draw" id="edit_draw" required min="0" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Verliezen:</label><br>
                <input type="number" name="loses" id="edit_loses" required min="0" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('editStatsModal').style.display='none'" class="btn-red" style="padding: 8px 15px;">Annuleren</button>
                <button type="submit" class="btn-green" style="padding: 8px 15px;">Opslaan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal voor speler toevoegen -->
<div id="addSpelerModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: white; margin: 5% auto; padding: 20px; border-radius: 10px; width: 500px; max-width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2>Speler Toevoegen</h2>
        <form method="POST">
            <input type="hidden" name="add_speler" value="1">
            <div style="margin-bottom: 15px;">
                <label>Voornaam:</label><br>
                <input type="text" name="voornaam" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Tussenvoegsels (optioneel):</label><br>
                <input type="text" name="tussenvoegsels" style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Achternaam:</label><br>
                <input type="text" name="achternaam" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Email:</label><br>
                <input type="email" name="email" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Wachtwoord:</label><br>
                <input type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Lidnummer:</label><br>
                <input type="text" name="lidnummer" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('addSpelerModal').style.display='none'" class="btn-red" style="padding: 8px 15px;">Annuleren</button>
                <button type="submit" class="btn-green" style="padding: 8px 15px;">Toevoegen</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(spelerId, goals, win, draw, loses) {
    document.getElementById('edit_speler_id').value = spelerId;
    document.getElementById('edit_goals').value = goals;
    document.getElementById('edit_win').value = win;
    document.getElementById('edit_draw').value = draw;
    document.getElementById('edit_loses').value = loses;
    document.getElementById('editStatsModal').style.display = 'block';
}

// Sluit modals bij klik buiten modal
window.onclick = function(event) {
    const editModal = document.getElementById('editStatsModal');
    const addModal = document.getElementById('addSpelerModal');
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
}
</script>
<?php endif; ?>

</body>
</html>
