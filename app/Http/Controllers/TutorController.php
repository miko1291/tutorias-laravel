<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function perfil(): View
    {
        $tutor = Tutor::with(['grupoMasters.carrera', 'grupoMasters.semestre', 'grupoMasters.grupoLetra'])
            ->where('id_user', Auth::id())
            ->firstOrFail();

        return view('tutor.perfil', ['tutor' => $tutor]);
    }
}
