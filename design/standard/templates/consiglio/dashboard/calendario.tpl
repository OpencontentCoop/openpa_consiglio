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
<div class="row dashboard">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-body">
                {if count($next_items)|gt(0)}
                {foreach $next_items as $post}
                    {include uri=concat( 'design:consiglio/dashboard/calendario/', $post.object.class_identifier, '.tpl' )}                    
                {/foreach}
                {else}
                    <em>Nessuna seduta in programma</em>
                {/if}
            </div>
        </div>
    </div>
</div>


