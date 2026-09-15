<div class="alert alert-info border-0" style="background: rgba(13,110,253,0.1); color: var(--bs-info);">
    <h6 class="alert-heading display-font fw-bold mb-2">Bulk Update Preview</h6>
    <div class="row text-center mb-3">
        <div class="col-4">
            <div class="fs-4 fw-bold text-success">{{ $previewData['valid'] }}</div>
            <div class="fs-8 text-uppercase">Valid Employees</div>
        </div>
        <div class="col-4">
            <div class="fs-4 fw-bold text-warning">{{ $previewData['warnings'] }}</div>
            <div class="fs-8 text-uppercase">Warnings</div>
        </div>
        <div class="col-4">
            <div class="fs-4 fw-bold text-danger">{{ $previewData['conflicts'] }}</div>
            <div class="fs-8 text-uppercase">Conflicts</div>
        </div>
    </div>
</div>

<h6 class="display-font text-white mb-3">Changes to be applied (Effective: {{ $request->effective_from }}):</h6>
<div class="overflow-auto border rounded border-secondary border-opacity-25 p-3" style="max-height: 250px; background: rgba(0,0,0,0.15);">
    @if(empty($previewData['changes']))
        <div class="text-secondary text-center py-3">No field changes detected. Verify your selection.</div>
    @else
        @foreach($previewData['changes'] as $change)
            <div class="mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                <div class="fw-bold text-white">{{ $change['name'] }} <span class="text-secondary fs-8">({{ $change['id'] }})</span></div>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($change['fields'] as $fieldStr)
                        <li class="text-light fs-8">{{ $fieldStr }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @endif
</div>
