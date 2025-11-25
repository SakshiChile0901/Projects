// API Endpoints
const taskApi = "/Task%20Management%20System/api/tasks.php";
const calendarApi = "/Task%20Management%20System/api/calendar.php";
const logoutApi = "/Task%20Management%20System/api/auth.php?action=logout";

let current = new Date();

// Helpers
function formatMonth(d) {
  return d.toLocaleString(undefined, { month: "long", year: "numeric" });
}

function escapeHtml(s) {
  if (!s) return "";
  return s.replace(/[&<>"']/g, function (m) {
    return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[m];
  });
}

async function fetchJSON(url) {
  try {
    const res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) throw new Error("Fetch failed");
    return await res.json();
  } catch (err) {
    showToast("Failed to fetch data", "error");
    return [];
  }
}

// Toast
function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.style.background = type === "success" ? "#16a34a" : "#dc2626";
  toast.innerText = msg;
  toast.classList.remove("hidden");
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2200);
}

// State
let selectedCategory = "";
let latestTasksCache = []; // keep last fetched tasks for today's list

// Render Calendar
async function renderCalendar() {
  const calendarTitleEl = document.getElementById("calendarTitle");
  const currentMonthEl = document.getElementById("currentMonth");
  const cal = document.getElementById("calendar");
  if (!calendarTitleEl || !currentMonthEl || !cal) return;

  calendarTitleEl.innerText = formatMonth(current);
  currentMonthEl.innerText = formatMonth(current);

  const year = current.getFullYear();
  const month = current.getMonth() + 1;

  const counts = await fetchJSON(`${calendarApi}?year=${year}&month=${month}`);
  const map = {};
  if (Array.isArray(counts)) counts.forEach(r => { if (r && r.due_date) map[r.due_date] = r.count; });

  cal.innerHTML = "";

  const days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  days.forEach(d => {
    const el = document.createElement("div");
    el.className = "text-center font-medium";
    el.innerText = d;
    cal.appendChild(el);
  });

  const start = new Date(year, month-1, 1);
  const end = new Date(year, month, 0);

  for (let i=0; i<start.getDay(); i++) {
    const e = document.createElement("div");
    e.className = "h-20 border border-gray-100 rounded bg-white";
    cal.appendChild(e);
  }

  const today = new Date();
  const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,"0")}-${String(today.getDate()).padStart(2,"0")}`;

  for (let day=1; day<=end.getDate(); day++) {
    const dateStr = `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
    const cell = document.createElement("div");
    cell.className = "p-2 h-20 border border-gray-100 rounded cursor-pointer bg-white flex flex-col justify-between";
    if (dateStr === todayStr) cell.classList.add("calendar-today");

    const top = document.createElement("div");
    top.className = "font-medium";
    top.innerText = day;

    const bottom = document.createElement("div");
    bottom.className = "text-xs muted";
    bottom.innerText = map[dateStr] ? `${map[dateStr]} tasks` : "";

    cell.appendChild(top);
    cell.appendChild(bottom);

    cell.addEventListener("click", () => {
      // set selected category/date filter and reload tasks
      loadTasks({ date: dateStr });
    });

    cal.appendChild(cell);
  }
}

// Load & Render Tasks
async function loadTasks(filters = {}) {
  const searchEl = document.getElementById("taskSearch");
  const priorityEl = document.getElementById("filterPriority");
  const statusEl = document.getElementById("filterStatus");

  const params = { ...filters };

  if (!filters.search && searchEl?.value) params.search = searchEl.value;
  if (!filters.priority && priorityEl?.value) params.priority = priorityEl.value;
  if (!filters.status && statusEl?.value) params.status = statusEl.value;
  if (!filters.category) params.category = selectedCategory;

  const url = new URL(taskApi, location.origin);
  Object.keys(params).forEach(k => {
    const v = params[k];
    if (v !== undefined && v !== "") url.searchParams.append(k, v);
  });

  const tasks = await fetchJSON(url.toString());
  latestTasksCache = Array.isArray(tasks) ? tasks : [];
  renderTaskList(latestTasksCache);
  renderTodayTasks();
}

function renderTaskList(tasks) {
  const list = document.getElementById("taskList");
  if (!list) return;
  list.innerHTML = "";
  if (!tasks || tasks.length === 0) {
    list.innerHTML = `<p class="muted text-sm">No tasks</p>`;
    return;
  }

  tasks.forEach(t => {
    const row = document.createElement("div");
    row.className = "flex items-center justify-between p-3 border rounded bg-white task-item-animate";
    row.dataset.id = t.id;

    const left = document.createElement("div");
    left.innerHTML = `<div class="font-medium">${escapeHtml(t.title)}</div><div class="text-xs muted">${t.due_date || ""} • ${t.priority}</div>`;

    const right = document.createElement("div");
    right.className = "flex items-center gap-2";

    if (t.category_name) {
      const c = document.createElement("span");
      c.className = "text-xs px-2 py-1 rounded";
      c.style.background = "#efefef";
      c.innerText = t.category_name;
      right.appendChild(c);
    }

    // Status button
    const statusBtn = document.createElement("button");
    statusBtn.className = "px-2 py-1 rounded text-sm font-semibold " + 
      (t.status === "completed" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800");
    statusBtn.innerText = t.status === "completed" ? "Completed" : "Pending";
    statusBtn.addEventListener("click", async () => {
      const newStatus = t.status === "completed" ? "pending" : "completed";
      await fetch(`${taskApi}?id=${t.id}`, {
        method: "PUT",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `status=${encodeURIComponent(newStatus)}`
      });
      showToast("Status updated");
      await loadTasks();
      await renderCalendar();
    });
    right.appendChild(statusBtn);

    // Edit button
    const editBtn = document.createElement("button");
    editBtn.className = "text-sm muted px-2 py-1 border rounded";
    editBtn.innerText = "Edit";
    editBtn.addEventListener("click", () => openEditPanel(t.id));
    right.appendChild(editBtn);

    // Delete button
    const delBtn = document.createElement("button");
    delBtn.className = "text-sm text-red-600";
    delBtn.innerText = "Delete";
    delBtn.addEventListener("click", async () => {
      if (!confirm("Delete this task?")) return;
      const rowEl = document.querySelector(`[data-id='${t.id}']`);
      if (rowEl) {
        rowEl.classList.add("task-item-remove");
        setTimeout(async () => {
          await fetch(`${taskApi}?id=${t.id}`, { method: "DELETE" });
          showToast("Task deleted");
          await loadTasks();
          await renderCalendar();
        }, 300);
      }
    });
    right.appendChild(delBtn);

    row.appendChild(left);
    row.appendChild(right);
    list.appendChild(row);
  });
}

// Today's Tasks 
function getTodayStr() {
  const t = new Date();
  return `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,"0")}-${String(t.getDate()).padStart(2,"0")}`;
}

function renderTodayTasks() {
  const todayList = document.getElementById("todayTaskList");
  const todayCount = document.getElementById("todayCount");
  if (!todayList) return;
  const today = getTodayStr();

  // Use latestTasksCache (already fetched) to avoid extra requests
  const todays = latestTasksCache.filter(t => t.due_date === today);
  todayList.innerHTML = "";

  if (todayCount) todayCount.innerText = `(${todays.length})`;

  if (todays.length === 0) {
    todayList.innerHTML = `<div class="text-gray-500 italic">No tasks for today.</div>`;
    return;
  }

  todays.forEach(t => {
    const card = document.createElement("div");
    card.className = "p-3 border rounded bg-white flex items-center justify-between cursor-pointer hover:shadow-sm";
    card.onclick = () => openEditPanel(t.id);

    const left = document.createElement("div");
    left.innerHTML = `<div class="font-medium">${escapeHtml(t.title)}</div>
                      <div class="text-xs muted">${t.category_name || ""} • ${t.priority} • ${t.status}</div>`;

    const right = document.createElement("div");
    right.className = "flex items-center gap-2";

    const statusBadge = document.createElement("span");
    statusBadge.className = "text-xs px-2 py-1 rounded font-semibold " + (t.status === "completed" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800");
    statusBadge.innerText = t.status === "completed" ? "Completed" : "Pending";
    right.appendChild(statusBadge);

    card.appendChild(left);
    card.appendChild(right);
    todayList.appendChild(card);
  });
}

// Add Task Form
const addTaskForm = document.getElementById("addTaskForm");
addTaskForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const f = e.target;

  const titleEl = f.elements["title"];
  const categoryEl = f.elements["category"];

  const title = titleEl?.value?.trim() || "";
  const category = categoryEl?.value?.trim() || "";

  if (!title) return showToast("Title is required", "error");
  if (!category) return showToast("Category is required", "error");

  const payload = {
    title,
    due_date: f.elements["due_date"].value || null,
    priority: f.elements["priority"].value || "medium",
    category
  };

  const res = await fetch(taskApi, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });

  if (res.ok) {
    f.reset();
    showToast("Task added!");
    await loadTasks();
    await renderCalendar();
  } else {
    const err = await res.json().catch(() => ({}));
    showToast(err.error || "Failed to add task", "error");
  }
});

// Filters & Category buttons
document.getElementById("taskSearch")?.addEventListener("input", () => loadTasks());
document.getElementById("filterPriority")?.addEventListener("change", () => loadTasks());
document.getElementById("filterStatus")?.addEventListener("change", () => loadTasks());
document.querySelectorAll(".filter-cat").forEach(btn => {
  btn.addEventListener("click", () => {
    selectedCategory = btn.dataset.cat || "";
    document.querySelectorAll(".filter-cat").forEach(b => b.classList.remove("bg-gray-200"));
    btn.classList.add("bg-gray-200");
    loadTasks();
  });
});

// Edit panel
const panel = document.getElementById("panel");
const overlay = document.getElementById("overlay");
const editForm = document.getElementById("editForm");
const panelTitle = document.getElementById("panelTitle");

function openPanel() { panel?.classList.add("open"); overlay?.classList.add("open"); }
function closePanel() { panel?.classList.remove("open"); overlay?.classList.remove("open"); }

document.getElementById("closePanel")?.addEventListener("click", closePanel);
overlay?.addEventListener("click", closePanel);

async function openEditPanel(id) {
  if (!panelTitle || !editForm) return showToast("UI not loaded", "error");
  panelTitle.innerText = "Edit Task";

  const res = await fetch(`${taskApi}?id=${id}`);
  if (!res.ok) return showToast("Failed to load", "error");
  const data = await res.json().catch(() => []);
  const t = Array.isArray(data) ? data[0] : data;
  if (!t) return showToast("Task not found", "error");

  editForm.id.value = t.id;
  editForm.title.value = t.title;
  editForm.due_date.value = t.due_date || "";
  editForm.priority.value = t.priority;
  editForm.category.value = t.category_name || "";
  editForm.status.value = t.status;
  openPanel();
}

editForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = editForm.id.value;
  const category = editForm.category.value.trim();
  if (!category) return showToast("Category is required", "error");

  const body = new URLSearchParams();
  ["title","due_date","priority","category","status"].forEach(k => {
    const el = editForm[k];
    if (el && el.value !== undefined) body.append(k, el.value);
  });

  const res = await fetch(`${taskApi}?id=${id}`, {
    method: "PUT",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: body.toString()
  });

  if (res.ok) {
    showToast("Task updated!");
    closePanel();
    await loadTasks();
    await renderCalendar();
  } else {
    showToast("Update failed", "error");
  }
});

document.getElementById("deleteTaskBtn")?.addEventListener("click", async () => {
  const id = editForm.id.value;
  if (!id || !confirm("Delete task?")) return;
  await fetch(`${taskApi}?id=${id}`, { method: "DELETE" });
  showToast("Task deleted");
  closePanel();
  await loadTasks();
  await renderCalendar();
});

// Month nav
document.getElementById("prevMonth")?.addEventListener("click", () => {
  current.setMonth(current.getMonth()-1);
  renderCalendar();
});
document.getElementById("nextMonth")?.addEventListener("click", () => {
  current.setMonth(current.getMonth()+1);
  renderCalendar();
});


//Delete All
document.getElementById("deleteAllBtn")?.addEventListener("click", async () => {
    if (!confirm("Are you sure you want to delete ALL tasks? This cannot be undone.")) return;

    const res = await fetch(`${taskApi}?action=delete_all`, { method: "DELETE" });

    if (res.ok) {
        showToast("All tasks deleted", "success");
        await loadTasks({});
        await renderCalendar();
    } else {
        showToast("Failed to delete all tasks", "error");
    }
});

// Logout
document.getElementById("logoutBtn")?.addEventListener("click", async () => {
  await fetch(logoutApi);
  location.href = "login.html";
});

// Export to PDF 
document.getElementById('exportBtn')?.addEventListener('click', async () => {
  const tasks = await fetchJSON(taskApi);
  if (!tasks || tasks.length === 0) {
    showToast("No tasks to export", "error");
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  const img = new Image();
  img.src = 'assets/logo.png';
  img.onload = () => generatePDF(doc, img);
  img.onerror = () => generatePDF(doc, null);

  function generatePDF(doc, logo) {
    const pageWidth = doc.internal.pageSize.getWidth();
    const title = "Task Management System";
    let startY = 14;

    if (logo) {
      const imgWidth = 30;
      const imgHeight = (logo.height / logo.width) * imgWidth;
      const totalWidth = imgWidth + 6 + doc.getTextWidth(title);
      const startX = (pageWidth - totalWidth) / 2;
      doc.addImage(logo, 'PNG', startX, 10, imgWidth, imgHeight);
      doc.setFontSize(18);
      doc.text(title, startX + imgWidth + 6, 24);
      startY = 10 + imgHeight + 8;
    } else {
      doc.setFontSize(18);
      doc.text(title, pageWidth / 2, 20, { align: 'center' });
      startY = 32;
    }

    const body = tasks.map((t, idx) => [
      idx + 1, t.title || '', t.due_date || '', t.priority || '', t.status || '', t.category_name || ''
    ]);

    doc.autoTable({
  startY,
  head: [['#', 'Title', 'Due Date', 'Priority', 'Status', 'Category']],
  body: tasks.map((t, idx) => [
    idx + 1, t.title || '', t.due_date || '', t.priority || '', t.status || '', t.category_name || ''
  ]),
  theme: 'grid',
  headStyles: { fillColor: [34, 197, 94], textColor: 255 },
  styles: { fontSize: 9, textColor: [0,0,0], cellPadding: 3 },
  didParseCell: (data) => {
    // This is the correct hook to style individual cells
    if (data.column.index === 4 && data.section === 'body') { 
      const status = String(data.cell.raw || '').toLowerCase();
      if (status === 'pending') {
        data.cell.styles.fillColor = [255, 229, 153]; 
        data.cell.styles.textColor = 0; 
      } else if (status === 'completed') {
        data.cell.styles.fillColor = [178, 235, 178]; 
        data.cell.styles.textColor = 0; 
      }
    }
  }
});


    doc.save('tasks.pdf');
  }
});

// CSV Import
document.getElementById("importFile")?.addEventListener("change", async (e) => {
  const f = e.target.files?.[0];
  if (!f) return;
  const form = new FormData();
  form.append("file", f);
  showToast("Uploading...", "success");

  const res = await fetch(`${taskApi}?action=import`, { method: "POST", body: form });
  const json = await res.json().catch(() => null);

  if (res.ok && json?.success) {
    showToast(`Imported ${json.inserted} tasks`);
    await loadTasks();
    await renderCalendar();
  } else {
    showToast(json?.error || "Import failed", "error");
  }

  e.target.value = "";
});

// Initial load
renderCalendar();
loadTasks();
