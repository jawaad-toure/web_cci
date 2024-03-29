<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use App\Repositories\Repository;
use App\Repositories\Data;

class ControllerTest extends TestCase
{
    public function testShowRanking()
    {
        $this->mock(Repository::class, function ($mock) {
            $mock->shouldReceive('sortedRanking')->once()->andReturn([
                [
                    'rank' => 2, 
                    'name' => 'Lyon', 
                    'team_id' => 3, 
                    'match_played_count' => 38, 
                    'match_won_count' => 19, 
                    'match_lost_count' => 13, 
                    'draw_count' => 6,
                    'goal_for_count' => 111, 
                    'goal_against_count' => 97, 
                    'goal_difference' => 14, 
                    'points' => 63
                ]
            ]);
        });
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSeeTextInOrder(['N°', 'Équipe', 'MJ', 'G', 'N', 'P', 'BP', 'BC', 'DB', 'PTS']);
        $response->assertSeeTextInOrder([2, 'Lyon', 38, 19, 6, 13, 111, 97, 14, 63]);
    }

    public function testShowTeam()
    {
        $this->mock(Repository::class, function ($mock) {
            $data = new Data();
            $mock->shouldReceive('rankingRow')->with(4)->once()->andReturn([
                    'rank' => 2, 
                    'name' => 'Lyon', 
                    'team_id' => 3, 
                    'match_played_count' => 38, 
                    'match_won_count' => 19, 
                    'match_lost_count' => 13, 
                    'draw_count' => 6,
                    'goal_for_count' => 111, 
                    'goal_against_count' => 97, 
                    'goal_difference' => 14, 
                    'points' => 63
            ]);
            
            $mock->shouldReceive('teamMatches')->with(4)->once()->andReturn([
               [
                    'id' => 7, 
                    'team0' => 3, 
                    'team1' => 18, 
                    'score0' => 3, 
                    'score1' => 5, 
                    'date' => '2048-08-03 00:00:00', 
                    'name0' => 'Lyon', 
                    'name1' => 'Angers'
               ]
            ]);
        });
        $response = $this->get('/teams/4');
        $response->assertStatus(200);
        $response->assertSeeTextInOrder(['N°', 'Équipe', 'MJ', 'G', 'N', 'P', 'BP', 'BC', 'DB', 'PTS']);
        $response->assertSeeTextInOrder([2, 'Lyon', 38, 19, 6, 13, 111, 97, 14, 63]);
        $response->assertSeeTextInOrder(['2048-08-03 00:00:00', 'Lyon', 3, 5, 'Angers']);
    }
}