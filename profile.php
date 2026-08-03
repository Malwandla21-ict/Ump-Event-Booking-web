<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include "db_connect.php";

// ✅ Handle cancel booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
  $user_id = $_SESSION['user_id'];
  $event_id = intval($_POST['event_id']);

  $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND event_id = ?");
  $stmt->bind_param("ii", $user_id, $event_id);

  if ($stmt->execute()) {
    $message = "Booking cancelled successfully.";
  } else {
    $error = "Error cancelling booking.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile - UMP Event Booking</title>
  <link rel="stylesheet" href="style2.css">
</head>
<body>
<?php include("navbar.php"); ?>

  <header>
    <h2>My Booked Events</h2>
    <p>Here are the events you’ve booked so far.</p>
  </header>

  <!-- ✅ Success / Error messages -->
  <?php if (!empty($message)): ?>
    <p style="color: green; text-align:center;"><?= $message; ?></p>
  <?php elseif (!empty($error)): ?>
    <p style="color: red; text-align:center;"><?= $error; ?></p>
  <?php endif; ?>

  <main class="event-list">
    <?php
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT e.event_id, e.title, e.event_date, e.location, e.image, b.booked_at
            FROM bookings b
            JOIN events e ON b.event_id = e.event_id
            WHERE b.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo '<div class="event-card">';

        // Show thumbnail if available
        if (!empty($row['image'])) {
            echo '<img src="' . htmlspecialchars($row['image'])
             . '" alt="Event Image" style="width:100%; height:150px; object-fit:cover;
              border-radius:8px 8px 0 0;">';
        }

        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
        echo '<p><b>Date:</b> ' . $row['event_date'] . '</p>';
        echo '<p><b>Location:</b> ' . htmlspecialchars($row['location']) . '</p>';
        echo '<p><b>Booked At:</b> ' . $row['booked_at'] . '</p>';

        // Cancel booking button
        echo '<form method="POST" action="profile.php" 
                onsubmit="return confirm(\'Are you sure you want to cancel this booking?\');">';
        echo '<input type="hidden" name="event_id" value="' . $row['event_id'] . '">';
        echo '<button type="submit" 
                style="background:#dc2626; color:white; padding:8px 12px; 
                       border:none; border-radius:6px; cursor:pointer;">
                Cancel Booking</button>';
        echo '</form>';

        echo '</div>';
      }
    } else {
      echo "<p>You haven’t booked any events yet.</p>";
    }
    ?>
  </main>

  <footer>
    <p>© 2025 Event Booking System | Group F Project</p>
  </footer>
</body>
</html>
