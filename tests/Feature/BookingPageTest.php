<?php

namespace Tests\Feature;

use App\Enums\PhotoKind;
use App\Models\Salon;
use App\Models\SalonPhoto;
use App\Models\User;
use Database\Factories\SalonPhotoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * "Minha página": o salão personaliza a página pública de agendamento.
 */
class BookingPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Salon $salon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->salon = $this->user->salon;
        $this->actingAs($this->user);
    }

    public function test_edit_page_starts_with_the_default_colors(): void
    {
        $this->get(route('booking-page.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('booking-page/edit')
                ->where('salon.page_accent_color', Salon::DEFAULT_ACCENT_COLOR)
                ->where('salon.page_background_color', Salon::DEFAULT_BACKGROUND_COLOR)
                ->where('photos.logo', null)
                ->where('bookingUrl', $this->salon->bookingUrl()));
    }

    public function test_saves_colors_texts_and_contacts(): void
    {
        $this->patch(route('booking-page.update'), [
            'page_accent_color' => '#B4536E',
            'page_background_color' => '#FFFFFF',
            'page_headline' => 'Cabelo e autoestima',
            'page_about' => 'Atendemos desde 2010.',
            'phone' => '(11) 98765-4321',
            'city' => 'Campinas - SP',
            'address' => 'Rua das Flores, 123',
            'instagram' => 'https://www.instagram.com/studiobela/',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->salon->refresh();
        $this->assertSame('#B4536E', $this->salon->page_accent_color);
        $this->assertSame('Cabelo e autoestima', $this->salon->page_headline);
        $this->assertSame('studiobela', $this->salon->instagram);
        $this->assertSame('Rua das Flores, 123', $this->salon->address);
    }

    public function test_rejects_invalid_color_and_instagram(): void
    {
        $this->patch(route('booking-page.update'), [
            'page_accent_color' => 'red; background: url(x)',
            'page_background_color' => '#FFFFFF',
            'instagram' => 'studio bela!',
        ])->assertSessionHasErrors(['page_accent_color', 'instagram']);

        $this->assertNull($this->salon->fresh()->page_accent_color);
    }

    public function test_new_logo_replaces_the_old_one_and_is_served_publicly(): void
    {
        $old = SalonPhoto::factory()->for($this->salon)->kind(PhotoKind::Logo)->create();

        $this->post(route('booking-page.photos.store'), ['kind' => 'logo', 'photo' => $this->png()])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($old);
        $logo = SalonPhoto::where('kind', PhotoKind::Logo)->sole();

        // A foto abre para qualquer pessoa, sem login
        auth()->logout();
        $this->get($logo->url())
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent(base64_decode(SalonPhotoFactory::PIXEL));
    }

    public function test_gallery_has_a_limit(): void
    {
        SalonPhoto::factory(PhotoKind::Gallery->limit())->for($this->salon)->create();

        $this->post(route('booking-page.photos.store'), ['kind' => 'galeria', 'photo' => $this->png()])
            ->assertSessionHasErrors('photo');

        $this->assertSame(PhotoKind::Gallery->limit(), SalonPhoto::count());
    }

    public function test_rejects_files_that_are_not_images(): void
    {
        $this->post(route('booking-page.photos.store'), ['kind' => 'capa', 'photo' => UploadedFile::fake()->createWithContent('capa.pdf', '%PDF-1.4')])
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, SalonPhoto::count());
    }

    public function test_cannot_delete_another_salons_photo(): void
    {
        $other = SalonPhoto::factory()->create();

        $this->delete(route('booking-page.photos.destroy', $other->id))->assertNotFound();

        $this->assertModelExists($other);
    }

    public function test_public_page_uses_the_salon_colors_and_photos(): void
    {
        $this->salon->update(['page_accent_color' => '#B4536E', 'page_headline' => 'Cabelo e autoestima', 'instagram' => 'studiobela']);
        $cover = SalonPhoto::factory()->for($this->salon)->kind(PhotoKind::Cover)->create();
        SalonPhoto::factory(2)->for($this->salon)->create();

        auth()->logout();
        $this->get(route('booking.service', $this->salon))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('booking/service')
                ->where('salon.theme.accent', '#B4536E')
                ->where('salon.theme.accentChannels', '180 83 110')
                ->where('salon.logoUrl', null)
                ->where('showcase.headline', 'Cabelo e autoestima')
                ->where('showcase.coverUrl', $cover->url())
                ->has('showcase.gallery', 2)
                ->where('showcase.instagramUrl', 'https://instagram.com/studiobela'));
    }

    private function png(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('foto.png', base64_decode(SalonPhotoFactory::PIXEL));
    }
}
