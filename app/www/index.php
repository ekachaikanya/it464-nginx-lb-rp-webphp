<?php
// ── อ่านค่าจาก environment variables ────────────────────────
$serverName  = getenv('SERVER_NAME')  ?: 'Unknown Server';
$serverColor = getenv('SERVER_COLOR') ?: '#22d3a5';
$serverEmoji = getenv('SERVER_EMOJI') ?: '⚡';
$hostname    = gethostname();
$phpVersion  = PHP_VERSION;
$serverIp    = $_SERVER['SERVER_ADDR']    ?? 'N/A';
$timestamp   = date('Y-m-d H:i:s');

// ── Client IP — อ่านจาก header ที่ reverse proxy เพิ่มให้ ──
$clientIp = $_SERVER['HTTP_X_REAL_IP']
         ?? $_SERVER['HTTP_X_FORWARDED_FOR']
         ?? $_SERVER['REMOTE_ADDR']
         ?? 'N/A';

// ── รู้ว่า request ผ่าน proxy ชนิดใด ─────────────────────────
$proxyBy     = $_SERVER['HTTP_X_PROXY_BY']     ?? 'none';
$forwardedBy = $_SERVER['HTTP_X_FORWARDED_BY'] ?? 'none';
$via = ($proxyBy !== 'none') ? $proxyBy : (($forwardedBy !== 'none') ? $forwardedBy : 'direct');

// ── นับ request (per-process, resets on restart) ─────────────
static $count = 0;
$count++;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($serverName) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'IBM Plex Mono', 'Courier New', monospace;
      background: #03070f;
      color: #c8d8f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    /* Blueprint grid background */
    body::before {
      content: '';
      position: fixed; inset: 0; pointer-events: none;
      background-image:
        linear-gradient(rgba(0,180,255,.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,180,255,.02) 1px, transparent 1px);
      background-size: 32px 32px;
    }

    .card {
      position: relative;
      background: #080f1c;
      border: 2px solid <?= htmlspecialchars($serverColor) ?>;
      border-radius: 20px;
      padding: 44px 48px;
      max-width: 520px;
      width: 100%;
      box-shadow: 0 0 80px <?= htmlspecialchars($serverColor) ?>22,
                  0 0 30px <?= htmlspecialchars($serverColor) ?>11;
      animation: appear .4s cubic-bezier(.2,0,.2,1);
    }

    @keyframes appear {
      from { opacity: 0; transform: scale(.92) translateY(12px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Corner accent */
    .card::before, .card::after {
      content: '';
      position: absolute;
      width: 20px; height: 20px;
    }
    .card::before {
      top: -2px; left: -2px;
      border-top: 3px solid <?= htmlspecialchars($serverColor) ?>;
      border-left: 3px solid <?= htmlspecialchars($serverColor) ?>;
      border-radius: 4px 0 0 0;
    }
    .card::after {
      bottom: -2px; right: -2px;
      border-bottom: 3px solid <?= htmlspecialchars($serverColor) ?>;
      border-right: 3px solid <?= htmlspecialchars($serverColor) ?>;
      border-radius: 0 0 4px 0;
    }

    .icon { font-size: 3.2rem; margin-bottom: 14px; }

    h1 {
      font-size: 1.7rem;
      font-weight: 700;
      color: <?= htmlspecialchars($serverColor) ?>;
      margin-bottom: 6px;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: <?= htmlspecialchars($serverColor) ?>18;
      border: 1px solid <?= htmlspecialchars($serverColor) ?>44;
      color: <?= htmlspecialchars($serverColor) ?>;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: .72rem;
      margin-bottom: 28px;
      letter-spacing: .5px;
    }

    .badge::before {
      content: '';
      width: 6px; height: 6px;
      border-radius: 50%;
      background: <?= htmlspecialchars($serverColor) ?>;
      animation: blink 1.5s infinite;
    }

    @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .2; } }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 20px;
    }

    .cell {
      background: #0c1628;
      border: 1px solid #0f2040;
      border-radius: 10px;
      padding: 13px 14px;
      transition: border-color .2s;
    }

    .cell:hover { border-color: <?= htmlspecialchars($serverColor) ?>44; }

    .lbl {
      color: #3d5878;
      font-size: .65rem;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .val {
      font-size: .85rem;
      color: #c8d8f0;
      word-break: break-all;
    }

    .val-highlight { color: <?= htmlspecialchars($serverColor) ?>; }

    .via-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(245,166,35,.08);
      border: 1px solid rgba(245,166,35,.25);
      color: #f5a623;
      padding: 8px 14px;
      border-radius: 8px;
      font-size: .78rem;
      width: 100%;
      margin-bottom: 16px;
    }

    .divider {
      border: none;
      border-top: 1px solid #0f2040;
      margin: 16px 0;
    }

    .foot {
      color: #3d5878;
      font-size: .72rem;
      line-height: 1.7;
      text-align: center;
    }

    .foot .ts { color: #4a6a8a; }

    .hint {
      margin-top: 14px;
      padding: 10px 14px;
      background: rgba(255,255,255,.02);
      border: 1px dashed #0f2040;
      border-radius: 8px;
      color: #3d5878;
      font-size: .72rem;
      text-align: center;
      line-height: 1.6;
    }
    .hint span { color: <?= htmlspecialchars($serverColor) ?>; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon"><?= htmlspecialchars($serverEmoji) ?></div>
    <h1><?= htmlspecialchars($serverName) ?></h1>
    <div class="badge">Nginx Web Server + PHP-FPM <?= PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION ?></div>

    <!-- Via badge -->
    <div class="via-badge">
      ⚡ via: <?= htmlspecialchars($via) ?>
    </div>

    <div class="grid">
      <div class="cell">
        <div class="lbl">Hostname</div>
        <div class="val val-highlight"><?= htmlspecialchars($hostname) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Server IP</div>
        <div class="val"><?= htmlspecialchars($serverIp) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Client IP</div>
        <div class="val"><?= htmlspecialchars($clientIp) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">PHP Version</div>
        <div class="val"><?= htmlspecialchars($phpVersion) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">X-Forwarded-By</div>
        <div class="val"><?= htmlspecialchars($forwardedBy) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">X-Proxy-By</div>
        <div class="val"><?= htmlspecialchars($proxyBy) ?></div>
      </div>
    </div>

    <hr class="divider">

    <div class="foot">
      <span class="ts"><?= $timestamp ?></span><br>
      Served by: <span style="color: <?= htmlspecialchars($serverColor) ?>"><?= htmlspecialchars($serverName) ?></span>
    </div>

    <div class="hint">
      🔄 กด <span>F5</span> เพื่อดู Load Balancing — server จะสลับไปเรื่อยๆ<br>
      <span>:80</span> = Load Balancer &nbsp;|&nbsp; <span>:8080</span> = Reverse Proxy (app1 เสมอ)
    </div>
  </div>
</body>
</html>
