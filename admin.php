<?php
session_start();
// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include "db_connect.php";

// ✅ Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);

    // Delete event thumbnail if exists
    $stmt = $conn->prepare("SELECT image FROM events WHERE event_id=?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    if (!empty($imagePath) && file_exists($imagePath)) {
        unlink($imagePath); // remove file
    }

    // Delete event and related bookings
    $conn->query("DELETE FROM bookings WHERE event_id=$event_id");
    $conn->query("DELETE FROM events WHERE event_id=$event_id");

    $_SESSION['message'] = "Event deleted successfully!";
    header("Location: admin.php");
    exit();
}

// ✅ Handle event creation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];

    $imagePath = "";

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); 
        }
        $fileName = time() . "_" . basename($_FILES['image']['name']); 
        $imagePath = $targetDir . $fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, capacity, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $title, $description, $date, $location, $capacity, $imagePath);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Event created successfully!";
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - UMP Event Booking</title>
  <link rel="stylesheet" href="style2.css">
</head>
<body>
<?php include("navbar.php"); ?>

  <header>
    <h2>Admin Dashboard</h2>
  </header>

  <!-- Messages -->
  <?php if (isset($_SESSION['message'])): ?>
    <p style="color:green; text-align:center;"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
  <?php endif; ?>

  <main>
    <!-- Event Creation Form -->
    <form method="POST" action="admin.php" class="form-container" enctype="multipart/form-data">
      <label>Event Title:</label>
      <input type="text" name="title" required>

      <label>Description:</label>
      <textarea name="description" required></textarea>

      <label>Date:</label>
      <input type="date" name="date" required>

      <label>Location:</label>
      <input type="text" name="location" required>

      <label>Capacity:</label>
      <input type="number" name="capacity" min="1" value="100" required>

      <label>Thumbnail Image:</label>
      <input type="file" name="image" accept="image/*">

      <button type="submit">Create Event</button>
    </form>

    <h2>Current Events</h2>
    <section class="event-list">
      <?php
      $res = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
      if ($res->num_rows > 0) {
        while ($event = $res->fetch_assoc()) {
          echo '<div class="event-card">';

          // ✅ Show thumbnail if available
          if (!empty($event['image'])) {
            echo '<img src="' . htmlspecialchars($event['image'])
             . '" alt="Event Image" style="width:100%; height:150px; object-fit:cover;
              border-radius:8px 8px 0 0;">';
          }

          echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
          echo '<p><b>Date:</b> ' . $event['event_date'] . '</p>';
          echo '<p><b>Location:</b> ' . htmlspecialchars($event['location']) . '</p>';
          echo '<p><b>Capacity:</b> ' . $event['capacity'] . '</p>';
          echo '<a href="view_bookings.php?event_id=' . $event['event_id'] . '">View Bookings</a> | ';
          echo '<a href="admin.php?delete=' . $event['event_id'] . '" onclick="return confirm(\'Are you sure you want to delete this event?\')">Delete</a>';
          echo '</div>';
        }
      } else {
        echo "<p>No events created yet.</p>";
      }
      ?>
    </section>
  </main>

  <footer>
    <p>© 2025 Event Booking System | Group F Project</p>
  </footer>
</body>
</html>
