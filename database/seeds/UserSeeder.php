<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create(
            [
                'phone' => 13211111111,
                'password' => '123456aA!',
            ]
        );

        $user->info()->create([
            'nickname' => 'foo',
            'avatar' => '',
        ]);

        $role = Role::create(['name' => 'Chestnut Manager']);

        $user->assignRole($role);
    }
}
