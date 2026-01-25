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
     * Alias "compatibilità".
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
     * ✅ Alias per compatibilità con controller/view che chiamano ancora computeRowForDay().
     * In questo progetto "ForDay" qui è usato come "calcoli parte gara (non giornalieri)".
     * $adminAny non serve: lo ignoro.
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
     * Totali "per gara" (una riga per crono).
     * Include:
     * - kmAmount
     * - spese crono (pedaggi/vitto/alloggio/spese_varie)
     * - mancati pasti * 15 (DSC gara)
     * - vanCost (se van_needed, preso da settings gara)
     */
    public function computeRowForRace(
        Race $race,
        ReportEntry $entry,
        ?ReportRaceDsc $dscRace,
        ?ReportAdminRaceSettings $settings
    ): array {
        $missedMeals = (int) ($dscRace?->missed_meals ?? 0);
        $missedMealsAmount = round($missedMeals * 15, 2);

        $coeff = $settings?->coeff_km !== null ? (float) $settings->coeff_km : 0.36;

        $kmAmount = $this->computeKmAmount($race, $entry, $settings);

        $pedaggi = (float) ($entry->pedaggi ?? 0);
        $vitto = (float) ($entry->vitto ?? 0);
        $alloggio = (float) ($entry->alloggio ?? 0);
        $speseVarie = (float) ($entry->spese_varie ?? 0);

        // Importo furgone (per gara) preso da settings gara
        $vanCost = 0.00;
        if (($dscRace?->van_needed ?? false) && $settings?->van_cost !== null) {
            $vanCost = (float) $settings->van_cost;
        }

        $totalRacePart = round(
            $kmAmount + $pedaggi + $vitto + $alloggio + $speseVarie + $missedMealsAmount + $vanCost,
            2
        );

        return [
            'coeffKm' => round($coeff, 4),
            'kmAmount' => round($kmAmount, 2),

            'missedMeals' => $missedMeals,
            'missedMealsAmount' => $missedMealsAmount,

            'vanCostApplied' => round($vanCost, 2),

            // Totale "parte gara" (tutto ciò che non è giornaliero)
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
     * Ore lavorate (per giornata) da orari DSC:
     * (fine mattina - inizio mattina) + (fine pomeriggio - inizio pomeriggio)
     */
    public function computeWorkedHoursForDay(?ReportDayDsc $dscDayHours): float
    {
        if (!$dscDayHours) {
            return 0.0;
        }

        $m = $this->diffHours($dscDayHours->morning_start, $dscDayHours->morning_end);
        $a = $this->diffHours($dscDayHours->afternoon_start, $dscDayHours->afternoon_end);

        return round($m + $a, 2);
    }

    private function diffHours(?string $start, ?string $end): float
    {
        if (!$start || !$end) {
            return 0.0;
        }

        try {
            $start = trim($start);
            $end = trim($end);

            // ✅ supporta sia "10:58" che "10:58:00"
            $formatStart = (substr_count($start, ':') === 2) ? 'H:i:s' : 'H:i';
            $formatEnd = (substr_count($end, ':') === 2) ? 'H:i:s' : 'H:i';

            $s = \Carbon\Carbon::createFromFormat($formatStart, $start);
            $e = \Carbon\Carbon::createFromFormat($formatEnd, $end);

            if ($e->lt($s)) {
                return 0.0;
            }

            return round($s->diffInMinutes($e) / 60, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }


    /**
     * ✅ Importo Ordinario a scaglioni:
     * prime 4 ore = 30€/h
     * oltre 4 ore = 36€/h (30 + 6)
     */
    private function amountOrdinary(float $hours): float
    {
        if ($hours <= 0)
            return 0.0;

        $first = min($hours, 4.0);
        $extra = max($hours - 4.0, 0.0);

        return round(($first * 30.0) + ($extra * 36.0), 2);
    }

    /**
     * ✅ Importo Specialistico a scaglioni:
     * prime 4 ore = 40€/h
     * oltre 4 ore = 50€/h (40 + 10)
     */
    private function amountSpecial(float $hours): float
    {
        if ($hours <= 0)
            return 0.0;

        $first = min($hours, 4.0);
        $extra = max($hours - 4.0, 0.0);

        return round(($first * 40.0) + ($extra * 50.0), 2);
    }

    /**
     * Totale servizio per giornata, per crono:
     * = importoOrd (scaglioni) + importoSpec (scaglioni)
     */
    public function computeServiceTotalForDay(?ReportDayAdmin $adminDayRow): array
    {
        $ordHours = (float) ($adminDayRow?->hours_ordinary_service ?? 0.0);
        $specHours = (float) ($adminDayRow?->hours_special_service ?? 0.0);

        $ordAmount = $this->amountOrdinary($ordHours);
        $specAmount = $this->amountSpecial($specHours);

        // “tariffa” mostrabile: qui metto la media (solo per display), NON usata nei calcoli
        $ordRateAvg = $ordHours > 0 ? round($ordAmount / $ordHours, 2) : 0.0;
        $specRateAvg = $specHours > 0 ? round($specAmount / $specHours, 2) : 0.0;

        $total = round($ordAmount + $specAmount, 2);

        return [
            'ordHours' => round($ordHours, 2),
            'specHours' => round($specHours, 2),

            // Solo display (media effettiva)
            'ordRate' => $ordRateAvg,
            'specRate' => $specRateAvg,

            'ordAmount' => $ordAmount,
            'specAmount' => $specAmount,
            'totalService' => $total,
        ];
    }

    /**
     * TotaleCrono (per crono, per gara)
     * Somma di "totalService" su tutti i giorni della gara.
     */
    public function computeTotaleCrono(iterable $adminRowsForUser): float
    {
        $sum = 0.0;

        foreach ($adminRowsForUser as $row) {
            $day = $this->computeServiceTotalForDay($row);
            $sum += (float) ($day['totalService'] ?? 0.0);
        }

        return round($sum, 2);
    }

    /**
     * GrandTotal (per crono, per gara)
     * = TotaleCrono (giornaliero sommato) + totale parte gara (km + spese + mancati + furgone)
     *
     * Contributo organizzativo + spese varie gara:
     * sono UNA SOLA VOLTA per tutta la gara totale => NON li sommo sul singolo crono.
     */
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

    /**
     * Giorni gara (inclusivo): date_end - date_of_race + 1
     */
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
