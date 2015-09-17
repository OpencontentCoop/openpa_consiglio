{set-block scope=root variable=subject}[{$punto.seduta.object.name|wash()}] Aggiornamenti punto di suo interesse{/set-block}

Gentile {$user.contentobject.name|wash()},<br />
sulla base delle preferenze di notifica da Lei selezionate,, {include uri='design:consiglio/notification/common/punto/descrizione.tpl'}

{include uri='design:consiglio/notification/common/punto/referenti.tpl'}

{include uri='design:consiglio/notification/common/punto/termini.tpl'}

<!--ITEMS DATA-->

{include uri='design:consiglio/notification/common/punto/info.tpl'}