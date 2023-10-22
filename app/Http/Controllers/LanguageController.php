<?php

namespace App\Http\Controllers;

use App\Utils\LanguagesUtils;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    public function lookup()
    {
        return collect(LanguagesUtils::languages())->reduce(function ($carry, $name, $code) {
            $carry[] = [
                'id' => $code,
                'name' => Str::title($name),
            ];
            return $carry;
        }, []);
    }
}
