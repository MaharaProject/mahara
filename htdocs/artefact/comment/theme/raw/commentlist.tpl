{foreach from=$data item=item}
  <tr class="{cycle name=rows values='r0,r1'}{if $item->highlight} highlight{/if}">
    <td>
      <div class="details commentleft">
      {if $item->author}
        <div class="icon"><a href="{$item->author->profileurl}">
          <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{$item->author|display_name}">
        </a><br />
        <a href="{$item->author->profileurl}" class="username">{$item->author|display_name}</a></div>
      {else}
        {$item->authorname}
      {/if}
      </div>
      <div class="commentrightwrap">
        <div class="fr">
        {if $item->makepublicform}<div class="makepublicbtn">{$item->makepublicform|safe}</div>{/if}
        {if $item->canedit}
          <form name="edit_{$post->id}" action="{$WWWROOT}artefact/comment/edit.php">
            <input type="hidden" name="id" value="{$item->id}">
            <input type="hidden" name="view" value="{$viewid}">
            <input type="image" src="{theme_url filename="images/edit.gif"}" title="{str tag=edit}">
          </form>
        {/if}
        {if $item->deleteform}{$item->deleteform|safe}{/if}
        </div>
      {if $item->deletedmessage}
        <span class="details">{str tag=commentremoved section=artefact.comment}</span>
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
        </div><br />
        {/if}
        {$item->description|safe|clean_html}
        {if $item->attachmessage}<div class="attachmessage">{$item->attachmessage}</div>{/if}
      {/if}
      </div>
      <div class="undercomment">
      {if $item->deletedmessage}
        <span>{$item->deletedmessage} | </span>
      {else}
        <span class="date">{$item->date} {if $item->updated}[{str tag=Updated}: {$item->updated}]{/if}</span>
        {if $item->pubmessage} | <span>{$item->pubmessage}</span>{/if}
        {if $item->makepublicrequested} | <span>{str tag=youhaverequestedpublic section=artefact.comment}</span>{/if}
        {strip}
        {foreach $item->attachments item=a name=attachments}
          {if $.foreach.attachments.first} | <span class="attachment"><label>{str tag=Attachments section=artefact.comment}:</label>{else},{/if} <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}">{$a->attachtitle}</a> <span class="attachsize">({$a->attachsize})</span></span>
        {/foreach}
        {/strip}
      {/if}</div>
    </td>
  </tr>
{/foreach}
