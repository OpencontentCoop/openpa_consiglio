<?php

class Proposta extends OpenPAConsiglioDefaultPost implements OCEditorialStuffPostInputActionInterface, OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;

    private $punti;

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'punti';        

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'punti') {
            return $this->getPunti();
        }

        return parent::attribute($property);
    }

    public function getPunti()
    {
    	if ($this->punti == null) {
            $this->punti = array();
            $reverseObjects = $this->getObject()->reverseRelatedObjectList(
                false,
                0,
                false,
                array('AllRelations' => true, 'AsObject' => false)
            );
            $puntoFactory = OCEditorialStuffHandler::instance('punto')->getFactory();            
            foreach ($reverseObjects as $reverseObject) {
                if ($reverseObject['contentclass_identifier'] == $puntoFactory->classIdentifier()) {
                    try {
                        $this->punti[] = $puntoFactory->instancePost(array('object_id' => $reverseObject['id']));
                    } catch (Exception $e) {

                    }
                }
            }            
        }

        return $this->punti;
    }

    public function executeAction($actionIdentifier, $actionParameters, eZModule $module = null)
    {
        $http = eZHTTPTool::instance();

        if ($actionIdentifier == 'AddToPunto') {
            
            $sedutaFactory = OCEditorialStuffHandler::instance('seduta')->getFactory();
            $puntoFactory = OCEditorialStuffHandler::instance('punto')->getFactory();            

            if ($http->hasPostVariable( 'BrowseCancelButton' )){
				
				return $module->redirectTo('/editorialstuff/edit/proposta/' . $this->id());
            
            }elseif ($http->hasPostVariable('BrowseActionName') && $http->postVariable('BrowseActionName') == 'SelectSeduta' ){
            	
            	$selectSedutaId = $http->postVariable('SelectedObjectIDArray')[0];
            	$seduta = $sedutaFactory->instancePost(array('object_id' => $selectSedutaId));
            	if ($seduta instanceof Seduta){
            		
            		if($seduta->is('draft') || $seduta->is('pending') || $seduta->is('published')){
	            		$languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
					    $object = eZContentObject::createWithNodeAssignment( 
					    	$seduta->getObject()->mainNode(),
					        eZContentClass::classIDByIdentifier($puntoFactory->classIdentifier()),
					        $languageCode,
					        false 
					    );
					    $dataMap = $object->dataMap();
					    $dataMap['oggetto']->fromString($this->stringAttribute('oggetto'));
					    $dataMap['oggetto']->store();
					    $dataMap['materia']->fromString($this->stringAttribute('materia'));
					    $dataMap['materia']->store();
					    $dataMap['proposte']->fromString($this->id());				    
					    $dataMap['proposte']->store();

					    $http->setSessionVariable('RedirectURIAfterPublish', '/editorialstuff/edit/proposta/' . $this->id());
					    $http->setSessionVariable('RedirectIfDiscarded', '/editorialstuff/edit/proposta/' . $this->id());
				        return $module->redirectTo('content/edit/' . $object->attribute('id') . '/' . $object->attribute('current_version'));
				    }else{
				    	$errorDetail = "Lo stato corrente è " . $seduta->currentState()->attribute('current_translation')->attribute('name');
				    }
            	}else{
            		$errorDetail = "Seduta non trovata";
            	}

            	return $module->redirectTo('/editorialstuff/edit/proposta/' . $this->id() . '/(error)/invalid_selected_seduta/(error_detail)/' . $errorDetail);
			
			}else{				
				eZContentBrowse::browse(
			        array(
			            'action_name' => 'SelectSeduta',
			            'return_type' => 'ObjectID',
			            'from_page' => '/editorialstuff/action/proposta/' . $this->id(),
			            'class_array' => array($sedutaFactory->classIdentifier()),
			            'start_node' => $sedutaFactory->creationRepositoryNode(),
			            'cancel_page' => '/editorialstuff/edit/proposta/' . $this->id(),
			            'persistent_data' => array(
			            	'ActionIdentifier' => 'AddToPunto',
			            	'AddToPunto' => true			            	
			            )
			        ),
			        $module
			    );
			}
        }
    }
}