<?php /* #?ini charset="utf-8"?

[AvailableFactories]
Identifiers[]=seduta
Identifiers[]=punto
Identifiers[]=allegati_seduta
Identifiers[]=invitato
Identifiers[]=invito
Identifiers[]=politico
Identifiers[]=tecnico
Identifiers[]=audizione
Identifiers[]=materia
Identifiers[]=convocazione_seduta
Identifiers[]=votazione
Identifiers[]=osservazioni

[Settings]
DefaultFactoryClassName=OpenPAConsiglioDefaultFactory

[seduta]
ClassName=SedutaFactory
ClassIdentifier=seduta
CreationRepositoryNode=1198
CreationButtonText=Crea nuova seduta
RepositoryNodes[]
RepositoryNodes[]=1198
AttributeIdentifiers[]
StateGroup=seduta
States[draft]=Non visibile
States[pending]=Non confermata
States[published]=Confermata
States[sent]=Invio convocazione
States[in_progress]=In corso
States[closed]=Conclusa
Name=Elenco sedute
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[punto]
ClassName=PuntoFactory
ClassIdentifier=punto
CreationButtonText=Crea nuovo punto
AttributeIdentifiers[]
StateGroup=punto
States[draft]=Bozza
States[published]=Pubblicato
States[in_progress]=In corso
States[closed]=Concluso
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl
Name=Elenco punti

[audizione]
ClassName=AudizioneFactory
ClassIdentifier=audizione
CreationButtonText=Crea nuova audizione
CreationRepositoryNode=1200
RepositoryNodes[]
RepositoryNodes[]=1200
AttributeIdentifiers[]
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl
Name=Elenco audizioni
StateGroup=audizione
States[draft]=Non visibile
States[pending]=Non confermata
States[published]=Confermata


[allegati_seduta]
Name=Allegati alle sedute
ClassName=AllegatoFactory
ClassIdentifier=allegato_seduta
CreationRepositoryNode=1213
CreationButtonText=Crea nuovo allegato
RepositoryNodes[]
RepositoryNodes[]=1213
StateGroup=visibilita_allegato_seduta
States[consiglieri]=Consiglieri
States[referenti]=Referenti all'argomento
AttributeIdentifiers[]
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[osservazioni]
Name=Osservazioni ai punti delle sedute
ClassName=OsservazioneFactory
ClassIdentifier=osservazione
CreationRepositoryNode=1207
CreationButtonText=Crea nuova osservazione
RepositoryNodes[]
RepositoryNodes[]=1207
StateGroup=visibilita_osservazione_seduta
States[consiglieri]=Consiglieri
States[referenti]=Referenti all'argomento
AttributeIdentifiers[]
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl


[convocazione_seduta]
Name=Convocazioni seduta
ClassName=ConvocazioneSedutaFactory
ClassIdentifier=convocazione_seduta
RepositoryNodes[]
RepositoryNodes[]=1
AttributeIdentifiers[]
PersistentVariable[top_menu]=false


[invitato]
ClassIdentifier=invitato
ClassName=InvitatoFactory
CreationButtonText=Crea nuovo invitato
AttributeIdentifiers[]
CreationRepositoryNode=1183
RepositoryNodes[]
RepositoryNodes[]=1183
Name=Invitati alle sedute
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[invito]
ClassIdentifier=invito
ClassName=InvitoFactory
AttributeIdentifiers[]
RepositoryNodes[]
RepositoryNodes[]=1
Name=Inviti a sedute
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[politico]
ClassIdentifier=politico
ClassName=PoliticoFactory
CreationButtonText=Crea nuovo politico
CreationRepositoryNode=668
RepositoryNodes[]=668
RepositoryNodes[]=1129
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[tecnico]
ClassIdentifier=tecnico
ClassName=TecnicoFactory
CreationButtonText=Crea nuovo tecnico
CreationRepositoryNode=1168
RepositoryNodes[]=1168
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl

[materia]
ClassIdentifier=materia
CreationButtonText=Crea nuova materia
CreationRepositoryNode=1135
RepositoryNodes[]
RepositoryNodes[]=1135
AttributeIdentifiers[]
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl
Name=Elenco materie

[votazione]
ClassIdentifier=votazione
CreationButtonText=Crea votazione
ClassName=VotazioneFactory
CreationRepositoryNode=1262
RepositoryNodes[]=1262
RepositoryNodes[]=1316
StateGroup=stato_votazione
States[pending]=In attesa
States[in_progress]=In corso
States[closed]=Conclusa
PersistentVariable[top_menu]=true
PersistentVariable[topmenu_template_uri]=design:consiglio/page_topmenu.tpl
