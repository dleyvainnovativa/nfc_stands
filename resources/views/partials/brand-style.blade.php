{{-- Applies a tenant's brand colours over the theme tokens on public pages.
     Expects an optional $brand (App\Models\Tenant). Colours are validated
     #rrggbb on save, so they're safe to inline. Selectors match theme.css'
     specificity and come later in source order, so they win in both themes. --}}
@isset($brand)
    @if ($brand && $brand->hasCustomBrand())
        <style>
            :root,
            :root[data-theme="light"],
            :root[data-theme="dark"] {
                --accent: {{ $brand->brandPrimary() }};
                --accent-hover: {{ $brand->brandSecondary() }};
                --accent-2: {{ $brand->brandSecondary() }};
                --accent-text: {{ $brand->brandPrimary() }};
                --accent-soft: color-mix(in srgb, {{ $brand->brandPrimary() }} 14%, transparent);
            }
        </style>
    @endif
@endisset
