<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'article_category',
            'article_id',
            'category_name'
        );
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'article_country',
            'article_id',
            'country_code'
        );
    }
}
