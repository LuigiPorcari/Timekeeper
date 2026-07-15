<?php

namespace App\Services;

use App\Models\Race;
use App\Models\ReportEntry;
use App\Models\ReportDayDsc;
use App\Models\ReportRaceDsc;
use App\Models\ReportDayAdmin;
use App\Models\ReportAdminRaceSettings;

class ReportCalculator
{
    /**
     * Alias di compatibilità.
     */
    public function computeRow(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings
    ): array {
        return $this->computeRowForRace($race, $entry, $dscRace, $settings);
    }

    /**
     * Alias per compatibilità con controller/view che chiamano ancora computeRowForDay().
     * In questo progetto "ForDay" qui è usato come "calcoli parte gara", non come calcolo giornaliero.
     */
    public function computeRowForDay(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportDayAdmin $adminAny,
        ?ReportAdminRaceSettings $settings
    ): array {
        return $this->computeRowForRace($race, $entry, $dscRace, $settings);
    }

    /**
     * Totali per gara, una riga per crono.
     */
    public function computeRowForRace(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings
    ): array {
        $userId = $entry->user_id ?? $entry->user?->id ?? null;
        $missedMeals = $this->computeMissedMealsCountForUser($dscRace, $userId);
        $missedMealsAmount = round($missedMeals * 15, 2);

        $coeff = $settings?->coeff_km !== null ? (float) $settings->coeff_km : 0.36;

        $kmAmount = $this->computeKmAmount($race, $entry, $settings);

        $pedaggi = (float) ($entry->pedaggi ?? 0);
        $vitto = (float) ($entry->vitto ?? 0);
        $speseVarie = (float) ($entry->spese_varie ?? 0);

        $vanCost = 0.00;
        if (($dscRace?->van_needed ?? false) && $settings?->van_cost !== null) {
            $vanCost = (float) $settings->van_cost;
        }

        $totalRacePart = round(
            $kmAmount + $pedaggi + $vitto + $speseVarie + $missedMealsAmount + $vanCost,
            2
        );

        return [
            'coeffKm' => round($coeff, 4),
            'kmAmount' => round($kmAmount, 2),

            'missedMeals' => $missedMeals,
            'missedMealsAmount' => $missedMealsAmount,

            'vanCostApplied' => round($vanCost, 2),

            'totalRacePart' => $totalRacePart,
        ];
    }

    /**
     * Importo Km:
     * - se domicilio == luogo gara => 10€
     * - altrimenti km * coeff_km
     */
    public function computeKmAmount(
        Race $race,
        ReportEntry $entry,
        ?ReportAdminRaceSettings $settings
    ): float {
        $coeff = $settings?->coeff_km !== null ? (float) $settings->coeff_km : 0.36;
        $km = $entry->km !== null ? (float) $entry->km : 0.0;

        $domicile = trim(mb_strtolower((string) ($entry->user->domicile ?? '')));
        $racePlace = trim(mb_strtolower((string) ($race->place ?? '')));

        if ($domicile !== '' && $racePlace !== '' && $domicile === $racePlace) {
            return 10.00;
        }

        return round($km * $coeff, 2);
    }

    public function computeMissedMealsAmount(?ReportRaceDsc $dscRace): float
    {
        $missedMeals = (int) ($dscRace?->missed_meals ?? 0);
        return round($missedMeals * 15, 2);
    }

    /**
     * Numero pasti mancati del singolo cronometrista.
     *
     * Il campo report_race_dsc.missed_meals resta il totale gara,
     * mentre missed_meals_detail contiene il dettaglio per user_id:
     * [user_id => ['pranzo' => true, 'cena' => true]].
     */
    public function computeMissedMealsCountForUser(?ReportRaceDsc $dscRace, $userId): int
    {
        if (!$dscRace) {
            return 0;
        }

        $detail = $this->normalizeMissedMealsDetail($dscRace->missed_meals_detail ?? null);

        // Compatibilità con vecchie gare: se non esiste ancora il dettaglio,
        // non possiamo sapere a chi appartenevano i pasti, quindi manteniamo il vecchio totale.
        if (empty($detail)) {
            return (int) ($dscRace->missed_meals ?? 0);
        }

        if ($userId === null || $userId === '') {
            return 0;
        }

        $mealData = $detail[$userId] ?? $detail[(string) $userId] ?? [];

        if (is_string($mealData)) {
            $decodedMealData = json_decode($mealData, true);
            $mealData = json_last_error() === JSON_ERROR_NONE && is_array($decodedMealData) ? $decodedMealData : [];
        }

        if (!is_array($mealData)) {
            $mealData = [];
        }

        $pranzo = !empty($mealData['pranzo']) || !empty($mealData['lunch']);
        $cena = !empty($mealData['cena']) || !empty($mealData['dinner']);

        return ($pranzo ? 1 : 0) + ($cena ? 1 : 0);
    }

    public function computeMissedMealsAmountForUser(?ReportRaceDsc $dscRace, $userId): float
    {
        return round($this->computeMissedMealsCountForUser($dscRace, $userId) * 15, 2);
    }

    private function normalizeMissedMealsDetail($detail): array
    {
        if (is_string($detail)) {
            $decoded = json_decode($detail, true);
            $detail = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        }

        return is_array($detail) ? $detail : [];
    }

    /**
     * Ore lavorate giornaliere da orari DSC.
     * Calcola mattina + pomeriggio. Se manca una coppia start/end, quella fascia vale 0.
     */
    public function computeWorkedHoursForDay(?ReportDayDsc $dscDayHours): float
    {
        if (!$dscDayHours) {
            return 0.0;
        }

        $morning = $this->diffHours($dscDayHours->morning_start ?? null, $dscDayHours->morning_end ?? null);
        $afternoon = $this->diffHours($dscDayHours->afternoon_start ?? null, $dscDayHours->afternoon_end ?? null);

        return round($morning + $afternoon, 2);
    }

    private function diffHours($start, $end): float
    {
        if (!$start || !$end) {
            return 0.0;
        }

        try {
            $start = trim((string) $start);
            $end = trim((string) $end);

            if (!preg_match('/\d{2}:\d{2}(?::\d{2})?/', $start, $startMatch)) {
                return 0.0;
            }

            if (!preg_match('/\d{2}:\d{2}(?::\d{2})?/', $end, $endMatch)) {
                return 0.0;
            }

            $start = $startMatch[0];
            $end = $endMatch[0];

            $formatStart = substr_count($start, ':') === 2 ? 'H:i:s' : 'H:i';
            $formatEnd = substr_count($end, ':') === 2 ? 'H:i:s' : 'H:i';

            $s = \Carbon\Carbon::createFromFormat($formatStart, $start);
            $e = \Carbon\Carbon::createFromFormat($formatEnd, $end);

            if ($e->lessThanOrEqualTo($s)) {
                return 0.0;
            }

            return round($s->diffInMinutes($e) / 60, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Ordinario:
     * - da più di 0 fino a 4 ore: 30€ totali
     * - oltre 4 ore: 30€ + 6€/h dalla quinta ora in poi
     */
    private function amountOrdinary(float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        if ($hours <= 4.0) {
            return 30.0;
        }

        return round(30.0 + (($hours - 4.0) * 6.0), 2);
    }

    /**
     * Specialistico:
     * - da più di 0 fino a 4 ore: 40€ totali
     * - oltre 4 ore: 40€ + 10€/h dalla quinta ora in poi
     */
    private function amountSpecial(float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        if ($hours <= 4.0) {
            return 40.0;
        }

        return round(40.0 + (($hours - 4.0) * 10.0), 2);
    }

    public function computeServiceTotalForDay(?ReportDayAdmin $adminDayRow): array
    {
        $ordHours = (float) ($adminDayRow?->hours_ordinary_service ?? 0.0);
        $specHours = (float) ($adminDayRow?->hours_special_service ?? 0.0);

        $ordAmount = $this->amountOrdinary($ordHours);
        $specAmount = $this->amountSpecial($specHours);

        // Tariffa media: serve solo per la colonna "Tar.", perché la tariffa reale è a scaglioni.
        $ordRateAvg = $ordHours > 0 ? round($ordAmount / $ordHours, 2) : 0.0;
        $specRateAvg = $specHours > 0 ? round($specAmount / $specHours, 2) : 0.0;

        $total = round($ordAmount + $specAmount, 2);

        return [
            'ordHours' => round($ordHours, 2),
            'specHours' => round($specHours, 2),

            'ordRate' => $ordRateAvg,
            'specRate' => $specRateAvg,

            'ordAmount' => $ordAmount,
            'specAmount' => $specAmount,
            'totalService' => $total,
        ];
    }

    public function computeTotaleCrono(iterable $adminRowsForUser): float
    {
        $sum = 0.0;

        foreach ($adminRowsForUser as $row) {
            $day = $this->computeServiceTotalForDay($row);
            $sum += (float) ($day['totalService'] ?? 0.0);
        }

        return round($sum, 2);
    }

    public function computeGrandTotalForCrono(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings,
        iterable $adminRowsForUser
    ): array {
        $racePart = $this->computeRowForRace($race, $entry, $dscRace, $settings);
        $totaleCrono = $this->computeTotaleCrono($adminRowsForUser);

        $grandTotal = round(((float) ($racePart['totalRacePart'] ?? 0.0)) + $totaleCrono, 2);

        return [
            'totalRacePart' => (float) ($racePart['totalRacePart'] ?? 0.0),
            'totaleCrono' => $totaleCrono,
            'grandTotal' => $grandTotal,
        ];
    }

    public function computeRaceDaysCount(Race $race): int
    {
        $start = \Carbon\Carbon::parse($race->date_of_race)->startOfDay();
        $end = $race->date_end ? \Carbon\Carbon::parse($race->date_end)->startOfDay() : $start->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return $start->diffInDays($end) + 1;
    }
}