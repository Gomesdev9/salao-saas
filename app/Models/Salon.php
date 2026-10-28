<?php

namespace App\Models;

use App\Enums\BusinessType;
use Database\Factories\SalonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'business_type', 'phone', 'city', 'pix_key'])]
class Salon extends Model
{
    /** @use HasFactory<SalonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'business_type' => BusinessType::class,
        ];
    }

    /**
     * Gera um slug único a partir do nome, ex.: "Salão da Maria" -> "salao-da-maria-2".
     */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'salao';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
