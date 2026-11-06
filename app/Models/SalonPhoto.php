<?php

namespace App\Models;

use App\Enums\PhotoKind;
use App\Models\Concerns\BelongsToSalon;
use Database\Factories\SalonPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Foto da página pública do salão. A imagem fica no banco, em base64 (ver a migração create_salon_photos_table).
 */
#[Fillable(['salon_id', 'kind', 'mime_type', 'content', 'position'])]
#[Hidden(['content'])]
class SalonPhoto extends Model
{
    /** @use HasFactory<SalonPhotoFactory> */
    use BelongsToSalon, HasFactory;

    /** Colunas sem a imagem, para listar as fotos sem carregar o arquivo inteiro. */
    public const SUMMARY_COLUMNS = ['id', 'salon_id', 'kind', 'position', 'updated_at'];

    protected function casts(): array
    {
        return [
            'kind' => PhotoKind::class,
        ];
    }

    /** Endereço público da imagem. O "v" muda quando a foto é trocada, para o navegador não mostrar a antiga. */
    public function url(): string
    {
        return route('salon-photos.show', ['photo' => $this->id, 'v' => $this->updated_at?->timestamp]);
    }
}
