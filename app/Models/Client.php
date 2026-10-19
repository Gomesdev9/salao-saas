<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSalon;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'whatsapp', 'birthday', 'notes'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToSalon, HasFactory;

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }
}
