<?php

namespace App\Services\NewsProviders\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NewsProviderModel
{
    protected array $attributes = [];

    protected array $providerAttributes = [];

    protected array $mapper = [];

    public function __construct(?array $attrs = null)
    {
        if ($attrs) {
            $this->fill($attrs);
        }
    }

    public function fill(array $attrs): void
    {
        $this->providerAttributes = $attrs;
        $this->mapData();
    }

    protected function mapData(): void
    {
        if (count($this->providerAttributes) === 0) {
            return;
        }
        foreach ($this->attributes as $name => $value) {
            try {
                $providerAttributeField = $this->mapper[$name] ?? $name;

                if (is_string($providerAttributeField) || is_array($providerAttributeField)) {
                    $this->attributes[$name] = $this->getAttributeValue($providerAttributeField, $this->providerAttributes);
                } else {
                    $this->attributes[$name] = $providerAttributeField($this->providerAttributes);
                }
            } catch (\Exception $e) {
                Log::error('Error mapping data for ' . $name . ': ' . $e->getMessage());
            }
        }
    }

    public function setMapper(array $mapper): void
    {
        $this->mapper = $mapper;
        $this->mapData();
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }


    protected function getAttributeValue(array|string $attributeName, array $source)
    {
        if (is_array($attributeName)) {
            foreach ($attributeName as $name) {
                $value = Arr::get($source, $name);
                if (!is_null($value)) {
                    return $value;
                }
            }
            return null;
        }
        return Arr::get($source, $attributeName);
    }
}
