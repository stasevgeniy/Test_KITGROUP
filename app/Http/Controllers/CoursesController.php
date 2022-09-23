<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    public function courses(Request $request) {
        $first = $request->first;
        $second = $request->second;
        if(($first && $second) || (!$first && !$second) || ($first && !is_numeric($first)) || ($second && !is_numeric($second))) {
            return response()->json([
                'success' => false,
                'error' => 'Check params',
            ], 400);
        }

        $courses = DB::table('bestcourses')
        ->where(function ($query) use ($first, $second) {
            $query->where('first', '=', $first)
                  ->orWhere('second', '=', $second);
        })
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ], 200);
    }

    public function getCourse($first, $second, Request $request) {
        if(!is_numeric($first) || !is_numeric($second)) {
            return response()->json([
                'success' => false,
                'error' => 'Check params',
            ], 400);
        }

        $course = DB::table('bestcourses')
        ->where('first', '=', $first)
        ->where('second', '=', $second)
        ->orderBy('id', 'desc')
        ->first();

        return response()->json([
            'success' => true,
            'data' => $course
        ], 200);
    }
}
