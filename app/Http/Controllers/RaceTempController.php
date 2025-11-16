<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceTemp;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RaceTempController extends Controller
{
    public function createRaceTempShow()
    {
        return view('guest.createRaceTemp');
    }

    public function storeRaceTemp(Request $request)
    {
        // Tipi gara da config
        $typeMap = config('races.types', []);
        $allowedTypes = array_keys($typeMap);

        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in($allowedTypes)],
            'date_of_race' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_of_race',
            'place' => 'required|string|max:255',
            'ente_fatturazione' => 'required|string|max:255',
            'preventivo_da_aggiungere' => 'required|boolean',
            'programma_allegato' => 'nullable|file|mimes:pdf,doc,docx,odt,zip|max:10240',
            'note' => 'nullable|string|max:5000',
        ]);

        $allegatoPath = null;
        if ($request->hasFile('programma_allegato')) {
            $allegatoPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
        }

        RaceTemp::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'date_of_race' => $validated['date_of_race'],
            'date_end' => $validated['date_end'] ?? null,
            'place' => $validated['place'],
            'ente_fatturazione' => $validated['ente_fatturazione'],
            'preventivo_da_aggiungere' => (bool) $validated['preventivo_da_aggiungere'],
            'programma_allegato' => $allegatoPath,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('homepage')->with('success', 'Gara temporanea creata con successo!');
    }

    public function accept(RaceTemp $race)
    {
        // 1) Recupero apparecchiature di base del tipo da config
        $typeMap = config('races.types', []);
        $baseEquip = $typeMap[$race->type] ?? [];

        // 2) Namespacing: typeSlug__labelSlug
        $typeSlug = Str::slug($race->type);
        $specialization = array_values(array_map(function ($label) use ($typeSlug) {
            $labelSlug = Str::slug($label);
            return "{$typeSlug}__{$labelSlug}";
        }, array_filter($baseEquip, fn($v) => filled($v))));

        // 3) Crea la gara definitiva usando le apparecchiature namespacizzate
        $newRace = Race::create([
            'name' => $race->name,
            'type' => $race->type,
            'date_of_race' => $race->date_of_race,
            'date_end' => $race->date_end,
            'place' => $race->place,
            'ente_fatturazione' => $race->ente_fatturazione,
            'programma_allegato' => $race->programma_allegato, // già su 'public'
            'note' => $race->note,
            'specialization_of_race' => $specialization,
        ]);

        // 4) Notifica
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara accettata',
            'emails.race.accepted',
            ['raceName' => $race->name]
        );

        // 5) Elimina la temporanea
        $race->delete();

        return redirect()->back()->with('success', 'Gara accettata e notificata con successo.');
    }

    public function reject(RaceTemp $race)
    {
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara rifiutata',
            'emails.race.rejected',
            ['raceName' => $race->name]
        );

        $race->delete();

        return redirect()->back()->with('success', 'Gara rifiutata e notificata.');
    }
}
