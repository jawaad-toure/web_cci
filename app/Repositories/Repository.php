<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Repositories\Data;
use App\Repositories\Ranking;

class Repository {

   
   function createDatabase(): void {
      DB::unprepared(file_get_contents("database/build.sql"));
   }

   function insertTeam(array $team): int {
      return DB::table("teams")
               ->insertGetId($team);
   }

   function insertMatch(array $match): int {
      return DB::table("matches")
               ->insertGetId($match);
   }

   function teams(): array {
      return DB::table("teams")
               ->orderBy("id")
               ->get()
               ->toArray();
   }

   function matches(): array {
      return DB::table("matches")
               ->orderBy("id")
               ->get()
               ->toArray();
   }

   function fillDatabase(): void {
      $data = new Data();       

      /* insertion automatique des équipes dans la table teams avec les données de data */
      foreach ($data->teams() as $dataTeam) {
         $this->insertTeam($dataTeam);
      }

      /* insertion automatique de la table matches avec les données de data */
      foreach ($data->matches() as $dataMatch) {
         $this->insertMatch($dataMatch);
      }
   }

   function team($teamId): array {
      $teams = DB::table('teams')
                  ->where('id', $teamId)
                  ->get()
                  ->toArray();
      
      if (count($teams) == 0) {
         throw new Exception("Équipe inconnue");
      }

      return $teams[0];
   }

   function match($matchId): array {
      $matches = DB::table('matches')
                     ->where('id', $matchId)
                     ->get()
                     ->toArray();

      if (count($matches) == 0) {
         throw new Exception("Match inconnu");
      }

      return $matches[0];
   }

   function updateRanking(): void {
      DB::table('ranking')
         ->delete();
      
      $teams = $this->teams();
      $matches = $this->matches();

      $ranking = new Ranking();

      $sortedRanks = $ranking->sortedRanking($teams, $matches);

      DB::table('ranking')
         ->insert($sortedRanks);

   }

   function sortedRanking(): array {
      $rows = DB::table('ranking')
               ->join('teams', 'ranking.team_id', '=', 'teams.id')
               ->orderBy("rank")
               ->get(["ranking.*", "teams.name"])
               ->toArray();
      return $rows;
   }

   function teamMatches($teamId): array {
      $matches = DB::table('matches')
                  ->join('teams as teams0', 'matches.team0', '=', 'teams0.id')
                  ->join('teams as teams1', 'matches.team1', '=', 'teams1.id')
                  ->where('matches.team0', $teamId)
                  ->orWhere('matches.team1', $teamId)
                  ->get(['matches.*', 'teams0.name as name0', 'teams1.name as name1'])
                  ->toArray();
      return $matches;
   }

   function rankingRow($teamId): array {
      $row = DB::table('ranking')
                  ->join('teams', 'ranking.team_id', '=', 'teams.id')
                  ->where("ranking.team_id", $teamId)
                  ->get(["ranking.*", "teams.name"])
                  ->toArray();

      if (count($row) == 0) {
         throw new Exception("Équipe inconnue");
      }

      return $row[0];
   }

   function addUser(string $email, string $password): int {
      // $user = DB::table("users")
      //          ->where('email', $email)
      //          ->get()
      //          ->toArray();

      // if (count($user) != 0) {
      //    throw new Exception("Utilisateur inconnu");
      // }

      $passwordHash = Hash::make($password);

      $userId = DB::table("users")
                  ->insertGetId(["email" => $email, "password_hash" => $passwordHash]);
      
      return $userId;
   }

   function getUser(string $email, string $password): array {
      $user = DB::table("users")
               ->where('email', $email)
               ->first();

      if (!$user) {
         throw new Exception("Utilisateur inconnu");
      }
      
      $passwordHash = $user['password_hash'];
      
      $ok = Hash::check($password, $passwordHash);
      
      if (!$ok) {
         throw new Exception("Utilisateur inconnu");
      }

      return ['id' => $user['id'], 'email' => $user['email']];
   }

   function changePassword(string $email, string $oldPassword, string $newPassword): void {
      $user = $this->getUser($email, $oldPassword);

      $passwordHash =  Hash::make($newPassword);

      DB::table("users")
         ->where("email", $email)
         ->update(['password_hash' => $passwordHash]);
   }

   function deleteMatch(int $matchId) {
      DB::table("matches")
            ->where('id', $matchId)
            ->delete();
   }
}
