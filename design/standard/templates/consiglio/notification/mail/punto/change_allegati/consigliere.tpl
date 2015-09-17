{set-block scope=root variable=subject}[{$punto.seduta.object.name|wash()}] Allegato al punto all'ordine del giorno{/set-block}

Gentile {$user.contentobject.name|wash()},<br />
{include uri='design:consiglio/notification/common/punto/descrizione.tpl'}

{include uri='design:consiglio/notification/common/punto/referenti.tpl'}

{include uri='design:consiglio/notification/common/punto/termini.tpl'}

{include uri='design:consiglio/notification/common/punto/change_allegati.tpl'}

{include uri='design:consiglio/notification/common/punto/info.tpl'}
