{include file="header.tpl"}
{$form|safe}
{if $groups}<div id="findgroups" class="fullwidth listing">
{foreach from=$groups item=group}
            <div class="listrow {cycle values='r0,r1'}">
                     {include file="group/group.tpl" group=$group returnto='mygroups'}
            <div class="cb"></div>
            </div>
{/foreach}
			</div>
{$pagination|safe}
{else}
            <div class="message">{str tag="nogroupsfound" section="group"}</div>
{/if}
{include file="footer.tpl"}
