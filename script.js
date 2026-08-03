// ===== Login Validation =====
document.getElementById("loginForm")?.addEventListener("submit", function (e) {
  e.preventDefault();
  let email = document.getElementById("loginEmail").value;
  let password = document.getElementById("loginPassword").value;

  if (email === "" || password === "") {
    alert("All fields are required!");
  } else {
    alert("Login successful (frontend only).");
  }
});

// ===== Register Validation =====
document.getElementById("registerForm")?.addEventListener("submit", function (e) {
  e.preventDefault();
  let password = document.getElementById("regPassword").value;
  let confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    alert("Passwords do not match!");
  } else {
    alert("Registration successful (frontend only).");
  }
});

// ===== Book Event Placeholder =====
function bookEvent(eventName) {
  alert("You selected: " + eventName + "\n(Booking system will connect later).");
}
