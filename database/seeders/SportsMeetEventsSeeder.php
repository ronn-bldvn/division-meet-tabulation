<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Sport;
use App\Support\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SportsMeetEventsSeeder extends Seeder
{
    public function run(): void
    {
        $eventsBySport = [
            'Athletics' => [
                ['100 m', 'Girls', 'none'],
                ['200 m', 'Girls', 'none'],
                ['400 m', 'Girls', 'none'],
                ['800 m', 'Girls', 'none'],
                ['1,500 m', 'Girls', 'none'],
                ['Long Jump', 'Girls', 'none'],
                ['High Jump', 'Girls', 'none'],
                ['Discus Throw', 'Girls', 'none'],
                ['Javelin Throw', 'Girls', 'none'],
                ['Shot Put', 'Girls', 'none'],
                ['Triple Jump', 'Girls', 'none'],
                ['4 x 100 m relay', 'Girls', 'none'],
                ['4 x 400 relay', 'Girls', 'none'],
            ],
            'Basketball' => [
                ['Basketball', 'Boys', 'single_elimination'],
            ],
            'Badminton' => [
                ['Single A', 'Boys', 'single_elimination'],
                ['Single B', 'Boys', 'single_elimination'],
                ['Doubles', 'Boys', 'single_elimination'],
                ['Single A', 'Girls', 'single_elimination'],
                ['Single B', 'Girls', 'single_elimination'],
                ['Doubles', 'Girls', 'single_elimination'],
            ],
            'Chess' => [
                ['Chess', 'Boys', 'none'],
                ['Chess', 'Girls', 'none'],
            ],
            'Gymnastics' => [
                ['Floor Exercise - Cluster 1 (7-9 y.o.)', "Men's Artistic Gymnastics", 'none'],
                ['Floor Exercise - Cluster 2 (10-12 y.o.)', "Men's Artistic Gymnastics", 'none'],
                ['Mushroom Exercise Cluster 1', "Men's Artistic Gymnastics", 'none'],
                ['Mushroom Exercise Cluster 2', "Men's Artistic Gymnastics", 'none'],
                ['Vault Exercise Cluster 1', "Men's Artistic Gymnastics", 'none'],
                ['Vault Exercise Cluster 2', "Men's Artistic Gymnastics", 'none'],
                ['Individual All Around Cluster 1', "Men's Artistic Gymnastics", 'none'],
                ['Individual All Around Cluster 2', "Men's Artistic Gymnastics", 'none'],
                ['Floor Exercise - Cluster 1', "Women's Artistic Gymnastics", 'none'],
                ['Floor Exercise - Cluster 2', "Women's Artistic Gymnastics", 'none'],
                ['Beam Exercise - Cluster 1', "Women's Artistic Gymnastics", 'none'],
                ['Beam Exercise - Cluster 2', "Women's Artistic Gymnastics", 'none'],
                ['Single Bar - Cluster 1', "Women's Artistic Gymnastics", 'none'],
                ['Single Bar - Cluster 2', "Women's Artistic Gymnastics", 'none'],
                ['Vault Exercise - Cluster 1', "Women's Artistic Gymnastics", 'none'],
                ['Vault Exercise - Cluster 2', "Women's Artistic Gymnastics", 'none'],
                ['Individual All Around Cluster 1', "Women's Artistic Gymnastics", 'none'],
                ['Individual All Around Cluster 2', "Women's Artistic Gymnastics", 'none'],
            ],
            'Aero Gymnastics' => [
                ['Individual Men', 'Individual', 'none'],
                ['Individual Women', 'Individual', 'none'],
                ['Mixed Pair', 'Pair', 'none'],
                ['Trio Championship', 'Trio', 'none'],
                ['Team Championship', 'Team', 'none'],
            ],
            'Sepak Takraw' => [
                ['Sepak Takraw', 'Boys - Elementary (Junior)', 'single_elimination'],
            ],
            'Arnis' => [
                ['Single Weapon', 'Individual - Boys', 'none'],
                ['Double Weapon', 'Individual - Boys', 'none'],
                ['Espada y daga', 'Individual - Boys', 'none'],
                ['Single Weapon', 'Synchronize - Boys', 'none'],
                ['Double Weapon', 'Synchronize - Boys', 'none'],
                ['Espada y daga', 'Synchronize - Boys', 'none'],
                ['Single Weapon', 'Individual - Girls', 'none'],
                ['Double Weapon', 'Individual - Girls', 'none'],
                ['Espada y daga', 'Individual - Girls', 'none'],
                ['Single Weapon', 'Synchronize - Girls', 'none'],
                ['Double Weapon', 'Synchronize - Girls', 'none'],
                ['Espada y daga', 'Synchronize - Girls', 'none'],
                ['Boys', 'Mix-Boys and Girls', 'none'],
                ['Girls', 'Mix-Boys and Girls', 'none'],
            ],
            'Tennis' => [
                ['Singles', 'Boys', 'single_elimination'],
                ['Doubles', 'Boys', 'single_elimination'],
                ['Singles', 'Girls', 'single_elimination'],
                ['Doubles', 'Girls', 'single_elimination'],
            ],
            'Dancesport' => [
                ['Dancesport', 'Open', 'none'],
            ],
            'Volleyball' => [
                ['Volleyball', 'Boys', 'single_elimination'],
            ],
        ];

        $highSchoolEventsBySport = [
            'Athletics' => [
                ['100 m', 'Boys', 'none'],
                ['200 m', 'Boys', 'none'],
                ['400 m', 'Boys', 'none'],
                ['800 m', 'Boys', 'none'],
                ['1,500 m', 'Boys', 'none'],
                ['3000 m', 'Boys', 'none'],
                ['5000 m', 'Boys', 'none'],
                ['Long Jump', 'Boys', 'none'],
                ['High Jump', 'Boys', 'none'],
                ['Triple Jump', 'Boys', 'none'],
                ['Discus Throw', 'Boys', 'none'],
                ['Javelin Throw', 'Boys', 'none'],
                ['Shot Put', 'Boys', 'none'],
                ['4 x 100 m relay', 'Boys', 'none'],
                ['4 x 400 m relay', 'Boys', 'none'],
                ['100 m', 'Girls', 'none'],
                ['200 m', 'Girls', 'none'],
                ['400 m', 'Girls', 'none'],
                ['800 m', 'Girls', 'none'],
                ['1,500 m', 'Girls', 'none'],
                ['3000 m', 'Girls', 'none'],
                ['Long Jump', 'Girls', 'none'],
                ['High Jump', 'Girls', 'none'],
                ['Triple Jump', 'Girls', 'none'],
                ['Discus Throw', 'Girls', 'none'],
                ['Javelin Throw', 'Girls', 'none'],
                ['Shot Put', 'Girls', 'none'],
                ['4 x 100 m relay', 'Girls', 'none'],
                ['4 x 400 m relay', 'Girls', 'none'],
            ],
            'Basketball' => [
                ['Basketball', 'Boys', 'single_elimination'],
                ['Basketball', 'Girls', 'single_elimination'],
            ],
            'Badminton' => [
                ['Single A', 'Boys', 'single_elimination'],
                ['Single B', 'Boys', 'single_elimination'],
                ['Doubles', 'Boys', 'single_elimination'],
                ['Single A', 'Girls', 'single_elimination'],
                ['Single B', 'Girls', 'single_elimination'],
                ['Doubles', 'Girls', 'single_elimination'],
            ],
            'Chess' => [
                ['Chess', 'Boys', 'none'],
                ['Chess', 'Girls', 'none'],
            ],
            'Gymnastics' => [
                ['Floor Exercise', "Men's Artistic Gymnastics", 'none'],
                ['Pommel Horse', "Men's Artistic Gymnastics", 'none'],
                ['Vault Exercise', "Men's Artistic Gymnastics", 'none'],
                ['Individual All Around', "Men's Artistic Gymnastics", 'none'],
                ['Floor Exercise', "Women's Artistic Gymnastics", 'none'],
                ['Beam Exercise', "Women's Artistic Gymnastics", 'none'],
                ['Vault Exercise', "Women's Artistic Gymnastics", 'none'],
                ['Individual All Around', "Women's Artistic Gymnastics", 'none'],
                ['Hoop Exercise', 'Rhythmic Gymnastics', 'none'],
                ['Ball Exercise', 'Rhythmic Gymnastics', 'none'],
                ['Ribbon Exercise', 'Rhythmic Gymnastics', 'none'],
                ['Clubs Exercise', 'Rhythmic Gymnastics', 'none'],
                ['Team Championship', 'Rhythmic Gymnastics', 'none'],
            ],
            'Aero Gymnastics' => [
                ['Individual Men', 'Individual', 'none'],
                ['Individual Women', 'Individual', 'none'],
                ['Mixed Pair', 'Pair', 'none'],
                ['Trio', 'Trio', 'none'],
            ],
            'Sepak Takraw' => [
                ['Sepak Takraw', 'Boys', 'single_elimination'],
                ['Sepak Takraw', 'Girls', 'single_elimination'],
            ],
            'Arnis' => [
                ['Single Weapon', 'Individual - Boys', 'none'],
                ['Double Weapon', 'Individual - Boys', 'none'],
                ['Espada y daga', 'Individual - Boys', 'none'],
                ['Single Weapon', 'Synchronize - Boys', 'none'],
                ['Double Weapon', 'Synchronize - Boys', 'none'],
                ['Espada y daga', 'Synchronize - Boys', 'none'],
                ['Single Weapon', 'Individual - Girls', 'none'],
                ['Double Weapon', 'Individual - Girls', 'none'],
                ['Espada y daga', 'Individual - Girls', 'none'],
                ['Single Weapon', 'Synchronize - Girls', 'none'],
                ['Double Weapon', 'Synchronize - Girls', 'none'],
                ['Espada y daga', 'Synchronize - Girls', 'none'],
                ['Pinweight', 'Combative - Boys', 'none'],
                ['Bantamweight', 'Combative - Boys', 'none'],
                ['Featherweight', 'Combative - Boys', 'none'],
                ['Extra Lightweight', 'Combative - Boys', 'none'],
                ['Half Lightweight', 'Combative - Boys', 'none'],
                ['Pinweight', 'Combative - Girls', 'none'],
                ['Bantamweight', 'Combative - Girls', 'none'],
                ['Featherweight', 'Combative - Girls', 'none'],
                ['Extra Lightweight', 'Combative - Girls', 'none'],
                ['Half Lightweight', 'Combative - Girls', 'none'],
            ],
            'Tennis' => [
                ['Singles', 'Boys', 'single_elimination'],
                ['Doubles', 'Boys', 'single_elimination'],
                ['Singles', 'Girls', 'single_elimination'],
                ['Doubles', 'Girls', 'single_elimination'],
            ],
            'Volleyball' => [
                ['Volleyball', 'Boys', 'single_elimination'],
                ['Volleyball', 'Girls', 'single_elimination'],
            ],
            'Taekwondo' => [
                ['Category 1', 'Boys (Kyorugi)', 'none'],
                ['Category 2', 'Boys (Kyorugi)', 'none'],
                ['Category 3', 'Boys (Kyorugi)', 'none'],
                ['Category 4', 'Boys (Kyorugi)', 'none'],
                ['Category 6', 'Boys (Kyorugi)', 'none'],
                ['Individual', 'Boys (Poomsae)', 'none'],
                ['Pair', 'Boys (Poomsae)', 'none'],
                ['Category 1', 'Girls (Kyorugi)', 'none'],
                ['Category 3', 'Girls (Kyorugi)', 'none'],
                ['Category 5', 'Girls (Kyorugi)', 'none'],
                ['Category 6', 'Girls (Kyorugi)', 'none'],
                ['Category 7', 'Girls (Kyorugi)', 'none'],
                ['Solo', 'Girls (Poomsae)', 'none'],
                ['Pair', 'Girls (Poomsae)', 'none'],
                ['Team', 'Girls (Poomsae)', 'none'],
            ],
            'Table Tennis' => [
                ['Bracket A', 'Boys', 'single_elimination'],
                ['Bracket B', 'Boys', 'single_elimination'],
                ['Bracket C', 'Boys', 'single_elimination'],
                ['Bracket D', 'Boys', 'single_elimination'],
                ['Bracket A', 'Girls', 'single_elimination'],
                ['Bracket B', 'Girls', 'single_elimination'],
                ['Bracket C', 'Girls', 'single_elimination'],
                ['Bracket D', 'Girls', 'single_elimination'],
            ],
            'Dancesport' => [
                ['Dancesport', 'Latin American', 'none'],
            ],
            'Archery' => [
                ['30 m Individual', 'Boys', 'none'],
                ['50 m Individual', 'Boys', 'none'],
                ['70 m Individual', 'Boys', 'none'],
                ['60 m Individual', 'Boys', 'none'],
                ['Olympic Round', 'Boys', 'none'],
                ['30 m Individual', 'Girls', 'none'],
                ['50 m Individual', 'Girls', 'none'],
                ['70 m Individual', 'Girls', 'none'],
                ['60 m Individual', 'Girls', 'none'],
                ['Olympic Round', 'Girls', 'none'],
            ],
            'Futsal' => [
                ['Futsal', 'Boys', 'single_elimination'],
                ['Futsal', 'Girls', 'single_elimination'],
            ],
            'Billiards' => [
                ['Billiards', 'Boys', 'none'],
                ['Billiards', 'Girls', 'none'],
            ],
        ];

        foreach ([
            Level::ELEMENTARY => $eventsBySport,
            Level::HIGH_SCHOOL => $highSchoolEventsBySport,
        ] as $level => $sportEvents) {
            foreach ($sportEvents as $sportName => $events) {
                $sport = Sport::updateOrCreate(
                    ['slug' => Str::slug($sportName)],
                    ['name' => $sportName],
                );

                foreach ($events as [$name, $category, $bracketType]) {
                    Game::updateOrCreate(
                        [
                            'sport_id' => $sport->id,
                            'name' => $name,
                            'category' => $category,
                            'level' => $level,
                        ],
                        ['bracket_type' => $bracketType],
                    );
                }
            }
        }
    }
}
