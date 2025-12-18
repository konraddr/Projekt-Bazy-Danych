<?php
session_start(); include 'db.php';
if(isset($_POST['start'])) $_SESSION['temp'] = $_POST;
if(!isset($_SESSION['uid'])) { header("Location: login.php"); exit; }

$d = isset($_SESSION['temp']) ? $_SESSION['temp'] : null; $msg = "";

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
        <div class="msg"><?php echo $msg; ?></div><a href="index.php">Wróć na główną</a>
    <?php elseif($d): ?>
        <h3>Potwierdź dane</h3>
        <p>Pokój ID: <?php echo $d['pid']; ?></p>
        <p>Data: <?php echo $d['od']; ?> - <?php echo $d['do']; ?></p>
        <form method="POST"><button name="final" class="btn" style="width:100%">POTWIERDZAM I PŁACĘ</button></form>
    <?php else: ?>
        <p>Brak danych.</p>
    <?php endif; ?>
</div>
</body></html>