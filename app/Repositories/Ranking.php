<?php

namespace App\Repositories;

class Ranking {

   function goalDifference(int $goalFor, int $goalAgainst): int {
      return $goalFor - $goalAgainst;
   }

   function points(int $matchWonCount, int $drawMatchCount): int {
      return 3*$matchWonCount + $drawMatchCount;
   }

   function teamWinsMatch(int $teamId, array $match): bool {
      return (
         $match['team0'] == $teamId && $match['score0'] > $match['score1'] ||
         $match['team1'] == $teamId && $match['score1'] > $match['score0']
      );
   }

   function teamLosesMatch(int $teamId, array $match): bool {
      return (
         $match['team0'] == $teamId && $match['score0'] < $match['score1'] ||
         $match['team1'] == $teamId && $match['score1'] < $match['score0']
      );
   }

   function teamDrawsMatch(int $teamId, array $match): bool {
      return (
         $match['team0'] == $teamId && $match['score0'] == $match['score1'] ||
         $match['team1'] == $teamId && $match['score1'] == $match['score0']
      );;
   }

   function goalForCountDuringAMatch(int $teamId, array $match): int {
      if ($match['team0'] == $teamId) {
         return $match['score0'];
      }

      if ($match['team1'] == $teamId) {
         return $match['score1'];
      }

      return 0;
   }

   function goalAgainstCountDuringAMatch(int $teamId, array $match): int {
      if ($match['team0'] == $teamId) {
         return $match['score1'];
      }
      
      if ($match['team1'] == $teamId) {
         return $match['score0'];
      }

      return 0;
   }

   function goalForCount(int $teamId, array $matches): int {
      $totalGoalsScored = 0;
      
      foreach ($matches as $match) {
         if ($match['team0'] == $teamId)
            $totalGoalsScored += $match['score0'];

         if ($match['team1'] == $teamId)
            $totalGoalsScored += $match['score1'];            
      }

      return $totalGoalsScored;
   }

   function goalAgainstCount(int $teamId, array $matches): int {
      $totalGoalsConceded = 0;

      foreach ($matches as $match) {
         if ($match['team0'] == $teamId)
            $totalGoalsConceded += $match['score1'];

         if ($match['team1'] == $teamId)
            $totalGoalsConceded += $match['score0'];
      }

      return $totalGoalsConceded;
   }

   function matchWonCount(int $teamId, array $matches): int {
      $totalMatchWon = 0;     

      foreach ($matches as $match) {
         if ($this->teamWinsMatch($teamId, $match))
            $totalMatchWon++;
      }

      return $totalMatchWon;
   }

   function matchLostCount(int $teamId, array $matches): int {
      $totalMatchLost = 0;

      foreach ($matches as $match) {
         if ($this->teamLosesMatch($teamId, $match))
            $totalMatchLost++;
      }

      return $totalMatchLost;
   }
   
   function drawMatchCount(int $teamId, array $matches): int {
      $totalMatchDraw = 0;

      foreach ($matches as $match) {
         if ($this->teamDrawsMatch($teamId, $match))
            $totalMatchDraw++;
      }

      return $totalMatchDraw;
   }

   function rankingRow(int $teamId, array $matches): array {
      $matchPlayedCount = 0;
      $matchWonCount = 0;
      $matchLostCount = 0;
      $drawMatchCount = 0;
      $goalForCount = 0;
      $goalAgainstCount = 0;
      $goalDifference = 0;
      $points = 0;
      
      foreach ($matches as $match) {
         $matchWonCount += $this->matchWonCount($teamId, [$match]);
         $matchLostCount += $this->matchLostCount($teamId, [$match]);
         $drawMatchCount += $this->drawMatchCount($teamId, [$match]);
         $matchPlayedCount = $matchWonCount + $matchLostCount + $drawMatchCount;

         $goalForCount += $this->goalForCount($teamId, [$match]);
         $goalAgainstCount += $this->goalAgainstCount($teamId, [$match]);
         $goalDifference = $this->goalDifference($goalForCount, $goalAgainstCount);

         $points = $this->points($matchWonCount, $drawMatchCount);
      }

      return [
         'team_id'            => $teamId,
         'match_played_count' => $matchPlayedCount,
         'match_won_count'    => $matchWonCount,
         'match_lost_count'   => $matchLostCount,
         'draw_count'         => $drawMatchCount,
         'goal_for_count'     => $goalForCount,
         'goal_against_count' => $goalAgainstCount,
         'goal_difference'    => $goalDifference,
         'points'             => $points
      ];
   }
   
   function unsortedRanking(array $teams, array $matches): array {
      $result = [];

      foreach($teams as $team) {
         $result[] = $this->rankingRow($team['id'], $matches);
      }

      return $result;
   }

   static function compareRankingRow(array $row1, array $row2): int {
      if ($row1['points'] > $row2['points']) {
         return -1;
      }
      
      if ($row1['points'] < $row2['points']) {
         return 1;
      }
      
      if ($row1['points'] == $row2['points'] && $row1['goal_difference'] > $row2['goal_difference']) {
         return -1;
      }

      if ($row1['points'] == $row2['points'] && $row1['goal_difference'] < $row2['goal_difference']) {
         return 1;
      }

      if ($row1['points'] == $row2['points'] && $row1['goal_difference'] == $row2['goal_difference'] && $row1['goal_for_count'] > $row2['goal_for_count']) {
         return -1;
      }

      if ($row1['points'] == $row2['points'] && $row1['goal_difference'] == $row2['goal_difference'] && $row1['goal_for_count'] < $row2['goal_for_count']) {
         return 1;
      }

      return 0;
   }

   function sortedRanking(array $teams, array $matches): array {
      $result = $this->unsortedRanking($teams, $matches);

      usort($result, ['App\Repositories\Ranking', 'compareRankingRow']);

      for ($rank = 1; $rank <= count($teams); $rank++) {
         $result[$rank - 1]['rank'] = $rank;
      }

      return $result;
   }

}

