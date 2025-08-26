// Pager event listeners
const prevBtn = document.getElementById('prev-page');
const nextBtn = document.getElementById('next-page');
if (prevBtn) {
  prevBtn.addEventListener('click', function() {
    if (currentPage > 1) {
      currentPage--;
      renderTasks();
    }
  });
}
if (nextBtn) {
  nextBtn.addEventListener('click', function() {
    let filteredTasks = tasks;
    if (currentFilter === 'completed') filteredTasks = tasks.filter(t => t.completed);
    else if (currentFilter === 'pending') filteredTasks = tasks.filter(t => !t.completed);
    const totalPages = Math.max(1, Math.ceil(filteredTasks.length / PAGE_SIZE));
    if (currentPage < totalPages) {
      currentPage++;
      renderTasks();
    }
  });
}
// Backend integration functions
async function loadTasks() {
  const res = await fetch('http://localhost:3001/tasks');
  tasks = await res.json();
  renderTasks();
}

async function addTask(text) {
  await fetch('http://localhost:3001/tasks', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text })
  });
}

async function deleteTask(idx) {
  const task = tasks[idx];
  await fetch(`http://localhost:3001/tasks/${task.id}`, { method: 'DELETE' });
  await loadTasks();
}

async function toggleComplete(idx) {
  const task = tasks[idx];
  const completed = !task.completed;
  await fetch(`http://localhost:3001/tasks/${task.id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ completed })
  });
  await loadTasks();
}
// Dark mode toggle
const darkModeToggle = document.getElementById('dark-mode-toggle');

function setDarkMode(enabled) {
  document.body.classList.toggle('dark-mode', enabled);
  if (darkModeToggle) {
    darkModeToggle.textContent = enabled ? '☀️ Light Mode' : '🌙 Dark Mode';
    darkModeToggle.setAttribute('aria-label', enabled ? 'Toggle light mode' : 'Toggle dark mode');
  }
  localStorage.setItem('darkMode', enabled ? '1' : '0');
}

if (darkModeToggle) {
  darkModeToggle.addEventListener('click', function() {
    const enabled = !document.body.classList.contains('dark-mode');
    setDarkMode(enabled);
  });
}

// Load dark mode preference
const darkPref = localStorage.getItem('darkMode');
if (darkPref === '1') setDarkMode(true);

// DOM element references
const taskForm = document.getElementById('task-form');
const taskInput = document.getElementById('task-input');
const taskList = document.getElementById('task-list');
const filterNav = document.querySelector('.filter-nav');
let tasks = [];
let currentFilter = 'all';
let currentPage = 1;
const PAGE_SIZE = 5;
function renderTasks() {
  taskList.innerHTML = '';
  let filteredTasks = tasks;
  if (currentFilter === 'completed') {
    filteredTasks = tasks.filter(t => t.completed);
  } else if (currentFilter === 'pending') {
    filteredTasks = tasks.filter(t => !t.completed);
  }

  // Pagination logic
  const totalPages = Math.max(1, Math.ceil(filteredTasks.length / PAGE_SIZE));
  if (currentPage > totalPages) currentPage = totalPages;
  const startIdx = (currentPage - 1) * PAGE_SIZE;
  const endIdx = startIdx + PAGE_SIZE;
  const pageTasks = filteredTasks.slice(startIdx, endIdx);

  pageTasks.forEach((task, idx) => {
    const li = document.createElement('li');
    li.className = 'task-item' + (task.completed ? ' completed' : '');
    li.setAttribute('role', 'listitem');
    li.setAttribute('aria-label', task.text + (task.completed ? ' completed' : ''));

    const span = document.createElement('span');
    span.textContent = task.text;

    const actions = document.createElement('div');
    actions.className = 'task-actions';

    const completeBtn = document.createElement('button');
    completeBtn.className = 'complete-btn';
    completeBtn.setAttribute('aria-label', task.completed ? 'Mark as incomplete' : 'Mark as completed');
    completeBtn.innerHTML = task.completed ? '↩️' : '✔️';
    completeBtn.onclick = () => toggleComplete(tasks.indexOf(task));

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'delete-btn';
    deleteBtn.setAttribute('aria-label', 'Delete task');
    deleteBtn.innerHTML = '🗑️';
    deleteBtn.onclick = () => deleteTask(tasks.indexOf(task));

    actions.appendChild(completeBtn);
    actions.appendChild(deleteBtn);

    li.appendChild(span);
    li.appendChild(actions);
    taskList.appendChild(li);
  });

  // Update pager controls
  const pageButtons = document.getElementById('page-buttons');
  const prevBtn = document.getElementById('prev-page');
  const nextBtn = document.getElementById('next-page');
  if (pageButtons) {
    pageButtons.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = i;
      btn.className = 'page-btn';
      btn.disabled = i === currentPage;
      btn.setAttribute('aria-label', `Go to page ${i}`);
      btn.onclick = () => {
        currentPage = i;
        renderTasks();
      };
      pageButtons.appendChild(btn);
    }
  }
  if (prevBtn) prevBtn.disabled = currentPage === 1;
}



taskForm.addEventListener('submit', async function(e) {
  e.preventDefault();
  const value = taskInput.value.trim();
  if (value.length === 0) {
    taskInput.value = '';
    taskInput.focus();
    taskInput.setAttribute('aria-invalid', 'true');
    taskInput.setCustomValidity('Task cannot be empty.');
    taskInput.reportValidity();
    return;
  } else {
    taskInput.setAttribute('aria-invalid', 'false');
    taskInput.setCustomValidity('');
  }
  await addTask(value);
  await loadTasks();
  taskInput.value = '';
  taskInput.focus();
});

if (filterNav) {
  filterNav.addEventListener('click', function(e) {
    if (e.target.classList.contains('filter-btn')) {
      document.querySelectorAll('.filter-btn').forEach(btn => btn.setAttribute('aria-pressed', 'false'));
      e.target.setAttribute('aria-pressed', 'true');
      currentFilter = e.target.getAttribute('data-filter');
      renderTasks();
    }
  });
}

// Keyboard accessibility for task list
// Allow delete with Del key, toggle complete with Enter
taskList.addEventListener('keydown', function(e) {
  const items = Array.from(taskList.children);
  const idx = items.indexOf(document.activeElement);
    if (idx !== -1) {
      if (e.key === 'Delete') {
        deleteTask(idx);
      } else if (e.key === 'Enter') {
        toggleComplete(idx);
      }
  }
});


// Initial load from backend
loadTasks();
