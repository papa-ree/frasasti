# bale/frasasti

Package fitur **Aduan Masuk** untuk ekosistem Bale. Menyediakan form aduan publik
berbasis reCAPTCHA, panel operasional untuk admin/landlord, activity log, dan API
read-only.

Package ini adalah extractor mandiri dari fitur Aduan yang sebelumnya berada di
`paparee/rakaca`. Tabel `frasasti_*` berada pada **koneksi landlord** (default),
bukan database tenant.

## Kebutuhan

| Dependency | Alasan |
|------------|--------|
| `bale/core` | Auth, permission, komponen UI, `Sanitize`, layout, `LogsActivity` |
| `bale/api` | Base API controller, token scope, middleware `bale.api` |
| `paparee/rakaca` | Reuse layout landlord `rakaca::layouts.app` |

## Instalasi

```bash
composer require bale/frasasti
```

Publish migration & jalankan:

```bash
php artisan frasasti:publish-migration
php artisan frasasti:migrate
```

Alternatif dengan tag publishing:

```bash
php artisan vendor:publish --tag="frasasti:migrations"
php artisan migrate
```

Seed kategori + permission:

```bash
php artisan frasasti:install
```

Publish config:

```bash
php artisan frasasti:publish-config
# atau
php artisan vendor:publish --tag="frasasti:config"
```

## Command

| Command | Fungsi |
|---------|--------|
| `frasasti:install` | Seed 4 kategori aduan + permission `aduan.read`, `aduan.update` (idempotent) |
| `frasasti:publish-migration` | Copy migration stub `.php.stub` ke `database/migrations` tanpa menimpa file yang ada |
| `frasasti:migrate` | Jalankan `frasasti:publish-migration` lalu `migrate` |
| `frasasti:publish-config` | Publish `config/frasasti.php` |
| `frasasti:import-legacy-aduan` | Import data dari `rakaca_aduans` (opsi `--dry-run`) |

## Routing

| Route | Nama | Akses |
|-------|------|-------|
| `GET /bantuan` | `frasasti.aduan.index` | Publik (`throttle:30,1`) |
| `GET /frasasti/aduan-masuk` | `frasasti.aduan-masuk.index` | `aduan.read` |
| `GET /frasasti/aduan-masuk/{aduan}` | `frasasti.aduan-masuk.edit` | `aduan.update` |

## Data & Privasi

- **Isi aduan immutable.** `nama_lengkap`, `nip`, `wa_number`, `aduan_category_id`,
  `deskripsi`, dan `ref_code` tidak dapat diubah setelah aduan dibuat — percobaan
  update akan ditolak dengan `LogicException` (lihat `Aduan::IMMUTABLE_ATTRIBUTES`).
  Yang boleh berubah hanya `status`, `handled_at`, dan `handled_by`.
- **PII tidak masuk activity log.** `Aduan` meng-override `getActivitylogOptions()`
  dengan whitelist `Aduan::LOGGED_ATTRIBUTES`, menggantikan `logAll()` dari trait
  bersama. Karena `activity_log` adalah tabel terpisah yang **tidak terenkripsi**,
  nama/NIP/nomor WA/IP tidak disalin ke sana. Yang tercatat hanya `ref_code`,
  `status`, `aduan_category_id`, `deskripsi`, `handled_at`, dan `handled_by`.
- `nama_lengkap`, `nip`, dan `wa_number` memakai cast `encrypted` — transparan di
  level model, nilainya selalu ter-dekripsi saat diakses.
- Nomor WhatsApp **tidak pernah** dikirim ke `wa.me` saat membangun pesan Share ke
  support, demi privasi pengirim.

## API

Scope: `frasasti.aduan.read` — data personal dikembalikan **ter-dekripsi**
(atas keputusan pemilik package).

| Method | Endpoint |
|--------|----------|
| `GET` | `/api/v1/frasasti/aduans` |
| `GET` | `/api/v1/frasasti/aduans/{aduan}` |

Query parameter (index): `status` (`pending`\|`processed`\|`done`), `category_id`,
`q` (cocok `ref_code`), `per_page` (default 50, max 100).

```bash
curl -H "Authorization: Bearer <token>" \
     -H "Accept: application/json" \
     https://example.test/api/v1/frasasti/aduans?status=pending
```

## Testing

```bash
vendor\bin\pest packages\frasasti
```

## License

MIT. Lihat [LICENSE.md](LICENSE.md).
