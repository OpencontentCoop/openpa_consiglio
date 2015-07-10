<?php /* #?ini charset="utf-8"?

[AvailableFactories]
Identifiers[]=seduta
Identifiers[]=punto
Identifiers[]=allegati_seduta
Identifiers[]=invitato
Identifiers[]=invito
Identifiers[]=politico

[seduta]
ClassName=SedutaFactory
ClassIdentifier=seduta
CreationRepositoryNode=2
CreationButtonText=Crea nuova seduta
RepositoryNodes[]
RepositoryNodes[]=1
AttributeIdentifiers[]
StateGroup=seduta
States[draft]=Non visibile
States[pending]=Non confermata
States[published]=Confermata
States[in_progress]=In corso
States[closed]=Conclusa
Name=Elenco sedute

[punto]
ClassName=PuntoFactory
ClassIdentifier=punto
CreationButtonText=Crea nuovo punto
AttributeIdentifiers[]
StateGroup=punto
States[draft]=Bozza
States[published]=Pubblicato

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

[invitato]
ClassIdentifier=invitato
CreationButtonText=Crea nuovo invitato
AttributeIdentifiers[]
CreationRepositoryNode=1183
RepositoryNodes[]
RepositoryNodes[]=1183
Name=Invitati alle sedute

[invito]
ClassIdentifier=invito
ClassName=InvitoFactory
AttributeIdentifiers[]
RepositoryNodes[]
RepositoryNodes[]=1
Name=Inviti a sedute

[politico]
ClassIdentifier=politico
CreationButtonText=Crea nuovo politico
CreationRepositoryNode=5
RepositoryNodes[]=1