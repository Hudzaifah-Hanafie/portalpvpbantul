<li class="mb-2">
    <div class="d-flex align-items-center gap-2">
        <span class="fw-bold">{{ $node->nama }}</span>
        @if($node->jabatan)
            <span class="text-muted">({{ $node->jabatan }})</span>
        @endif
        <span class="badge bg-light text-dark">Urutan: {{ $node->urutan }}</span>
        <div class="ms-2 d-inline-flex gap-1">
            <a href="{{ route('admin.struktur.edit', $node->id) }}" class="btn btn-sm btn-outline-secondary">Ubah</a>
            <form action="{{ route('admin.struktur.destroy', $node->id) }}" method="POST" onsubmit="return confirm('Hapus data ini beserta anaknya?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
        </div>
    </div>
    @if($node->children->count())
        <ul class="list-unstyled ms-3">
            @foreach($node->children as $child)
                @include('admin.struktur.node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
