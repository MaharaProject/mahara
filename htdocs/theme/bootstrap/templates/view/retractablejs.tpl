$j('#blockinstance_{$id} .retractor').click(function (e) {literal}{{/literal}
    var $this = $j('#blockinstance_{$id} > .blockinstance-header');
    if ($this.next().hasClass('js-hidden')) {
        $this.next().slideUp(0);
        $this.next().removeClass('js-hidden');
    }
    if ($this.hasClass('retracted')) {literal}{{/literal}
        $this.removeClass('retracted');
        $this.next().slideDown('fast');
    {literal}}{/literal}
    else {literal}{{/literal}
        $this.addClass('retracted');
        $this.next().slideUp('fast');
    {literal}}{/literal}
    e.preventDefault();
{literal}}{/literal});
addLoadEvent(function() {literal}{{/literal}
    var $content = $j('#blockinstance_{$id} > .blockinstance-content');
    if (!$content.hasClass('js-hidden')) {literal}{{/literal}
        return;
    {literal}}{/literal}
    $content.slideUp(0);
    $content.removeClass('js-hidden');
{literal}}{/literal});
