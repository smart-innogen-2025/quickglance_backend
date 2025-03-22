<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

use App\Models\Shortcut;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $userId = Auth::id();


        $validated = $request->validate([
            'model' => 'required|string',
            'query' => 'required|string',
        ]);

        $modelName = ucfirst(strtolower($validated['model']));
        $query = $validated['query'];

        
        $modelMap = [
            'Shortcut' => Shortcut::class,
        ];

        if (array_key_exists($modelName, $modelMap)) {
            $modelClass = $modelMap[$modelName];
            $results = $modelClass::where('user_id', $userId)
                                  ->where('name', 'like', "%{$query}%")
                                  ->get();
            return response()->json($results);
        } else {
            return response()->json(['error' => 'Model not found'], 404);
        }
    }
}
