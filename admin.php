<?php
session_start(); include 'db.php';
if(!in_array($_SESSION['rola'], ['admin', 'manager'])) die("Brak dostępu");
$hid = $_SESSION['hid'];

if(isset($_POST['zmien_status'])){
    $conn->prepare("UPDATE rezerwacje SET status=? WHERE id_rezerwacji=?")->execute([$_POST['st'], $_POST['rid']]);
    if($_POST['st']=='anulowana') $conn->prepare("UPDATE platnosci SET status='zwrocona' WHERE id_rezerwacji=?")->execute([$_POST['rid']]);
}
?>
<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav"><strong>PANEL <?php echo strtoupper($_SESSION['rola']); ?></strong> <a href="admin_users.php">Użytkownicy</a> <a href="index.php">Widok Klienta</a></div>

<div class="box">
    <h3>1. Zarządzanie Rezerwacjami</h3>
    <table>
        <tr><th>ID</th><th>Hotel</th><th>Klient</th><th>Status</th><th>Akcja</th></tr>
        <?php
        $sql = "SELECT r.*, u.email, h.nazwa as hotel FROM rezerwacje r 
                JOIN uzytkownik u ON r.id_uzytkownika=u.id_uzytkownika 
                JOIN pokoje p ON r.id_pokoj=p.id_pokoj JOIN hotele h ON p.hotel_id=h.hotel_id";
        if($_SESSION['rola']=='manager') $sql .= " WHERE h.hotel_id = $hid";
        $sql .= " ORDER BY r.id_rezerwacji DESC LIMIT 10";
        
        $res = $conn->query($sql);
        while($r=$res->fetch()){
            echo "<tr><td>{$r['id_rezerwacji']}</td><td>{$r['hotel']}</td><td>{$r['email']}</td><td><b>{$r['status']}</b></td>
            <td><form method='POST'><input type='hidden' name='rid' value='{$r['id_rezerwacji']}'>
            <select name='st'><option>potwierdzona</option><option>anulowana</option><option>zrealizowana</option></select>
            <button name='zmien_status' class='btn'>Zmień</button></form></td></tr>";
        }
        ?>
    </table>

    <h3>2. Logi SQL (Relacyjne)</h3>
    <table>
        <tr><th>ID</th><th>Stary</th><th>Nowy</th><th>Msg</th></tr>
        <?php
        $logs = $conn->query("SELECT * FROM logi_systemowe ORDER BY id_logu DESC LIMIT 5");
        while($l=$logs->fetch()){ echo "<tr><td>{$l['id_logu']}</td><td>{$l['stary_status']}</td><td>{$l['nowy_status']}</td><td>{$l['komunikat']}</td></tr>"; }
        ?>
    </table>

    <h3>3. Logi NoSQL (JSONB) - Wymóg Projektowy</h3>
    <table>
        <tr><th>Data</th><th>Dokument JSON</th></tr>
        <?php
        $logs_no = $conn->query("SELECT * FROM logi_nosql ORDER BY id DESC LIMIT 3");
        while($l=$logs_no->fetch()){ echo "<tr><td>{$l['data_zdarzenia']}</td><td><small>{$l['dokument_json']}</small></td></tr>"; }
        ?>
    </table>
    
    <h3>4. Raport Finansowy</h3>
    <table>
        <tr><th>Hotel</th><th>Typ</th><th>Ilość</th><th>Zysk</th></tr>
        <?php
        $rap = $conn->query("SELECT * FROM raport_przychodow");
        while($r=$rap->fetch()){
            if($_SESSION['rola']=='manager'){ 
                // Filtr PHP 
                $my_hotel = $conn->query("SELECT nazwa FROM hotele WHERE hotel_id=$hid")->fetchColumn();
                if($r['hotel']!=$my_hotel) continue;
            }
            echo "<tr><td>{$r['hotel']}</td><td>{$r['typ']}</td><td>{$r['liczba_rezerwacji']}</td><td>{$r['laczny_zysk']} PLN</td></tr>"; 
        }
        ?>
    </table>
</div>
</body></html>