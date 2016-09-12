@extends('site.page.botBase')
@section('content')

<div>
{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->body)}}
</div>

@stop