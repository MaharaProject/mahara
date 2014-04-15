{if $LOGGEDIN}
  <a id="objection_link" class="objection" href="">{str tag=reportobjectionablematerial}</a>
{/if}
<a id="print_link" class="print" href="" onclick="window.print(); return false;">{str tag=print section=view}</a>
{if $LOGGEDIN}
  <a id="toggle_watchlist_link" class="watchlist" href="">{if $viewbeingwatched}{str tag=removefromwatchlist section=view}{else}{str tag=addtowatchlist section=view}{/if}</a>
  {contextualhelp plugintype='core' pluginname='view' section='viewmenu'}
{/if}

