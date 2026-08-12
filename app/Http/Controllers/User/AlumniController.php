<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    /**
     * Display all alumni (students assigned to the 'alumni' class).
     */
    public function index(Request $request)
    {
        abort_if(! userCan('admission.index'), 403);

        $query = User::active()->student()->latest()->with(['profile', 'courses.course:id,name', 'parents']);

        // filter => keyword
        if ($request->has('keyword') && $request->keyword !== null) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->keyword.'%')
                  ->orWhere('email', 'like', '%'.$request->keyword.'%');
            });
        }

        // restrict to course with slug 'alumni'
        $query->whereHas('courses', function ($q) {
            $q->whereHas('course', function ($q) {
                $q->where('slug', 'alumni');
            });
        });

        $users = $query->paginate(15)->onEachSide(-1)->withQueryString();

        $classes = Course::get(['id', 'name', 'slug']);

        return inertia('Admin/Admission/AllAlumni', [
            'alumnies' => $users,
            'filter_data' => $request,
            'classes' => $classes,
        ]);
    }
}
