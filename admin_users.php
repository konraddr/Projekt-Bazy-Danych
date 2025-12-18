<?php
session_start(); include 'db.php';

// Ochrona: Tylko admin
if ($_SESSION['rola'] != 'admin') die("Brak dostępu");

// 1. OBSŁUGA BLOKOWANIA / ODBLOKOWYWANIA
if (isset($_POST['toggle_block_id'])) {
    $stmt = $conn->prepare("UPDATE uzytkownik SET czy_zablokowany = NOT czy_zablokowany WHERE id_uzytkownika = ?");
    $stmt->execute([$_POST['toggle_block_id']]);
}

// 2. OBSŁUGA USUWANIA
if (isset($_POST['delete_user_id'])) {
    $stmt = $conn->prepare("DELETE FROM uzytkownik WHERE id_uzytkownika = ?");
    $stmt->execute([$_POST['delete_user_id']]);
}

// 3. PRZYPISANIE MANAGERA DO HOTELU (NOWOŚĆ)
if (isset($_POST['assign_hotel'])) {
    $uid = $_POST['user_id'];
    $hid = $_POST['hotel_id'];
    // Jeśli wybrano "Brak" (value="NULL"), ustawiamy NULL w bazie
    if($hid == "NULL") $hid = null;
    
    $stmt = $conn->prepare("UPDATE uzytkownik SET manager_hotel_id = ? WHERE id_uzytkownika = ?");
    $stmt->execute([$hid, $uid]);
    echo "<script>alert('Zaktualizowano przypisanie hotelu!');</script>";
}

// Pobranie listy hoteli do dropdowna
$hotele = $conn->query("SELECT hotel_id, nazwa FROM hotele")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>

<div class="nav">
    <strong>Zarządzanie Użytkownikami</strong>
    <a href="admin.php">Wróć do Panelu</a>
</div>

<div class="box" style="max-width:1100px;">
    <h3>Lista Użytkowników</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Użytkownik</th>
            <th>Rola</th>
            <th>Zarządza Hotelem (Dla Managera)</th> <th>Status</th>
            <th>Akcje</th>
        </tr>
        <?php
        // Pobieramy użytkowników + nazwę hotelu którym zarządzają (LEFT JOIN)
        $sql = "SELECT u.*, h.nazwa as hotel_nazwa 
                FROM uzytkownik u 
                LEFT JOIN hotele h ON u.manager_hotel_id = h.hotel_id 
                ORDER BY u.id_uzytkownika ASC";
        $users = $conn->query($sql);
        
        while ($u = $users->fetch(PDO::FETCH_ASSOC)) {
            // Status wizualny
            if ($u['czy_zablokowany']) {
                $status_text = "<span style='color:red; font-weight:bold'>ZABLOKOWANY</span>";
                $btn_text = "Odblokuj"; $btn_color = "background: green;";
            } else {
                $status_text = "<span style='color:green'>Aktywny</span>";
                $btn_text = "Zablokuj"; $btn_color = "background: orange; color: black;";
            }

            echo "<tr>";
            echo "<td>{$u['id_uzytkownika']}</td>";
            echo "<td>{$u['imie']} {$u['nazwisko']}<br><small>{$u['email']}</small></td>";
            echo "<td><b>{$u['rola']}</b></td>";
            
            // KOLUMNA PRZYPISYWANIA HOTELU
            echo "<td>";
            if($u['rola'] == 'manager') {
                echo "<form method='POST' style='display:flex; gap:5px;'>
                        <input type='hidden' name='user_id' value='{$u['id_uzytkownika']}'>
                        <select name='hotel_id' style='padding:5px; margin:0;'>
                            <option value='NULL'>-- Brak --</option>";
                            foreach($hotele as $h) {
                                $selected = ($u['manager_hotel_id'] == $h['hotel_id']) ? 'selected' : '';
                                echo "<option value='{$h['hotel_id']}' $selected>{$h['nazwa']}</option>";
                            }
                echo   "</select>
                        <button name='assign_hotel' class='btn' style='padding:5px; font-size:12px;'>Zapisz</button>
                      </form>";
            } else {
                echo "<span style='color:#ccc'>-</span>";
            }
            echo "</td>";

            echo "<td>$status_text</td>";
            
            echo "<td style='display:flex; gap:5px;'>";
            if ($u['rola'] != 'admin') {
                echo "<form method='POST'><input type='hidden' name='toggle_block_id' value='{$u['id_uzytkownika']}'><button class='btn' style='$btn_color padding:5px 10px; font-size:12px;'>$btn_text</button></form>";
                echo "<form method='POST' onsubmit='return confirm(\"Usunąć?\");'><input type='hidden' name='delete_user_id' value='{$u['id_uzytkownika']}'><button class='btn btn-red' style='padding:5px 10px; font-size:12px;'>Usuń</button></form>";
            } else {
                echo "ADMIN";
            }
            echo "</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>