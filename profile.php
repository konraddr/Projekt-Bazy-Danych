<?php
session_start(); include 'db.php';
if(!isset($_SESSION['uid'])) header("Location: login.php");
$uid = $_SESSION['uid']; $msg = "";

// ANULOWANIE
if(isset($_POST['anuluj_rez'])){
    try {
        $stmt = $conn->prepare("SELECT anuluj_rezerwacje_klienta(:rid, :uid)");
        $stmt->execute([':rid'=>$_POST['rid'], ':uid'=>$uid]);
        $msg = $stmt->fetchColumn();
    } catch(Exception $e) { $msg = "Błąd: ".$e->getMessage(); }
}
// CRUD
if(isset($_POST['del'])){
    $conn->prepare("DELETE FROM uzytkownik WHERE id_uzytkownika=?")->execute([$uid]);
    session_destroy(); header("Location: index.php");
}
if(isset($_POST['upd'])){
    $conn->prepare("UPDATE uzytkownik SET imie=?, nazwisko=? WHERE id_uzytkownika=?")->execute([$_POST['imie'], $_POST['nazwisko'], $uid]);
    $msg="Zaktualizowano dane!";
}
$u = $conn->query("SELECT * FROM uzytkownik WHERE id_uzytkownika=".$uid)->fetch();
?>
<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav"><strong>Mój Profil</strong><a href="index.php">Wróć</a></div>
<div class="box">
    <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
    <h3>Moje Dane</h3>
    <form method="POST">
        Imię: <input type="text" name="imie" value="<?php echo $u['imie']; ?>">
        Nazwisko: <input type="text" name="nazwisko" value="<?php echo $u['nazwisko']; ?>">
        <button name="upd" class="btn">Zapisz</button>
    </form>
    <hr>
    <h3>Moje Rezerwacje</h3>
    <table>
        <tr><th>Hotel / Pokój</th><th>Termin</th><th>Cena (Rabat)</th><th>Status</th><th>Akcja</th></tr>
        <?php
        $sql = "SELECT r.*, p.nr_pokoj, h.nazwa, pl.rabat FROM rezerwacje r 
                JOIN pokoje p ON r.id_pokoj=p.id_pokoj 
                JOIN hotele h ON p.hotel_id=h.hotel_id
                LEFT JOIN platnosci pl ON r.id_rezerwacji=pl.id_rezerwacji 
                WHERE r.id_uzytkownika=$uid ORDER BY r.rezerwacja_od DESC";
        $res = $conn->query($sql);
        while($r=$res->fetch()){
            $cena = $r['cena_ostateczna']; $rabat = $r['rabat'] ?? 0; $start = $cena + $rabat;
            echo "<tr><td>{$r['nazwa']} {$r['nr_pokoj']}</td><td>{$r['rezerwacja_od']}<br>{$r['rezerwacja_do']}</td><td>";
            if($rabat>0) echo "<s style='color:red'>$start</s> <b>$cena PLN</b>"; else echo "<b>$cena PLN</b>";
            echo "</td><td>{$r['status']}</td><td>";
            if(in_array($r['status'], ['potwierdzona','oczekujaca']))
                echo "<form method='POST' onsubmit='return confirm(\"Anulować?\")'><input type='hidden' name='rid' value='{$r['id_rezerwacji']}'><button name='anuluj_rez' class='btn btn-red'>Anuluj</button></form>";
            else echo "-";
            echo "</td></tr>";
        }
        ?>
    </table>
    <hr>
    <form method="POST" onsubmit="return confirm('Usunąć konto?');"><button name="del" class="btn btn-red">USUŃ KONTO</button></form>
</div>
</body></html>