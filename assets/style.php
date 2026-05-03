<?php
declare(strict_types=1);

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=300');

$app = $_GET['app'] ?? 'default';
$accent = match ($app) {
    'sso' => '#7c3aed',
    'app1' => '#2563eb',
    'app2' => '#059669',
    default => '#0f172a',
};
?>
:root{
  --bg:#0b1020;
  --panel:#0f172a;
  --card:#111c33;
  --text:#e5e7eb;
  --muted:#94a3b8;
  --border:rgba(148,163,184,.18);
  --accent:<?= $accent ?>;
  --accent2:rgba(255,255,255,.08);
  --shadow:0 10px 30px rgba(0,0,0,.35);
  --radius:16px;
  --mono:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
  --sans:ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:var(--sans);
  background:
    radial-gradient(1000px 500px at 20% -10%, rgba(124,58,237,.25), transparent 55%),
    radial-gradient(900px 600px at 110% 10%, rgba(37,99,235,.20), transparent 55%),
    radial-gradient(700px 450px at 50% 120%, rgba(5,150,105,.18), transparent 55%),
    var(--bg);
  color:var(--text);
}
a{color:inherit;text-decoration:none}
code{font-family:var(--mono); font-size:.95em}
.container{max-width:1000px;margin:40px auto;padding:0 18px}
.header{display:flex;gap:10px;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;margin-bottom:18px}
.header h1{margin:0;font-size:28px;letter-spacing:.2px}
.muted{color:var(--muted);margin:6px 0 0 0}
.grid{display:grid;grid-template-columns:repeat(3, minmax(0,1fr));gap:14px;margin:18px 0}
@media (max-width:860px){.grid{grid-template-columns:1fr}}
.card{
  background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:16px 16px;
  box-shadow:var(--shadow);
}
.card:hover{border-color:rgba(148,163,184,.35)}
.card h2{margin:0 0 8px 0;font-size:18px}
.card p{margin:0;color:var(--muted);line-height:1.4}
.row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 12px;border-radius:12px;
  border:1px solid rgba(148,163,184,.25);
  background:rgba(255,255,255,.04);
  cursor:pointer;
}
.btn.primary{
  background:linear-gradient(180deg, color-mix(in srgb, var(--accent) 75%, #ffffff 0%), color-mix(in srgb, var(--accent) 55%, #000000 0%));
  border-color:rgba(255,255,255,.18);
}
.btn:hover{border-color:rgba(148,163,184,.45)}
.btn.primary:hover{filter:brightness(1.06)}
.input{
  width:100%;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(148,163,184,.22);
  border-radius:12px;
  padding:10px 12px;
  color:var(--text);
  outline:none;
}
.input:focus{border-color:color-mix(in srgb, var(--accent) 65%, #ffffff 0%)}
.label{display:block;margin:10px 0 6px 0;color:var(--muted);font-size:13px}
.alert{
  border:1px solid rgba(239,68,68,.45);
  background:rgba(239,68,68,.08);
  padding:10px 12px;
  border-radius:12px;
  margin:10px 0;
}
.pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid rgba(148,163,184,.22);
  background:rgba(255,255,255,.03);
  color:var(--muted);
}
.divider{height:1px;background:var(--border);margin:14px 0}
.list{margin:0;padding-left:18px;color:var(--muted)}
.table{width:100%;border-collapse:collapse}
.table td,.table th{border-bottom:1px solid var(--border);padding:10px 8px;text-align:left}
.table th{color:var(--muted);font-weight:600;font-size:13px}

