{if $images}
<object width="{$width}" height="{$height}">
    <param name="flashvars" value="offsite=true&page_show_url=%2Fphotos%2F{$images.user}%2Fsets%2F{$images.gallery}%2Fshow%2F&set_id={$images.gallery}"></param>
    <param name="movie" value="https://www.flickr.com/apps/slideshow/show.swf?v=71649"></param>
    <param name="allowFullScreen" value="true"></param>
    <embed type="application/x-shockwave-flash" src="https://www.flickr.com/apps/slideshow/show.swf?v=71649" allowFullScreen="true" flashvars="offsite=true&page_show_url=%2Fphotos%2F{$images.user}%2Fsets%2F{$images.gallery}%2Fshow%2F&set_id={$images.gallery}" width="{$width}" height="{$height}"></embed>
</object>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
