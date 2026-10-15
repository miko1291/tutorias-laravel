<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\ExpedientePreguntaController;
use App\Http\Controllers\GrupoMasterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordRecoveryController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\SelectGrupoController;
use App\Http\Controllers\SemanaRealController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Route;

// Rutas espejo de las URLs originales de Yii2 (sin urlManager) para
// conservar compatibilidad con marcadores y enlaces existentes.

Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/site/index', [SiteController::class, 'index']);
Route::get('/site/acceso', [SiteController::class, 'acceso']);
Route::get('/manual', [SiteController::class, 'manual'])->name('manual');
Route::get('/site/login', [SiteController::class, 'showLogin'])->name('login');
Route::post('/site/login', [SiteController::class, 'login']);
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordRecoveryController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordRecoveryController::class, 'sendLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordRecoveryController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordRecoveryController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});
Route::post('/site/logout', [SiteController::class, 'logout'])->name('logout');
Route::get('/site/assign-id-grupo/{id_grupo}', [SiteController::class, 'assignIdGrupo']);
Route::get('/site/clear-id-grupo', [SiteController::class, 'clearIdGrupo']);

Route::middleware('auth')->group(function () {
    Route::get('/expediente/index', [ExpedienteController::class, 'formulario']);
    Route::post('/expediente/guardar', [ExpedienteController::class, 'guardar']);
    Route::get('/expediente/ficha', [ExpedienteController::class, 'ficha']);
    Route::get('/expediente/perfil', [ExpedienteController::class, 'perfil']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::post('/admin/users/save', [AdminController::class, 'saveUser']);
        Route::post('/admin/users/{id}/save', [AdminController::class, 'saveUser']);
        Route::post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser']);
        Route::get('/admin/tutors', [AdminController::class, 'tutors']);
        Route::post('/admin/tutors/save', [AdminController::class, 'saveTutor']);
        Route::post('/admin/tutors/{id}/save', [AdminController::class, 'saveTutor']);
        Route::post('/admin/tutors/{id}/delete', [AdminController::class, 'deleteTutor']);
        Route::get('/admin/groups', [AdminController::class, 'groups']);
        Route::post('/admin/groups/save', [AdminController::class, 'saveGroup']);
        Route::post('/admin/groups/{id}/save', [AdminController::class, 'saveGroup']);
        Route::post('/admin/groups/{id}/assign-student', [AdminController::class, 'assignStudent']);
        Route::post('/admin/groups/{id}/students/{idAlumno}/unassign', [AdminController::class, 'unassignStudent']);
        Route::post('/admin/groups/{id}/delete', [AdminController::class, 'deleteGroup']);
        Route::get('/admin/pat', [AdminController::class, 'pats']);
        Route::post('/admin/pat/save', [AdminController::class, 'savePat']);
        Route::post('/admin/pat/{id}/delete', [AdminController::class, 'deletePat']);
        Route::get('/admin/semanas', [AdminController::class, 'semanasAdmin']);
        Route::post('/admin/semanas/save', [AdminController::class, 'saveSemana']);
        Route::post('/admin/semanas/{id}/delete', [AdminController::class, 'deleteSemana']);
        Route::get('/admin/performance', [AdminController::class, 'performances']);
        Route::post('/admin/performance/save', [AdminController::class, 'savePerformance']);
        Route::post('/admin/performance/{id}/delete', [AdminController::class, 'deletePerformance']);
        Route::get('/admin/catalogos/{catalog}', [AdminController::class, 'catalog']);
        Route::post('/admin/catalogos/{catalog}/save', [AdminController::class, 'saveCatalog']);
        Route::post('/admin/catalogos/{catalog}/{id}/save', [AdminController::class, 'saveCatalog']);
        Route::post('/admin/catalogos/{catalog}/{id}/delete', [AdminController::class, 'deleteCatalog']);

    });

    Route::middleware('role:admin,psicologo,psicologa')->group(function () {
        Route::get('/expediente-pregunta/index', [ExpedientePreguntaController::class, 'index']);
        Route::post('/expediente-pregunta/diseno', [ExpedientePreguntaController::class, 'guardarDiseno']);
        Route::post('/expediente-pregunta/guardar/{id?}', [ExpedientePreguntaController::class, 'guardar']);
        Route::post('/expediente-pregunta/ordenar', [ExpedientePreguntaController::class, 'ordenar']);
        Route::post('/expediente-pregunta/eliminar/{id}', [ExpedientePreguntaController::class, 'eliminar']);
        Route::post('/expediente-pregunta/toggle/{id}', [ExpedientePreguntaController::class, 'toggle']);
    });

    Route::get('/select-grupo/index', [SelectGrupoController::class, 'index']);
    Route::post('/select-grupo/set-grupo', [SelectGrupoController::class, 'setGrupo']);
    Route::get('/tutor/index', [TutorController::class, 'perfil'])
        ->middleware('role:tutor,admin');

    Route::get('/grupo-master/index', [GrupoMasterController::class, 'index']);
    Route::get('/grupo-master/pendientes/{idGrupo?}', [GrupoMasterController::class, 'pendientes'])->middleware('role:admin,tutor');
    Route::post('/grupo-master/enviar-prueba', [GrupoMasterController::class, 'enviarPrueba']);
    Route::post('/grupo-master/enviar-pendientes', [GrupoMasterController::class, 'enviarPendientes']);

    Route::get('/notification/index', [NotificationController::class, 'index']);
    Route::get('/notification/read/{id}', [NotificationController::class, 'read']);

    Route::middleware('role:admin,tutor,psicologo')->group(function () {
        Route::match(['get', 'post'], '/asistencia/tomar-lista/{idGrupo}', [AsistenciaController::class, 'tomarLista'])->name('asistencia.tomar-lista');
        Route::get('/asistencia/index', [AsistenciaController::class, 'index'])->name('asistencia.index');
        Route::get('/asistencia/view/{idGrupo}/{fecha}', [AsistenciaController::class, 'view'])->name('asistencia.view');
        Route::post('/asistencia/delete/{idGrupo}/{fecha}', [AsistenciaController::class, 'delete'])->name('asistencia.delete');
        Route::get('/asistencia/exportar-reporte/{idGrupo}', [AsistenciaController::class, 'exportarReporte'])->name('asistencia.exportar');

        Route::get('/diagnostico/index', [ProgramaController::class, 'diagnostico']);
        Route::post('/diagnostico/guardar', [ProgramaController::class, 'guardarDiagnostico']);
        Route::post('/diagnostico/eliminar/{id}', [ProgramaController::class, 'eliminarDiagnostico']);
        Route::get('/diagnostico/editar/{id}', [ProgramaController::class, 'editarDiagnostico']);
        Route::post('/diagnostico/actualizar/{id}', [ProgramaController::class, 'actualizarDiagnostico']);
        Route::get('/evaluacion/index', [ProgramaController::class, 'evaluacion']);
        Route::post('/evaluacion/guardar', [ProgramaController::class, 'guardarEvaluacion']);
        Route::post('/evaluacion/eliminar/{id}', [ProgramaController::class, 'eliminarEvaluacion']);
        Route::get('/evaluacion/editar/{id}', [ProgramaController::class, 'editarEvaluacion']);
        Route::post('/evaluacion/actualizar/{id}', [ProgramaController::class, 'actualizarEvaluacion']);
        Route::get('/canalizacion/index', [ProgramaController::class, 'canalizacion']);
        Route::post('/canalizacion/guardar', [ProgramaController::class, 'guardarCanalizacion']);
        Route::post('/canalizacion/eliminar/{id}', [ProgramaController::class, 'eliminarCanalizacion']);
        Route::get('/canalizacion/editar/{id}', [ProgramaController::class, 'editarCanalizacion']);
        Route::post('/canalizacion/actualizar/{id}', [ProgramaController::class, 'actualizarCanalizacion']);
        Route::get('/pat/index', [ProgramaController::class, 'pat']);
        Route::get('/semana/index', [ProgramaController::class, 'semanas']);
        Route::get('/performance/index', [ProgramaController::class, 'performance']);
        Route::get('/semana-real/index', [SemanaRealController::class, 'index'])->name('semana-real.index');
        Route::match(['get', 'post'], '/semana-real/create/{idSemana}/{idGrupo}', [SemanaRealController::class, 'create'])->name('semana-real.create');
        Route::match(['get', 'post'], '/semana-real/update/{id}', [SemanaRealController::class, 'update'])->name('semana-real.edit');
        Route::post('/semana-real/delete/{id}', [SemanaRealController::class, 'delete'])->name('semana-real.delete');
    });

    // alumno/ficha era pública en el filtro RBAC global del original;
    // la protección efectiva la da el chequeo de id_grupo en el controlador.
    Route::get('/alumno/ficha/{id}', [AlumnoController::class, 'ficha']);

    Route::middleware('role:tutor,admin')->group(function () {
        Route::get('/alumno/view/{id}', [AlumnoController::class, 'view']);
    });

    // En el original solo admin alcanza estas rutas (admin → All → /*);
    // el rol tutor no tiene asignado /alumno/index|create|update|delete.
    Route::middleware('role:admin')->group(function () {
        Route::get('/alumno/index', [AlumnoController::class, 'index']);
        Route::match(['get', 'post'], '/alumno/create/{id_grupo?}', [AlumnoController::class, 'create']);
        Route::match(['get', 'post'], '/alumno/update/{id}', [AlumnoController::class, 'update']);
        Route::post('/alumno/delete/{id}', [AlumnoController::class, 'delete']);
    });
});
