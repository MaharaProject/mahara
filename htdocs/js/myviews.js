$j = jQuery;
$j(function() {
    $j('.expand').click(function(e) {
        e.preventDefault();
        $j('#' + this.id + '-expand').toggleClass('js-hidden');
    });
});