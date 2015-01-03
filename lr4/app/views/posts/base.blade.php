<!DOCTYPE html>
 <html>
 <head>
 <meta charset="utf-8">
 <title>Builwing</title>
 <meta name="viewport" content="width=device-width,minimum-scale=1">
 {{HTML::style('//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css')}}
 {{HTML::script('//code.jquery.com/jquery-1.10.2.min.js')}}
 {{HTML::script('//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js')}}
 {{HTML::script('js/jquery.serializejson.min.js')}}
  </head>
 <body>
 @section('page')
 <div data-role="page" id="contact" data-back-btn-text="戻る">
 @show
 @section('header')
 @include('posts.header')
 @show
 @yield('content')
 @yield('popup')
 @section('footer')
 @include('posts.footer')
 @show
 </div><!--end of page-->
 </body>
 </html>
