<?php

namespace Database\Factories;

use App\Models\Logg;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoggFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Logg::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'action'=> $this->faker->sentence(3,true) ,
        'user'=> $this->faker->sentence(1,true),
        'client'=> $this->faker->sentence(1,true),
        'request'=> $this->faker->sentences(20,true) ,
        'respond'=> $this->faker-> sentences(20,true) ,
        'link'=> $this->faker->address ,
        ];
    }
}
