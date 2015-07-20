/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
jQuery(function($) {
"use strict";

    /*
     * Make sure an affix is always the width of its container element
     */
    function affixSize(){
        var affix = $('[data-spy="affix"]'),
            affixwidth,
            affixheight,
            i;

        for(i = 0; i < affix.length; i = i + 1) {
            affixwidth = $(affix[i]).parent().width();
            affixheight = $(affix[i]).height() + 100;

           $(affix[i]).width(affixwidth);

           // prevents short pages from bouncing the user back to the top
           $(affix[i]).parent().height(affixheight);

        }

        $(window).on('resize colresize', function(){
            var i;

            for(i = 0; i < affix.length; i = i + 1) {
                affixwidth = $(affix[i]).parent().width();
                affixheight = $(affix[i]).height() + 100;

                $(affix[i]).width(affixwidth);

                // prevents short pages from bouncing the user back to the top
                $(affix[i]).parent().height(affixheight);
            }
        });
    }

    /*
     * We need to know the number of site message in ordder to adjust positioning
     */
    function siteMessages() {
        var message;

        // Remove extra padding when there are no site message
        if ($('.site-messages').length === 0) {
            $('.header').addClass('no-site-messages');
            $('.main-nav').addClass('no-site-messages');
        } else if ($('.site-messages') !== undefined) {
            message = $('.site-messages').find('.site-message');
            $('.header').addClass('message-count-'+ message.length);
        }
    }

    /*
     * Clear form when a form is collapsed
     */
    function resetOnCollapse() {

        $('[data-action="reset-on-collapse"]').on('hidden.bs.collapse', function () {
            var i,
                forms =$(this).find('form');
            for (i = 0; i < forms.length; i = i + 1){
                forms[i].reset();
            }
        });
    }

    function attachTooltip() {
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            placement: 'right',
            viewport: 'body'
        });
    }

    /*
     * Calculate carousel(image gallery) height
     */
    function carouselHeight() {
        var carousel = $('.carousel'),
            i, j,
            image,
            height;

        carousel.removeClass('carousel-ready');

        for (i = 0; i < carousel.length; i = i + 1) {

            $(carousel[i]).find('.item').addClass('inline');

            height = 0;
            image = $(carousel[i]).find('.item img');

            for (j = 0; j < image.length; j = j + 1){
                if($(image[j]).height() > height){
                    height = $(image[j]).height();
                }
            }

            $(carousel[i]).find('.item').removeClass('inline');

            $(carousel[i]).height(height);
            $(carousel[i]).addClass('carousel-ready');
        }
    }

    function handleInputDropdown(context) {
        var val = context.find('select').find('option:selected').text();


        context.find('.js-with-dropdown input').attr('placeholder', val);
        if(context.find('.js-dropdown-context').length > 0){
            context.find('.js-dropdown-context').html('(' + val + ')');
        } else {
            context.find('.js-with-dropdown label').append('<em class="js-dropdown-context metadata">('+ val + ')</em>');
        }
    }

    function attachInputDropdown() {

        var context = $('.js-dropdown-group');

        handleInputDropdown(context);

        $('.js-dropdown-group select').on('change', function(){
            var context = $(this).closest('.js-dropdown-group');
            handleInputDropdown(context);
        });
    }

    $(window).on('resize colresize', function(){
        carouselHeight();
    });

    $('.modal-docked-right').on('click',function(e) {
        var dialog = $('.modal-dialog'),
            dialogParent = $(e.target).closest('.modal-dialog').length;

        if(e.target !== dialog && !dialogParent){
            $(this).find('button.close').trigger('click');
        }
    });

    carouselHeight();
    affixSize();
    siteMessages();
    resetOnCollapse();
    attachTooltip();

    if ($('.js-dropdown-group').length > 0){
        attachInputDropdown();
    }

    $(".js-select2 select").select2({});

});
