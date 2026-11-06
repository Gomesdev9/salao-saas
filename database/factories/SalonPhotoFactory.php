<?php

namespace Database\Factories;

use App\Enums\PhotoKind;
use App\Models\Salon;
use App\Models\SalonPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalonPhoto>
 */
class SalonPhotoFactory extends Factory
{
    /** PNG de 1x1 pixel. */
    public const PIXEL = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'kind' => PhotoKind::Gallery,
            'mime_type' => 'image/png',
            'content' => self::PIXEL,
            'position' => 0,
        ];
    }

    public function kind(PhotoKind $kind): static
    {
        return $this->state(['kind' => $kind]);
    }
}
