<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
{literal}
<script>
    var CurrentSedutaId = {/literal}{$seduta.object_id}{literal};
    var BaseUrl = "{/literal}{concat('consiglio/data/seduta/',$seduta.object_id)|ezurl(no)}{literal}/";
    var socket = io('localhost:8000');
    socket.on('connect', function(){
    });
    socket.on('presenze',function(data){
        if ( data.seduta_id == CurrentSedutaId ){
            $('#current_text').html( data.id );
        }
    });
</script>
{/literal}
<div id="alert-area">
    {if count( $errors )}
        <div class="alert alert-danger">
            {foreach $errors as $error}
                <p>{$error|wash()}</p>
            {/foreach}
        </div>
    {/if}
</div>

<h1 id="current_text"></h1>
