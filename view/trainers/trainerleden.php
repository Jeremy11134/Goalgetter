<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/speler.php';

if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'trainer' && $_SESSION['role'] !== 'admin')) {

    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();
$spelerClass = new Speler($pdo);

/* DELETE speler */
if (isset($_GET['delete'])) {
    $spelerClass->delete((int)$_GET['delete']);
    header("Location: trainerleden.php");
    exit;
}

/* ADD speler */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $voornaam   = $_POST['voornaam'] ?? '';
    $tussen     = $_POST['tussenvoegsels'] ?? '';
    $achternaam = $_POST['achternaam'] ?? '';
    $email      = $_POST['email'] ?? '';
    $password   = $_POST['password'] ?? '';
    $lidnummer  = $_POST['lidnummer'] ?? '';
    $club_id    = 1; // tijdelijk vaste club (later dynamisch)

    if ($voornaam && $achternaam && $email && $password && $lidnummer) {
        $spelerClass->registerspeler(
            $voornaam,
            $tussen,
            $achternaam,
            $email,
            $password,
            $lidnummer,
            $club_id
        );
    }

    header("Location: trainerleden.php");
    exit;
}

/* Spelers ophalen */
$stmt = $pdo->query("
    SELECT 
        s.id,
        p.voornaam,
        p.tussenvoegsels,
        p.achternaam,
        st.id AS statistieken_id,
        st.goals,
        st.win,
        st.draw,
        st.loses
    FROM speler s
    JOIN person p ON s.person_id = p.id
    JOIN statistieken st ON s.statistieken_id = st.id
    ORDER BY p.achternaam ASC
");
$spelers = $stmt->fetchAll();
/* STATISTIEKEN UPDATE */
if (isset($_POST['update_stats'])) {

    $stat_id = (int)$_POST['stat_id'];
    $goals   = (int)$_POST['goals'];
    $win     = (int)$_POST['win'];
    $draw    = (int)$_POST['draw'];
    $loses   = (int)$_POST['loses'];

    $stmt = $pdo->prepare("
        UPDATE statistieken
        SET goals = :goals,
            win = :win,
            draw = :draw,
            loses = :loses
        WHERE id = :id
    ");

    $stmt->execute([
        'goals' => $goals,
        'win'   => $win,
        'draw'  => $draw,
        'loses' => $loses,
        'id'    => $stat_id
    ]);

    header("Location: trainerleden.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trainer - Leden</title>
    <link rel="stylesheet" href="/Goalgetter/view/trainers/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Trainer Menu</h2>
        <a href="trainerdashboard.php">Dashboard</a>
        <a href="trainerwedstrijden.php">Wedstrijden</a>
        <a href="trainertrainingen.php">Trainingen</a>
        <a href="trainerleden.php" class="active">Leden</a>
    </div>

    <div class="content">

        <div class="header-row">
            <h2>Spelers Beheren</h2>
            <button class="btn-add" onclick="openModal()">+</button>
        </div>

<table class="stat-table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Goals</th>
            <th>W</th>
            <th>D</th>
            <th>L</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($spelers as $speler): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($speler['voornaam']) ?>
                    <?= htmlspecialchars($speler['tussenvoegsels']) ?>
                    <?= htmlspecialchars($speler['achternaam']) ?>
                </td>

                <td><?= $speler['goals'] ?></td>
                <td><?= $speler['win'] ?></td>
                <td><?= $speler['draw'] ?></td>
                <td><?= $speler['loses'] ?></td>

                <td class="actie-buttons">

                    <button class="btn-edit"
                        onclick="openStatsModal(
                            '<?= $speler['statistieken_id'] ?>',
                            '<?= $speler['goals'] ?>',
                            '<?= $speler['win'] ?>',
                            '<?= $speler['draw'] ?>',
                            '<?= $speler['loses'] ?>'
                        )">
                        ✏️
                    </button>

                    <a href="trainerleden.php?delete=<?= $speler['id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Weet je zeker dat je deze speler wilt verwijderen?');">
                       🗑
                    </a>

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- MODAL -->
<div id="spelerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Speler Toevoegen</h2>

        <form method="POST">
            <input type="text" name="voornaam" placeholder="Voornaam" required>
            <input type="text" name="tussenvoegsels" placeholder="Tussenvoegsels">
            <input type="text" name="achternaam" placeholder="Achternaam" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Wachtwoord" required>
            <input type="text" name="lidnummer" placeholder="Lidnummer" required>

            <button type="submit" class="btn-save">Opslaan</button>
        </form>
    </div>
</div>

<div id="statsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeStatsModal()">&times;</span>
        <h2>Statistieken Bewerken</h2>

        <form method="POST">
            <input type="hidden" name="stat_id" id="statId">

            <label>Goals</label>
            <input type="number" name="goals" id="statGoals" min="0" required>

            <label>Wins</label>
            <input type="number" name="win" id="statWin" min="0" required>

            <label>Draws</label>
            <input type="number" name="draw" id="statDraw" min="0" required>

            <label>Losses</label>
            <input type="number" name="loses" id="statLoses" min="0" required>

            <button type="submit" name="update_stats" class="btn-save">
                Opslaan
            </button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById("spelerModal");

function openModal() {
    modal.style.display = "block";
}

function closeModal() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<script>
const statsModal = document.getElementById("statsModal");

function openStatsModal(id, goals, win, draw, loses) {
    document.getElementById("statId").value = id;
    document.getElementById("statGoals").value = goals;
    document.getElementById("statWin").value = win;
    document.getElementById("statDraw").value = draw;
    document.getElementById("statLoses").value = loses;
    statsModal.style.display = "block";
}

function closeStatsModal() {
    statsModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == statsModal) {
        statsModal.style.display = "none";
    }
}
</script>

</body>
</html>
