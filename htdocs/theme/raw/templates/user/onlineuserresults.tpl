{if $data}
{foreach from=$data item=user}
    <tr class="{cycle values='r0,r1'}">
        <td class="onlineinfo">
          <div class="peoplelistinfo">
            <div class="leftdiv" id="onlineinfo_{$user->id}">
                <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
            </div>

            <div class="rightdiv">
              <h3><a href="{profile_url($user)}">{$user->display_name}</a></h3>
            </div>
          </div>
        </td>
    </tr>
{/foreach}
{else}
    <tr><td><div class="message">{str tag=noonlineusersfound section=mahara}</div></td></tr>
{/if}
