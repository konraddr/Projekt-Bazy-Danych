<?php
session_start(); include 'db.php';
if($_SESSION['rola']!='admin') die("Brak dostępu");

if(isset($_POST['toggle'])){
    $conn->prepare("UPDATE uzytkownik SET czy_zablokowany = NOT czy_zablokowany WHERE id_uzytkownika=?")->execute([$_POST['uid']]);
}
if(isset($_POST['del'])){
    $conn->prepare("DELETE FROM uzytkownik WHERE id_uzytkownika=?")->execute([$_POST['uid']]);
}
?>
<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav"><strong>Użytkownicy</strong><a href="admin.php">Wróć</a></div>
<div class="box">
    <table>
        <tr><th>Email</th><th>Rola</th><th>Status</th><th>Akcja</th></tr>
        <?php
        $users = $conn->query("SELECT * FROM uzytkownik ORDER BY id_uzytkownika");
        while($u=$users->fetch()){
            $st = $u['czy_zablokowany'] ? "<b style='color:red'>ZABLOKOWANY</b>" : "Aktywny";
            echo "<tr><td>{$u['email']}</td><td>{$u['rola']}</td><td>$st</td><td>";
            if($u['rola']!='admin'){
                echo "<form method='POST' style='display:inline'><input type='hidden' name='uid' value='{$u['id_uzytkownika']}'><button name='toggle' class='btn'>Blok/Odblok</button></form> ";
                echo "<form method='POST' style='display:inline' onsubmit='return confirm(\"Usunąć?\")'><input type='hidden' name='uid' value='{$u['id_uzytkownika']}'><button name='del' class='btn btn-red'>Usuń</button></form>";
            } else echo "ADMIN";
            echo "</td></tr>";
        }
        ?>
    </table>
</div>
</body></html>