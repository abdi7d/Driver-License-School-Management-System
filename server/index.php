<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Driver License School Server</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: #f4f7fb;
      color: #1f2937;
    }
    .card {
      max-width: 640px;
      margin: 24px;
      padding: 32px;
      border-radius: 16px;
      background: #ffffff;
      box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
      line-height: 1.6;
    }
    h1 {
      margin-top: 0;
      margin-bottom: 12px;
    }
    code {
      background: #eef2f7;
      padding: 2px 6px;
      border-radius: 6px;
    }
    a {
      color: #0f62fe;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <main class="card">
    <h1>Driver License School Server is running</h1>
    <p>This PHP server hosts the backend API. The root page is here to avoid a 404 when you open <code>http://localhost:8000</code>.</p>
    <p>Next steps:</p>
    <ul>
      <li>Import the database schema from <code>server/database/schema.sql</code></li>
      <li>Open the frontend from the <code>client</code> folder in Apache/XAMPP</li>
      <li>Call API endpoints under <code>/api/</code></li>
    </ul>
    <p>Example API endpoint: <a href="/api/login.php">/api/login.php</a></p>
  </main>
</body>
</html>