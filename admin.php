<?php
session_start(); include 'db.php';

// Sprawdzamy czy to admin LUB manager
if(!in_array($_SESSION['rola'], ['admin', 'manager'])) die("Brak dostępu");

$hid = $_SESSION['hid']; // ID hotelu menadżera
$rola = $_SESSION['rola'];

// 1. DODAWANIE NOWEGO HOTELU (Tylko Admin)
if(isset($_POST['add_hotel']) && $rola == 'admin'){
    try {
        $stmt = $conn->prepare("INSERT INTO hotele (nazwa, miasto, mnoznik_lato, mnoznik_zima) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nazwa'], $_POST['miasto'], $_POST['lato'], $_POST['zima']]);
        echo "<script>alert('Dodano nowy hotel!');</script>";
    } catch(Exception $e) { echo "<script>alert('Błąd: ".$e->getMessage()."');</script>"; }
}

// 2. ZMIANA STATUSU REZERWACJI
if(isset($_POST['zmien_status'])){
    $conn->prepare("UPDATE rezerwacje SET status=? WHERE id_rezerwacji=?")->execute([$_POST['st'], $_POST['rid']]);
    if($_POST['st']=='anulowana') $conn->prepare("UPDATE platnosci SET status='zwrocona' WHERE id_rezerwacji=?")->execute([$_POST['rid']]);
    if($_POST['st']=='zrealizowana') $conn->prepare("UPDATE platnosci SET status='oplacona' WHERE id_rezerwacji=?")->execute([$_POST['rid']]);
}
?>
<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav">
    <strong>PANEL <?php echo strtoupper($rola); ?></strong> 
    <a href="admin_users.php">Użytkownicy</a> 
    <a href="index.php">Widok Klienta</a>
</div>

<div class="box" style="max-width:1100px">
    
    <h3>1. Lista Hoteli (Zarządzaj Mnożnikami i Pokojami)</h3>
    
    <?php if($rola == 'admin'): ?>
    <form method="POST" style="background:#eef; padding:15px; border-radius:5px; display:flex; gap:10px; margin-bottom:15px; align-items:center;">
        <b>+ Nowy:</b>
        <input type="text" name="nazwa" placeholder="Nazwa" required style="width:150px; margin:0;">
        <input type="text" name="miasto" placeholder="Miasto" required style="width:120px; margin:0;">
        <input type="number" step="0.01" name="lato" placeholder="Lato (1.2)" required style="width:90px; margin:0;">
        <input type="number" step="0.01" name="zima" placeholder="Zima (0.9)" required style="width:90px; margin:0;">
        <button name="add_hotel" class="btn btn-green" style="margin:0;">Dodaj</button>
    </form>
    <?php endif; ?>

    <table>
        <tr><th>ID</th><th>Nazwa</th><th>Miasto</th><th>Mnożnik Lato</th><th>Mnożnik Zima</th><th>Akcja</th></tr>
        <?php
        $sql = "SELECT * FROM hotele";
        if($rola == 'manager') $sql .= " WHERE hotel_id = $hid"; // Manager widzi tylko swój
        $hotele = $conn->query($sql);
        
        while($h=$hotele->fetch()){
            echo "<tr>
                <td>{$h['hotel_id']}</td>
                <td><b>{$h['nazwa']}</b></td>
                <td>{$h['miasto']}</td>
                <td style='color:orange'>x{$h['mnoznik_lato']}</td>
                <td style='color:blue'>x{$h['mnoznik_zima']}</td>
                <td>
                    <a href='admin_hotel_details.php?hid={$h['hotel_id']}' class='btn' style='text-decoration:none; display:block; text-align:center;'>
                        Zarządzaj Pokojami & Edytuj
                    </a>
                </td>
            </tr>";
        }
        ?>
    </table>
    
    <hr>

    <h3>2. Ostatnie Rezerwacje</h3>
    <table>
        <tr><th>ID</th><th>Hotel</th><th>Klient</th><th>Termin</th><th>Status</th><th>Akcja</th></tr>
        <?php
        $sql = "SELECT r.*, u.email, h.nazwa as hotel FROM rezerwacje r 
                JOIN uzytkownik u ON r.id_uzytkownika=u.id_uzytkownika 
                JOIN pokoje p ON r.id_pokoj=p.id_pokoj JOIN hotele h ON p.hotel_id=h.hotel_id";
        if($rola == 'manager') $sql .= " WHERE h.hotel_id = $hid";
        $sql .= " ORDER BY r.id_rezerwacji DESC LIMIT 10";
        
        $res = $conn->query($sql);
        while($r=$res->fetch()){
            echo "<tr>
                <td>{$r['id_rezerwacji']}</td>
                <td>{$r['hotel']}</td>
                <td>{$r['email']}</td>
                <td>{$r['rezerwacja_od']}<br>{$r['rezerwacja_do']}</td>
                <td><b>{$r['status']}</b></td>
                <td><form method='POST'><input type='hidden' name='rid' value='{$r['id_rezerwacji']}'>
                <select name='st'><option>potwierdzona</option><option>anulowana</option><option>zrealizowana</option></select>
                <button name='zmien_status' class='btn' style='padding:5px;'>OK</button></form></td>
            </tr>";
        }
        ?>
    </table>

    <h3>3. Raport Finansowy</h3>
    <table>
        <tr><th>Hotel</th><th>Typ</th><th>Ilość</th><th>Zysk</th></tr>
        <?php
        $rap = $conn->query("SELECT * FROM raport_przychodow");
        $my_hotel_name = ($rola == 'manager') ? $conn->query("SELECT nazwa FROM hotele WHERE hotel_id=$hid")->fetchColumn() : "";
        while($r=$rap->fetch()){
            if($rola == 'manager' && $r['hotel'] != $my_hotel_name) continue;
            echo "<tr><td>{$r['hotel']}</td><td>{$r['typ']}</td><td>{$r['liczba_rezerwacji']}</td><td style='color:green'><b>{$r['laczny_zysk']} PLN</b></td></tr>"; 
        }
        ?>
    </table>
</div>
</body></html>