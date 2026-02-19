<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/trainingen.php';

if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'trainer' && $_SESSION['role'] !== 'admin')) {

    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();
$description = $_POST['description'] ?? '';
$trainingenClass = new Trainingen($pdo);

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $trainingenClass->delete($id);
    header("Location: trainertrainingen.php");
    exit;
}

/* SAVE (ADD / EDIT) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = $_POST['id'] ?? null;
    $date   = $_POST['date'];
    $start  = $_POST['start'];
    $end    = $_POST['end'];
    $titel  = $_POST['titel'];
    $status = $_POST['status'];

    if ($id) {
        $trainingenClass->update(
            $id,
            0, // training_aanwezigen_id (indien niet gebruikt → 0)
            $start,
            $end,
            $titel,
            $date,
            null,
            $status
        );
    } else {
    $trainingenClass->create(
        $start,
        $end,
        $titel,
        $date,
        $description,
        $status
    );

    header("Location: trainertrainingen.php");
    exit;
}
}

$trainingen = $trainingenClass->readAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer - Trainingen</title>
    <link rel="stylesheet" href="/Goalgetter/view/trainers/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Trainer Menu</h2>
        <a href="trainerdashboard.php">Dashboard</a>
        <a href="trainerwedstrijden.php">Wedstrijden</a>
        <a href="trainertrainingen.php" class="active">Trainingen</a>
        <a href="trainerleden.php">Leden</a>
    </div>

    <div class="content">

        <div class="header-row">
            <h2>Trainingen Beheren</h2>
            <button class="btn-add" onclick="openAddModal()">+</button>
        </div>

        <table class="wedstrijd-table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Titel</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>Einde</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trainingen as $training): ?>
                    <tr>
                        <td><?= htmlspecialchars($training['date']) ?></td>
                        <td><?= htmlspecialchars($training['titel']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($training['status'])) ?></td>
                        <td><?= htmlspecialchars($training['start']) ?></td>
                        <td><?= htmlspecialchars($training['end']) ?></td>
                        <td class="actie-buttons">

                            <button class="btn-edit"
                                onclick="openEditModal(
                                    '<?= $training['id'] ?>',
                                    '<?= $training['date'] ?>',
                                    '<?= $training['start'] ?>',
                                    '<?= $training['end'] ?>',
                                    '<?= htmlspecialchars($training['titel'], ENT_QUOTES) ?>',
                                    '<?= $training['status'] ?>'
                                )">
                                ✏️
                            </button>

                            <a href="trainertrainingen.php?delete=<?= $training['id'] ?>"
                               class="btn-delete"
                               onclick="return confirm('Weet je zeker dat je deze training wilt verwijderen?');">
                               🗑
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>
<!-- MODAL -->
<div id="trainingModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <h2 id="modalTitle">Training Toevoegen</h2>

        <form method="POST">
            <input type="hidden" name="id" id="trainingId">

            <label>Datum</label>
            <input type="date" name="date" id="trainingDate" required>

            <label>Starttijd</label>
            <input type="time" name="start" id="trainingStart" required>

            <label>Eindtijd</label>
            <input type="time" name="end" id="trainingEnd" required>

            <label>Titel</label>
            <input type="text" name="titel" id="trainingTitel" required>

            <label>Status</label>
            <select name="status" id="trainingStatus">
                <option value="gepland">Gepland</option>
                <option value="voltooid">Voltooid</option>
                <option value="geannuleerd">Geannuleerd</option>
            </select>

            <button type="submit" class="btn-save">Opslaan</button>
        </form>
    </div>
</div>
<script>
const modal = document.getElementById("trainingModal");

function openAddModal() {
    document.getElementById("modalTitle").innerText = "Training Toevoegen";
    document.getElementById("trainingId").value = "";
    document.getElementById("trainingDate").value = "";
    document.getElementById("trainingStart").value = "";
    document.getElementById("trainingEnd").value = "";
    document.getElementById("trainingTitel").value = "";
    document.getElementById("trainingStatus").value = "gepland";
    modal.style.display = "block";
}

function openEditModal(id, date, start, end, titel, status) {
    document.getElementById("modalTitle").innerText = "Training Bewerken";
    document.getElementById("trainingId").value = id;
    document.getElementById("trainingDate").value = date;
    document.getElementById("trainingStart").value = start;
    document.getElementById("trainingEnd").value = end;
    document.getElementById("trainingTitel").value = titel;
    document.getElementById("trainingStatus").value = status;
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
</body>
</html>