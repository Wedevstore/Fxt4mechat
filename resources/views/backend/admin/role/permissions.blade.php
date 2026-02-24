@foreach ($role->permissions as $permission)
        <span class="badge">{{ $permission }}</span>
@endforeach