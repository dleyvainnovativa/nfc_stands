{{-- Server → client flash bridge. app.js reads these on load and shows toasts. --}}
@foreach (['success' => 'flash.success', 'error' => 'flash.error', 'warning' => 'flash.warning', 'info' => 'flash.info'] as $type => $key)
    @if (session($key))
        <div data-flash="{{ $type }}" data-flash-msg="{{ session($key) }}" hidden></div>
    @endif
@endforeach

@if ($errors->any())
    <div data-flash="error" data-flash-msg="{{ $errors->first() }}" hidden></div>
@endif
