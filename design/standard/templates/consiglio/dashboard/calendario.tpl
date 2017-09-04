{ezscript_require(array('ezjsc::jquery','jquery.editorialstuff_default.js'))}

<style>{literal}
    .events .calendar-date .month {
        background: #d9534f;
        font-size: 1.1em;
        line-height: 1;
        color: #fff;
        display: block;
        text-align: center;
        padding: 5px 3px;
        text-transform: uppercase;
    }

    .events .calendar-date .day {
        background: #fff;
        color: $ brand-primary;
        font-size: 2em;
        font-weight: bold;
        display: block;
        border: 1px solid #d9534f;
        padding: 8px 3px;
        text-align: center;
    }

{/literal}</style>

{def $next_items = fetch(consiglio, next_items)}
{foreach $next_items as $post}
    {include uri=concat( 'design:consiglio/dashboard/calendario/', $post.object.class_identifier, '.tpl' )}
    {undef $post}
{/foreach}


