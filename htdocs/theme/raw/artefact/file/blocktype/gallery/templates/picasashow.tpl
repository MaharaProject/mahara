{if $images}
<object width="{$width}" height="{$height}">
    <param name="flashvars" value="host=picasaweb.google.com&feat=flashalbum&RGB=0x000000&feed=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2Ffeed%2Fapi%2Fuser%2F{$images.user}%2Falbum%2F{$images.gallery}%3Falt%3Drss%26kind%3Dphoto"></param>
    <param name="movie" value="https://picasaweb.google.com/s/c/bin/slideshow.swf"></param>
    <param name="allowFullScreen" value="true"></param>
    <embed type="application/x-shockwave-flash" src="https://picasaweb.google.com/s/c/bin/slideshow.swf" width="{$width}" height="{$height}" allowFullScreen="true" flashvars="host=picasaweb.google.com&feat=flashalbum&RGB=0x000000&feed=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2Ffeed%2Fapi%2Fuser%2F{$images.user}%2Falbum%2F{$images.gallery}%3Falt%3Drss%26kind%3Dphoto"></embed>
</object>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
