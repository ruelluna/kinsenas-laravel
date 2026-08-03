<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PwaLaunchController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        $team = $user->currentTeam;

        if ($team === null) {
            return redirect()->route('teams.index');
        }

        return redirect()->route('dashboard', ['current_team' => $team->slug]);
    }
}
