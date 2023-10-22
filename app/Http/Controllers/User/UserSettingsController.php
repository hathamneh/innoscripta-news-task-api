<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->settings;
    }

    public function update(Request $request)
    {
        $request->validate([
            'sources.*' => 'int|nullable',
            'categories.*' => 'string|nullable',
            'countries.*' => 'string|nullable',
            'languages.*' => 'string|nullable',
        ]);

        $params = $request->only([
            'sources',
            'categories',
            'countries',
            'languages',
        ]);

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'settings' => $params,
            ]
        );

        return response()->noContent();
    }
}
