{include file="header.tpl"}
<span id="top"></span>

<p>{$description}</p>

<ul id="category-tabs" class="nav nav-tabs">
</ul>

{*
    examples go here,
    each one should be formatted like so:
<section data-markdown data-category="category-name-goes-here">
### Title of element
Description of element, this can include any markdown formatting, multiple paragraphs etc (optional).
```
<code for the element goes in between the triple backticks - there should only be one triple backtick part per section>
```
</section>

*}



<section data-markdown data-category="buttons">
### Add button
This button has padding on the right of the icon due to the plus class.
```
<button class="btn-default button btn">
    <span class="icon icon-plus icon-lg left" role="presentation"></span>
    Create page
</button>
```
</section>



<section data-markdown data-category="buttons">
### Pagination
Has forwards and back buttons.
```
<ul class="pagination pagination-xs">
    <li class=""><span>«<span class="sr-only">Previous page</span></span></li>
    <li class="active"><span>1</span></li>
    <li class=""><a title="" href="link">2</a></li>
    <li class=""><a title="Next page" href="link"> »<span class="sr-only">Next page</span></a></li>
</ul>
```
</section>


<section data-markdown data-category="panels">
### Panel
A basic panel.
```
<div class="panel panel-default">
    <h3 class="panel-heading has-link">
        <a href="#">Tags <span class="icon icon-arrow-right pull-right" role="presentation"></span></a>
    </h3>
    <div class="tagblock panel-body">
        <a title="1 item" href="#" class="tag">llamas</a> &nbsp;
        <a title="1 item" href="#" class="tag">pineapple</a> &nbsp;
    </div>
</div>
```
</section>


<section data-markdown data-category="icons">
### Copy to clipboard
As used on the secret URLs page.
```
<i class="icon icon-files-o" role="presentation"></i>
```
</section>



{*
    end of examples
*}

<div id="scroll-to-top" class="container">
    <a href="#top" class="btn btn-primary">{$scrollup}</a>
</div>

<script type="text/javascript" src="https://cdn.rawgit.com/chjj/marked/v0.3.5/marked.min.js"></script>
<script src="https://cdn.rawgit.com/zenorocha/clipboard.js/v1.5.1/dist/clipboard.min.js"></script>
<script type="text/javascript">
    // using inline js here because it's so specific to the use case of the style guide
    // this is all done on the client side and would be to inefficient for anything other than the styleguide

    var categories = [];

    (function styleguide(){

      [].forEach.call( document.querySelectorAll('[data-markdown]'), function  fn(elem, i){

        // modified from https://gist.github.com/paulirish/1343518
        // strip leading whitespace so it isn't evaluated as code
        var text      = elem.innerHTML.replace(/\n\s*\n/g,'\n'),
            // set indentation level so your markdown can be indented within your HTML
            leadingws = text.match(/^\n?(\s*)/)[1].length,
            regex     = new RegExp('\\n?\\s{' + leadingws + '}','g'),
            md        = text.replace(regex,'\n'),
            html      = marked(md);

        elem.innerHTML = html;

        // add in the example code using jQuery
        var codeElem = $j(elem).find('code');
        var code = $j.parseHTML(codeElem.text());
        codeElem.parent().before(code);
        codeElem.attr('id', 'code-block-' + i);

        // add copy button
        codeElem.before('<button class="copy" role="presentation" data-clipboard-target="#code-block-' + i + '" title="{$copy}"><i class="icon icon-files-o"></i></button>');

        // add the category to the sections index
        var category = $j(elem).data('category');

        if ($j.inArray(category, categories) === -1) {
            categories.push(category);
        }

        // hide this section if it isn't part of the first category in the array
        if (category !== categories[0]) {
            $j(elem).hide();
        }
      });

      // init copy to clipboard buttons
      new Clipboard('.copy');

      // build section tabs
      $j.each(categories, function(i, category) {
          var readableName = category.replace("-", " ");
          if (i === 0) {
              $j('#category-tabs').append('<li class="active"><a href="#" data-category="' + category + '">' + readableName + '</a></li>');
          } else {
              $j('#category-tabs').append('<li><a href="#" data-category="' + category + '">' + readableName + '</a></li>');
          }
      });

      // handle tab click
      $j('#category-tabs a').click(function(event) {
          var category = $j(this).data('category');
          event.preventDefault();
          $j(this).parent().siblings().removeClass('active');
          $j(this).parent().addClass('active');

          $j('[data-markdown]').each(function(){
              if ($j(this).data('category') !== category) {
                  $j(this).hide();
              } else {
                  $j(this).show();
              }
          });

      });

      // prevent example clicks going elsewhere
      $j('[data-markdown] a').click(function(event) {
          event.preventDefault();
      });

      // scroll to top button position
      $j(window).scroll(function() {
          var scroll = $j(window).scrollTop();
          if (scroll < 100) {
              $j('#scroll-to-top').removeClass('fixed');
          } else {
              $j('#scroll-to-top').addClass('fixed');
          }
      });

      $j('#scroll-to-top a').click(function(event) {
          event.preventDefault();
          $j(window).scrollTop(0);
      });

    }());

</script>




{include file="footer.tpl"}
