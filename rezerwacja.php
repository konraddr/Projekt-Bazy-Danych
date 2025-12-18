<?php
session_start(); include 'db.php';
if(isset($_POST['start'])) $_SESSION['temp'] = $_POST;
if(!isset($_SESSION['uid'])) { header("Location: login.php"); exit; }

$d = isset($_SESSION['temp']) ? $_SESSION['temp'] : null;
$msg = "";
$wycena = ""; // Zmienna na cenę

// 1. SYMULACJA CENY (Wykonuje się od razu po wejściu na stronę)
if($d) {
    try {
        $sql = "SELECT symuluj_cene_rezerwacji(:uid, :pid, :od, :do)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':uid'=>$_SESSION['uid'], ':pid'=>$d['pid'], ':od'=>$d['od'], ':do'=>$d['do']]);
        $wycena = $stmt->fetchColumn();
    } catch(Exception $e) { $wycena = "Błąd wyceny: ".$e->getMessage(); }
}

// 2. FINALIZACJA (Dopiero po kliknięciu przycisku)
if(isset($_POST['final']) && $d){
    try {
        $sql = "SELECT dokonaj_rezerwacji(:uid, :pid, :od, :do, :kids)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':uid'=>$_SESSION['uid'], ':pid'=>$d['pid'], ':od'=>$d['od'], ':do'=>$d['do'], ':kids'=>$d['dzieci']]);
        $msg = $stmt->fetchColumn();
        if(strpos($msg, 'Sukces')!==false) { unset($_SESSION['temp']); $d=null; }
    } catch(Exception $e) { $msg = "Błąd Bazy: ".$e->getMessage(); }
}
?>
<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav"><a href="index.php">Wróć</a></div>
<div class="box">
    <?php if($msg): ?>
        <h3>Status Operacji</h3>
        <div class="msg"><?php echo $msg; ?></div>
        <a href="index.php">Wróć na stronę główną</a>
        
    <?php elseif($d): ?>
        <h3>Podsumowanie i Płatność</h3>
        <p>Pokój ID: <b><?php echo $d['pid']; ?></b></p>
        <p>Termin: <?php echo $d['od']; ?> - <?php echo $d['do']; ?></p>
        <p>Dzieci: <?php echo $d['dzieci']; ?></p>
        
        <hr>
        <div style="background:#eef; padding:15px; border-radius:5px; margin-bottom:15px;">
            <p>Szacowany koszt pobytu:</p>
            <div style="font-size:1.2em; color:#003580;">
                <?php echo $wycena; ?>
            </div>
        </div>

        <form method="POST">
            <button name="final" class="btn" style="width:100%">POTWIERDZAM I PŁACĘ</button>
        </form>
        
    <?php else: ?>
        <p>Brak danych. <a href="index.php">Wybierz pokój</a>.</p>
    <?php endif; ?>
</div>
</body></html>