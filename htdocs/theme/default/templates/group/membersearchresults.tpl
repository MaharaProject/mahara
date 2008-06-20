{if !empty($results.data)}
        {foreach from=$results.data item=r}
          <tr class="{cycle values="r0,r1"}">
            <td style="width: 300px; border: 1px solid red;">
              <!--<div style="float: right;"><select><option>Member</option></select></div>-->
              <h4 style="margin:0;"><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h4>
              <div style="float: left;"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$r.id|escape}" alt=""></div>
              <p style="margin: .25em 0 0 65px;">{$r.introduction|str_shorten:80:true}</p>
              <p style="margin: .25em 0 0 65px;"><strong>Joined:</strong> {$r.jointime}</p>
            </td>
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
