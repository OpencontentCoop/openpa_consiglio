<div class="panel-body" style="background: #fff">

    <div id="presenze-seduta" data-url="{concat('openpa/data/timeline_presenze_seduta?seduta=',$post.object.id)|ezurl(no)}">
    {foreach $post.partecipanti as $politico}
        <div class="row" style="margin-top:10px; padding-bottom: 10px; border-bottom: 1px solid #ccc">
            <div class="col-md-3">
                <p>
                    <a href="#{$politico.object.id}" data-url="{concat('layout/set/modal/consiglio/presenze/',$post.object.id, '/',$politico.object.id)|ezurl(no)}" data-toggle="modal" data-target="#detailPresenze">
                        {$politico.object.name|wash()}
                    </a>
                </p>
                {if $post.current_state.identifier|eq( 'closed' )}
                    {if $politico.percentuale_presenza[$post.object.id]|gt(25)}
                        <form action="{concat('editorialstuff/action/seduta/', $post.object_id)|ezurl(no)}" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="ActionIdentifier" value="GetAttestatoPresenza"/>
                            <input type="hidden" name="ActionParameters[presente]" value="{$politico.object_id}"/>
                            <button class="btn btn-success btn-xs" type="submit" name="GetAttestatoPresenza"><i class="fa fa-download"></i> Attestato</button>
                        </form>
                    {/if}
                {/if}
            </div>
            <div class="col-md-9">
                <div class="timeline" data-userID="{$politico.object.id}"></div>
            </div>
        </div>
    {/foreach}
    </div>

</div>

<script src="{'javascript/socket.io-1.3.5.js'|ezdesign(no)}"></script>
{ezscript_require( array( 'ezjsc::jquery', 'timeline_presenze.js' ) )}
<script type="application/javascript">
    var SocketUrl = "{openpaini('OpenPAConsiglio','SocketUrl','cal')}";
    var SocketPort = "{openpaini('OpenPAConsiglio','SocketPort','8090')}";
    var CurrentSedutaId = {$post.object_id};
    {literal}
    $(document).ready(function(){
        var timeline = $("#presenze-seduta").timelinePresenze().data( 'timeline_presenze' );
        //timeline.add( {foo:'bar'} );
        $('#detailPresenze').on('show.bs.modal', function (event) {
            var url = $(event.relatedTarget).data('url');
            $(this).find('.modal-content').load(url);
        }).on('hide.bs.modal', function (event) {
            $(this).find('.modal-content').html('<em>Caricamento...</em>');
        });
{/literal}{if or( $post.current_state.identifier|eq( 'sent' ), $post.current_state.identifier|eq( 'in_progress' ) )}{literal}
        var socket = io(SocketUrl+':'+SocketPort);
        socket.on('presenze', function (data) {
            if (data.seduta_id == CurrentSedutaId) {
                timeline.add({
                    CreatedTime: data.timestamp,
                    ID: data.id,
                    IsIn: data.is_in,
                    InOut: data.in_out,
                    SedutaID: data.seduta_id,
                    Type: data.type,
                    UserID: data.user_id
                });
            }
        });
        socket.on('start_seduta', function (data) {
            if (data.id == CurrentSedutaId) {
                timeline.refresh();
            }
        });
        socket.on('stop_seduta', function (data) {
            if (data.id == CurrentSedutaId) {
                timeline.refresh();
            }
        });
{/literal}{/if}{literal}
    });
    {/literal}
</script>
<style>
    .tooltip,.modal, #navigation {ldelim}z-index:11000{rdelim}
    .modal-backdrop{ldelim}z-index:10001{rdelim}
</style>


<div id="detailPresenze" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"><em>Caricamento...</em></div>
    </div>
</div>