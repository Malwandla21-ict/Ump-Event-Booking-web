<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UMP Event Booking - Events</title>
  <link rel="stylesheet" href="style2.css">
</head>
<body>

  <!-- Navigation -->
  <?php include("navbar.php"); ?>

  <!-- Header -->
  <header>
    <h2>Available Events</h2>
    <p>Choose your event and book a seat.</p>
  </header>

  <?php if(isset($_SESSION['message'])): ?>
    <p style="color: green; text-align:center;">
      <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </p>
  <?php endif; ?>

  <!-- Events Grid -->
  <main class="event-list">
    <?php
    $sql = "SELECT * FROM events ORDER BY event_date ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      while ($event = $result->fetch_assoc()) {
        echo '<div class="event-card">';
        
        // ✅ Use image from DB if available
        if (!empty($event['image'])) {
            echo '<img src="' . htmlspecialchars($event['image'])
             . '" alt="Event Image" style="width:100%; height:150px; object-fit:cover;
              border-radius:8px 8px 0 0;">';
          }

        echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
        echo '<p><b>Date:</b> ' . $event['event_date'] . '</p>';
        echo '<p><b>Location:</b> ' . htmlspecialchars($event['location']) . '</p>';
        echo '<p>' . htmlspecialchars($event['description']) . '</p>';

        if (isset($_SESSION['user_id'])) {
          echo '<form method="POST" action="book_event.php">';
          echo '<input type="hidden" name="event_id" value="' . $event['event_id'] . '">';
          echo '<button type="submit">Book Now</button>';
          echo '</form>';
        } else {
          echo '<p><a href="login.php">Login to book</a></p>';
        }

        echo '</div>';
      }
    } else {
      echo "<p>No events available right now.</p>";
    }
    ?>
  </main>

  <!-- Footer -->
  <footer>
    <p>© 2025 Event Booking System | Group F Project</p>
  </footer>
</body>
</html>
