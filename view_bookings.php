<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include "db_connect.php";

if (!isset($_GET['event_id'])) {
  echo "No event selected.";
  exit();
}

$event_id = intval($_GET['event_id']);

// Get event details
$event = $conn->query("SELECT title FROM events WHERE event_id = $event_id")->fetch_assoc();

// Get bookings for this event
$sql = "SELECT u.full_name, u.email, b.booked_at 
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        WHERE b.event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bookings - <?= htmlspecialchars($event['title']); ?></title>
  <link rel="stylesheet" href="style2.css">
</head>
<body>
<?php include("navbar.php"); ?>

<header>
  <h2>Bookings for <?= htmlspecialchars($event['title']); ?></h2>
</header>

<main>
  <?php if ($result->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0" style="margin:20px auto; border-collapse:collapse; width:80%;">
      <tr style="background:#2563eb; color:white;">
        <th>Name</th>
        <th>Email</th>
        <th>Booked At</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['full_name']); ?></td>
          <td><?= htmlspecialchars($row['email']); ?></td>
          <td><?= $row['booked_at']; ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p style="text-align:center;">No bookings yet for this event.</p>
  <?php endif; ?>
</main>

<footer>
  <p>© 2025 Event Booking System | Group Project</p>
</footer>
</body>
</html>
