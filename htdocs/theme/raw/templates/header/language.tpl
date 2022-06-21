{if $LANGCHOICES}
<nav aria-label="{str tag="language" section="mahara"}">
  <div id="main-language" class="nav collapse navbar-collapse nav-language" role="tabcard">
    <ul id="navadmin" class="nav navbar-nav">{strip}
      {foreach from=$LANGCHOICES key=key item=item name=lang}
      <li class="{if $LANGCURRENT == $key}active{/if}">
        <a href="{$WWWROOT}changelanguage.php?lang={$key}">{$item}</a>
      </li>
      {/foreach}{/strip}
    </ul>
  </div>
</nav>
{/if}
