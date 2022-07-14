                       <div id="lastnamelist" class="col-md-4 userserach-filter">
                           <span class="pseudolabel" id="lastname">{str tag="lastname"}:</span>
                           <br/>
                           <a class="badge last-initial{if !$search->l} bg-primary active{else} bg-default{/if} all" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{str tag="All"}</a>

                          {foreach from=$alphabet item=a}
                           <a class="badge last-initial{if $a == $search->l} bg-primary active{else} bg-default{/if}" aria-describedby="lastname" href="{$WWWROOT}admin/users/search.php?query={$search->query}&amp;l={$a}{if $search->f}&amp;f={$search->f}{/if}{if $search->sortby}&amp;sortby={$search->sortby}{/if}{if $search->sortdir}&amp;sortdir={$search->sortdir}{/if}{if $limit}&amp;limit={$limit}{/if}">{$a}</a>
                          {/foreach}
                       </div>
