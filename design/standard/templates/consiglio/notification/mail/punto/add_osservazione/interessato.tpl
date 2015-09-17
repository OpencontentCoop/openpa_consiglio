{set-block scope=root variable=subject}[{$punto.seduta.object.name|wash()}] Osservazione al punto all'ordine del giorno{/set-block}

Gentile {$user.contentobject.name|wash()},<br />
sulla base delle preferenze di notifica da Lei selezionate, {include uri='design:consiglio/notification/common/punto/descrizione.tpl'}

{include uri='design:consiglio/notification/common/punto/referenti.tpl'}

{include uri='design:consiglio/notification/common/punto/termini.tpl'}

{include uri='design:consiglio/notification/common/punto/add_osservazione.tpl'}

{include uri='design:consiglio/notification/common/punto/info.tpl'}