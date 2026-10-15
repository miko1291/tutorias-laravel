<form action="{{ route('logout') }}" method="post" class="form-inline">
    @csrf
    <button type="submit" class="btn btn-link logout" title="Cerrar sesión" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right"></i><span class="logout-label">Salir</span></button>
</form>
