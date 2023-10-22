<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $categoriesOnArticle = isset($this->categories) ? $this->categories->pluck('name') : collect();
        $categoriesOnSource = isset($this->source->categories) ? $this->source->categories->pluck('name') : collect();
        $categories = collect()
            ->merge($categoriesOnArticle)
            ->merge($categoriesOnSource)
            ->values()
            ->filter()
            ->unique()
            ->map(fn(string $category) => [
                'id' => $category,
                'name' => Str::ucfirst($category)
            ])->values();

        return [
            'id' => $this->id,
            'source' => [
                'id' => $this->source->id,
                'name' => $this->source->name,
            ],
            'author' => $this->author,
            'title' => $this->title,
            'description' => strip_tags($this->description),
            'url' => $this->url,
            'image' => $this->image,
            'publishedAt' => $this->published_at,
            'categories' => $categories,
            'language' => $this->language,
        ];
    }
}
