// db.js - SQLite database helper
import sqlite3 from 'sqlite3';
const db = new sqlite3.Database('./tasks.db');

db.serialize(() => {
  db.run('CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY, text TEXT, completed INTEGER)');
});

export default {
  getTasks: () => new Promise((resolve, reject) => {
    db.all('SELECT * FROM tasks', [], (err, rows) => err ? reject(err) : resolve(rows));
  }),
  addTask: (text) => new Promise((resolve, reject) => {
    db.run('INSERT INTO tasks (text, completed) VALUES (?, 0)', [text], function(err) {
      if (err) reject(err);
      else resolve({ id: this.lastID, text, completed: 0 });
    });
  }),
  updateTask: (id, completed) => new Promise((resolve, reject) => {
    db.run('UPDATE tasks SET completed = ? WHERE id = ?', [completed ? 1 : 0, id], err => err ? reject(err) : resolve());
  }),
  deleteTask: (id) => new Promise((resolve, reject) => {
    db.run('DELETE FROM tasks WHERE id = ?', [id], err => err ? reject(err) : resolve());
  })
};
