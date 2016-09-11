<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
 <sitemap>
  <loc>{{{Request::root()}}}/site/{{{$site->id}}}/{{{$page->id}}}/get-sitemap</loc>
  <lastmod>{{{(new DateTime($page->lastMessage_at))->format(DateTime::ATOM)}}}</lastmod>
 </sitemap>
@endforeach
</sitemapindex>
