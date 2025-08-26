// server.js - Express API for Task Manager
import express from 'express';
import cors from 'cors';
import db from './db.js';
const app = express();
app.use(cors());
app.use(express.json());

// Get all tasks
app.get('/tasks', async (req, res) => {
  const tasks = await db.getTasks();
  res.json(tasks);
});

// Add a task
app.post('/tasks', async (req, res) => {
  const { text } = req.body;
  const task = await db.addTask(text);
  res.json(task);
});

// Update a task (mark complete/incomplete)
app.put('/tasks/:id', async (req, res) => {
  const { completed } = req.body;
  await db.updateTask(req.params.id, completed);
  res.sendStatus(200);
});

// Delete a task
app.delete('/tasks/:id', async (req, res) => {
  await db.deleteTask(req.params.id);
  res.sendStatus(200);
});

app.listen(3001, () => console.log('API running on port 3001'));
