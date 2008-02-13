<div id="column-full">
    <div class="content">
        <div class="box-cnrs"><div class="cnr-tl"><div class="cnr-tr"><div class="cnr-bl"><div class="cnr-br">
            <div class="maincontent">
                {if $PAGEHELPNAME && $heading && $noheadingescape} <h2>{$heading}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span></h2>
                {elseif $PAGEHELPNAME && $heading} <h2>{$heading|escape}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span></h2>{/if}
