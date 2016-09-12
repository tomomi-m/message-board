<?php
$lastMod= (new DateTime($page->lastMessage_at))->format(DateTime::ATOM);
?>
<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
 <url>
  <loc>{{{Request::root()}}}/site/{{{$site->id}}}/{{{$page->id}}}</loc>
  <lastmod>{{{$lastMod}}}</lastmod>
  <changefreq>daily</changefreq>
 </url>
@if($page->hasChat == 'Y' && $page->isPublic == 'Y' && $messageCount > 0)
<?php
$maxIndex = ceil ( $messageCount / SitePageController::PAGING_LENGTH );
?>
@for ($i = 1; $i <= $maxIndex; $i++)
 <url>
  <loc>{{{Request::root()}}}/site/{{{$site->id}}}/{{{$page->id}}}?pagingNo={{{$i}}}</loc>
@if ($i < $maxIndex)
  <changefreq>monthly</changefreq>
@else
  <changefreq>daily</changefreq>
  <lastmod>{{{$lastMod}}}</lastmod>
@endif
 </url>
@endfor
@endif
</urlset>