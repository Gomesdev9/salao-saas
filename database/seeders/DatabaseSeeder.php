<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Professional;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria um salão de demonstração para testar o sistema.
     * Login: dono@salao.test / senha: password
     */
    public function run(): void
    {
        $salon = Salon::create([
            'name' => 'Salão Demonstração',
            'slug' => 'salao-demonstracao',
            'phone' => '(11) 99999-0000',
            'city' => 'Minha Cidade',
        ]);

        User::factory()->for($salon)->create([
            'name' => 'Dono do Salão',
            'email' => 'dono@salao.test',
        ]);

        $services = collect([
            ['Corte feminino', 'Cabelo', 60, 70],
            ['Escova', 'Cabelo', 45, 45],
            ['Manicure', 'Unhas', 40, 30],
            ['Pedicure', 'Unhas', 45, 35],
            ['Design de sobrancelha', 'Estética', 30, 40],
        ])->map(fn ($s) => Service::create([
            'salon_id' => $salon->id,
            'name' => $s[0],
            'category' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
        ]));

        $ana = Professional::factory()->for($salon)->create(['name' => 'Ana Souza', 'commission_percent' => 40]);
        $bia = Professional::factory()->for($salon)->create(['name' => 'Bia Lima', 'commission_percent' => 50]);
        $ana->services()->attach($services->where('category', 'Cabelo')->pluck('id'));
        $bia->services()->attach($services->whereIn('category', ['Unhas', 'Estética'])->pluck('id'));

        Client::factory(15)->for($salon)->create();
    }
}
