<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Events\AfterSheet;

class AdministrationAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $start_date;
    protected $end_date;
    protected $keyword;
    protected $department;
    protected $dates = [];
    protected $data; // will hold prepared data rows for mapping and summaries

    public function __construct($start_date, $end_date, $keyword = null, $department = null)
    {
        $this->start_date = Carbon::parse($start_date)->toDateString();
        $this->end_date = Carbon::parse($end_date)->toDateString();
        $this->keyword = $keyword;
        $this->department = $department;

        // build dates array (skipping weekends)
        $period = collect(Carbon::parse($this->start_date)->daysUntil($this->end_date));
        $this->dates = $period->filter(function ($date) {
            $dayName = Carbon::parse((string)$date)->format('l');
            return !in_array($dayName, ['Saturday', 'Sunday']);
        })->map(fn($date) => Carbon::parse((string)$date)->toDateString())->values()->toArray();
    }

    public function collection()
    {
        $users = User::active()->administration()->with('department', 'leaves');

        if ($this->keyword) {
            $users->where('name', 'LIKE', '%' . $this->keyword . '%')
                  ->orWhere('email', 'LIKE', '%' . $this->keyword . '%');
        }

        if ($this->department && $this->department !== 'all') {
            $users->whereHas('department', function ($q) {
                $q->where('slug', $this->department);
            });
        }

        $admins = $users->get();

        $this->data = $admins->map(function ($admin, $index) {
            // get attendance
            $attend = Attendance::where('user_id', $admin->id)
                ->whereBetween('date', [$this->start_date, $this->end_date])
                ->get()
                ->groupBy('date');

            $leaveDates = collect();
            foreach ($admin->leaves as $leave) {
                $period = collect(Carbon::parse($leave->start)->daysUntil($leave->end))
                    ->map(fn($d) => Carbon::parse((string)$d)->toDateString());
                $leaveDates = $leaveDates->merge($period);
            }

            $attendanceStatus = collect($this->dates)->mapWithKeys(function ($date) use ($attend, $leaveDates) {
                if ($leaveDates->contains($date)) {
                    return [$date => 'I'];
                }
                $a = $attend->get($date, collect())->first();
                return [$date => $a ? 'H' : 'A'];
            });

            $summary = [
                'hadir' => $attendanceStatus->filter(fn($s) => $s === 'H')->count(),
                'sakit' => 0,
                'ijin' => $attendanceStatus->filter(fn($s) => $s === 'I')->count(),
                'alfa' => $attendanceStatus->filter(fn($s) => $s === 'A')->count(),
            ];

            return [
                'no' => $index + 1,
                'user_name' => $admin->name,
                'study_program' => '-',
                'attendance' => $attendanceStatus->values()->toArray(),
                'summary' => $summary,
            ];
        });

        return $this->data;
    }

    public function map($row): array
    {
        return array_merge(
            [
                $row['no'],
                $row['user_name'],
                $row['study_program'] ?? '-',
            ],
            $row['attendance'],
            [
                (int) $row['summary']['hadir'],
                (int) $row['summary']['sakit'],
                (int) $row['summary']['ijin'],
                (int) $row['summary']['alfa'],
            ]
        );
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama TU', 'Program Studi'];
        foreach ($this->dates as $date) {
            $headings[] = Carbon::parse($date)->format('d/m/Y');
        }
        $headings = array_merge($headings, ['Hadir', 'Sakit', 'Ijin', 'Alfa']);
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = count($this->headings());
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:{$lastCol}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        foreach (range(1, $colCount) as $i) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        return [];
    }

        public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $totalHadir = collect($this->data)->sum(fn($row) => $row['summary']['hadir']);
                $totalIjin = collect($this->data)->sum(fn($row) => $row['summary']['ijin']);
                $totalAlfa = collect($this->data)->sum(fn($row) => $row['summary']['alfa']);

                $startRow = collect($this->data)->count() + 3;
                $sheet->setCellValue("B{$startRow}", "Total Kehadiran: {$totalHadir}");
                $sheet->setCellValue("B" . ($startRow + 1), "Total Izin: {$totalIjin}");
                $sheet->setCellValue("B" . ($startRow + 2), "Total Alfa: {$totalAlfa}");
            },
        ];
    }
}
