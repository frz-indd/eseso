# Demo 2 Aplikasi + SSO API (PHP)

Struktur:

- `sso/` = server SSO (authorize, token, userinfo, logout)
- `app1/` = aplikasi 1 (Todo) login via SSO
- `app2/` = aplikasi 2 (Notes) login via SSO
- `assets/style.php` = CSS terpisah (di-serve oleh PHP)

Cara akses (XAMPP):

1. Jalankan Apache.
2. Jalankan MySQL (XAMPP).
2. Buka: `http://localhost/web12/`

Akun demo:

- `demo` / `demo123` (ubah di `sso/config.php`)

Client app (client_id/secret/redirect):

- ubah di `shared/sso_clients.php`

Catatan:

- Ini demo lokal (bukan OAuth2 lengkap), tapi alurnya mirip: `authorize -> code -> token -> userinfo`.
- Jika MySQL aktif, token/code disimpan di database `web12_demo` (tabel `oauth_codes`/`oauth_tokens`). Jika tidak, fallback ke file JSON: `data/sso_store.json`.
- Data App1/App2 disimpan di database (tabel `todos`/`notes`) bila MySQL aktif.

Setup DB (sudah saya eksekusi di localhost ini):

- Schema: `db/schema.sql`
- Seed user demo: `scripts/seed_demo_user.php`
- Buat user baru (hash aman): `php scripts/create_user.php <username> <password> [name]`

Endpoint utama:

- `GET /web12/sso/authorize.php?client_id=app1&redirect_uri=http://localhost/web12/app1/callback.php&state=xyz`
- `POST /web12/sso/token.php` (form): `client_id`, `client_secret`, `code`, `redirect_uri`
- `GET /web12/sso/userinfo.php` header: `Authorization: Bearer <token>`
