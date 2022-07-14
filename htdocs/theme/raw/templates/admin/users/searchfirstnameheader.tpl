                       <div id="firstnamelist" class="col-md-4 userserach-filter">
                           <span class="pseudolabel" id="firstname">{str tag="firstname"}:</span>
                           <br/>
                           <a class="badge first-initial{if !$search->f} bg-primary active{else} bg-default{/if} all" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                          {foreach from=$alphabet item=a}
                           <a class="badge first-initial{if $a == $search->f} bg-primary active{else} bg-default{/if}" aria-describedby="firstname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;f={$a}{if $search->l}&amp;l={$search->l}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                          {/foreach}
                       </div>
