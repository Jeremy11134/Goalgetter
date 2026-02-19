<?php
session_start();

require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . '/../../app/wedstrijden.php';

if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'trainer' && $_SESSION['role'] !== 'club_admin')) {

    header("Location: ../dashboard.php");
    exit;
}

$connect = new Connect();
$pdo = $connect->pdo();

$wedstrijdenClass = new Wedstrijden($pdo);

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $wedstrijdenClass->delete($id);
    header("Location: trainerwedstrijden.php");
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
        $wedstrijdenClass->update(
            $id,
            $start,
            $end,
            $titel,
            $date,
            $status
        );
    } else {
        $wedstrijdenClass->create(
            $start,
            $end,
            $titel,
            $date,
            $status
        );
    }

    header("Location: trainerwedstrijden.php");
    exit;
}

/* Alle wedstrijden ophalen */
$wedstrijden = $wedstrijdenClass->readAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trainer - Wedstrijden</title>
    <link rel="stylesheet" href="/Goalgetter/view/trainers/style.css">
</head>
<body>

<div class="layout">

    <div class="sidebar">
        <h2>Trainer Menu</h2>
        <a href="trainerdashboard.php">Dashboard</a>
        <a href="trainerwedstrijden.php" class="active">Wedstrijden</a>
        <a href="trainertrainingen.php">Trainingen</a>
        <a href="trainerleden.php">Leden</a>
    </div>

    <div class="content">

        <div class="header-row">
            <h2>Wedstrijden Beheren</h2>
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
        <?php foreach ($wedstrijden as $wedstrijd): ?>
            <tr>
                <td><?= htmlspecialchars($wedstrijd['date']) ?></td>
                <td><?= htmlspecialchars($wedstrijd['titel']) ?></td>
                <td><?= ucfirst(htmlspecialchars($wedstrijd['status'])) ?></td>
                <td><?= htmlspecialchars($wedstrijd['start']) ?></td>
                <td><?= htmlspecialchars($wedstrijd['end']) ?></td>
                <td class="actie-buttons">

                    <button class="btn-edit"
                        onclick="openEditModal(
                            '<?= $wedstrijd['id'] ?>',
                            '<?= $wedstrijd['date'] ?>',
                            '<?= $wedstrijd['start'] ?>',
                            '<?= $wedstrijd['end'] ?>',
                            '<?= htmlspecialchars($wedstrijd['titel'], ENT_QUOTES) ?>',
                            '<?= $wedstrijd['status'] ?>'
                        )">
                        ✏️
                    </button>

                    <a href="trainerwedstrijden.php?delete=<?= $wedstrijd['id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
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
<div id="wedstrijdModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>

        <h2 id="modalTitle">Wedstrijd Toevoegen</h2>

        <form method="POST">
            <input type="hidden" name="id" id="wedstrijdId">

            <label>Datum</label>
            <input type="date" name="date" id="wedstrijdDate" required>

            <label>Starttijd</label>
            <input type="time" name="start" id="wedstrijdStart" required>

            <label>Eindtijd</label>
            <input type="time" name="end" id="wedstrijdEnd" required>

            <label>Titel</label>
            <input type="text" name="titel" id="wedstrijdTitel" required>

            <label>Status</label>
            <select name="status" id="wedstrijdStatus">
                <option value="gepland">Gepland</option>
                <option value="gespeeld">Gespeeld</option>
                <option value="geannuleerd">Geannuleerd</option>
            </select>

            <button type="submit" class="btn-save">Opslaan</button>
        </form>
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("wedstrijdModal");
    const closeBtn = document.querySelector(".close");

    window.openAddModal = function () {
        document.getElementById("modalTitle").innerText = "Wedstrijd Toevoegen";
        document.getElementById("wedstrijdId").value = "";
        document.getElementById("wedstrijdDate").value = "";
        document.getElementById("wedstrijdStart").value = "";
        document.getElementById("wedstrijdEnd").value = "";
        document.getElementById("wedstrijdTitel").value = "";
        document.getElementById("wedstrijdStatus").value = "gepland";
        modal.style.display = "block";
    };

    window.openEditModal = function (id, date, start, end, titel, status) {
        document.getElementById("modalTitle").innerText = "Wedstrijd Bewerken";
        document.getElementById("wedstrijdId").value = id;
        document.getElementById("wedstrijdDate").value = date;
        document.getElementById("wedstrijdStart").value = start;
        document.getElementById("wedstrijdEnd").value = end;
        document.getElementById("wedstrijdTitel").value = titel;
        document.getElementById("wedstrijdStatus").value = status;
        modal.style.display = "block";
    };

    if (closeBtn) {
        closeBtn.onclick = function () {
            modal.style.display = "none";
        };
    }

    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

});

</script>

</body>
</html>
