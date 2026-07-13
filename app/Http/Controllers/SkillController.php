<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Skill::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name'],
        ]);

        $skill = Skill::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Skill added successfully.',
            'data'    => $skill,
        ], 201);
    }
}
