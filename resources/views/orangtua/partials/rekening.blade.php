@if ($rekening->isNotEmpty())
    <div class="d-flex flex-column gap-2">
        @foreach ($rekening as $method)
            <div class="d-flex align-items-center gap-3 p-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                <div class="stat-icon bg-grad-1" style="width:42px;height:42px;font-size:1rem;flex-shrink:0">
                    <i class="bi bi-bank2"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold" style="font-size:.86rem">{{ $method->display_name }}</div>
                    <div class="fw-bold" style="font-family:monospace;font-size:1rem;letter-spacing:.04em">{{ $method->account_number }}</div>
                    <div class="text-muted-2" style="font-size:.74rem">a.n. {{ $method->account_holder ?: '-' }}</div>
                </div>
                <button type="button" class="btn btn-soft btn-sm" data-copy="{{ $method->account_number }}"
                    onclick="if(navigator.clipboard){navigator.clipboard.writeText(this.dataset.copy);this.textContent='Tersalin';setTimeout(()=>{this.textContent='Salin'},1500)}">
                    Salin
                </button>
            </div>
        @endforeach
    </div>
@endif
