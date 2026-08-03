<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $event_id = $_POST['event_id'];
  $user_id = $_SESSION['user_id'];

  // prevent duplicate bookings
  $check = $conn->prepare("SELECT * FROM bookings WHERE user_id=? AND event_id=?");
  $check->bind_param("ii", $user_id, $event_id);
  $check->execute();
  $res = $check->get_result();

  if ($res->num_rows > 0) {
    $_SESSION['message'] = "You already booked this event!";
  } else {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $event_id);
    if ($stmt->execute()) {
      $_SESSION['message'] = "Booking successful!";
    } else {
      $_SESSION['message'] = "Error booking event.";
    }
  }
  header("Location: events.php");
  exit();
}
?>
