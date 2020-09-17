{if $data}
{foreach from=$data item=user}
<div class="list-group-item flush">
    <div class="row" id="onlineinfo_{$user->id}">
        <div class="col-md-12">
            <div class="user-icon user-icon-40 float-left">
                <a href="{profile_url($user)}"><img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}"></a>
            </div>
            <h2 class="list-group-item-heading">
              <a href="{profile_url($user)}">{$user->display_name}</a>
            </h2>
            {if $user->institutions}
            <div class="memberof detail text-small">
                <span class="icon text-default icon-university left" role="presentation" aria-hidden="true"></span>
                {$user->institutions|safe}
            </div>
            {/if}
            {if $user->introduction}
            <div class="text-small detail text-midtone">
                <a class="inner-link text-link collapsed with-introduction" data-toggle="collapse" data-target="#userintro{$user->id}"
                    href="#userintro{$user->id}" role="button" aria-expanded="false"
                    aria-controls="userintro{$user->id}">
                    <span class="icon icon-chevron-down collapse-indicator float-left" role="presentation" aria-hidden="true"></span>
                    {str tag=showintroduction section=group}
                </a>
            </div>
            <div class="introduction detail text-small">
                <div class="collapse" id="userintro{$user->id}">
                    {$user->introduction|safe}
                </div>
            </div>
            {/if}
        </div>
    </div>
</div>
{/foreach}
{else}
    <div class="message">{str tag=nopeopleonlinefound section=mahara}</div>
{/if}
