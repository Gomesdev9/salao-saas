<?php

namespace App\Http\Controllers;

use App\Models\SalonPhoto;
use Illuminate\Http\Response;

/**
 * Entrega as fotos da página pública do salão, que ficam guardadas no banco.
 */
class SalonPhotoController extends Controller
{
    public function __invoke(int $photo): Response
    {
        // Sem o filtro por salão: a foto é pública, e quem está logado pode estar vendo a página de outro salão
        $photo = SalonPhoto::withoutGlobalScopes()->findOrFail($photo);

        return response(base64_decode($photo->content), 200, [
            'Content-Type' => $photo->mime_type,
            // O endereço muda quando a foto é trocada (?v=...), então o navegador pode guardar por bastante tempo
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
