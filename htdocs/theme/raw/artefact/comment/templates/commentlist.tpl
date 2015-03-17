{if $position == 'blockinstance' && !$onview}
<tr>
  <td>
{/if}
{foreach from=$data item=item}
  <div class="{cycle name=rows values='r0,r1'}{if $item->highlight} highlight{/if}{if $item->makepublicform} private{/if}">
      <div class="commentleft">
      {if $item->author}
        <a href="{$item->author->profileurl}">
            <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}">
        </a>
      {else}
            <img src="{profile_icon_url user=null maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetextanonymous}">
      {/if}
      </div>
      <div class="commentrightwrap">
        {if !$onview}
            <div class="fr">
            {if $item->makepublicform}<div class="makepublicbtn">{$item->makepublicform|safe}</div>{/if}
            {if $item->canedit}
              <form name="edit_{$post->id}" action="{$WWWROOT}artefact/comment/edit.php">
                <input type="hidden" name="id" value="{$item->id}">
                <input type="hidden" name="view" value="{$viewid}">
                <input type="image" src="{theme_image_url filename="btn_edit"}" title="{str tag=edit}">
              </form>
            {/if}
            {if $item->deleteform}{$item->deleteform|safe}{/if}
            </div>
        {/if}
        {if $item->author}
            <div class="author"><a href="{$item->author->profileurl}" class="username">{$item->author|display_name}</a><span class="postedon"> - {$item->date} {if $item->updated}[{str tag=Updated}: {$item->updated}]{/if}</span></div>
        {else}
            <div class="author">{$item->authorname}<span class="postedon"> - {$item->date}</span></div>
        {/if}
      {if $item->deletedmessage}
        <div class="deleteddetails">{$item->deletedmessage}</div>
      {else}
        {if $item->ratingdata}
        <div class="commentrating">
          {for i $item->ratingdata->min_rating $item->ratingdata->max_rating}
            {if !$item->ratingdata->export}
          <input name="star{$item->id}" type="radio" class="star" {if $i === $item->ratingdata->value} checked="checked" {/if} disabled="disabled" />
            {else}
          <div class="star-rating star star-rating-applied star-rating-readonly{if $i <= $item->ratingdata->value} star-rating-on{/if}"><a>&nbsp;</a></div>
            {/if}
          {/for}
          <div class="cb"></div>
        </div>
        {/if}
        <div class="detail">{$item->description|safe|clean_html}</div>
        {if $item->attachmessage}<div class="attachmessage">{$item->attachmessage}</div>{/if}
        {if $item->pubmessage}<div class="privatemessage">{$item->pubmessage}</div>{/if}
        {if $item->makepublicrequested}<div class="requestmessage">{str tag=youhaverequestedpublic section=artefact.comment}</div>{/if}
      {/if}
      </div>
      <div class="undercomment">
      {if $item->deletedmessage}
      {else}
        {strip}
        {foreach $item->attachments item=a name=attachments}
          {if $.foreach.attachments.first}<strong>{str tag=Attachments section=artefact.comment}:</strong>{else},{/if} <span class="attachment"><a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}">{$a->attachtitle}</a> <span class="attachsize">({$a->attachsize})</span></span>
        {/foreach}
        {/strip}
      {/if}</div>
      <div class="cb"></div>
  </div>
{/foreach}
{if $position == 'blockinstance' && !$onview}
  </td>
</tr>
{/if}