{if $images}
<iframe width="{$width}" height="{$height}" src="{$images.url}/{$images.user}/{$images.album}?albumview=slideshow"></iframe>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
