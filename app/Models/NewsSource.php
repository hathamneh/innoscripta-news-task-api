<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->hasMany(Article::class, 'source_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_news_source',
            'news_source_id',
            'category_name'
        );
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'country_news_source',
            'news_source_id',
            'country_code'
        );
    }
}
