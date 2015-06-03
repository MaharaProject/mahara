{if $images}
<iframe marginheight="0" marginwidth="0" width="{$width}" height="{$height}" scrolling="no" frameborder="0" src="http://www.panoramio.com/user/{$images.user}/slideshow"></iframe>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
