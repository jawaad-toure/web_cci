@extends('base')

@section('title')
Match de l'équipe
@endsection

@section('content')
<a class="btn btn-primary" href="{{ route('teams.follow', ['teamId'=>$teamRank['team_id']]) }}">Suivre</a><br><br>
<table class="table table-striped">
    <thead class="thead-dark">
        <tr>                
            <th>N°</th>
            <th>Équipe</th>
            <th>MJ</th>
            <th>G</th>
            <th>N</th>
            <th>P</th>
            <th>BP</th>
            <th>BC</th>
            <th>DB</th>
            <th>PTS</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{ $teamRank["rank"] }}</td>
            <td>
                <a href="{{route('teams.show', ['teamId'=> $teamRank['team_id']])}}">
                    {{ $teamRank["name"] }}
                </a>
            </td>
            <td>{{ $teamRank["match_played_count"] }}</td>
            <td>{{ $teamRank["match_won_count"] }}</td>
            <td>{{ $teamRank["draw_count"] }}</td>
            <td>{{ $teamRank["match_lost_count"] }}</td>
            <td>{{ $teamRank["goal_for_count"] }}</td>
            <td>{{ $teamRank["goal_against_count"] }}</td>
            <td>{{ $teamRank["goal_difference"] }}</td>
            <td>{{ $teamRank["points"] }}</td>
        </tr>
    </tbody>
</table>


<table class="table table-striped">
    @foreach ($teamMatches as $teamMatch)
        <tr>
            <td>{{ $teamMatch['date'] }}</td>
            <td>
                <a href="{{route('teams.show', ['teamId'=> $teamMatch['team0']])}}">
                    {{ $teamMatch['name0'] }}
                </a>
            </td>
            <td>
                {{ $teamMatch['score0'] }} - {{ $teamMatch['score1'] }} 
            <td>
                <a href="{{route('teams.show', ['teamId'=> $teamMatch['team1']])}}">
                    {{ $teamMatch['name1'] }}
                </a>                        
            </td>

            <td>
                <form method="POST" action="{{ route('matches.delete', ['matchId' => $teamMatch['id']]) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>
@endsection