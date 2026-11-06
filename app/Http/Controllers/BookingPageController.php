<?php

namespace App\Http\Controllers;

use App\Enums\PhotoKind;
use App\Models\SalonPhoto;
use App\Support\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Minha página": o salão escolhe como fica a página pública em que a cliente agenda
 * (cores, logo, capa, galeria de fotos, textos e contatos).
 */
class BookingPageController extends Controller
{
    public function edit(Request $request): Response
    {
        $salon = $request->user()->salon;

        return Inertia::render('booking-page/edit', [
            'salon' => [
                'name' => $salon->name,
                'page_headline' => $salon->page_headline,
                'page_about' => $salon->page_about,
                'page_accent_color' => $salon->page_accent_color ?: $salon::DEFAULT_ACCENT_COLOR,
                'page_background_color' => $salon->page_background_color ?: $salon::DEFAULT_BACKGROUND_COLOR,
                'phone' => $salon->phone,
                'city' => $salon->city,
                'address' => $salon->address,
                'instagram' => $salon->instagram,
            ],
            'photos' => $salon->pagePhotos(),
            'galleryLimit' => PhotoKind::Gallery->limit(),
            'bookingUrl' => $salon->bookingUrl(),
            'hoursSummary' => $salon->openingHoursSummary(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Aceita o Instagram como "@studio", "studio" ou o link do perfil
        $request->merge([
            'instagram' => preg_replace('~^(https?://)?(www\.)?instagram\.com/|^@|/+$~i', '', trim((string) $request->input('instagram'))) ?: null,
        ]);

        $data = $request->validate([
            'page_headline' => ['nullable', 'string', 'max:120'],
            'page_about' => ['nullable', 'string', 'max:1000'],
            'page_accent_color' => ['required', 'regex:'.Color::PATTERN],
            'page_background_color' => ['required', 'regex:'.Color::PATTERN],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'regex:/^[A-Za-z0-9._]{1,30}$/'],
        ], [
            'page_accent_color.regex' => 'Escolha uma cor da lista ou no seletor.',
            'page_background_color.regex' => 'Escolha uma cor da lista ou no seletor.',
            'instagram.regex' => 'Digite só o seu @ do Instagram, por exemplo @studiobela.',
        ]);

        $request->user()->salon->update($data);

        return back()->with('status', 'Página salva.');
    }

    /**
     * Envia uma foto. Logo e capa trocam a foto anterior; a galeria vai somando até o limite.
     * O navegador já manda a imagem reduzida, então o limite de tamanho só barra arquivos fora do comum.
     */
    public function storePhoto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::enum(PhotoKind::class)],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'photo.image' => 'Escolha uma imagem (JPG, PNG ou WEBP).',
            'photo.mimes' => 'Escolha uma imagem JPG, PNG ou WEBP.',
            'photo.max' => 'A foto é grande demais. Escolha uma de até 3 MB.',
        ]);

        $kind = PhotoKind::from($data['kind']);
        $salon = $request->user()->salon;
        $file = $data['photo'];

        DB::transaction(function () use ($salon, $kind, $file) {
            $photos = SalonPhoto::where('kind', $kind);

            if ($kind === PhotoKind::Gallery && $photos->count() >= $kind->limit()) {
                throw ValidationException::withMessages(['photo' => "A galeria aceita até {$kind->limit()} fotos. Apague uma para colocar outra."]);
            }

            if ($kind !== PhotoKind::Gallery) {
                $photos->delete();
            }

            $salon->photos()->create([
                'kind' => $kind,
                'mime_type' => $file->getMimeType(),
                'content' => base64_encode($file->getContent()),
                'position' => (int) SalonPhoto::where('kind', $kind)->max('position') + 1,
            ]);
        });

        return back()->with('status', 'Foto salva.');
    }

    public function destroyPhoto(SalonPhoto $photo): RedirectResponse
    {
        $photo->delete();

        return back()->with('status', 'Foto apagada.');
    }
}
