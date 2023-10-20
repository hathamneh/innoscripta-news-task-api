<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class NewsSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'id_from_provider',
        'name',
        'description',
        'url',
        'category',
        'language',
        'country'
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'name');
    }

    public function scopeFromProvider($query, string $provider): void
    {
        $query->where('provider', $provider);
    }

    public static function getOrCreateMany(Collection $providerSources): Collection
    {
        $providerSourceIds = $providerSources->pluck('id_from_provider')->toArray();
        $existingSources = self::whereIn('id_from_provider', $providerSourceIds)->get();

        $existingSourceIds = $existingSources->pluck('id_from_provider')->toArray();
        $sourcesToCreate = $providerSources->reject(function ($source) use ($existingSourceIds) {
            return in_array($source['id_from_provider'], $existingSourceIds);
        });
        self::insert($sourcesToCreate->toArray());

        return self::whereIn('id_from_provider', $providerSourceIds)->get();
    }
}
