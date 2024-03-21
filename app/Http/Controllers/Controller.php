<?php

namespace App\Http\Controllers;

use Exception;
use App\Repositories\Repository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $repository;
    
    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }    

    public function showRanking() {
        $ranking = $this->repository->sortedRanking();
        return view('ranking', ['ranking' => $ranking]);
    }

    public function showTeam(int $teamId) {
        $teamMatches = $this->repository->teamMatches($teamId);
        $teamRank = $this->repository->rankingRow($teamId);
        return view('teams', ['teamMatches' => $teamMatches, 'teamRank' => $teamRank]);
    }

    public function createTeam(Request $request) {
        if(!$request->session()->has('user')) {
            return redirect()->route('login');
        }
        return view('team_create');
    }

    public function createMatch(Request $request) {
        if(!$request->session()->has('user')) {
            return redirect()->route('login');
        }
        $teams = $this->repository->teams();
        return view('match_create', ["teams" => $teams]);
    }

    public function storeTeam(Request $request) {
        if(!$request->session()->has('user')) {
            return redirect()->route('login');
        }

        $rules = ['team_name' => ['required', 'min:3', 'max:20', 'unique:teams,name']];
        
        $messages = [
            'team_name.required' => "Vous devez saisir un nom d'équipe.",
            'team_name.min' => "Le nom doit contenir au moins :min caractères.",
            'team_name.max' => "Le nom doit contenir au plus :max caractères.",
            'team_name.unique' => "Le nom d'équipe existe déjà."
        ];        
        
        $validatedData = $request->validate($rules, $messages);        

        try {
            $teamId = $this->repository->insertTeam(["name" => $validatedData["team_name"]]);
            $this->repository->updateRanking();
        } catch (Exception $exception) {
            return redirect()->route('teams.create')->withInput()->withErrors("Impossible de créer l'équipe.");
        }

        return redirect()->route('teams.show', ['teamId' => $teamId]);
    }

    public function storeMatch(Request $request) {
        if(!$request->session()->has('user')) {
            return redirect()->route('login');
        }

        $rules = [
            'team0' => ['required', 'exists:teams,id'],
            'team1' => ['required', 'exists:teams,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'score0' => ['required', 'integer', 'between:0,50'],
            'score1' => ['required', 'integer', 'between:0,50']
        ];

        $messages = [
            'team0.required' => 'Vous devez choisir une équipe.',
            'team0.exists' => 'Vous devez choisir une équipe qui existe.',
            'team1.required' => 'Vous devez choisir une équipe.',
            'team1.exists' => 'Vous devez choisir une équipe qui existe.',
            'date.required' => 'Vous devez choisir une date.',
            'date.date' => 'Vous devez choisir une date valide.',
            'time.required' => 'Vous devez choisir une heure.',
            'time.date_format' => 'Vous devez choisir une heure valide.',
            'score0.required' => 'Vous devez choisir un nombre de buts.',
            'score0.integer' => 'Vous devez choisir un nombre de buts entier.',
            'score0.between' => 'Vous devez choisir un nombre de buts entre 0 et 50.',
            'score1.required' => 'Vous devez choisir un nombre de buts.',
            'score1.integer' => 'Vous devez choisir un nombre de buts entier.',
            'score1.between' => 'Vous devez choisir un nombre de buts entre 0 et 50.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $date = $validatedData['date'];
        $time = $validatedData['time'];
        $datetime = "$date $time";

        try {
            $matchId = $this->repository->insertMatch([
                "team0" => $validatedData["team0"],
                "team1" => $validatedData["team1"],
                'date' => $datetime,
                'score0' => $validatedData["score0"],
                'score1' => $validatedData["score1"]
            ]);
            
            $this->repository->updateRanking();
        } catch (Exception $e) {
            return redirect()->route('matches.create')->withInput()->withErrors("Impossible de créer le match.");
        }

        return redirect()->route('ranking.show');
    }

    public function showLoginForm() {
        return view('login');
    }

    public function login(Request $request) {
        $rules = [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required']
        ];

        $messages = [
            'email.required' => 'Vous devez saisir un e-mail.',
            'email.email' => 'Vous devez saisir un e-mail valide.',
            'email.exists' => "Cet utilisateur n'existe pas.",
            'password.required' => "Vous devez saisir un mot de passe.",
        ];

        $validatedData = $request->validate($rules, $messages);

        try {
            # TODO 1 : lever une exception si le mot de passe de l'utilisateur n'est pas correct
            $user = $this->repository->getUser($validatedData['email'], $validatedData['password']);
            # TODO 2 : se souvenir de l'authentification de l'utilisateur
            $request->session()->put('user', $user);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors("Impossible de vous authentifier.");
        }
        return redirect()->route('ranking.show');
    }

    public function followTeam(int $teamId) {
        return redirect()->route('ranking.show')->cookie('followed_team', $teamId);
    }

    public function logout(Request $request) {
        $request->session()->forget('user');
        return redirect()->route('ranking.show'); 
    }

    public function deleteMatch(Request $request, int $matchId) {
        if (!$request->session()->has('user')) {
            return redirect()->route('login');
        }
        
        $this->repository->deleteMatch($matchId);

        $this->repository->updateRanking();

        return redirect()->back();
    }
}
