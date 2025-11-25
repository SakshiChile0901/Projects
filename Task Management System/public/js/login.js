
const loginApi = "/Task%20Management%20System/api/auth.php?action=login";

/* Toast */
function showToast(msg, type = "success") {
    const toast = document.getElementById("toast");
    if (!toast) return;

    toast.style.background = type === "success" ? "#16a34a" : "#dc2626";
    toast.innerText = msg;

    toast.classList.remove("hidden");
    toast.classList.add("show");

    setTimeout(() => toast.classList.remove("show"), 2200);
}

document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("fade-in");  //Fade-in effect

    // Auto-login if token exists (session only)
    const token = sessionStorage.getItem("userToken");
    if (token) {
        location.href = "index.html";
    }
});

/* Login */
document.getElementById("loginForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const btn = document.getElementById("loginBtn") || e.submitter;
    btn.disabled = true;
    btn.innerText = "Logging in...";

    const email = e.target.email.value.trim();
    const password = e.target.password.value.trim();

    if (!email || !password) {
        showToast("All fields are required", "error");
        btn.disabled = false;
        btn.innerText = "Login";
        return;
    }

    const res = await fetch(loginApi, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    });

    const data = await res.json().catch(() => null);

    if (res.ok && data?.success) {
        showToast("Login successful!");

        sessionStorage.setItem("userToken", "1"); // simple flag

        setTimeout(() => location.href = "index.html", 700);
    } else {
        showToast(data?.error || "Invalid credentials", "error");
        btn.disabled = false;
        btn.innerText = "Login";
    }
});
