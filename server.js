const express = require('express');
const mysql = require('mysql2');
const bcrypt = require('bcrypt');
const path = require('path');

const app = express();
app.use(express.json());

// Servir automáticamente tus archivos estáticos (index.html, imágenes, CSS, JS)
app.use(express.static(path.join(__dirname)));

// Conexión a tu base de datos MySQL local
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'powerspot'
});

db.connect((err) => {
    if (err) {
        console.error('Error al conectar a MySQL:', err);
        return;
    }
    console.log('Conectado a la base de datos MySQL de PowerSpot');
});

// Ruta de Registro
app.post('/api/register', async (req, res) => {
    const { nombre, email, password } = req.body;

    if (!nombre || !email || !password) {
        return res.json({ success: false, message: "Por favor, completa todos los campos." });
    }

    db.query('SELECT id FROM usuarios WHERE email = ?', [email], async (err, results) => {
        if (results.length > 0) {
            return res.json({ success: false, message: "Este correo ya está registrado." });
        }

        try {
            const hashedPassword = await bcrypt.hash(password, 10);
            db.query('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)', 
                [nombre, email, hashedPassword], (err, result) => {
                    if (err) {
                        return res.json({ success: false, message: "Error al registrar el usuario." });
                    }
                    res.json({ success: true, message: "¡Cuenta creada con éxito! Ya puedes iniciar sesión." });
                });
        } catch (error) {
            res.json({ success: false, message: "Error al procesar la contraseña." });
        }
    });
});

// Ruta de Login
app.post('/api/login', (req, res) => {
    const { email, password } = req.body;

    if (!email || !password) {
        return res.json({ success: false, message: "Ingresa tu correo y contraseña." });
    }

    db.query('SELECT * FROM usuarios WHERE email = ?', [email], async (err, results) => {
        if (results.length === 0) {
            return res.json({ success: false, message: "Correo o contraseña incorrectos." });
        }

        const user = results[0];
        const match = await bcrypt.compare(password, user.password);

        if (match) {
            res.json({
                success: true,
                message: `¡Bienvenido de nuevo, ${user.nombre}!`,
                user: { id: user.id, nombre: user.nombre, email: user.email }
            });
        } else {
            res.json({ success: false, message: "Correo o contraseña incorrectos." });
        }
    });
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Servidor Node.js corriendo en http://localhost:${PORT}`);
});