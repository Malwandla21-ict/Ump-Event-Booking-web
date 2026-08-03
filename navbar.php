<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<nav>

  <div class="logo-container">
    <img src="ump logo.png" alt="UMP Logo" class="logo">
    <h1>UMP Event Booking</h1>
  </div>
  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="events.php">Events</a></li>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
   
<?php endif; ?>

    <?php if(isset($_SESSION['user_id'])): ?>
      <?php if($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php">Admin Panel</a></li>
      <?php else: ?>

        <li><a href="profile.php">Profile</a></li>
      <?php endif; ?>
      <li><a href="logout.php">Logout</a></li>
        <li class="user-badge">
          <li class="user-badge">
           <img src="user.png" alt="User" class="profile-icon">
          <?php echo htmlspecialchars($_SESSION['user_name']); ?>
  
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Register</a></li>
    <?php endif; ?>
  </ul>
</nav>
