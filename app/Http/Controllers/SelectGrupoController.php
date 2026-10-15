<?php

namespace App\Http\Controllers;

use App\Models\GrupoMaster;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelectGrupoController extends Controller
{
    public function index()
    {
        $tutor = Tutor::where('id_user', Auth::id())->first();

        if (! $tutor) {
            abort(403, 'No se encontró un tutor asociado.');
        }

        $grupos = GrupoMaster::with(['carrera', 'semestre', 'grupoLetra'])
            ->where('id_tutor', $tutor->id)
            ->get();

        return view('select-grupo.index', ['grupos' => $grupos]);
    }

    public function setGrupo(Request $request)
    {
        $idGrupo = (int) $request->post('id_grupo');
        $tutor = Tutor::where('id_user', Auth::id())->first();
        $grupo = $tutor
            ? GrupoMaster::where('id', $idGrupo)->where('id_tutor', $tutor->id)->first()
            : null;

        if (! $grupo) {
            abort(403, 'No tienes permiso para seleccionar este grupo.');
        }

        $request->session()->put('id_grupo', $grupo->id);

        return response()->json(['status' => 'success']);
    }
}
