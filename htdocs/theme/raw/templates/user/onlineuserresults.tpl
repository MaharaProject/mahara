{if $data}
{foreach from=$data item=user}
    <div class="{cycle values='r0,r1'} listrow">
          <div class="peoplelistinfo">
            <div class="leftdiv user-icon user-icon-40" id="onlineinfo_{$user->id}">
                <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
            </div>

            <div class="rightdiv">
              <h3 class="title"><a href="{profile_url($user)}">{$user->display_name}</a></h3>
            </div>

            <div class="cb"></div>

          </div>
    </div>
{/foreach}
{else}
    <div class="message">{str tag=noonlineusersfound section=mahara}</div>
{/if}
