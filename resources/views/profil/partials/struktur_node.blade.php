<div class="org-tree ps-4 border-start border-2 border-primary position-relative">
    @foreach ($nodes as $node)
        <div class="org-node mb-3 position-relative">
            {{-- Decorative Dot Point --}}
            <div class="position-absolute bg-primary rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -25px; top: 18px; border: 3px solid white;"></div>
            
            <div class="card shadow-sm border-0 rounded-3 transition-all org-card" style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-primary mb-1">{{ $node->nama }}</h6>
                    @if($node->jabatan)
                        <p class="text-muted small mb-0"><i class="fas fa-id-badge me-2 text-secondary"></i>{{ $node->jabatan }}</p>
                    @endif
                </div>
            </div>

            @if($node->children->isNotEmpty())
                <div class="mt-3">
                    @include('profil.partials.struktur_node', ['nodes' => $node->children])
                </div>
            @endif
        </div>
    @endforeach
</div>

<style>
.org-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    border-left: 4px solid var(--bs-primary) !important;
}
</style>
