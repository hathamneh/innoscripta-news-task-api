<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    public $incrementing = false;

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'article_category',
            'category_name',
            'article_id'
        );
    }

    public function newsSources(): BelongsToMany
    {
        return $this->belongsToMany(
            NewsSource::class,
            'category_news_source',
            'category_name',
            'news_source_id'
        );
    }
}
