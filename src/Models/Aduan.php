<?php

namespace Bale\Frasasti\Models;

use App\Models\User;
use Bale\Core\Traits\LogsActivity;
use Bale\Frasasti\Database\Factories\AduanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
use Spatie\Activitylog\Support\LogOptions;

class Aduan extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    /**
     * Atribut yang boleh masuk ke activity log.
     *
     * PII (nama, NIP, nomor WhatsApp, IP) sengaja tidak dicatat: nilai-nilai
     * itu sudah terenkripsi di tabel utama, sedangkan `activity_log` adalah
     * tabel terpisah yang tidak terenkripsi dan biasanya dibaca role yang
     * lebih luas.
     */
    public const LOGGED_ATTRIBUTES = [
        'ref_code',
        'status',
        'aduan_category_id',
        'deskripsi',
        'handled_at',
        'handled_by',
    ];

    /**
     * Atribut yang tidak boleh berubah setelah aduan dibuat.
     *
     * Yang boleh berubah hanya `status`, `handled_at`, dan `handled_by` —
     * tiga field itu menandai progres penanganan oleh admin.
     */
    public const IMMUTABLE_ATTRIBUTES = [
        'ref_code',
        'nama_lengkap',
        'nip',
        'wa_number',
        'aduan_category_id',
        'deskripsi',
    ];

    protected static function newFactory(): AduanFactory
    {
        return AduanFactory::new();
    }

    /**
     * Batasi atribut yang dicatat, menggantikan `logAll()` dari trait bersama.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::LOGGED_ATTRIBUTES)
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }

    /**
     * Aduan bersifat immutable: isi aduan yang dikirim publik tidak boleh
     * diubah setelah tersimpan, sehingga riwayat yang tercatat tidak dapat
     * direkayasa oleh siapa pun.
     */
    protected static function booted(): void
    {
        static::updating(function (Aduan $aduan): void {
            $violations = array_intersect(
                self::IMMUTABLE_ATTRIBUTES,
                array_keys($aduan->getDirty())
            );

            if ($violations !== []) {
                throw new LogicException(sprintf(
                    'Aduan bersifat immutable: atribut [%s] tidak dapat diubah setelah dibuat.',
                    implode(', ', $violations)
                ));
            }
        });
    }

    protected $table = 'frasasti_aduans';

    protected $fillable = [
        'ref_code',
        'nama_lengkap',
        'nip',
        'wa_number',
        'aduan_category_id',
        'deskripsi',
        'status',
        'ip_address',
        'handled_at',
        'handled_by',
    ];

    protected $casts = [
        'nama_lengkap' => 'encrypted',
        'nip' => 'encrypted',
        'wa_number' => 'encrypted',
        'handled_at' => 'datetime:d M Y H:i',
        'created_at' => 'datetime:d M Y H:i',
        'updated_at' => 'datetime:d M Y H:i',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AduanCategory::class, 'aduan_category_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by', 'uuid');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'processed' => 'blue',
            'done' => 'green',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'processed' => 'Diproses',
            'done' => 'Selesai',
            default => 'Unknown',
        };
    }
}
