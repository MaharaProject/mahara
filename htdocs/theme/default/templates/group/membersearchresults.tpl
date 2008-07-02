{if !empty($results.data)}
        {foreach from=$results.data item=r}
          <tr class="{cycle values="r0,r1"}">
            <td>
              <h4><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h4>
              <div class="fl"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$r.id|escape}" alt=""></div>
              <p><strong>Joined:</strong> {$r.jointime}</p>
              <p>{$r.introduction|str_shorten:80:true}</p>
            </td>
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
