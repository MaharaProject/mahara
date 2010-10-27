<div class="slideshow" id="slideshow{$instanceid}">
    <table class="images">
    <tr>
    <td class="control" onClick="slideshow{$instanceid}.rewind();"
        onMouseOver="this.className='control highlight'"
        onMouseOut="this.className='control'">&#060;</td>
    <td>
    {foreach from=$images item=image}
        <a href="{$image.link}" target="_blank"><img src="{$image.source}" alt="{$image.title}" title="{$image.title}" /></a>
    {/foreach}
    </td>
    <td class="control" onClick="slideshow{$instanceid}.advance();"
        onMouseOver="this.className='control highlight'"
        onMouseOut="this.className='control'">&#062;</td>
    </tr>
    </table>
</div>
<script>
var slideshow{$instanceid} = new Slideshow({$instanceid}, {$count});
</script>
