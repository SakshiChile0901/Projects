
const registerApi = "/Task%20Management%20System/api/auth.php?action=register";

// Toast
function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.style.background = type === "success" ? "#16a34a" : "#dc2626";
  toast.innerText = msg;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2500);
}

document.getElementById("registerForm")?.addEventListener("submit", async function (e) {
  e.preventDefault();

  const btn = document.getElementById("registerBtn");
  btn.disabled = true;
  const originalText = btn.innerText;
  btn.innerText = "Creating account...";

  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  // Client-side validation
  if (!name || !email || !password) {
    showToast("All fields are required", "error");
    btn.disabled = false;
    btn.innerText = originalText;
    return;
  }
  if (password.length < 6) {
    showToast("Password must be at least 6 characters", "error");
    btn.disabled = false;
    btn.innerText = originalText;
    return;
  }

  try {
    const res = await fetch(registerApi, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, email, password })
    });

    const json = await res.json().catch(() => null);

    if (res.ok && json && json.success) {
      showToast("Account created — redirecting...");
      setTimeout(() => { window.location.href = "login.html"; }, 900);
    } else {
      // Server returned error
      const message = (json && json.error) ? json.error : "Registration failed";
      showToast(message, "error");
      btn.disabled = false;
      btn.innerText = originalText;
    }
  } catch (err) {
    console.error(err);
    showToast("Network error", "error");
    btn.disabled = false;
    btn.innerText = originalText;
  }
});
