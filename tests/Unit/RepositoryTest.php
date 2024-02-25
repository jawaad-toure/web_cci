<?php

namespace Tests\Unit;

use PDO;
use Exception;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Repositories\Data;
use App\Repositories\Ranking;
use App\Repositories\Repository;

class RepositoryTest extends TestCase {
   protected $ranking;
   protected $data;
   protected $repository;

   public function setUp(): void {
      parent::setUp();
      $this->ranking = new Ranking();
      $this->data = new Data();
      $this->repository = new Repository();
      $this->repository->createDatabase();

   }
   
   function testTeamsAndInsertTeam(): void {
      $teams = $this->data->teams();
      $this->assertEquals(5, $this->repository->insertTeam($teams[4]));
      $this->assertEquals(3, $this->repository->insertTeam($teams[2]));
      $this->assertEquals(8, $this->repository->insertTeam($teams[7]));
      $this->assertEquals([$teams[2], $teams[4], $teams[7]], $this->repository->teams());
   }
   
   function testMatchesAndInsertMatch(): void {
      $teams = $this->data->teams();
      $matches = $this->data->matches();
      $this->assertEquals(7, $this->repository->insertTeam($teams[6]));
      $this->assertEquals(19, $this->repository->insertTeam($teams[18]));
      $this->assertEquals(6, $this->repository->insertTeam($teams[5]));
      $this->assertEquals(11, $this->repository->insertTeam($teams[10]));
      $this->assertEquals(6, $this->repository->insertMatch($matches[5]));
      $this->assertEquals(1, $this->repository->insertMatch($matches[0]));
      $this->assertEquals([$matches[0], $matches[5]], $this->repository->matches());
   }

   function testTeam(): void {
      $this->repository->fillDatabase();

      foreach ($this->data->teams() as $team) {
        $this->assertEquals($team, $this->repository->team($team['id']));
      }
   }

   function testTeamThrowsExceptionIfTeamDoesNotExist(): void {
      $this->repository->fillDatabase();
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Équipe inconnue');
      $this->repository->team(10000);
   }

   function testMatchThrowsExceptionIfMatchDoesNotExist(): void {
      $this->repository->fillDatabase();
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Match inconnu');
      $this->repository->match(10000);
   }

   function testUpdateRanking(): void {
      $this->repository->updateRanking();
      $this->repository->fillDatabase();
      $this->repository->updateRanking();
      $this->repository->updateRanking();
      $ranking = DB::table('ranking')->orderBy('rank')->get()->toArray();
      $this->assertEquals($this->data->expectedSortedRanking(), $ranking);
   }

   function testSortedRanking(): void {
      $this->repository->fillDatabase();
      $this->repository->updateRanking();
      $this->assertEquals($this->data->expectedSortedRankingWithName(), $this->repository->sortedRanking());
   }

   function testTeamMatches(): void {
      $this->repository->fillDatabase();
      $this->assertEquals($this->data->expectedMatchesForTeam4(), $this->repository->teamMatches(4));
   }

   function rankingRow(): void {
      $this->repository->fillDatabase();
      $this->repository->updateRanking();
      foreach ($this->data->expectedSortedRankingWithName() as $row) {
         $this->assertEquals($this->repository->rankingRow($row['team_id']), $row);
      }
   }

   function testRankingRowThrowsExceptionIfTeamDoesNotExist(): void {
      $this->repository->fillDatabase();
      $this->repository->updateRanking();
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Équipe inconnue');
      $this->repository->rankingRow(10000);
   }

   function testGetUserThrowsExceptionIfEmailNotExists(): void {
      $this->repository->addUser('test1@example.com', 'secret1');
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Utilisateur inconnu');
      $this->repository->getUser('test2@example.com', 'secret1');
   }

   function testGetUserThrowsExceptionIfPasswordIsIncorrect(): void {
      $this->repository->addUser('test1@example.com', 'secret1');
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Utilisateur inconnu');
      $this->repository->getUser('test1@example.com', 'secret2');
   }

   function testAddUserThrowsExceptionIfEmailAlreadyExists(): void {
      $this->repository->addUser('test@example.com', 'secret1');
      $this->expectException(Exception::class);
      $this->repository->addUser('test@example.com', 'secret2');
   }

   function testChangePassword(): void {
      $this->repository->addUser('test@example.com', 'secret1');
      $this->repository->changePassword('test@example.com', 'secret1', 'secret2');
      $user = $this->repository->getUser('test@example.com', 'secret2');
      $this->assertEquals($user, ['id'=>1, 'email'=> 'test@example.com']);
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Utilisateur inconnu');
      $user = $this->repository->getUser('test@example.com', 'secret1');
   }

   function testChangePasswordThrowsExceptionIfOldPasswordIsIncorrect(): void {
      $this->repository->addUser('test@example.com', 'secret1');
      $this->expectException(Exception::class);
      $this->expectExceptionMessage('Utilisateur inconnu');
      $this->repository->changePassword('test@example.com', 'secret2', 'secret1');
   }
}