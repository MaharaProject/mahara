{include file="header.tpl"}

{include file="view/editviewtabs.tpl" selected='layout' new=$new issiteview=$issiteview}

{$form|safe}


{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
