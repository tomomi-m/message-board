@extends('site.page.botBase')
@section('content')
@foreach ($messages as $message)
<div>
	<div>
	{{$message->userName}}
	</div>
	<div>
	{{$message->updated_at}}
	</div>
	<div>
	{{$message->message}}
	</div>
</div>
@endforeach
@stop