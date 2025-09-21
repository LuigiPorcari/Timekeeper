<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceTemp;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use App\Mail\RaceAcceptedMail;
use App\Mail\RaceRejectedMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class RaceTempController extends Controller
{
    private array $allowedTypes = [
        'NUOTO -NUOTO SALVAMENTO',
        'SCI ALPINO – SCI NORDICO',
        'ATLETICA LEGGERA',
        'MOTORALLY',
        'RALLY',
        'ENDURO MOTO',
        'ENDURO MTB',
        'MOTOCROSS',
        'CANOA',
        'CANOTTAGGIO',
        'CICLISMO SU STRADA',
        'CICLISMO PISTA',
        'DOWHINILL',
        'AUTO REGOLARITA’',
        'AUTO STORICHE',
        'AUTOMOBILSMO CIRCUITO',
        'CONCORSO IPPICO',
        'TROTTO',
    ];

    private array $typeToSpecs = [
        'NUOTO -NUOTO SALVAMENTO' => ['elaborazione_dati', 'vasca'],
        'SCI ALPINO – SCI NORDICO' => ['partenza', 'arrivo', 'elaborazione_dati_completa', 'elaborazione_dati_parziale_live'],
        'ATLETICA LEGGERA' => ['fotofinish', 'manuale'],
        'MOTORALLY' => ['centro_classifica', 'tracking'],
        'RALLY' => ['centro_classifica', 'start_ps', 'fine_ps', 'controllo_orari_co', 'riordini', 'assistenza_partenza_arrivo', 'palco_premiazioni'],
        'ENDURO MOTO' => ['transponder_pc', 'solo_cronometraggio_start', 'solo_cronometraggio_fine', 'co_con_pc', 'co_solo_tablet'],
        'ENDURO MTB' => ['elaborazione_dati', 'partenza_prova', 'fine_prova'],
        'MOTOCROSS' => ['elaborazione_dati', 'arrivo'],
        'CANOA' => ['elaborazione_dati', 'arrivo', 'partenza_orologio_tablet', 'fotofinish'],
        'CANOTTAGGIO' => ['arrivo', 'partenza_orologio_tablet'],
        'CICLISMO SU STRADA' => ['arrivo', 'fotofinish'],
        'CICLISMO PISTA' => ['arrivo_bandelle', 'fotofinish'],
        'DOWHINILL' => ['partenza', 'arrivo', 'elaborazione_dati'],
        'AUTO REGOLARITA’' => ['pressostati', 'tablet'],
        'AUTO STORICHE' => ['arrivo', 'start'],
        'AUTOMOBILSMO CIRCUITO' => ['elaborazione_dati', 'contagiri', 'transponder'],
        'CONCORSO IPPICO' => ['prog_spec_concorso_ippico'],
        'TROTTO' => ['utilizzo_spec_programma'],
    ];
    public function createRaceTempShow()
    {
        return view('guest.createRaceTemp');
    }

    public function storeRaceTemp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255', // niente unique:users
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in($this->allowedTypes)],
            'date_of_race' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_of_race',
            'place' => 'required|string|max:255',
            'ente_fatturazione' => 'nullable|string|max:255',
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
            'ente_fatturazione' => $validated['ente_fatturazione'] ?? null,
            'programma_allegato' => $allegatoPath,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('homepage')->with('success', 'Gara temporanea creata con successo!');
    }

    public function accept(RaceTemp $race)
    {
        // calcola specializzazioni dal type
        $specs = $this->typeToSpecs[$race->type] ?? [];

        // crea la gara definitiva
        $newRace = Race::create([
            'name' => $race->name,
            'type' => $race->type,
            'date_of_race' => $race->date_of_race,
            'date_end' => $race->date_end,
            'place' => $race->place,
            'ente_fatturazione' => $race->ente_fatturazione,
            'programma_allegato' => $race->programma_allegato, // già su 'public'
            'note' => $race->note,
            'specialization_of_race' => $specs,
        ]);

        // Invia la mail di notifica all'indirizzo salvato
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara accettata',
            'emails.race.accepted',
            ['raceName' => $race->name]
        );

        // elimina la temporanea
        $race->delete();

        return redirect()->back()->with('success', 'Gara accettata e notificata con successo.');
    }

    public function reject(RaceTemp $race)
    {
        // Invia la mail di rifiuto
        $brevo = new BrevoMailer();
        $brevo->sendEmail(
            $race->email,
            'Gara rifiutata',
            'emails.race.rejected',
            ['raceName' => $race->name]
        );

        // Elimina la gara temporanea
        $race->delete();

        return redirect()->back()->with('success', 'Gara rifiutata e notificata.');
    }

}
