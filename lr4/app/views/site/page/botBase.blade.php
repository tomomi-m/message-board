<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="content-language" content="ja">
<title>{{{$page->title}}}</title>
<link rel="icon"
	href="{{{ Request::getBasePath() }}}/image/site/{{{ $site->id}}}/favicon.ico"
	type="image/vnd.microsoft.icon" />
</head>
<body>
@yield('content')
@show
</body>
</html>
