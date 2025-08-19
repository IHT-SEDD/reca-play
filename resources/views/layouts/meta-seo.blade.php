<!-- SEO -->
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="author" content="{{ $metaAuthor }}">

<!-- Open Graph / Facebook -->
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name', 'RECA') }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
<meta name="twitter:image" content="{{ $twitterImage }}">

<!-- Canonical URL -->
<link rel="canonical" href="{{ url()->current() }}">

<!-- Additional SEO -->
<meta name="robots" content="index, follow">
<meta name="language" content="en">
<meta name="revisit-after" content="7 days">
<meta name="theme-color" content="#000000">