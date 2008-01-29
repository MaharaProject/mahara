{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <h2>{str tag="usersearch" section="admin"}</h2>
    <p>{str tag="usersearchinstructions" section="admin"}</p>
    <table id="initials"><tbody>
     <tr id="firstnamelist">
      <td class="initial-label">{str tag="firstname"}:</td>
      <td class="initial-letters">
       <span class="first-initial{if empty($search->f)} selected{/if} all">
        <a href="{$WWWROOT}admin/users/search.php{if $search->l}?l={$search->l|escape}{/if}">{str tag="All"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="first-initial{if $a == $search->f} selected{/if}">
        <a href="{$WWWROOT}admin/users/search.php?f={$a}{if $search->l}&amp;l={$search->l|escape}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
     <tr id="lastnamelist">
      <td class="initial-label">{str tag="lastname"}:</td>
      <td class="initial-letters">
       <span class="last-initial{if empty($search->l)} selected{/if} all">
        <a href="{$WWWROOT}admin/users/search.php{if $search->f}?f={$search->f|escape}{/if}">{str tag="All"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="last-initial{if $a == $search->l} selected{/if}">
        <a href="{$WWWROOT}admin/users/search.php?l={$a}{if $search->f}&amp;f={$search->f|escape}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
    </tbody></table>
    <form action="{$WWWROOT}admin/users/search.php" method="post">
        <div class="searchform">
            <label>{str tag='Query' section='admin'}:
                <input type="text" name="query" id="query"{if !empty($search->query)} value="{$search->query|escape}"{/if}>
            </label>
            {if count($institutions) > 1}
            <span class="institutions">
                <label>{str tag='Institution' section='admin'}:
                    {if $USER->get('admin')}
                    <select name="institution" id="institution">
                    {else}
                    <select name="institution_requested" id="institution_requested">
                    {/if}
                        <option value="all"{if !$smarty.request.institution} selected="selected"{/if}>{str tag=All}</option>
                        {foreach from=$institutions item=i}
                        <option value="{$i->name|escape}"{if $i->name == $smarty.request.institution}" selected="selected"{/if}>{$i->displayname|escape}</option>
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

