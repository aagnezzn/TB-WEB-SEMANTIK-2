{{-- Open Graph Protocol. Variabel: $ogTitle, $ogDescription, $ogType (opsional), $ogImage (opsional) --}}
<meta property="og:title" content="{{ $ogTitle }}" />
<meta property="og:type" content="{{ $ogType ?? 'website' }}" />
<meta property="og:description" content="{{ $ogDescription }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="{{ $ogImage ?? asset('images/icon.svg') }}" />
<meta property="og:image:alt" content="AeroSearch ASEAN" />
<meta property="og:site_name" content="AeroSearch ASEAN" />
<meta property="og:locale" content="{{ app()->getLocale() === 'id' ? 'id_ID' : 'en_US' }}" />
