<?php

namespace App\Services;

use App\Models\Race;
use App\Models\ReportEntry;
use App\Models\ReportRaceDsc;
use App\Models\ReportAdminRaceSettings;
use App\Models\ReportDayDsc;
use App\Models\ReportDayAdmin;
use Illuminate\Support\Carbon;

class RaceReportFullBuilder
{
    public function build(Race $race, ReportCalculator $calc): array
    {
        // 1) Giorni gara (array di stringhe Y-m-d)
        $start = Carbon::parse($race->date_of_race)->startOfDay();
        $end = $race->date_end ? Carbon::parse($race->date_end)->startOfDay() : $start->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->toDateString();
        }

        $raceDaysCount = $calc->computeRaceDaysCount($race);

        // 2) Dati base
        $timekeepers = $race->users()
            ->select('users.id', 'users.name', 'users.surname', 'users.domicile')
            ->orderBy('surname')
            ->get();

        $entries = ReportEntry::where('race_id', $race->id)
            ->with(['user', 'attachments'])
            ->get()
            ->keyBy('user_id');

        $dscRace = ReportRaceDsc::where('race_id', $race->id)->first();
        $settings = ReportAdminRaceSettings::where('race_id', $race->id)->first();

        // 3) Orari DSC per giorno (work_date normalizzato)
        $dscHoursByDay = ReportDayDsc::where('race_id', $race->id)
            ->whereIn('work_date', $days)
            ->get()
            ->keyBy(function ($row) {
                return $row->work_date instanceof \Carbon\Carbon
                    ? $row->work_date->toDateString()
                    : (string) $row->work_date;
            });

        // 4) Ore segreteria per giorno+crono
        $adminRows = ReportDayAdmin::where('race_id', $race->id)
            ->whereIn('work_date', $days)
            ->get();

        $adminByDayByUser = [];
        foreach ($adminRows as $row) {
            $dayKey = $row->work_date instanceof \Carbon\Carbon
                ? $row->work_date->toDateString()
                : (string) $row->work_date;

            $adminByDayByUser[$dayKey][$row->user_id] = $row;
        }

        // 5) Righe “complete” per ogni crono
        $rows = $timekeepers->map(function ($tk) use ($race, $entries, $dscRace, $settings, $days, $dscHoursByDay, $adminByDayByUser, $calc) {
            $entry = $entries->get($tk->id);

            if (!$entry) {
                $entry = new ReportEntry([
                    'race_id' => $race->id,
                    'user_id' => $tk->id,
                    'km' => null,
                    'pedaggi' => null,
                    'vitto' => null,
                    'alloggio' => null,
                    'spese_varie' => null,
                    'note' => null,
                    'confirmed' => false,
                ]);
                $entry->setRelation('user', $tk);
                $entry->setRelation('attachments', collect());
            }

            $sysRace = $calc->computeRowForRace($race, $entry, $dscRace, $settings);

            $perDay = [];
            $adminRowsForUser = [];

            foreach ($days as $day) {
                $dscDay = $dscHoursByDay->get($day);
                $workedHours = $calc->computeWorkedHoursForDay($dscDay);

                $adminDayRow = $adminByDayByUser[$day][$tk->id] ?? null;
                if ($adminDayRow) {
                    $adminRowsForUser[] = $adminDayRow;
                }

                $service = $calc->computeServiceTotalForDay($adminDayRow);

                $perDay[$day] = [
                    'day' => $day,
                    'dscDay' => $dscDay,
                    'workedHours' => $workedHours,
                    'adminDayRow' => $adminDayRow,
                    'service' => $service,
                ];
            }

            $totaleCrono = $calc->computeTotaleCrono($adminRowsForUser);
            $grand = $calc->computeGrandTotalForCrono($race, $entry, $dscRace, $settings, $adminRowsForUser);

            return [
                'user' => $tk,
                'entry' => $entry,
                'sysRace' => $sysRace,
                'totaleCrono' => $totaleCrono,
                'grandTotal' => $grand['grandTotal'] ?? 0.0,
                'perDay' => $perDay,
            ];
        });

        return [
            'race' => $race,
            'raceDaysCount' => $raceDaysCount,
            'days' => $days,
            'dscRace' => $dscRace,
            'settings' => $settings,
            'rows' => $rows,
        ];
    }

    // Variante: build filtrato su UN crono (per admin report “per crono”)
    public function buildForTimekeeper(Race $race, int $userId, ReportCalculator $calc): array
    {
        $data = $this->build($race, $calc);
        $data['rows'] = collect($data['rows'])->filter(fn($r) => (int) $r['user']->id === (int) $userId)->values();
        return $data;
    }
}
