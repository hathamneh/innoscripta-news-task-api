<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'source_id',
        'author',
        'title',
        'description',
        'url',
        'image',
        'published_at',
        'content',
        'category',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'name');
    }

    public function scopeFromProvider($query, string $provider): void
    {
        $query->where('provider', $provider);
    }

    public function scopeFromSource($query, string $sourceId): void
    {
        $query->where('source_id', $sourceId);
    }
}
