<div class="d-flex flex-wrap align-items-center gap-3">
    @if($dbPath)
        <div class="border rounded p-2 text-center bg-light" style="width: 120px;">
            <img src="{{ asset('storage/' . $dbPath) }}" class="img-fluid rounded" style="max-height: 80px; object-fit: contain;">
        </div>
        <div class="flex-grow-1">
            <div class="fs-8 text-secondary mb-2">{{ $dimensions }}</div>
            <div class="d-flex gap-2">
                <a href="{{ asset('storage/' . $dbPath) }}" target="_blank" download class="btn btn-sm btn-outline-secondary py-1 px-2 border-0">
                    <i class="bi bi-download"></i> Download
                </a>
                @if(!$readOnly)
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input check-custom me-2" type="checkbox" name="{{ $fieldName }}_remove" value="1" id="remove_{{ $fieldName }}">
                        <label class="form-check-label text-danger fs-8" for="remove_{{ $fieldName }}">Remove</label>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="w-100 fs-8 text-secondary mb-2">{{ $dimensions }}</div>
    @endif
</div>

@if(!$readOnly)
    <div class="mt-3">
        <input type="file" class="form-control form-control-custom" name="{{ $fieldName }}" accept=".png, .jpg, .jpeg, .svg, .webp">
    </div>
@endif
