{if $images}
<center><div width="100" height="115" style="width:100px;height:115px;padding:0;background-color:#fcfcfc;"><center><a href="http://{$images.user}.photos.live.com/play.aspx/{$images.album}?ref=1"><img src="{$WWWROOT}/artefact/file/blocktype/gallery/thumb.png" width="96" height="96" border="0"></a><br />{$images.album}</center></div></center>
{else}
  {str tag=cannotdisplayslideshow section=blocktype.file/gallery}
{/if}
