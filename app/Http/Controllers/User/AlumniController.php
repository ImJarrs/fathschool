<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlumniController extends Controller
{
    /**
     * Display a listing of the alumni.
     */
    public function index(Request $request)
    {
        abort_if(! userCan('admission.index'), 403);

        $query = Alumni::with('user:id,name,email')->latest();

        // filter => keyword
        if ($request->has('keyword') && $request->keyword !== null) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->keyword.'%')
                  ->orWhere('email', 'like', '%'.$request->keyword.'%')
                  ->orWhere('jurusan', 'like', '%'.$request->keyword.'%')
                  ->orWhere('tahun_lulus', 'like', '%'.$request->keyword.'%');
            });
        }

        // filter => status
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $alumnis = $query->paginate(15)->onEachSide(-1)->withQueryString();

        return inertia('Admin/Alumni/Index', [
            'alumnis' => $alumnis,
            'filter_data' => $request,
        ]);
    }

    /**
     * Show the form for batch promoting class 12 students to alumni.
     */
    public function create(Request $request)
    {
        abort_if(! userCan('admission.create'), 403);

        // Get all class 12 (XII) courses
        $xii_courses = Course::where('name', 'like', 'XII%')
            ->orWhere('name', 'like', 'XII %')
            ->get(['id', 'name', 'slug']);

        // Get students enrolled in class 12 courses
        $students = User::active()
            ->student()
            ->whereHas('courses', function ($q) {
                $q->whereHas('course', function ($q) {
                    $q->where('name', 'like', 'XII%')
                      ->orWhere('name', 'like', 'XII %');
                });
            })
            ->with(['courses.course:id,name', 'profile'])
            ->latest()
            ->get();

        // filter => keyword
        if ($request->has('keyword') && $request->keyword !== null) {
            $students = $students->filter(function ($student) use ($request) {
                return str_contains(strtolower($student->name), strtolower($request->keyword))
                    || str_contains(strtolower($student->email ?? ''), strtolower($request->keyword))
                    || str_contains(strtolower($student->id_reference ?? ''), strtolower($request->keyword));
            })->values();
        }

        // filter => course_id
        if ($request->has('course_id') && $request->course_id !== null && $request->course_id !== '') {
            $students = $students->filter(function ($student) use ($request) {
                return $student->courses->contains(function ($uc) use ($request) {
                    return $uc->course_id == $request->course_id;
                });
            })->values();
        }

        // Add is_alumni flag
        $alumni_user_ids = Alumni::pluck('user_id')->filter()->toArray();
        $students = $students->map(function ($student) use ($alumni_user_ids) {
            $student->is_alumni = in_array($student->id, $alumni_user_ids);

            return $student;
        });

        return inertia('Admin/Alumni/BatchPromote', [
            'xii_courses' => $xii_courses,
            'students' => $students,
            'filter_data' => $request,
        ]);
    }

    /**
     * Batch promote selected class 12 students to alumni.
     */
    public function store(Request $request)
    {
        abort_if(! userCan('admission.create'), 403);

        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'tahun_lulus' => 'required|numeric|digits:4|min:2000|max:2100',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        // Normalize tahun_lulus to string (frontend sends it as integer from number input)
        $tahun_lulus = (string) $request->tahun_lulus;

        DB::beginTransaction();

        try {
            $students = User::whereIn('id', $request->student_ids)
                ->student()
                ->with(['courses.course:id,name', 'profile'])
                ->get();

            $created = 0;

            foreach ($students as $student) {
                // Skip if already an alumni
                if (Alumni::where('user_id', $student->id)->exists()) {
                    continue;
                }

                // Determine jurusan from the class 12 course name
                $jurusan = $this->extractJurusan($student);

                Alumni::create([
                    'user_id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'gender' => $student->gender,
                    'profile_photo_path' => $student->profile_photo_path,
                    'jurusan' => $jurusan,
                    'tahun_lulus' => $tahun_lulus,
                    'status' => 'active',
                ]);

                $created++;
            }

            DB::commit();

            $this->flashSuccess($created.' siswa berhasil dipindahkan menjadi alumni.');

            return redirect()->route('alumni.index');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Batch promote alumni failed: '.$e->getMessage());

            return back()->withErrors(['error' => 'Gagal memindahkan siswa menjadi alumni. Silakan coba lagi.']);
        }
    }

    /**
     * Remove the specified alumni from storage.
     */
    public function destroy(Alumni $alumni)
    {
        abort_if(! userCan('admission.destroy'), 403);

        $alumni->delete();

        $this->flashSuccess('Alumni berhasil dihapus.');

        return back();
    }

    /**
     * Extract jurusan (major) from the student's class 12 course name.
     */
    protected function extractJurusan(User $student)
    {
        $course = $student->courses->first(function ($uc) {
            if ($uc->course && str_starts_with(strtoupper($uc->course->name), 'XII')) {
                return true;
            }
            return false;
        });

        if ($course && $course->course) {
            $name = $course->course->name;
            // Example: "XII RPL 1" -> "RPL"
            $parts = explode(' ', $name);
            array_shift($parts); // remove "XII"
            // Remove trailing number if present
            $parts = array_filter($parts, function ($part) {
                return !is_numeric($part);
            });

            return implode(' ', $parts) ?: $name;
        }

        return null;
    }
}