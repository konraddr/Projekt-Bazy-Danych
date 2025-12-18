<?php session_start(); include 'db.php'; 

// POBIERANIE DANYCH DO LIST ROZWIJANYCH
$hotele = $conn->query("SELECT * FROM hotele")->fetchAll();
$typy = $conn->query("SELECT DISTINCT typ_pokoju FROM pokoje")->fetchAll();

// LOGIKA WYSZUKIWANIA
$where = "1=1"; 
$params = [];

if(!empty($_GET['h'])) { $where .= " AND p.hotel_id = ?"; $params[] = $_GET['h']; }
if(!empty($_GET['t'])) { $where .= " AND p.typ_pokoju = ?"; $params[] = $_GET['t']; }
if(!empty($_GET['os'])) { $where .= " AND p.pojemnosc >= ?"; $params[] = $_GET['os']; }
if(!empty($_GET['dz'])) { $where .= " AND p.max_dzieci >= ?"; $params[] = $_GET['dz']; }

$sql = "SELECT p.*, h.nazwa as hotel_nazwa, h.miasto 
        FROM pokoje p 
        JOIN hotele h ON p.hotel_id = h.hotel_id 
        WHERE $where 
        ORDER BY h.nazwa, p.cena_doba";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$pokoje = $stmt->fetchAll();
?>

<!DOCTYPE html><html><head><link rel="stylesheet" href="style.css"></head><body>
<div class="nav">
    <strong>System Rezerwacji</strong>
    <div>
        <?php if(isset($_SESSION['uid'])): ?>
            Witaj, <?php echo $_SESSION['email']; ?> | <a href="profile.php">Mój Profil</a>
            <?php if(in_array($_SESSION['rola'], ['admin','manager'])) echo ' | <a href="admin.php">PANEL</a>'; ?>
            | <a href="logout.php">Wyloguj</a>
        <?php else: ?>
            <a href="login.php">Zaloguj</a> | <a href="register.php">Rejestracja</a>
        <?php endif; ?>
    </div>
</div>

<div class="box" style="max-width:1100px"> <form method="GET" style="background: #003580; padding: 20px; border-radius: 8px; display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1;">
            <label style="color:white; font-size:12px;">Hotel:</label>
            <select name="h" style="margin:0;">
                <option value="">Wszystkie hotele</option>
                <?php foreach($hotele as $h): ?>
                    <option value="<?php echo $h['hotel_id']; ?>" <?php if(isset($_GET['h']) && $_GET['h']==$h['hotel_id']) echo 'selected'; ?>>
                        <?php echo $h['nazwa']; ?> (<?php echo $h['miasto']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="width: 100px;">
            <label style="color:white; font-size:12px;">Osób:</label>
            <select name="os" style="margin:0;">
                <option value="">Min</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4+</option>
            </select>
        </div>
        <div style="width: 100px;">
            <label style="color:white; font-size:12px;">Dzieci:</label>
            <select name="dz" style="margin:0;">
                <option value="">Min</option>
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2+</option>
            </select>
        </div>
        <div style="width: 150px;">
            <label style="color:white; font-size:12px;">Typ:</label>
            <select name="t" style="margin:0;">
                <option value="">Wszystkie</option>
                <?php foreach($typy as $t): ?>
                    <option value="<?php echo $t['typ_pokoju']; ?>"><?php echo $t['typ_pokoju']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-green" style="height: 42px; margin:0;">SZUKAJ</button>
    </form>

    <hr>

    <h2>Znalezione Pokoje (<?php echo count($pokoje); ?>)</h2>
    <table>
        <tr><th>Hotel</th><th>Pokój</th><th>Szczegóły</th><th>Cena/noc</th><th>Wybierz Termin i Rezerwuj</th></tr>
        <?php foreach($pokoje as $row): ?>
        <tr>
            <td>
                <b><?php echo $row['hotel_nazwa']; ?></b><br>
                <small><?php echo $row['miasto']; ?></small>
            </td>
            <td>Nr <b><?php echo $row['nr_pokoj']; ?></b></td>
            <td>
                Typ: <?php echo $row['typ_pokoju']; ?><br>
                Osoby: <?php echo $row['pojemnosc']; ?><br>
                Max Dzieci: <?php echo $row['max_dzieci']; ?>
            </td>
            <td><b><?php echo $row['cena_doba']; ?> PLN</b></td>
            
            <td style="min-width: 320px;">
                <form action="rezerwacja.php" method="POST">
                    <div style="display:flex; gap:5px; margin-bottom:5px;">
                        <div>
                            <small>Od:</small><br>
                            <input type="date" name="od" value="<?php echo date('Y-m-d'); ?>" required style="width:130px; padding:5px;">
                        </div>
                        <div>
                            <small>Do:</small><br>
                            <input type="date" name="do" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required style="width:130px; padding:5px;">
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:5px; align-items:center; margin-bottom:5px;">
                        <small>Dzieci:</small>
                        <input type="number" name="dzieci" value="0" min="0" max="<?php echo $row['max_dzieci']; ?>" style="width:60px; padding:5px;">
                        <input type="hidden" name="pid" value="<?php echo $row['id_pokoj']; ?>">
                        
                        <button type="submit" name="start" class="btn" style="flex:1;">Rezerwuj</button>
                    </div>
                </form>

                <a href="terminy.php?pid=<?php echo $row['id_pokoj']; ?>" class="btn" style="background:#6c757d; text-decoration:none; display:block; text-align:center; font-size:12px; padding:5px;">
                    📅 Zobacz zajęte terminy
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body></html>