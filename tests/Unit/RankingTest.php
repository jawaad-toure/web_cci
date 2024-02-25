<?php

namespace tests\Unit;

use Tests\TestCase;
use App\Repositories\Ranking;
use App\Repositories\Data;

class RankingTest extends TestCase
{
   protected $ranking;
   protected $data;
   protected $match0;
   protected $match1;
   protected $match2;

   public function setUp(): void {
      parent::setUp();
      $this->ranking = new Ranking();
      $this->data = new Data();
      $this->match0 = ['id' => 123, 'team0' => 1, 'team1' => 3, 'score0' => 3, 'score1' => 5, 'date' => '2048-01-01 00:00:00'];
      $this->match1 = ['id' => 231, 'team0' => 4, 'team1' => 2, 'score0' => 2, 'score1' => 2, 'date' => '2048-01-01 00:00:00'];
      $this->match2 = ['id' => 222, 'team0' => 3, 'team1' => 2, 'score0' => 1, 'score1' => 3, 'date' => '2048-01-01 00:00:00'];
   }

   function testGoalDifference(): void {
      $this->assertEquals(-1, $this->ranking->goalDifference(2, 3));
      $this->assertEquals(0, $this->ranking->goalDifference(0, 0));
      $this->assertEquals(3, $this->ranking->goalDifference(4, 1));
   }
   
   function testPoints(): void {
      $this->assertEquals(3, $this->ranking->points(1, 0));
      $this->assertEquals(1, $this->ranking->points(0, 1));
      $this->assertEquals(0, $this->ranking->points(0, 0));
      $this->assertEquals(5, $this->ranking->points(1, 2));
   }

   function testTeamWinsMatch(): void {
      $this->assertNotTrue($this->ranking->teamWinsMatch(1, $this->match0));
      $this->assertTrue($this->ranking->teamWinsMatch(3, $this->match0));
      $this->assertNotTrue($this->ranking->teamWinsMatch(4, $this->match1));
      $this->assertNotTrue($this->ranking->teamWinsMatch(2, $this->match1));
      $this->assertNotTrue($this->ranking->teamWinsMatch(3, $this->match2));
      $this->assertTrue($this->ranking->teamWinsMatch(2, $this->match2));
      $this->assertNotTrue($this->ranking->teamWinsMatch(4, $this->match0));
   }

   function testTeamLosesMatch(): void {
      $this->assertTrue($this->ranking->teamLosesMatch(1, $this->match0));
      $this->assertNotTrue($this->ranking->teamLosesMatch(3, $this->match0));
      $this->assertNotTrue($this->ranking->teamLosesMatch(4, $this->match1));
      $this->assertNotTrue($this->ranking->teamLosesMatch(2, $this->match1));
      $this->assertTrue($this->ranking->teamLosesMatch(3, $this->match2));
      $this->assertNotTrue($this->ranking->teamLosesMatch(2, $this->match2));
      $this->assertNotTrue($this->ranking->teamLosesMatch(4, $this->match0));
   }

   function testTeamDrawsMatch(): void {
      $this->assertNotTrue($this->ranking->teamDrawsMatch(1, $this->match0));
      $this->assertNotTrue($this->ranking->teamDrawsMatch(3, $this->match0));
      $this->assertTrue($this->ranking->teamDrawsMatch(4, $this->match1));
      $this->assertTrue($this->ranking->teamDrawsMatch(2, $this->match1));
      $this->assertNotTrue($this->ranking->teamDrawsMatch(8, $this->match1));
      $this->assertNotTrue($this->ranking->teamDrawsMatch(3, $this->match2));
      $this->assertNotTrue($this->ranking->teamDrawsMatch(2, $this->match2));
      $this->assertNotTrue($this->ranking->teamDrawsMatch(4, $this->match0));
   }

   function testGoalForCountDuringAMatch(): void {
      $this->assertEquals(3, $this->ranking->goalForCountDuringAMatch(1, $this->match0));
      $this->assertEquals(5, $this->ranking->goalForCountDuringAMatch(3, $this->match0));
      $this->assertEquals(0, $this->ranking->goalForCountDuringAMatch(4, $this->match0));
   }

   function testGoalAgainstCountDuringAMatch(): void {
      $this->assertEquals(5, $this->ranking->goalAgainstCountDuringAMatch(1, $this->match0));
      $this->assertEquals(3, $this->ranking->goalAgainstCountDuringAMatch(3, $this->match0));
      $this->assertEquals(0, $this->ranking->goalAgainstCountDuringAMatch(4, $this->match0));
   }

   function testGoalForCount(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
         $this->assertEquals($row['goal_for_count'], $this->ranking->goalForCount($row['team_id'], $this->data->matches()));
      }
   }

   function testGoalAgainstCount(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
         $this->assertEquals($row['goal_against_count'], $this->ranking->goalAgainstCount($row['team_id'], $this->data->matches()));
      }
   }

   function testMatchWonCount(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
         $this->assertEquals($row['match_won_count'], $this->ranking->matchWonCount($row['team_id'], $this->data->matches()));
      }
   }

   function testMatchLostCount(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
         $this->assertEquals($row['match_lost_count'], $this->ranking->matchLostCount($row['team_id'], $this->data->matches()));
      }
   }

   function testDrawCount(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
         $this->assertEquals($row['draw_count'], $this->ranking->drawMatchCount($row['team_id'], $this->data->matches()));
      }
   }

   function testRankingRow(): void {
      foreach ($this->data->expectedUnsortedRanking() as $row) {
        $this->assertEquals($row, $this->ranking->rankingRow($row['team_id'], $this->data->matches()));
      }
   }

   function testUnsortedRanking(): void {
      $this->assertEquals($this->data->expectedUnsortedRanking(), $this->ranking->unsortedRanking($this->data->teams(), $this->data->matches()));
   }

   function testCompareRankingRow(): void {
      $this->assertLessThan(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>3], 
                                                               ['points' => 3, 'goal_difference'=>4, 'goal_for_count'=>4]));
      $this->assertLessThan(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>4, 'goal_for_count'=>3], 
                                                               ['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>4]));
      $this->assertLessThan(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>4], 
                                                               ['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>3]));
      $this->assertEquals(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>2], 
                                                               ['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>2]));
      $this->assertGreaterThan(0, $this->ranking->compareRankingRow(['points' => 3, 'goal_difference'=>4, 'goal_for_count'=>4], 
                                                               ['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>3]));
      $this->assertGreaterThan(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>4], 
                                                               ['points' => 4, 'goal_difference'=>4, 'goal_for_count'=>3]));
      $this->assertGreaterThan(0, $this->ranking->compareRankingRow(['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>3], 
                                                               ['points' => 4, 'goal_difference'=>3, 'goal_for_count'=>4]));
   }

   function testSortedRanking(): void {
      $this->assertEquals($this->data->expectedSortedRanking(), $this->ranking->sortedRanking($this->data->teams(), $this->data->matches()));
   }
   
}


