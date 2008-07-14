{if !empty($results.data)}
        {foreach from=$results.cdata item=row}
          <tr class="r1">
            {foreach from=$row item=r}
            <td>
              <div class="fl"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$r.id|escape}" alt=""></div>
                <h4><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h4>
                <p>{$results.roles[$r.role]->display}</p>
                <p><strong>Joined:</strong> {$r.jointime}</p>
                <p>{$r.introduction|str_shorten:80:true}</p>
            </td>
            {/foreach}
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
