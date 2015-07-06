<?php /* #?ini charset="utf-8"?

[AvailableFactories]
Identifiers[]=seduta
Identifiers[]=punto
Identifiers[]=allegati_seduta
Identifiers[]=invitato
Identifiers[]=invito

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
CreationRepositoryNode=1233
CreationButtonText=Crea nuovo allegato
RepositoryNodes[]
RepositoryNodes[]=1233
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