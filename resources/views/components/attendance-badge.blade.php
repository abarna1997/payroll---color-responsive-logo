@php
    $map = $getMapping();
    $bgClass = $map['bg'] ?? ('bg-' . $map['color']);
    $textClass = isset($map['bg']) ? '' : 'text-white';
    $label = $map['label'];
    if ($detail) {
        $label .= ' ' . $detail;
    }
@endphp

<span class="badge {{ $bgClass }} {{ $textClass }} d-inline-flex align-items-center gap-1" data-bs-toggle="tooltip" title="{{ $map['tooltip'] }}">
    @if(isset($map['icon']))
        <i data-feather="{{ $map['icon'] }}" class="icon-sm"></i>
    @endif
    {{ $label }}
</span>