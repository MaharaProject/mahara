{if $images}
<div style="width:{$width}px; text-align:center;">
<object type="application/x-shockwave-flash" data="http://media.roytanck.com/flickrwidget.swf" width="{$width}" height="{$width}">
    <param name="movie" value="http://media.roytanck.com/flickrwidget.swf" />
    <param name="bgcolor" value="#ffffff" />
    <param name="flashvars" value="feed={$images}" />
    <param name="AllowScriptAccess" value="always"/>
    <p>
    <a href="http://www.roytanck.com">Roy Tanck</a>'s Flickr Widget requires Flash Player 9 or better.</p>
</object>
<span class="s"><a href="http://www.roytanck.com">roytanck.com</a></span>
</div>
{else}
  {str tag=noimagesfound section=artefact.file}
{/if}
