@props(['data'])

{{-- Structured data for search engines. JSON_HEX_TAG keeps a stray "</script>" in the data from ending the tag. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
