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
let tasks = [];
const taskForm = document.getElementById('task-form');
const taskInput = document.getElementById('task-input');
const taskList = document.getElementById('task-list');
const filterNav = document.querySelector('.filter-nav');
let currentFilter = 'all';

// Load tasks from localStorage
function loadTasks() {
  const saved = localStorage.getItem('tasks');
  if (saved) {
    try {
      tasks = JSON.parse(saved);
    } catch {
      tasks = [];
    }
  }
}

// Save tasks to localStorage
function saveTasks() {
  localStorage.setItem('tasks', JSON.stringify(tasks));
}

// Initialize app
loadTasks();
renderTasks();

function renderTasks() {
  taskList.innerHTML = '';
  let filteredTasks = tasks;
  if (currentFilter === 'completed') {
    filteredTasks = tasks.filter(t => t.completed);
  } else if (currentFilter === 'pending') {
    filteredTasks = tasks.filter(t => !t.completed);
  }
  filteredTasks.forEach((task, idx) => {
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
  saveTasks();
    completeBtn.setAttribute('aria-label', task.completed ? 'Mark as incomplete' : 'Mark as completed');
    completeBtn.innerHTML = task.completed ? '↩️' : '✔️';
    completeBtn.onclick = () => toggleComplete(tasks.indexOf(task));

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'delete-btn';
    deleteBtn.setAttribute('aria-label', 'Delete task');
        saveTasks();
    deleteBtn.innerHTML = '🗑️';
    deleteBtn.onclick = () => deleteTask(tasks.indexOf(task));

    actions.appendChild(completeBtn);
    actions.appendChild(deleteBtn);

    li.appendChild(span);
    li.appendChild(actions);
    taskList.appendChild(li);
  });
}

function addTask(text) {
  tasks.push({ text, completed: false });
  renderTasks();
}

      loadTasks();
function deleteTask(idx) {
  tasks.splice(idx, 1);
  renderTasks();
}

function toggleComplete(idx) {
  tasks[idx].completed = !tasks[idx].completed;
  renderTasks();
}

taskForm.addEventListener('submit', function(e) {
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
  addTask(value);
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

renderTasks();
