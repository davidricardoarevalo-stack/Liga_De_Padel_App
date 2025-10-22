// --- MIGRACIÓN DE FECHAS AL FORMATO ISO ---
// =============================
// 1. DEPENDENCIAS Y CONFIGURACIÓN
// =============================
const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const app = express();
const PORT = process.env.PORT || 3001;
const JWT_SECRET = process.env.JWT_SECRET || 'secret_key';
app.use(cors());
app.use(express.json());

function authenticateToken(req, res, next) {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];
  if (!token) return res.sendStatus(401);
  jwt.verify(token, JWT_SECRET, (err, user) => {
    if (err) return res.sendStatus(403);
    req.user = user;
    next();
  });
}

const db = new sqlite3.Database('./users.db', (err) => {
    if (err) {
        console.error('Error opening database', err);
        return;
    }
    // --- Crear tablas principales ---
    db.run(`CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      first_name TEXT,
      middle_name TEXT,
      last_name TEXT,
      second_last_name TEXT,
      email TEXT UNIQUE,
      password TEXT,
      birthdate TEXT,
      club_id INTEGER,
      status TEXT,
      role TEXT
    )`);
    // Migración de columnas faltantes en users
    db.all("PRAGMA table_info(users)", (err, columns) => {
      let alterOps = [];
      if (columns && !columns.some(col => col.name === 'role')) {
        alterOps.push(new Promise(resolve => {
          db.run("ALTER TABLE users ADD COLUMN role TEXT", (err) => {
            if (!err) console.log("Columna 'role' agregada a users");
            resolve();
          });
        }));
      }
      if (columns && !columns.some(col => col.name === 'status')) {
        alterOps.push(new Promise(resolve => {
          db.run("ALTER TABLE users ADD COLUMN status TEXT", (err) => {
            if (!err) console.log("Columna 'status' agregada a users");
            resolve();
          });
        }));
      }
      if (columns && !columns.some(col => col.name === 'club_id')) {
        alterOps.push(new Promise(resolve => {
          db.run("ALTER TABLE users ADD COLUMN club_id INTEGER", (err) => {
            if (!err) console.log("Columna 'club_id' agregada a users");
            resolve();
          });
        }));
      }
      Promise.all(alterOps).then(() => {
        // Migrar fechas y usuario de prueba solo después de migrar columnas
        migrateDates();
        db.get('SELECT * FROM users WHERE email = ?', ['app@app.com'], (err, user) => {
          if (!user) {
            db.run('INSERT INTO users (first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
              ['App', '', 'Admin', '', 'app@app.com', '123', '1990-01-01', 'Activo', 'Administrador']);
            console.log('Usuario de prueba app@app.com agregado como Administrador');
          } else {
            db.run('UPDATE users SET role = ?, status = ? WHERE email = ?', ['Administrador', 'Activo', 'app@app.com']);
            console.log('Usuario app@app.com actualizado como Administrador');
          }
        });
      });
    });

    db.run(`CREATE TABLE IF NOT EXISTS roles (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT UNIQUE
    )`);

    db.run(`CREATE TABLE IF NOT EXISTS athletes (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      first_name TEXT,
      middle_name TEXT,
      last_name TEXT,
      second_last_name TEXT,
      email TEXT,
      birthdate TEXT,
      club_id INTEGER,
      document_type TEXT,
      document_number TEXT,
      mobile_phone TEXT
      ,rep_legal_name TEXT,
      rep_legal_email TEXT,
      rep_legal_phone TEXT
    )`);
    // Ensure new representative fields exist on older DBs (migration)
    db.all("PRAGMA table_info(athletes)", (err, cols) => {
      if (err) return console.error('Error leyendo esquema de athletes (rep fields):', err.message);
      const existing = (cols || []).map(c => c.name);
      const addIfMissing = (colName) => {
        if (!existing.includes(colName)) {
          db.run(`ALTER TABLE athletes ADD COLUMN ${colName} TEXT`, (err2) => {
            if (err2) console.error(`No se pudo agregar columna ${colName} a athletes:`, err2.message);
            else console.log(`Columna '${colName}' agregada a athletes`);
          });
        }
      };
      addIfMissing('rep_legal_name');
      addIfMissing('rep_legal_email');
      addIfMissing('rep_legal_phone');
    });
    // Ensure new document fields exist on older DBs (migration)
    db.all("PRAGMA table_info(athletes)", (err, cols) => {
      if (err) return console.error('Error leyendo esquema de athletes:', err.message);
      const existing = (cols || []).map(c => c.name);
      const addIfMissing = (colName) => {
        if (!existing.includes(colName)) {
          db.run(`ALTER TABLE athletes ADD COLUMN ${colName} TEXT`, (err2) => {
            if (err2) console.error(`No se pudo agregar columna ${colName} a athletes:`, err2.message);
            else console.log(`Columna '${colName}' agregada a athletes`);
          });
        }
      };
      addIfMissing('document_type');
      addIfMissing('document_number');
      addIfMissing('mobile_phone');
    });

    db.run(`CREATE TABLE IF NOT EXISTS clubs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT,
      legal_representative TEXT,
      status TEXT,
      address TEXT,
      phone TEXT,
      contact_person TEXT,
      director_tecnico TEXT,
      fisioterapeuta TEXT,
      asistente_tecnico TEXT,
      delegado TEXT
    )`);
    // Migration for optional club staff fields
    db.all("PRAGMA table_info(clubs)", (err, cols) => {
      if (err) return console.error('Error leyendo esquema de clubs:', err.message);
      const existing = (cols || []).map(c => c.name);
      const addIfMissing = (colName) => {
        if (!existing.includes(colName)) {
          db.run(`ALTER TABLE clubs ADD COLUMN ${colName} TEXT`, (err2) => {
            if (err2) console.error(`No se pudo agregar columna ${colName} a clubs:`, err2.message);
            else console.log(`Columna '${colName}' agregada a clubs`);
          });
        }
      };
      addIfMissing('director_tecnico');
      addIfMissing('fisioterapeuta');
      addIfMissing('asistente_tecnico');
      addIfMissing('delegado');
    });

    db.run(`CREATE TABLE IF NOT EXISTS tournaments (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT,
      date TEXT,
      place TEXT,
      observations TEXT
    )`);

    // Insertar roles si no existen
    const roles = ['Administrador', 'Asistente', 'Club', 'Deportista'];

    roles.forEach(role => {
      db.get('SELECT * FROM roles WHERE name = ?', [role], (err, r) => {
        if (!r) db.run('INSERT INTO roles (name) VALUES (?)', [role]);
      });
    });
});
// --- Funciones globales fuera del bloque principal ---
function normalizeDate(dateStr) {
  if (!dateStr) return '';
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
  const parts = dateStr.split(/[\/\-.]/);
  if (parts.length === 3) {
    if (parts[0].length === 4) return `${parts[0]}-${parts[1].padStart(2,'0')}-${parts[2].padStart(2,'0')}`;
    if (parts[2].length === 4) return `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
  }
  return dateStr;
}

// --- Función y llamada migrateDates fuera del bloque principal ---
function migrateDates() {
  // Users
  db.all('SELECT id, birthdate FROM users', [], (err, rows) => {
    rows?.forEach(u => {
      const newDate = normalizeDate(u.birthdate);
      if (newDate !== u.birthdate) {
        db.run('UPDATE users SET birthdate = ? WHERE id = ?', [newDate, u.id]);
      }
    });
  });
  // Athletes
  db.all('SELECT id, birthdate FROM athletes', [], (err, rows) => {
    rows?.forEach(a => {
      const newDate = normalizeDate(a.birthdate);
      if (newDate !== a.birthdate) {
        db.run('UPDATE athletes SET birthdate = ? WHERE id = ?', [newDate, a.id]);
      }
    });
  });
  // Tournaments
  db.all('SELECT id, date FROM tournaments', [], (err, rows) => {
    rows?.forEach(t => {
      const newDate = normalizeDate(t.date);
      if (newDate !== t.date) {
        db.run('UPDATE tournaments SET date = ? WHERE id = ?', [newDate, t.id]);
      }
    });
  });
}
migrateDates();
// --- Insertar usuario de prueba si no existe ---
db.get('SELECT * FROM users WHERE email = ?', ['app@app.com'], (err, user) => {
  if (!user) {
    db.run('INSERT INTO users (first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
      ['App', '', 'Admin', '', 'app@app.com', '123', '1990-01-01', 'Activo', 'Administrador']);
    console.log('Usuario de prueba app@app.com agregado como Administrador');
  } else {
    db.run('UPDATE users SET role = ?, status = ? WHERE email = ?', ['Administrador', 'Activo', 'app@app.com']);
    console.log('Usuario app@app.com actualizado como Administrador');
  }
});
// =============================
// 5. ENDPOINTS DE DEPORTISTAS
// =============================
app.get('/athletes', authenticateToken, (req, res) => {
  // If the authenticated user is a Club, return only athletes belonging to that club
  if (req.user && req.user.role === 'Club') {
    db.get('SELECT club_id FROM users WHERE id = ?', [req.user.id], (err, row) => {
      if (err) return res.status(500).json({ error: 'Error en la base de datos' });
      const clubId = row?.club_id;
      if (!clubId) return res.json([]);
      db.all('SELECT * FROM athletes WHERE club_id = ?', [clubId], (err2, rows) => {
        if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
        res.json(rows);
      });
    });
    return;
  }
  else
  {
    db.all('SELECT * FROM athletes', [], (err, rows) => {
      if (err) {
        return res.status(500).json({ error: 'Error en la base de datos' });
      }
      res.json(rows);
    });
  }
});

app.post('/athletes', authenticateToken, (req, res) => {
  const { first_name, middle_name, last_name, second_last_name, email, birthdate, club_id, document_type, document_number, mobile_phone, rep_legal_name, rep_legal_email, rep_legal_phone } = req.body;
  // club_id is mandatory
  if (!club_id) return res.status(400).json({ error: 'club_id es obligatorio para deportistas' });
  // verify club exists
  db.get('SELECT id FROM clubs WHERE id = ?', [club_id], (err, clubRow) => {
    if (err) return res.status(500).json({ error: 'Error en la base de datos' });
    if (!clubRow) return res.status(400).json({ error: 'Club no encontrado' });
    // proceed with insert
    // Compute age from birthdate
    const computeAge = (bdate) => {
      if (!bdate) return null;
      const dob = new Date(bdate);
      if (isNaN(dob)) return null;
      const diff = Date.now() - dob.getTime();
      const ageDt = new Date(diff);
      return Math.abs(ageDt.getUTCFullYear() - 1970);
    };
    const age = computeAge(birthdate);
    // If athlete is a minor (under 18), representative fields are required
    if (age !== null && age < 18) {
      if (!rep_legal_name || !rep_legal_email || !rep_legal_phone) {
        return res.status(400).json({ error: 'Deportista menor de 18 años requiere datos de representante legal: nombre, email y celular' });
      }
    }
    db.run(
      'INSERT INTO athletes (first_name, middle_name, last_name, second_last_name, email, birthdate, club_id, document_type, document_number, mobile_phone, rep_legal_name, rep_legal_email, rep_legal_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [first_name, middle_name, last_name, second_last_name, email, birthdate, club_id, document_type || '', document_number || '', mobile_phone || '', rep_legal_name || '', rep_legal_email || '', rep_legal_phone || ''],
      function(err) {
        if (err) {
          console.error('Error al insertar deportista:', err.message, req.body);
          return res.status(400).json({ error: 'No se pudo registrar el deportista' });
        }
        db.get('SELECT * FROM athletes WHERE id = ?', [this.lastID], (err2, athlete) => {
          if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
          res.json({ message: 'Deportista registrado', athlete });
        });
      }
    );
  });
});
// Editar deportista
app.put('/athletes/:id', authenticateToken, (req, res) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'ID inválido' });
  const { first_name, middle_name, last_name, second_last_name, email, birthdate, club_id, document_type, document_number, mobile_phone, rep_legal_name, rep_legal_email, rep_legal_phone } = req.body;
  // club_id is mandatory when editing
  if (!club_id) return res.status(400).json({ error: 'club_id es obligatorio para deportistas' });
  // verify club exists before proceeding
  db.get('SELECT id FROM clubs WHERE id = ?', [club_id], (err, clubRow) => {
    if (err) return res.status(500).json({ error: 'Error en la base de datos' });
    if (!clubRow) return res.status(400).json({ error: 'Club no encontrado' });
    // continue with update below
    const fields = [];
    const values = [];
    if (first_name !== undefined) { fields.push('first_name = ?'); values.push(first_name); }
    if (middle_name !== undefined) { fields.push('middle_name = ?'); values.push(middle_name); }
    if (last_name !== undefined) { fields.push('last_name = ?'); values.push(last_name); }
    if (second_last_name !== undefined) { fields.push('second_last_name = ?'); values.push(second_last_name); }
    if (email !== undefined) { fields.push('email = ?'); values.push(email); }
    if (birthdate !== undefined) { fields.push('birthdate = ?'); values.push(normalizeDate(birthdate)); }
      // If birthdate provided, compute age and enforce representative fields for minors
      if (birthdate !== undefined) {
        const computeAge = (bdate) => {
          if (!bdate) return null;
          const dob = new Date(bdate);
          if (isNaN(dob)) return null;
          const diff = Date.now() - dob.getTime();
          const ageDt = new Date(diff);
          return Math.abs(ageDt.getUTCFullYear() - 1970);
        };
        const age = computeAge(birthdate);
        if (age !== null && age < 18) {
          if (!rep_legal_name || !rep_legal_email || !rep_legal_phone) {
            return res.status(400).json({ error: 'Deportista menor de 18 años requiere datos de representante legal: nombre, email y celular' });
          }
        }
      }
  if (club_id !== undefined) { fields.push('club_id = ?'); values.push(club_id); }
  if (document_type !== undefined) { fields.push('document_type = ?'); values.push(document_type); }
  if (document_number !== undefined) { fields.push('document_number = ?'); values.push(document_number); }
  if (mobile_phone !== undefined) { fields.push('mobile_phone = ?'); values.push(mobile_phone); }
    if (rep_legal_name !== undefined) { fields.push('rep_legal_name = ?'); values.push(rep_legal_name); }
    if (rep_legal_email !== undefined) { fields.push('rep_legal_email = ?'); values.push(rep_legal_email); }
    if (rep_legal_phone !== undefined) { fields.push('rep_legal_phone = ?'); values.push(rep_legal_phone); }

    if (fields.length === 0) return res.status(400).json({ error: 'No hay campos para actualizar' });

    const sql = `UPDATE athletes SET ${fields.join(', ')} WHERE id = ?`;
    values.push(id);
    db.run(sql, values, function(err) {
      if (err) {
        console.error('Error al actualizar deportista:', err.message);
        return res.status(500).json({ error: 'Error en la base de datos' });
      }
      if (this.changes === 0) return res.status(404).json({ error: 'Deportista no encontrado' });
      db.get('SELECT * FROM athletes WHERE id = ?', [id], (err2, athlete) => {
        if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
        res.json({ message: 'Deportista actualizado', athlete });
      });
    });
  });
});
// =============================
// ENDPOINTS DE CLUBS
// =============================
app.get('/clubs', authenticateToken, (req, res) => {
  // If user is a Club, return only their club
  if (req.user && req.user.role === 'Club') {
    db.get('SELECT club_id FROM users WHERE id = ?', [req.user.id], (err, row) => {
      if (err) return res.status(500).json({ error: 'Error en la base de datos' });
      const clubId = row?.club_id;
      if (!clubId) return res.json([]);
      db.get('SELECT * FROM clubs WHERE id = ?', [clubId], (err2, club) => {
        if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
        if (!club) return res.json([]);
        res.json([club]);
      });
    });
    return;
  }

  db.all('SELECT * FROM clubs', [], (err, rows) => {
    if (err) {
      console.error('Error al obtener clubs:', err.message);
      return res.status(500).json({ error: 'Error en la base de datos' });
    }
    res.json(rows);
  });
});

app.post('/clubs', authenticateToken, (req, res) => {
  // Don't allow a user with role 'Club' to create new clubs
  if (req.user && req.user.role === 'Club') {
    return res.status(403).json({ error: 'Usuarios con rol Club no pueden registrar nuevos clubes' });
  }
  const { name, legal_representative, status, address, phone, contact_person, director_tecnico, fisioterapeuta, asistente_tecnico, delegado } = req.body;
  db.run(
    'INSERT INTO clubs (name, legal_representative, status, address, phone, contact_person, director_tecnico, fisioterapeuta, asistente_tecnico, delegado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [name, legal_representative, status, address, phone, contact_person, director_tecnico || '', fisioterapeuta || '', asistente_tecnico || '', delegado || ''],
    function(err) {
      if (err) {
        console.error('Error al registrar club:', err.message, req.body);
        return res.status(400).json({ error: 'No se pudo registrar el club', details: err.message });
      }
      db.get('SELECT * FROM clubs WHERE id = ?', [this.lastID], (err2, club) => {
        if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
        res.json({ message: 'Club registrado', club });
      });
    }
  );
});

// Editar club
app.put('/clubs/:id', authenticateToken, (req, res) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'ID inválido' });
  // Do not allow users with role 'Club' to edit clubs
  if (req.user && req.user.role === 'Club') {
    return res.status(403).json({ error: 'Usuarios con rol Club no pueden editar clubes' });
  }
  // Prevent Club-role users from modifying clubs other than their own could be added; for now require authentication
  const { name, legal_representative, status, address, phone, contact_person, director_tecnico, fisioterapeuta, asistente_tecnico, delegado } = req.body;
  const fields = [];
  const values = [];
  if (name !== undefined) { fields.push('name = ?'); values.push(name); }
  if (legal_representative !== undefined) { fields.push('legal_representative = ?'); values.push(legal_representative); }
  if (status !== undefined) { fields.push('status = ?'); values.push(status); }
  if (address !== undefined) { fields.push('address = ?'); values.push(address); }
  if (phone !== undefined) { fields.push('phone = ?'); values.push(phone); }
  if (contact_person !== undefined) { fields.push('contact_person = ?'); values.push(contact_person); }
  if (director_tecnico !== undefined) { fields.push('director_tecnico = ?'); values.push(director_tecnico); }
  if (fisioterapeuta !== undefined) { fields.push('fisioterapeuta = ?'); values.push(fisioterapeuta); }
  if (asistente_tecnico !== undefined) { fields.push('asistente_tecnico = ?'); values.push(asistente_tecnico); }
  if (delegado !== undefined) { fields.push('delegado = ?'); values.push(delegado); }
  if (fields.length === 0) return res.status(400).json({ error: 'No hay campos para actualizar' });
  const sql = `UPDATE clubs SET ${fields.join(', ')} WHERE id = ?`;
  values.push(id);
  db.run(sql, values, function(err) {
    if (err) {
      console.error('Error al actualizar club:', err.message, req.body);
      return res.status(500).json({ error: 'No se pudo actualizar el club' });
    }
    if (this.changes === 0) return res.status(404).json({ error: 'Club no encontrado' });
    db.get('SELECT * FROM clubs WHERE id = ?', [id], (err2, club) => {
      if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
      res.json({ message: 'Club actualizado', club });
    });
  });
});
// =============================
// ENDPOINTS DE TORNEOS
// =============================
app.get('/tournaments', authenticateToken, (req, res) => {
  db.all('SELECT * FROM tournaments', [], (err, rows) => {
    if (err) {
      console.error('Error al obtener torneos:', err?.message);
      return res.status(500).json({ error: 'Error en la base de datos' });
    }
    res.json(rows);
  });
});

app.post('/tournaments', authenticateToken, (req, res) => {
  const { name, date, place, observations } = req.body;
  // Debugging: log the authenticated user and authorization header to verify role
  try {
    console.log('POST /tournaments - req.user:', req.user);
    console.log('POST /tournaments - Authorization header:', req.headers['authorization']);
  } catch (e) {
    console.error('Error logging request user/header:', e && e.message);
  }
  // Only allow certain roles to create tournaments — clubs are not permitted
  if (req.user.role === 'Club') {
    return res.status(403).json({ error: 'Usuarios con rol Club no pueden crear torneos' });
  }
  if (!name || !date) {
    return res.status(400).json({ error: 'Faltan campos obligatorios: name y date' });
  }
  const normalized = normalizeDate(date);
  db.run(
    'INSERT INTO tournaments (name, date, place, observations) VALUES (?, ?, ?, ?)',
    [name, normalized, place || '', observations || ''],
    function(err) {
      if (err) {
        console.error('Error al registrar torneo:', err.message, req.body);
        return res.status(400).json({ error: 'No se pudo registrar el torneo', details: err.message });
      }
      res.json({ message: 'Torneo registrado', tournamentId: this.lastID });
    }
  );
});

// =============================
// 6. ENDPOINTS DE AUTENTICACIÓN Y USUARIOS
// =============================
// ENDPOINTS DE USUARIOS (solo administrador)
app.get('/users', authenticateToken, (req, res) => {
  if (req.user.role !== 'Administrador') {
    return res.status(403).json({ error: 'Acceso solo para administradores' });
  }
  // Devolver users junto con el nombre del club (si existe)
  const sql = `SELECT users.*, clubs.name as club_name, clubs.id as club_id FROM users LEFT JOIN clubs ON users.club_id = clubs.id`;
  db.all(sql, [], (err, rows) => {
    if (err) return res.status(500).json({ error: 'Error en la base de datos' });
    res.json(rows);
  });
});

app.post('/users', authenticateToken, (req, res) => {
  if (req.user.role !== 'Administrador') {
    return res.status(403).json({ error: 'Acceso solo para administradores' });
  }
  const { first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role, club_id } = req.body;

  // Si el rol es Club, club_id es obligatorio
  if (role === 'Club' && !club_id) {
    return res.status(400).json({ error: 'club_id es obligatorio para usuarios con rol Club' });
  }

  // Si se proporcionó club_id, verificar que exista
  const ensureClub = club_id ? new Promise((resolve, reject) => {
    db.get('SELECT id FROM clubs WHERE id = ?', [club_id], (err, row) => {
      if (err) return reject(err);
      if (!row) return reject(new Error('Club no encontrado'));
      resolve();
    });
  }) : Promise.resolve();

  ensureClub.then(() => {
    // Validate password policy and hash before insert
    const pwd = password || '';
    const policy = /(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}/;
    if (pwd && !policy.test(pwd)) {
      return res.status(400).json({ error: 'La contraseña debe tener al menos 6 caracteres, una mayúscula, un número y un carácter especial' });
    }
    const hash = pwd ? bcrypt.hashSync(pwd, 10) : '';
    db.run(
      'INSERT INTO users (first_name, middle_name, last_name, second_last_name, email, password, birthdate, club_id, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [first_name, middle_name, last_name, second_last_name, email, hash, birthdate, club_id || null, status, role],
      function(err) {
        if (err) {
          console.error('Error al registrar usuario:', err.message, req.body);
          return res.status(400).json({ error: 'No se pudo registrar el usuario', details: err.message });
        }
        res.json({ message: 'Usuario registrado', userId: this.lastID });
      }
    );
  }).catch(err => {
    console.error('Error validando club:', err.message);
    return res.status(400).json({ error: err.message });
  });
});

// Editar usuario (administradores o el propio usuario)
app.put('/users/:id', authenticateToken, (req, res) => {
  const id = parseInt(req.params.id, 10);
  if (isNaN(id)) return res.status(400).json({ error: 'ID inválido' });

  // Solo admins o el propio usuario pueden editar
  if (req.user.role !== 'Administrador' && req.user.id !== id) {
    return res.status(403).json({ error: 'No tienes permiso para editar este usuario' });
  }

  const { first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role, club_id } = req.body;
  const normalizedBirth = birthdate ? normalizeDate(birthdate) : null;

  // Construir SQL dinámico para actualizar solo campos provistos
  const fields = [];
  const values = [];
  if (first_name !== undefined) { fields.push('first_name = ?'); values.push(first_name); }
  if (middle_name !== undefined) { fields.push('middle_name = ?'); values.push(middle_name); }
  if (last_name !== undefined) { fields.push('last_name = ?'); values.push(last_name); }
  if (second_last_name !== undefined) { fields.push('second_last_name = ?'); values.push(second_last_name); }
  if (email !== undefined) { fields.push('email = ?'); values.push(email); }
  if (password !== undefined) { fields.push('password = ?'); values.push(password); }
  if (normalizedBirth !== null) { fields.push('birthdate = ?'); values.push(normalizedBirth); }
  if (status !== undefined) { fields.push('status = ?'); values.push(status); }
  if (role !== undefined) { fields.push('role = ?'); values.push(role); }
  if (club_id !== undefined) { fields.push('club_id = ?'); values.push(club_id); }

  if (fields.length === 0) return res.status(400).json({ error: 'No hay campos para actualizar' });

  const sql = `UPDATE users SET ${fields.join(', ')} WHERE id = ?`;
  values.push(id);

  // If role is Club (either updating role to Club or user already Club), ensure club_id provided and exists
  const verifyClubPromise = (role === 'Club' || (club_id !== undefined)) ? new Promise((resolve, reject) => {
    const cid = club_id;
    if (!cid) return reject(new Error('club_id es obligatorio para rol Club'));
    db.get('SELECT id FROM clubs WHERE id = ?', [cid], (err, row) => {
      if (err) return reject(err);
      if (!row) return reject(new Error('Club no encontrado'));
      resolve();
    });
  }) : Promise.resolve();

  verifyClubPromise.then(() => {
    // If password provided, hash it before updating
    const pwdIndex = (function() {
      // find index of password in fields to hash the corresponding value
      for (let i = 0; i < fields.length; i++) {
        if (fields[i].startsWith('password')) return i;
      }
      return -1;
    })();
    if (pwdIndex !== -1) {
      try {
        // validate password policy before hashing
        const rawPwd = values[pwdIndex];
        const policy = /(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}/;
        if (rawPwd && !policy.test(rawPwd)) {
          return res.status(400).json({ error: 'La contraseña debe tener al menos 6 caracteres, una mayúscula, un número y un carácter especial' });
        }
        values[pwdIndex] = bcrypt.hashSync(rawPwd, 10);
      } catch (e) {
        console.error('Error hasheando password:', e.message);
      }
    }

    db.run(sql, values, function(err) {
    if (err) {
      console.error('Error al actualizar usuario:', err.message, req.body);
      return res.status(500).json({ error: 'Error al actualizar usuario', details: err.message });
    }
    if (this.changes === 0) return res.status(404).json({ error: 'Usuario no encontrado' });
    db.get('SELECT * FROM users WHERE id = ?', [id], (err, user) => {
      if (err) return res.status(500).json({ error: 'Error al recuperar usuario' });
      res.json({ message: 'Usuario actualizado', user });
    });
  });
  }).catch(err => {
    console.error('Error validando club en update:', err.message);
    return res.status(400).json({ error: err.message });
  });
});
app.post('/login', (req, res) => {
  const { email, password } = req.body;
  db.get('SELECT * FROM users WHERE email = ? AND password = ?', [email, password], (err, user) => {
    if (err) {
      return res.status(500).json({ error: 'Error en la base de datos' });
    }
    // Try fetching by email only to handle hashed passwords
    db.get('SELECT * FROM users WHERE email = ?', [email], (err2, u) => {
      if (err2) return res.status(500).json({ error: 'Error en la base de datos' });
      if (!u) return res.status(401).json({ error: 'Credenciales inválidas' });
      const stored = u.password || '';
      // If stored looks like a bcrypt hash (starts with $2), compare; else compare plaintext and migrate
      if (stored.startsWith('$2') || stored.startsWith('$argon') ) {
        if (!bcrypt.compareSync(password, stored)) return res.status(401).json({ error: 'Credenciales inválidas' });
      } else {
        // plaintext stored; compare directly
        if (password !== stored) return res.status(401).json({ error: 'Credenciales inválidas' });
        // migrate: hash and update
        try {
          const newHash = bcrypt.hashSync(password, 10);
          db.run('UPDATE users SET password = ? WHERE id = ?', [newHash, u.id]);
        } catch (e) {
          console.error('Error migrando password a hash:', e.message);
        }
      }
      const token = jwt.sign({ id: u.id, email: u.email, role: u.role }, JWT_SECRET, { expiresIn: '2h' });
      res.json({ message: 'Login exitoso', token });
    });
  });
});

// Endpoint para registrar usuario (para pruebas)
app.post('/register', (req, res) => {
  const {
    first_name = '',
    middle_name = '',
    last_name = '',
    second_last_name = '',
    email,
    password,
    birthdate = '',
    status = 'Activo',
    role = 'Deportista'
  } = req.body;
  db.run(
    'INSERT INTO users (first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [first_name, middle_name, last_name, second_last_name, email, password, birthdate, status, role],
    function(err) {
      if (err) {
        return res.status(400).json({ error: 'No se pudo registrar el usuario' });
      }
      res.json({ message: 'Usuario registrado', userId: this.lastID });
    }
  );
});

// Ejemplo de endpoint protegido
app.get('/profile', authenticateToken, (req, res) => {
  // Return full user record from DB (includes club_id and other fields)
  db.get('SELECT id, first_name, middle_name, last_name, second_last_name, email, birthdate, club_id, status, role FROM users WHERE id = ?', [req.user.id], (err, user) => {
    if (err) return res.status(500).json({ error: 'Error en la base de datos' });
    if (!user) return res.status(404).json({ error: 'Usuario no encontrado' });
    res.json({ message: 'Acceso autorizado', user });
  });
});

// Endpoint de logout (dummy, JWT es stateless)
app.post('/logout', (req, res) => {
  // El frontend debe borrar el token
  res.json({ message: 'Logout exitoso' });
});

app.listen(PORT, () => {
  console.log(`Servidor backend escuchando en puerto ${PORT}`);
});

