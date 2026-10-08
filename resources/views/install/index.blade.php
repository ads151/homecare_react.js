<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install Website</title>
<style>
  :root{--blue:#1f5c99;--green:#2c9a55;--dark:#0f2a52;--line:#dbe4ea;--bg:#eef4f7;--bad:#c0392b}
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:#1f2937;line-height:1.5}
  .wrap{max-width:760px;margin:0 auto;padding:28px 16px 60px}
  .top{display:flex;align-items:center;gap:14px;margin-bottom:18px}
  .top img{width:56px;height:56px;border-radius:12px;background:#fff}
  h1{font-size:1.5rem;margin:0;color:var(--dark)}
  .sub{margin:2px 0 0;color:#5b6b7b}
  .card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;margin-bottom:18px}
  .card h2{font-size:1.05rem;margin:0 0 4px;color:var(--dark)}
  .card p.help{margin:0 0 14px;color:#5b6b7b;font-size:.92rem}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .full{grid-column:1/-1}
  label{display:block;font-weight:600;font-size:.9rem;margin-bottom:5px}
  input{width:100%;padding:11px 12px;border:1px solid var(--line);border-radius:10px;font-size:1rem}
  input:focus{outline:2px solid #bcd3ea;border-color:var(--blue)}
  small{display:block;color:#6b7a89;font-size:.8rem;margin-top:4px}
  ul.checks{list-style:none;margin:0;padding:0;columns:2;column-gap:20px}
  ul.checks li{padding:4px 0;font-size:.9rem;break-inside:avoid}
  .ok{color:var(--green)} .no{color:var(--bad)}
  .hint{display:block;color:#6b7a89;font-size:.8rem}
  .err{background:#fdecea;border:1px solid #f5c2bd;color:#8a1f13;border-radius:12px;padding:12px 14px;margin-bottom:16px}
  .field-err{color:var(--bad);font-size:.82rem;margin-top:4px}
  button{width:100%;padding:15px;border:0;border-radius:40px;background:var(--green);color:#fff;font-size:1.05rem;font-weight:700;cursor:pointer}
  button[disabled]{opacity:.6;cursor:wait}
  .steps{font-size:.9rem;color:#5b6b7b;margin:0;padding-left:18px}
  @media (max-width:640px){.grid{grid-template-columns:1fr}ul.checks{columns:1}}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <img src="{{ rtrim(request()->getBaseUrl(), '/') }}/images/general/logo.png" alt="">
    <div>
      <h1>Website Installer</h1>
      <p class="sub">One-time setup. Takes about 1 minute.</p>
    </div>
  </div>

  @php $allOk = collect($checks)->every(fn ($c) => $c['ok']); @endphp

  <div class="card">
    <h2>1. Server check {!! $allOk ? '<span class="ok">✔ All good</span>' : '<span class="no">✖ Fix the red items</span>' !!}</h2>
    <ul class="checks">
      @foreach ($checks as $c)
        <li>
          <span class="{{ $c['ok'] ? 'ok' : 'no' }}">{{ $c['ok'] ? '✔' : '✖' }}</span> {{ $c['label'] }}
          @unless ($c['ok'])<span class="hint">{{ $c['hint'] }}</span>@endunless
        </li>
      @endforeach
    </ul>
  </div>

  @if ($errors->any())
    <div class="err">
      @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="post" action="{{ rtrim(request()->getBaseUrl(), '/') }}/install" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Installing… please wait';">
    @csrf
    <div class="card">
      <h2>2. Database</h2>
      <p class="help">Create it first in <b>Hostinger hPanel → Databases → MySQL Databases</b>, then copy the details here.</p>
      <div class="grid">
        <div>
          <label for="db_host">Database host</label>
          <input id="db_host" name="db_host" value="{{ old('db_host', 'localhost') }}" required>
          <small>On Hostinger this is usually <b>localhost</b>.</small>
        </div>
        <div>
          <label for="db_port">Port</label>
          <input id="db_port" name="db_port" value="{{ old('db_port', '3306') }}" required>
        </div>
        <div class="full">
          <label for="db_database">Database name</label>
          <input id="db_database" name="db_database" value="{{ old('db_database') }}" placeholder="u123456789_homecare" required>
        </div>
        <div>
          <label for="db_username">Database username</label>
          <input id="db_username" name="db_username" value="{{ old('db_username') }}" placeholder="u123456789_admin" required>
        </div>
        <div>
          <label for="db_password">Database password</label>
          <input id="db_password" type="password" name="db_password" autocomplete="new-password">
        </div>
      </div>
    </div>

    <div class="card">
      <h2>3. Website address</h2>
      <label for="site_url">Website URL</label>
      <input id="site_url" name="site_url" value="{{ old('site_url', $siteUrl) }}" required>
      <small>This admin / backend address (detected automatically). Example: https://admin.yourdomain.com</small>
      <p></p>
      <label for="frontend_url">Next.js website address (optional)</label>
      <input id="frontend_url" name="frontend_url" value="{{ old('frontend_url') }}" placeholder="https://www.yourdomain.com">
      <small>Where visitors open the website. You can also set this later in Admin → Site Settings → Website (Next.js).</small>
    </div>

    <div class="card">
      <h2>4. Admin login</h2>
      <p class="help">You will use this to log in to the admin panel at <b>/admin</b>.</p>
      <div class="grid">
        <div>
          <label for="admin_name">Your name</label>
          <input id="admin_name" name="admin_name" value="{{ old('admin_name', 'Admin') }}" required>
        </div>
        <div>
          <label for="admin_email">Admin email</label>
          <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" required>
        </div>
        <div>
          <label for="admin_password">Password (min 8 characters)</label>
          <input id="admin_password" type="password" name="admin_password" autocomplete="new-password" required minlength="8">
        </div>
        <div>
          <label for="admin_password_confirmation">Confirm password</label>
          <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" autocomplete="new-password" required minlength="8">
        </div>
      </div>
    </div>

    <div class="card">
      <h2>5. Install</h2>
      <ol class="steps">
        <li>Creates all database tables</li>
        <li>Copies all current website text, services, images and settings into the database</li>
        <li>Creates your admin login and locks this installer</li>
      </ol>
      <p></p>
      <button type="submit" {{ $allOk ? '' : 'disabled' }}>Install Website</button>
    </div>
  </form>
</div>
</body>
</html>
