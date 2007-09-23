{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <h2>{str tag="usersearch" section="admin"}</h2>
    <table id="initials"><tbody>
     <tr id="firstnamelist">
      <td>{str tag="firstname"}:</td>
      <td>
       <span class="first-initial{if empty($search->f)} selected{/if} all">
        <a href="?{if $search->l}l={$search->l}{/if}">{str tag="all"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="first-initial{if $a == $search->f} selected{/if}">
        <a href="?f={$a}{if $search->l}&amp;l={$search->l}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
     <tr id="lastnamelist">
      <td>{str tag="lastname"}:</td>
      <td>
       <span class="last-initial{if empty($search->l)} selected{/if} all">
        <a href="?{if $search->f}f={$search->f}{/if}">{str tag="all"}</a>
       </span>
       {foreach from=$alphabet item=a}
       <span class="last-initial{if $a == $search->l} selected{/if}">
        <a href="?l={$a}{if $search->f}&amp;f={$search->f}{/if}">{$a}</a>
       </span>
       {/foreach}
      </td>
     </tr>
    </tbody></table>
    <form action="" method="post">
        <div class="searchform">
            <label>Query: 
                <input type="text" name="query" id="query">
                <button id="query-button" type="submit">{str tag="go"}</button>
            </label>
        </div>
        <div id="results">
            {$results}
        </div>
    </form>

{include file="columnfullend.tpl"}
{include file="footer.tpl"}

