<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'article_country',
            'country_code',
            'article_id'
        );
    }

    public function newsSources(): BelongsToMany
    {
        return $this->belongsToMany(
            NewsSource::class,
            'country_news_source',
            'country_code',
            'news_source_id'
        );
    }
}
