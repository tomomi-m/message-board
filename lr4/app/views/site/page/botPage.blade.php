@extends('site.page.botBase')
@section('content')
{{ str_replace('${siteImage}', Request::getBasePath().'/image/site/'. $page->site, $page->body)}}
@stop