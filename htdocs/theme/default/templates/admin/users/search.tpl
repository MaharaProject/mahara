{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <h2>{str tag="usersearch" section="admin"}</h2>
    <p>{str tag="usersearchinstructions" section="admin"}</p>
    <table id="initials"><tbody>
     <tr id="firstnamelist">
      <td class="initial-label">{str tag="firstname"}:</td>
      <td class="initial-letters">
       <span class="first-initial{if empty($search->f)} selected{/if} all">
        <a href="{$WWWROOT}admin/users/search.php{if $search->l}?l={$search->l}{/if}">{str tag="all"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="first-initial{if $a == $search->f} selected{/if}">
        <a href="{$WWWROOT}admin/users/search.php?f={$a}{if $search->l}&amp;l={$search->l}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
     <tr id="lastnamelist">
      <td class="initial-label">{str tag="lastname"}:</td>
      <td class="initial-letters">
       <span class="last-initial{if empty($search->l)} selected{/if} all">
        <a href="{$WWWROOT}admin/users/search.php{if $search->f}?f={$search->f}{/if}">{str tag="all"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="last-initial{if $a == $search->l} selected{/if}">
        <a href="{$WWWROOT}admin/users/search.php?l={$a}{if $search->f}&amp;f={$search->f}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
    </tbody></table>
    <form action="{$WWWROOT}admin/users/search.php" method="post">
        <div class="searchform">
            <label>Query: 
                <input type="text" name="query" id="query"{if !empty($search->query)} value="{$search->query}"{/if}>
            </label>
            {if $USER->get('admin') && count($institutions) > 1}
            <span class="institutions">
                <label>Institution: 
                    <select name="institution" id="institution">
                        <option value="all" selected>{str tag=all}</option>
                        {foreach from=$institutions item=i}
                        <option value="{$i->name}">{$i->displayname}</option>
                        {/foreach}
                    </select>
                </label>
            </span>
            {/if}
            <button id="query-button" type="submit">{str tag="go"}</button>
        </div>
        <div id="results">
            {$results}
        </div>
    </form>

{include file="columnfullend.tpl"}
{include file="footer.tpl"}

