{if $images}
<iframe marginheight="0" marginwidth="0" width="{$width}" height="{$height}" scrolling="no" frameborder="0" src="{$images.url}/{$images.user}/{$images.album}?albumview=slideshow"></iframe>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
