<?php

namespace App\Http\Controllers;

use App\Models\GrupoMaster;
use App\Models\Notification;
use App\Models\Tutor;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('notification.index', ['notifications' => $notifications]);
    }

    public function read(int $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $notification) {
            abort(404, 'Notificación no encontrada.');
        }

        if ($notification->read_at === null) {
            $notification->read_at = time();
            $notification->save();
        }

        if (! $notification->url) {
            return redirect('/notification/index');
        }

        // Las notificaciones creadas desde el panel administrativo pueden
        // conservar el dominio del backend, aunque el destino pertenece al
        // portal del tutor. Resolvemos esa ruta dentro de esta aplicación.
        $notificationUrl = rawurldecode($notification->url);
        if (str_contains($notificationUrl, 'grupo-master/pendientes')) {
            parse_str((string) parse_url($notificationUrl, PHP_URL_QUERY), $params);
            $idGrupo = (int) ($params['idGrupo'] ?? 0);

            // Compatibilidad con avisos anteriores, creados sin idGrupo.
            if (! $idGrupo) {
                $tutor = Tutor::where('id_user', Auth::id())->first();
                if ($tutor) {
                    $grupos = GrupoMaster::where('id_tutor', $tutor->id)
                        ->with(['carrera', 'semestre', 'grupoLetra'])
                        ->get();
                    foreach ($grupos as $grupo) {
                        if (str_contains($notification->message, $grupo->nombre)) {
                            $idGrupo = (int) $grupo->id;
                            break;
                        }
                    }
                }
            }

            return redirect()->to('/grupo-master/pendientes'.($idGrupo ? '?idGrupo='.$idGrupo : ''));
        }

        return redirect()->to($notification->url);
    }
}
