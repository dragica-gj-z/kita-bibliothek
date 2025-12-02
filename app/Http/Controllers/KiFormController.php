<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use App\Enums\KiConfidence;

class KiFormController extends Controller
{
    // public function autofill(Request $request)
    // {
    //     $data = $request->validate([
    //         'isbn' => 'required|string',
    //         'lang' => 'nullable|string|in:de'
    //     ]);

    //     $isbn = $this->normalizeIsbn($data['isbn']);

    //     if (!$isbn) {
    //         return response()->json(['error' => 'Ungültige ISBN'], 422);
    //     }

    //     $lang = $data['lang'] ?? 'de';

    //     // Metadaten zuerst von Open Library holen (kostenlos, kein API-Key nötig)
    //     $meta = $this->fetchFromOpenLibrary($isbn);

    //     // Fallback: Wenn Open Library nichts liefert, Google Books versuchen
    //     if (!$meta) {
    //         $meta = $this->fetchFromGoogleBooks($isbn);
    //     }

    //     // Wenn beide Quellen keine Daten liefern, Fehler 404 zurückgeben
    //     if (!$meta) {
    //         return response()->json(['error' => 'Keine Metadaten gefunden. Prüfe ISBN.'], 404);
    //     }

    //     // Gemini aufrufen, um aus den Metadaten eine Kurzbeschreibung + Altersempfehlung zu erzeugen
    //     // summarizeWithGemini gibt ein Array mit 4 Werten zurück:
    //     // [0] = summary (Text), [1] = ageMin, [2] = ageMax, [3] = confidence
    //     [$summary, $ageMin, $ageMax, $confidence] = $this->summarizeWithGemini($meta, $lang);

    //     // JSON-Antwort fürs Frontend-Formular zusammenbauen
    //     return response()->json([
    //         'isbn'        => $isbn,                       // Normalisierte ISBN
    //         'title'       => $meta['title']        ?? null,
    //         'authors'     => $meta['authors']      ?? [],
    //         'publisher'   => $meta['publisher']    ?? null,
    //         'publishedAt' => $meta['publishedAt']  ?? null,
    //         'pageCount'   => $meta['pageCount']    ?? null,
    //         'language'    => $meta['language']     ?? null,
    //         'categories'  => $meta['categories']   ?? [],
    //         'cover'       => $meta['cover']        ?? null,
    //         'description' => $summary,                    // KI-generierte Kurzbeschreibung
    //         'age'         => ['min' => $ageMin, 'max' => $ageMax], 
    //         'confidence'  => $confidence                  // Einschätzung von Gemini (high/medium/low oder null)
    //     ]);
    // }

    public function autofill(Request $request)
{
    $data = $request->validate([
        'isbn' => 'required|string',
        'lang' => 'nullable|string|in:de',
    ]);

    $isbn = $this->normalizeIsbn($data['isbn']);

    if (!$isbn) {
        return response()->json(['error' => 'Ungültige ISBN'], 422);
    }

    $lang = $data['lang'] ?? 'de';

    $meta = $this->fetchFromOpenLibrary($isbn)
          ?? $this->fetchFromGoogleBooks($isbn);

    if (!$meta) {
        return response()->json(['error' => 'Keine Metadaten gefunden. Prüfe ISBN.'], 404);
    }

    $summary  = $meta['description'] ?? null;
    $ageMin   = null;
    $ageMax   = null;
    $confEnum = KiConfidence::LOW;

    try {
        [$gSummary, $gAgeMin, $gAgeMax, $gConfidence] = $this->summarizeWithGemini($meta, $lang);

        if ($gSummary) {
            $summary = $gSummary;
        }

        $ageMin = $gAgeMin;
        $ageMax = $gAgeMax;

        if ($gConfidence instanceof KiConfidence) {
            $confEnum = $gConfidence;
        } elseif (is_string($gConfidence)) {
            $confEnum = KiConfidence::tryFrom($gConfidence)
                      ?? $this->inferConfidence($summary, $ageMin, $ageMax);
        } else {
            $confEnum = $this->inferConfidence($summary, $ageMin, $ageMax);
        }
    } catch (\Throwable $e) {
        Log::warning('Gemini summarize failed', [
            'isbn' => $isbn,
            'msg'  => $e->getMessage(),
        ]);

        $confEnum = $this->inferConfidence($summary, $ageMin, $ageMax);
    }

    return response()->json([
        'isbn'        => $isbn,
        'title'       => $meta['title']       ?? null,
        'authors'     => $meta['authors']     ?? [],
        'publisher'   => $meta['publisher']   ?? null,
        'publishedAt' => $meta['publishedAt'] ?? null,
        'pageCount'   => $meta['pageCount']   ?? null,
        'language'    => $meta['language']    ?? null,
        'categories'  => $meta['categories']  ?? [],
        'cover'       => $meta['cover']       ?? null,
        'description' => $summary,
        'age'         => ['min' => $ageMin, 'max' => $ageMax],
        'confidence'       => $confEnum->value,
        'confidence_label' => $this->confidenceLabel($confEnum),
    ]);
}
    /**
     * Fallback-Heuristik, falls Gemini keinen Confidence-Wert liefert.
     */
    private function inferConfidence(?string $summary, ?int $ageMin, ?int $ageMax): KiConfidence
    {
        if ($summary && $ageMin !== null && $ageMax !== null) {
            return KiConfidence::HIGH;
        }

        if ($summary || $ageMin !== null || $ageMax !== null) {
            return KiConfidence::MEDIUM;
        }

        return KiConfidence::LOW;
    }

    /**
     * Label für Frontend (deutsche Anzeige).
     */
    private function confidenceLabel(KiConfidence $confidence): string
    {
        return match ($confidence) {
            KiConfidence::HIGH   => 'hoch',
            KiConfidence::MEDIUM => 'mittel',
            KiConfidence::LOW    => 'niedrig',
        };
    }


    // Normalisierung der ISBN
    private function normalizeIsbn(string $raw): ?string
    {
        // Alles entfernen, was kein Ziffernzeichen oder X/x ist (z.B. Bindestriche, Leerzeichen)
        $digits = preg_replace('/[^0-9Xx]/', '', $raw);

        // Gültige Längen: 10 oder 13 Zeichen (ISBN-10 oder ISBN-13)
        if (strlen($digits) === 10 || strlen($digits) === 13) return strtoupper($digits);

        return null;
    }

    // Metadaten von Open Library holen
    private function fetchFromOpenLibrary(string $isbn): ?array
    {
        // HTTP-GET Request an Open Library ISBN-Endpoint
        // timeout(6) = maximal 6 Sekunden warten
        $book = Http::timeout(6)->get("https://openlibrary.org/isbn/{$isbn}.json");

        if (!$book->ok()) return null;

        $b = $book->json();

        // Autoren-Namen auflösen (Open Library liefert hier nur Referenzen)
        $authors = [];
        if (!empty($b['authors'])) {
            foreach ($b['authors'] as $a) {
                // Jede Autor-Referenz hat z.B. ein 'key' wie "/authors/OL34184A"
                $key = $a['key'] ?? null;
                if ($key) {
                    // Für jeden Autor eine eigene Anfrage an Open Library
                    $res = Http::timeout(4)->get("https://openlibrary.org{$key}.json");
                    if ($res->ok()) {
                        // Aus der Autor-JSON den Namen lesen
                        $name = $res->json()['name'] ?? null;
                        if ($name) $authors[] = $name;
                    }
                }
            }
        }

        // Cover-URL aufbauen (Open Library nutzt IDs und liefert darüber das Bild)
        $cover = null;
        if (!empty($b['covers'][0])) {
            // "-L" steht für große Version (Large)
            $cover = "https://covers.openlibrary.org/b/id/{$b['covers'][0]}-L.jpg";
        }

        // Vereinheitlichte Metadaten-Struktur zurückgeben
        return [
            'title'       => $b['title'] ?? null,
            'authors'     => $authors,
            'publisher'   => $b['publishers'][0] ?? null,
            'publishedAt' => $b['publish_date'] ?? null,
            'pageCount'   => $b['number_of_pages'] ?? null,
            'language'    => !empty($b['languages'][0]['key']) ? str_replace('/languages/','',$b['languages'][0]['key']) : null,
            'categories'  => [], 
            'cover'       => $cover,
            'raw'         => $b  // vollständige Rohdaten, falls du später noch mehr brauchst
        ];
    }

    // Metadaten von Google Books holen (Fallback)
    private function fetchFromGoogleBooks(string $isbn): ?array
    {
        // Request an Google Books API mit Query "isbn:<nummer>"
        $gb = Http::timeout(6)->get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn,
        ]);


        // Wenn Response nicht ok → null
        if (!$gb->ok()) return null;

        // 'items' enthält die gefundenen Bücher
        $items = $gb->json()['items'] ?? [];

        // Wenn leer → kein Treffer
        if (empty($items)) return null;

        // Wir nehmen einfach den ersten Treffer
        $v = $items[0]['volumeInfo'] ?? [];

        // Metadaten in dein einheitliches Format bringen
        return [
            'title'       => $v['title'] ?? null,
            'authors'     => $v['authors'] ?? [],
            'publisher'   => $v['publisher'] ?? null,
            'publishedAt' => $v['publishedDate'] ?? null,
            'pageCount'   => $v['pageCount'] ?? null,
            'language'    => $v['language'] ?? null,
            'categories'  => $v['categories'] ?? [],
            'cover'       => $v['imageLinks']['thumbnail'] ?? null,     // kleines Cover
            'description' => $v['description'] ?? null,                 // hier gibt es oft schon eine Beschreibung
            'raw'         => $v
        ];

    }


    // Aufruf der Gemini-API, um Kurzbeschreibung + Altersspanne als strukturiertes JSON zu bekommen
    private function summarizeWithGemini(array $meta, string $lang): array
    {
        // Prompt-Text für das KI-Modell zusammenbauen
        // Außerdem werden vorhandene Metadaten im JSON-Format mitgegeben (title, authors, usw.)
        $prompt = "Erzeuge eine kurze, neutrale Buchbeschreibung (3–4 Sätze) auf {$lang}. "
                . "Zielgruppe: Eltern, Erzieher:innen, Pädagog:innen. Keine Spoiler. "
                . "Gib außerdem eine empfohlene Altersspanne in Jahren (min/max) aus. "
                . "Wenn die Daten zu vage sind, gib eine allgemeine Altersspanne für Kinderbücher aus.\n\n"
                . "Vorliegende Metadaten:\n"
                . json_encode([
                    'title'       => $meta['title'] ?? null,
                    'authors'     => $meta['authors'] ?? [],
                    'publisher'   => $meta['publisher'] ?? null,
                    'publishedAt' => $meta['publishedAt'] ?? null,
                    'pageCount'   => $meta['pageCount'] ?? null,
                    'language'    => $meta['language'] ?? null,
                    'categories'  => $meta['categories'] ?? [],
                    'sourceDesc'  => $meta['description'] ?? null, 
                ], JSON_UNESCAPED_UNICODE);

        // Payload für den Gemini-API-Call
        // - contents: eigentliche Anfrage mit dem Prompt
        // - generationConfig: steuert Temperatur, maximale Tokens und das Response-Format
        $payload = [
            'contents' => [[
                'parts' => [[ 'text' => $prompt ]]
            ]],
            'generationConfig' => [
                'temperature'        => 0.5,          
                // 'maxOutputTokens'    => 220,                 // maximale Antwortlänge
                'responseMimeType'   => 'application/json',     // Modell soll JSON zurückgeben
                'responseJsonSchema'     => [                   
                    'type'       => 'object',
                    'properties' => [
                        'summary'    => ['type' => 'string'],   // Kurzbeschreibung
                        'age_min'    => ['type' => 'integer'],                   
                        'age_max'    => ['type' => 'integer'],                   
                        'confidence' => [
                            'type' => 'string',
                            'enum' => ['high','medium','low']                   
                        ]
                    ],
                    'required'   => ['summary','age_min','age_max']              
                ],
            ],
        ];

        // HTTP-Request an Gemini-Endpoint
        $res = Http::withHeaders([
            'x-goog-api-key' => env('GEMINI_API_KEY'),
            'Content-Type'   => 'application/json',
        ])->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            $payload
        );

        Log::info('Book meta for ISBN 978-3-407-79230-9', context: $meta);

        Log::info('Gemini API Key?', ['key' => env('GEMINI_API_KEY') ? 'SET' : 'MISSING']);

        Log::info('Gemini status', ['status' => $res->status()]);
        Log::info('Gemini body', ['body' => $res->body()]);


        // Wenn der Request fehlschlägt (kein 2xx Status), Fallback-Werte zurückgeben
        if (!$res->ok()) {
            // Fallback: generische Beschreibung und Standard-Altersspanne 3–6 Jahre
            return ['Kurzbeschreibung nicht verfügbar – bitte prüfen.', 3, 6, null];
        }

        // Aus der Response den Text holen, den das Modell geliefert hat.
        // Laut API-Struktur: candidates[0] → content → parts[0] → text
        // $jsonText = $res->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $data = $res->json();

        $candidate = $data['candidates'][0] ?? null;
        $jsonText = '{}';

        if (!empty($candidate['content']['parts'][0]['text'])) {
            $jsonText = $candidate['content']['parts'][0]['text'];
        }

        Log::info('Gemini raw text', ['text' => $jsonText]);

        $obj = json_decode($jsonText, true) ?: [];


        // Diesen Text nun als JSON dekodieren (sollte dank responseSchema ein JSON-Objekt sein)
        $obj = json_decode($jsonText, true) ?: [];

        // Rückgabe als [summary, age_min, age_max, confidence]
        // Mit Default-Werten, falls im JSON etwas fehlt
        return [
            $obj['summary'] ?? 'Kurzbeschreibung nicht verfügbar – bitte prüfen.',
            isset($obj['age_min']) ? (int)$obj['age_min'] : 3,
            isset($obj['age_max']) ? (int)$obj['age_max'] : 6,
            $obj['confidence'] ?? null
        ];
    }

    public function confidenceOptions()
    {
        $options = array_map(
            fn (KiConfidence $case) => [
                'value' => $case->value,
                'label' => match ($case) {
                    KiConfidence::HIGH   => 'hoch',
                    KiConfidence::MEDIUM => 'mittel',
                    KiConfidence::LOW    => 'niedrig',
                },
            ],
            KiConfidence::cases()
        );

        return response()->json($options);
    }
}
