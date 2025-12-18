<?php session_start(); include 'db.php'; ?>
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

<div class="box">
    <h2>Dostępne Pokoje</h2>
    <table>
        <tr><th>Hotel</th><th>Pokój</th><th>Typ</th><th>Cena Bazowa</th><th>Rezerwacja</th></tr>
        <?php
        $stmt = $conn->query("SELECT p.*, h.nazwa as hotel FROM pokoje p JOIN hotele h ON p.hotel_id=h.hotel_id ORDER BY h.nazwa, p.nr_pokoj");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <tr>
            <form action="rezerwacja.php" method="POST">
                <td><?php echo $row['hotel']; ?></td>
                <td><?php echo $row['nr_pokoj']; ?></td>
                <td><?php echo $row['typ_pokoju']; ?> (Max dzieci: <?php echo $row['max_dzieci']; ?>)</td>
                <td><?php echo $row['cena_doba']; ?> PLN</td>
                <td>
                    Od: <input type="date" name="od" value="<?php echo date('Y-m-d'); ?>" style="width:130px">
                    Do: <input type="date" name="do" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="width:130px">
                    Dzieci: <input type="number" name="dzieci" value="0" min="0" max="<?php echo $row['max_dzieci']; ?>" style="width:50px">
                    <input type="hidden" name="pid" value="<?php echo $row['id_pokoj']; ?>">
                    <button type="submit" name="start" class="btn">Rezerwuj</button>
                </td>
            </form>
        </tr>
        <?php } ?>
    </table>
</div>
</body></html>