<?php

namespace Bale\Frasasti\Models;

use Bale\Core\Traits\LogsActivity;
use Bale\Frasasti\Database\Factories\AduanCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AduanCategory extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'frasasti_aduan_categories';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime:d M Y H:i',
        'updated_at' => 'datetime:d M Y H:i',
    ];

    protected static function newFactory(): AduanCategoryFactory
    {
        return AduanCategoryFactory::new();
    }

    public function aduans(): HasMany
    {
        return $this->hasMany(Aduan::class, 'aduan_category_id');
    }
}
