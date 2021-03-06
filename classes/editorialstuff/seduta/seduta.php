<?php

use Fusonic\SpreadsheetExport\Spreadsheet;
use Fusonic\SpreadsheetExport\ColumnTypes\TextColumn;
use Fusonic\SpreadsheetExport\Writers\OdsWriter;


class Seduta extends OCEditorialStuffPostNotifiable implements OCEditorialStuffPostFileContainerInterface, OCEditorialStuffPostInputActionInterface, OpenPAConsiglioStringAttributeInterface
{
    use OpenPAConsiglioStringAttributeTrait;
    use OpenPAConsiglioDiffTrait;
    use SolrFieldsTrait;

    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected static $partecipantiPerSeduta = array();

    protected $consiglieri;

    protected $percentualePresenza;

    protected $odg = array();

    public function __construct(
        array $data = array(),
        OCEditorialStuffPostFactoryInterface $factory
    ) {
        parent::__construct($data, $factory);
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'data_ora';
        $attributes[] = 'data_ora_fine';
        $attributes[] = 'referenti';
        $attributes[] = 'odg';
        $attributes[] = 'count_documenti';
        $attributes[] = 'documenti';
        $attributes[] = 'presenze';
        $attributes[] = 'partecipanti';
        $attributes[] = 'consiglieri';
        $attributes[] = 'registro_presenze';
        $attributes[] = 'votazioni';
        $attributes[] = 'verbale_object';
        $attributes[] = 'verbale';
        $attributes[] = 'verbale_fields';
        $attributes[] = 'can_edit_verbale';
        $attributes[] = 'protocollo';
        $attributes[] = 'current_punto';
        $attributes[] = 'percentuale_presenza';
        $attributes[] = 'competenza';
        $attributes[] = 'liquidata';
        $attributes[] = 'organo';
        $attributes[] = 'can_modify_odg';

        return $attributes;
    }

    public function attribute($property)
    {
        if ($property == 'data_ora') {
            return $this->dataOra();
        }

        if ($property == 'data_ora_fine') {
            return $this->dataOraFine();
        }

        if ($property == 'referenti') {
            return $this->referenti();
        }

        if ($property == 'odg') {
            return $this->odg();
        }

        if ($property == 'count_documenti') {
            return $this->getCount('documenti');
        }

        if ($property == 'documenti') {
            return $this->getAllegati('documenti');
        }

        if ($property == 'presenze') {
            return $this->presenze();
        }

        if ($property == 'partecipanti') {
            return $this->partecipanti();
        }

        if ($property == 'consiglieri') {
            return $this->consiglieri();
        }

        if ($property == 'registro_presenze') {
            return $this->registroPresenze();
        }

        if ($property == 'votazioni') {
            return $this->votazioni();
        }

        if ($property == 'verbale_object') {
            return $this->getVerbaleObject();
        }

        if ($property == 'verbale') {
            return $this->verbale();
        }

        if ($property == 'verbale_fields') {
            return $this->verbaleFields();
        }

        if ($property == 'can_edit_verbale') {
            return $this->canEditVerbale();
        }

        if ($property == 'protocollo') {
            return $this->protocollo();
        }

        if ($property == 'current_punto') {
            return $this->getPuntoInProgress();
        }

        if ($property == 'percentuale_presenza') {
            return $this->getPercentualePresenza();
        }

        if ($property == 'competenza') {
            $competenza = $this->getOrgano()->getObject()->attribute('name');

            return $competenza;
        }

        if ($property == 'liquidata') {
            return OpenPAConsiglioGettoniHelper::isSedutaLiquidata($this);
        }

        if ($property == 'organo') {
            return $this->getOrgano();
        }

        if ($property == 'can_modify_odg') {
            return $this->canModifyOdg();
        }        

        return parent::attribute($property);
    }

    protected function canModifyOdg()
    {
        return $this->getObject()->attribute('main_node')->canCreate() && $this->isAfter('sent');
    }

    /**
     * @return Organo
     */
    protected function getOrgano()
    {
        $organoIdList = $this->stringRelatedObjectAttribute('organo', 'id');
        if (isset($organoIdList[0]))
            return OCEditorialStuffHandler::instance('organo')->getFactory()->instancePost(array('object_id' => $organoIdList[0]));

        return false;
    }

    protected function getPercentualePresenza()
    {
        if ($this->percentualePresenza == null) {
            $helper = new OpenPAConsiglioPresenzaHelper($this);
            $values = $helper->getPercent();
            $this->percentualePresenza = $values;
        }

        return $this->percentualePresenza;
    }

    /**
     * @see SedutaFactory::fields()
     */
    public function indexFromTime()
    {
        return ezfSolrDocumentFieldBase::preProcessValue($this->dataOra(), 'date');
    }

    public function createUpdateConvocazione()
    {
        return ConvocazioneSeduta::create($this->getObject());
    }

    public function protocollo()
    {
        return $this->stringAttribute('protocollo', 'intval');
    }

    public function verbaleFields()
    {
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tpl = eZTemplate::factory();        

        $listOrgano = $this->dataMap['organo']->content();
        $organo = ( isset( $listOrgano['relation_list'][0]['contentobject_id'] ) ) ?
            eZContentObject::fetch($listOrgano['relation_list'][0]['contentobject_id'])->attribute('name') : '';

        $luogo = isset( $this->dataMap['luogo'] ) ? $this->dataMap['luogo']->content() : '';
        $dataOra = $this->dataOraEffettivaInizio();

        $punti = array();
        $locale = eZLocale::instance();
        foreach ($this->odg() as $punto) {
            if ($punto->is('published') || $punto->is('in_progress') || $punto->is('closed')){
                $puntoDataMap = $punto->getObject()->dataMap();

                /** @var eZDateTime $orarioTrattazione */
                $orarioTrattazione = $puntoDataMap['orario_trattazione']->content();

                $punti[$puntoDataMap['n_punto']->content()] = array(
                    'object_id' => $punto->object->ID,
                    'n_punto' => $puntoDataMap['n_punto']->content(),
                    'ora' => $locale->formatShortTime($orarioTrattazione->attribute( 'timestamp' )),
                    'oggetto' => $puntoDataMap['oggetto']->content()
                );
            }
        }

        $variables = array(
            'seduta' => $this,
            'organo' => $organo,
            'luogo' => $luogo,
            'data_seduta' => $dataOra,
            'punti' => $punti,
        );

        foreach ($variables as $name => $value) {
            $tpl->setVariable($name, $value);
        }

        $fields = array(
            'numero' => array(
                'name' => 'Numero',
                'type' => 'string',
                'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/numero.tpl"),
            ),
            'intro' => array(
                'name' => 'Introduzione',
                'type' => 'text',
                'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/intro.tpl"),
                'rows' => 5,
            ),
            'odg' => array(
                'name' => 'Ordine del giorno',
                'type' => 'text',
                'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/odg.tpl"),
                'rows' => 5,
            ),
            'partecipanti' => array(
                'name' => 'Partecipanti',
                'type' => 'text',
                'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/partecipanti.tpl"),
                'rows' => 5,
            ),
            'presidente' => array(
                'name' => 'Presidente',
                'type' => 'text',
                'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/presidente.tpl"),
                'rows' => 5,
            ),
        );
        foreach ($this->odg() as $punto) {
            if ($punto->is('published') || $punto->is('in_progress') || $punto->is('closed')){
                $propostaVerbale = '';
                $dataMapPunto = $punto->getObject()->dataMap();
                if(isset($dataMapPunto['verbale']) && $dataMapPunto['verbale']->hasContent()){
                    $propostaVerbale = $dataMapPunto['verbale']->content()->attribute('output')->attribute('output_text');
                }
                $fields[$punto->id()] = array(
                    'name' => $punto->getObject()->attribute('name'),
                    'type' => 'text',
                    'default_value' => $propostaVerbale,
                    'rows' => 5,
                );
            }
        }
        $fields['conclusione'] = array(
            'name' => 'Conclusione',
            'type' => 'text',
            'default_value' => $tpl->fetch("design:{$templatePath}/parts/verbale/conclusione.tpl"),
            'rows' => 5,
        );

        return $fields;
    }

    public function createVerbaleObject()
    {
        Verbale::create($this->getObject());
        return Verbale::get($this->getObject());
    }

    public function getVerbaleObject()
    {
        return Verbale::get($this->getObject());
    }

    public function canEditVerbale()
    {
        return $this->getObject()->attribute('can_edit') && !$this->getVerbaleObject() instanceof Verbale;
    }

    public function verbale($identifier = null)
    {
        $verbali = array();
        if (isset( $this->dataMap['verbale'] )) {            
            $data = $this->stringAttribute('verbale');
            if (empty( $data )) {
                $hash = array();
                foreach ($this->verbaleFields() as $field) {
                    $hash[$field['identifier']] = $field['default_value'];
                }                
                $this->saveVerbale($hash);
                $data = $this->stringAttribute('verbale');
            }
            $rows = explode('&', $data);
            foreach ($rows as $row) {
                $columns = explode('|', $row);
                $verbali[$columns[0]] = $this->cleanOutputVerbaleValue($columns[0], $columns[1]);
            }

            if ($identifier)
                return isset( $verbali[$identifier] ) ? $verbali[$identifier] : null;

            return $verbali;
        }
        eZDebug::writeError("Attribute verbale not found", __METHOD__);

        return null;
    }

    private function cleanInputVerbaleValue($id, $text)
    {
        $text = html_entity_decode($text);
        $text = str_replace('&', '$$',$text);
        return $text;
    }

    private function cleanOutputVerbaleValue($id, $text)
    {        
        $text = str_replace('$$', '&',$text);
        return $text;
    }

    public function saveVerbale($hash)
    {
        $data = array();
        foreach ($hash as $id => $text) {
            $data[] = $id . '|' . $this->cleanInputVerbaleValue($id, $text);
        }
        $string = implode('&', $data);
        if (isset( $this->dataMap['verbale'] )) {
            $this->dataMap['verbale']->fromString($string);
            $this->dataMap['verbale']->store();
        } else {
            eZDebug::writeError("Attributo verbale non trovato", __METHOD__);
        }
    }

    public function reorderOdg()
    {
        foreach ($this->odg() as $index => $punto) {
            $number = $index + 1;
            $punto->setNumber($number);
        }

        $this->createUpdateConvocazione();

        //empty odg cache
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $hasAccess = $currentUser->hasAccessTo('consiglio', 'admin');
        $isAdmin = $hasAccess['accessWord'] != 'no';
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Informazioni',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            ),
            array(
                'identifier' => 'documenti',
                'name' => 'Documenti',
                'template_uri' => "design:{$templatePath}/parts/documenti.tpl"
            )
        );
        if ($isAdmin) {
            $tabs[] = array(
                'identifier' => 'presenze',
                'name' => 'Presenze',
                'template_uri' => "design:{$templatePath}/parts/presenze.tpl"
            );
        }
        if (OpenPAConsiglioSettings::instance()->enableVotazioniInCruscotto()) {
            $tabs[] = array(
                'identifier' => 'votazioni',
                'name' => 'Votazioni e esito',
                'template_uri' => "design:{$templatePath}/parts/votazioni.tpl",
                'async_template_uri' => 'parts:votazioni'
            );
        }

        if ($isAdmin) {
            $tabs[] = array(
                'identifier' => 'verbale',
                'name' => 'Verbale',
                'template_uri' => "design:{$templatePath}/parts/verbale.tpl",
                'async_template_uri' => 'parts:verbale'
            );
        }

        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl",
            'async_template_uri' => 'parts:history'
        );

        return $tabs;
    }

    public function addFile(eZContentObject $object, $attributeIdentifier)
    {
        $result = false;
        if ($attributeIdentifier == 'documenti') {
            if (isset( $this->dataMap[$attributeIdentifier] )) {
                $ids = explode('-', $this->dataMap[$attributeIdentifier]->toString());
                $ids[] = $object->attribute('id');
                $ids = array_unique($ids);
                $this->dataMap[$attributeIdentifier]->fromString(implode('-', $ids));
                $this->dataMap[$attributeIdentifier]->store();

                $this->assignSection($object);

                eZSearch::addObject($this->getObject());
                eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());

                OCEditorialStuffHistory::addHistoryToObjectId(
                    $this->id(),
                    'add_file',
                    array(
                        'object_id' => $object->attribute('id'),
                        'name' => $object->attribute('name'),
                        'attribute' => $attributeIdentifier
                    )
                );
                $result = true;
            }
        }

        return $result;
    }

    public function removeFile(eZContentObject $object, $attributeIdentifier)
    {
        if ($attributeIdentifier == 'documenti') {
            OCEditorialStuffHistory::addHistoryToObjectId(
                $this->id(),
                'remove_file',
                array(
                    'object_id' => $object->attribute('id'),
                    'name' => $object->attribute('name'),
                    'attribute' => $attributeIdentifier
                )
            );
        }
    }

    public function fileFactory($attributeIdentifier)
    {
        if ($attributeIdentifier == 'documenti') {
            return OCEditorialStuffHandler::instance('allegati_seduta')->getFactory();
        }
        throw new Exception("FileFactory for $attributeIdentifier not found");
    }

    public function onChangeState(
        eZContentObjectState $beforeState,
        eZContentObjectState $afterState
    ) {
        $this->setObjectLastModified();

        if ($beforeState->attribute('identifier') == 'pending'
            && $afterState->attribute('identifier') == 'published'
        ) {
            foreach ($this->odg() as $punto) {
                if ($punto->is('_public')) {
                    $punto->createNotificationEvent('publish');
                }
            }
        }

        if ($afterState->attribute('identifier') == 'sent'){                        
            $this->createNotificationEvent('send_convocation');
        }
    }

    /**
     * @param string $returnFormat
     *
     * @return DateTime|string
     */
    public function dataOra($returnFormat = 'U')
    {
        /** @var eZDate $data */
        $data = $this->dataMap['data']->content();
        /** @var eZTime $ora */
        $ora = $this->dataMap['orario']->content();

        $dateTime = $this->getDateTimeFromEzContents($data, $ora);

        if ($returnFormat) {
            return $dateTime->format($returnFormat);
        }

        return $dateTime;
    }

    /**
     * @param eZDate $data
     * @param eZTime $ora
     *
     * @return DateTime
     */
    protected function getDateTimeFromEzContents(eZDate $data, eZTime $ora)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($data->attribute('timestamp'));
        $dateTime->setTime($ora->attribute('hour'), $ora->attribute('minute'));

        return $dateTime;
    }

    public function dataOraEffettivaInizio($returnFormat = 'U')
    {
        if (isset( $this->dataMap['orario_inizio_effettivo'] ) && $this->dataMap['orario_inizio_effettivo']->hasContent()) {
            /** @var eZDate $data */
            $data = $this->dataMap['orario_inizio_effettivo']->content();

            $dateTime = new DateTime();
            $dateTime->setTimestamp($data->attribute('timestamp'));
            if ($returnFormat) {
                return $dateTime->format($returnFormat);
            }

            return $dateTime;
        } else {
            return $this->dataOra($returnFormat);
        }
    }

    /**
     * @param string $returnFormat
     *
     * @return DateTime|string
     */
    public function dataOraFine($returnFormat = 'U')
    {
        if ($this->is('closed') && isset( $this->dataMap['orario_conclusione_effettivo'] ) && $this->dataMap['orario_conclusione_effettivo']->hasContent()) {
            /** @var eZDate $data */
            $data = $this->dataMap['orario_conclusione_effettivo']->content();

            $dateTime = new DateTime();
            $dateTime->setTimestamp($data->attribute('timestamp'));
        } else {
            /** @var eZDate $data */
            $data = $this->dataMap['data']->content();
            /** @var eZTime $ora */
            if (isset( $this->dataMap['orario_conclusione'] )
                && $this->dataMap['orario_conclusione']->hasContent()
            ) {
                $ora = $this->dataMap['orario_conclusione']->content();

                $dateTime = new DateTime();
                $dateTime->setTimestamp($data->attribute('timestamp'));
                $dateTime->setTime($ora->attribute('hour'), $ora->attribute('minute'));
            } else {
                $dateTime = new DateTime();
                $dateTime->setTimestamp($data->attribute('timestamp'));
                $dateTime->setTime(20, 0);
            }
        }

        if ($returnFormat) {
            return $dateTime->format($returnFormat);
        }

        return $dateTime;
    }

    public function referenti()
    {
        //@todo
        return array();
    }

    /**
     * @return Punto[]
     */
    public function odg()
    {
        if (empty( $this->odg )) {
            $factory = OCEditorialStuffHandler::instance('punto', array('seduta' => $this->id()))->getFactory();
            $attributeID = eZContentObjectTreeNode::classAttributeIDByIdentifier('punto/seduta_di_riferimento');
            $params = array(
                'AllRelations' => eZContentFunctionCollection::contentobjectRelationTypeMask(
                    array('attribute')
                ),
                'AsObject' => true
            );
            /** @var eZContentObject[] $reverseObjects */
            $reverseObjects = $this->getObject()->reverseRelatedObjectList(
                false,
                $attributeID,
                false,
                $params
            );
            $items = array();
            foreach ($reverseObjects as $object) {
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $object->attribute('data_map');
                $orario = $dataMap['orario_trattazione']->content();
                if ($orario instanceof eZTime) {
                    $timestamp = $orario->attribute('time_of_day');
                    $items[$timestamp][] = new Punto(
                        array('object_id' => $object->attribute('id')),
                        $factory
                    );
                }
            }

            ksort($items);
            $this->odg = array();
            foreach ($items as $i) {
                $this->odg = array_merge($this->odg, $i);
            }
        }

        return $this->odg;
    }

    /**
     * @return string[]
     */
    public function odgTimes()
    {
        $attributeID = eZContentObjectTreeNode::classAttributeIDByIdentifier(
            'punto/seduta_di_riferimento'
        );
        $params = array(
            'AllRelations' => eZContentFunctionCollection::contentobjectRelationTypeMask(
                array('attribute')
            ),
            'AsObject' => true
        );
        /** @var eZContentObject[] $reverseObjects */
        $reverseObjects = $this->getObject()->reverseRelatedObjectList(
            false,
            $attributeID,
            false,
            $params
        );
        $items = array();
        foreach ($reverseObjects as $object) {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $object->attribute('data_map');
            $orario = $dataMap['orario_trattazione']->content();
            $timestamp = 0;
            if ($orario instanceof eZTime) {
                $timestamp = $orario->attribute('timestamp');
            }
            $items[] = $timestamp;
        }
        asort($items);

        return ( $items );
    }

    public function odgSerialized()
    {
        $rows = array();
        $items = $this->odg();
        foreach ($items as $v) {
            if ($v->is('published')){
                /** @var eZContentObjectAttribute[] $tempDataMap */
                $tempDataMap = $v->getObject()->dataMap();
                $rows[$tempDataMap['n_punto']->content()] = $v->jsonSerialize();
            }
        }

        return $rows;
    }


    protected function getAllegati($identifier)
    {
        $result = array();
        if (isset( $this->dataMap[$identifier] )) {
            $factory = OCEditorialStuffHandler::instance('allegati_seduta')->getFactory();
            $idArray = explode('-', $this->dataMap[$identifier]->toString());
            foreach ($idArray as $id) {
                try {
                    $result[] = new Allegato(array('object_id' => $id), $factory);
                } catch (Exception $e) {

                }
            }
        }

        return $result;
    }

    protected function getCount($identifier)
    {
        if (isset( $this->dataMap[$identifier] )) {
            $contentArray = explode('-', $this->dataMap[$identifier]->toString());
            if (isset( $contentArray[0] ) && $contentArray[0] == '') {
                unset( $contentArray[0] );
            }

            return count($contentArray);
        }

        return 0;
    }


    /**
     *
     *   Seduta
     *   id             integer    id univoco Seduta
     *   data           string     data della seduta in formato 'Y-m-d H:i:s'
     *   protocollo     integer    numero di protocollo
     *   stato          string     identificatre stato draft|pending|published
     *   documenti      integer    numero dei documenti allegati
     *
     * @see ConsiglioApiController
     * @return array
     */
    public function jsonSerialize()
    {
        if (isset( $this->dataMap['data'] ) && $this->dataMap['data']->hasContent()
            && isset( $this->dataMap['orario'] )
            && $this->dataMap['orario']->hasContent()
        ) {
            try {
                $competenza = $this->getOrgano()->getObject()->attribute('name');

                $data = array(
                    'id' => $this->id(),
                    'competenza' => $competenza,
                    'data_svolgimento' => $this->dataOra(self::DATE_FORMAT),
                    'protocollo' => $this->protocollo(),
                    'stato' => $this->currentState()->attribute('identifier'),
                    'documenti' => $this->attribute('count_documenti'),
                    'timestamp' => $this->getObject()->attribute('modified'),
                    '_timestamp_readable' => date(Seduta::DATE_FORMAT, $this->getObject()->attribute('modified'))
                );

                return $data;
            } catch (Exception $e) {

            }
        }

        return false;
    }

    public function presenze($startTime = null, $inOut = null, $type = null, $userId = null)
    {
        return OpenPAConsiglioPresenza::fetchBySeduta($this, $startTime, $inOut, $type, $userId);
    }

    /**
     * @param bool $asObject
     *
     * @return OCEditorialStuffPostInterface[]|int[]
     */
    public function partecipanti($asObject = true)
    {
        if (!isset( self::$partecipantiPerSeduta[$this->id()] )) {
            $partecipanti = array();
            if (isset( $this->dataMap['partecipanti'] )
                && $this->dataMap['partecipanti']->hasContent()
            ) {
                $ids = explode('-', $this->dataMap['partecipanti']->toString());
                foreach ($ids as $id) {
                    $partecipanti[] = OCEditorialStuffHandler::instance('politico')->getFactory()->instancePost(array('object_id' => $id));
                }
            } else {
                $partecipanti = $this->getOrgano()->getComponenti();
            }

            usort($partecipanti,
                function (OpenPAConsiglioStringAttributeInterface $a, OpenPAConsiglioStringAttributeInterface $b) {
                    return strcmp($a->stringAttribute('cognome'), $b->stringAttribute('cognome'));
                });
            self::$partecipantiPerSeduta[$this->id()] = $partecipanti;
        }

        if (!$asObject) {
            $ids = array();
            /** @var Politico $partecipante */
            foreach (self::$partecipantiPerSeduta[$this->id()] as $partecipante) {
                $ids[] = $partecipante->id();
            }

            return $ids;
        }

        return self::$partecipantiPerSeduta[$this->id()];
    }

    public function setPartecipanti()
    {
        /** @var Politico[] $partecipanti */
        $partecipanti = $this->getOrgano()->getComponenti(false);
        if (isset( $this->dataMap['partecipanti'] )) {
            $this->dataMap['partecipanti']->fromString(implode('-', $partecipanti));
            $this->dataMap['partecipanti']->store();
        }
    }

    /**
     * @param bool $asObject
     *
     * @return OCEditorialStuffPostInterface[]|int[]
     */
    public function consiglieri($asObject = true)
    {
//        if ($this->consiglieri === null) {
//            $this->consiglieri = OCEditorialStuffHandler::instance('politico')->fetchItems(
//                array(
//                    'limit' => 100,
//                    'offset' => 0,
//                    'sort' => array('attr_cognome_s' => 'asc')
//                )
//            );
//        }
//
//        if (!$asObject) {
//            $ids = array();
//            foreach ($this->consiglieri as $consigliere) {
//                $ids[] = $consigliere->id();
//            }
//
//            return $ids;
//        }

        return $this->partecipanti();
    }

    public function registroPresenze()
    {
        $data = array(
            'total' => 0,
            'in' => 0,
            'out' => 0,
            'hash_user_id' => array(),
            'hash_user_id_presenza' => array()
        );
        $partecipanti = $this->partecipanti();
        $data['total'] = count($partecipanti);
        foreach ($partecipanti as $partecipante) {
            $presenza = OpenPAConsiglioPresenza::getUserInOutInSeduta($this, $partecipante->id());
            if ($presenza instanceof OpenPAConsiglioPresenza) {
                $presente = $presenza->attribute('is_in');
            } else {
                $presente = false;
                $presenza = new OpenPAConsiglioPresenza(array());
            }
            $data['hash_user_id'][$partecipante->id()] = $presente;
            $data['hash_user_id_presenza'][$partecipante->id()] = $presenza;
            if ($presente) {
                $data['in']++;
            } else {
                $data['out']++;
            }
        }

        return $data;
    }

    public function addPresenza($inOut, $type = 'manual', $userId = null)
    {
        if ($inOut === null) {
            throw new ConsiglioApiException("Parametro in_out non trovato",
                ConsiglioApiException::PRESENZA_NOT_VALID_INOUT_PARAMETER);
        }

        if ($type === null) {
            throw new ConsiglioApiException("Parametro type non trovato",
                ConsiglioApiException::PRESENZA_NOT_VALID_TYPE_PARAMETER);
        }

        if ($userId === null) {
            $userId = eZUser::currentUserID();
        }

        $this->checkAccess($userId);
        $presenza = OpenPAConsiglioPresenza::create($this, $inOut, $type, $userId);
        $presenza->store();
        OpenPAConsiglioPushNotifier::instance()->emit(
            'presenze',
            $presenza->jsonSerialize()
        );

        return $presenza;
    }

    /**
     * Checks if the object is visible by App
     *
     * @return bool
     */

    public function isVisibleByApp()
    {
        $notVisibleStates = array('draft', 'pending', 'published');
        if (in_array($this->currentState()->attribute('identifier'), $notVisibleStates)) {
            return false;
        } else {
            return true;
        }
    }


    public function checkAccess($userId)
    {
        if (!in_array($userId, $this->partecipanti(false))) {
            throw new ConsiglioApiException('Politico non abilitato a presiedere in questa seduta',
                ConsiglioApiException::POLITICO_NOT_ALLOWED);
        }

        //check $userId: se non è un politico viene sollevata eccezione
        try {
            OCEditorialStuffHandler::instance('politico')->fetchByObjectId($userId);
        } catch (Exception $e) {
            throw new ConsiglioApiException('Politico non trovato', ConsiglioApiException::POLITICO_NOT_FOUND);
        }

        //check data ora seduta
        $dataOra = $this->dataOra(false);
        if (!$dataOra instanceof DateTime) {
            throw new Exception('Errore nella definizione del valore data-ora della seduta');
        }
        $now = new DateTime();
        if ($dataOra->diff($now)->days > 1) {
            //throw new Exception( 'Seduta svolta in data ' . $dataOra->format( self::DATE_FORMAT ) );
        }

        //check valid in progress Seduta
        if (!$this->is('in_progress')) {
            if ($this->is('sent')) {
                throw new ConsiglioApiException('Seduta non ancora in corso',
                    ConsiglioApiException::SEDUTA_NOT_IN_PROGRESS);
            } elseif ($this->is('closed')) {
                throw new ConsiglioApiException('Seduta conclusa', ConsiglioApiException::SEDUTA_CLOSED);
            } else {
                throw new ConsiglioApiException('Seduta non in corso', ConsiglioApiException::SEDUTA_NOT_SCHEDULED);
            }
        }
    }

    public function getPuntoInProgress()
    {
        if ($this->currentState()->attribute('identifier') == 'in_progress') {
            foreach ($this->odg() as $punto) {
                if ($punto->currentState()->attribute('identifier') == 'in_progress') {
                    return $punto;
                }
            }
        }

        return null;
    }

    public function getPuntoLastClosed()
    {
        $last = array();
        foreach ($this->odg() as $punto) {
            if ($punto->currentState()->attribute('identifier') == 'closed') {
                $last[$punto->getObject()->attribute('modified')] = $punto;
            }
        }
        ksort($last);
        $last = array_pop($last);

        return $last;
    }

    public function getVotazioneInProgress()
    {
        $inProgress = $this->votazioni(array(
            'state' => 'in_progress',
            'limit' => 1
        ));
        if (isset( $inProgress[0] )) {
            return $inProgress[0];
        }

        return null;
    }

    public function getVotazioneLastModified()
    {
        $last = $this->votazioni(array(
            'state' => array('closed', 'in_progress'),
            'limit' => 1,
            'sort' => array('meta_modified_dt' => 'desc')
        ));
        if (isset( $last[0] )) {
            return $last[0];
        }

        return null;
    }

    public function start()
    {
        $this->setState('seduta.in_progress');
        OpenPAConsiglioPushNotifier::instance()->emit(
            'start_seduta',
            $this->jsonSerialize()
        );
        if (isset( $this->dataMap['orario_inizio_effettivo'] )) {
            $now = time();
            $this->dataMap['orario_inizio_effettivo']->fromString($now);
            $this->dataMap['orario_inizio_effettivo']->store();
        }
    }

    public function stop()
    {
        foreach ($this->partecipanti(false) as $userId) {
            if(OpenPAConsiglioSettings::instance()->useApp()) {
                OpenPAConsiglioPresenza::create($this, 0, 'checkin', $userId)->store();
                OpenPAConsiglioPresenza::create($this, 0, 'beacons', $userId)->store();
            }
            $presenza = OpenPAConsiglioPresenza::create($this, 0, 'manual', $userId);
            $presenza->store();

            OpenPAConsiglioPushNotifier::instance()->emit(
                'presenze',
                $presenza->jsonSerialize()
            );
        }

        $this->setState('seduta.closed');

        if (isset( $this->dataMap['orario_conclusione_effettivo'] )) {
            $now = time();
            $this->dataMap['orario_conclusione_effettivo']->fromString($now);
            $this->dataMap['orario_conclusione_effettivo']->store();
        }

        // Imposto lo stato manualmente a closed per un ritardo sulla transazione del db provocata dallo store del datamap
        $fakeSerialize = $this->jsonSerialize();
        $fakeSerialize['stato'] = 'closed';

        OpenPAConsiglioPushNotifier::instance()->emit(
            'stop_seduta',
            $fakeSerialize
        );

        $this->storePresenti();
    }

    public function intraOmnes()
    {
        foreach ($this->partecipanti(false) as $userId) {
           $this->addPresenza( 1, 'manual', $userId );
        }
    }

    public function extraOmnes()
    {
        foreach ($this->partecipanti(false) as $userId) {
           $this->addPresenza( 0, 'manual', $userId );
        }
    }

    public function storePresenti()
    {
        $presenti = array();
        $helper = new OpenPAConsiglioPresenzaHelper($this);
        $dataPercent = $helper->getPercent();
        foreach ($dataPercent as $userId => $value) {
            if ($value > 0) {
                $presenti[] = $userId;
            }
        }
        if (isset( $this->dataMap['presenti'] )) {
            $this->dataMap['presenti']->fromString(implode('-', $presenti));
            $this->dataMap['presenti']->store();
            eZSearch::addObject($this->getObject());
            eZContentCacheManager::clearObjectViewCacheIfNeeded($this->id());
        }
    }

    /**
     * @param array $parameters
     *
     * @return Votazione[]
     */
    public function votazioni($parameters = array())
    {
        return OCEditorialStuffHandler::instance('votazione')->fetchItems(
            array_merge(
                array(
                    'filters' => array(self::generateSolrSubMetaField('seduta', 'id') . ':' . $this->id()),
                    'sort' => array('published' => 'desc'),
                    'limit' => 100,
                    'offset' => 0
                ),
                $parameters
            ),
            array()
        );
    }

    public function onCreate()
    {
        $this->setPartecipanti();
        $this->createUpdateConvocazione();
        $object = $this->getObject();
        $object->setAttribute('published', $this->dataOra());
        $object->store();

        $this->assignSection($object);

        eZSearch::addObject($object);
    }

    public function onUpdate()
    {
        $this->setPartecipanti();
        $this->createUpdateConvocazione();
        $object = $this->getObject();
        $object->setAttribute('published', $this->dataOra());
        $object->store();

        $this->assignSection($object);

        eZSearch::addObject($object);

        $hasValidOdg = false;
        foreach ($this->odg() as $punto) {
            if ($punto->is('published')) {
                $hasValidOdg = true;
                break;
            }
        }

        if ($this->is('pending') && $hasValidOdg) {
            $lastVersion = $this->getObject()->attribute('current_version') - 1;
            $diff = $this->diff($lastVersion);
            if (isset( $diff['data'] ) || isset( $diff['orario'] )) {
                $this->createNotificationEvent('update_data_ora');
            }
        }
    }

    public function assignSection($object)
    {
        $organo = $this->getOrgano();
        if ($organo instanceof Organo){
            $section = $organo->getSection();
            if($section instanceof eZSection){
                eZContentOperationCollection::updateSection($object->attribute( "main_node_id" ), $section->attribute('id'));
            }
        }
    }

    public function handleUpdateNotification($event)
    {
        foreach ($this->partecipanti(false) as $partecipanteId) {
            $this->createNotificationItem(
                $event,
                $partecipanteId,
                'default'
            );
        }
    }

    public function handleSendConvocationNotification($event)
    {
        foreach ($this->partecipanti(false) as $partecipanteId) {
            $this->createNotificationItem(
                $event,
                $partecipanteId,
                'default'
            );
        }
    }

    /**
     * @param eZNotificationEvent $event
     * @param int $userId
     * @param $subscribersRuleString
     *
     * @return OpenPAConsiglioNotificationItem
     */
    protected function createNotificationItem(eZNotificationEvent $event, $userId, $subscribersRuleString)
    {
        $type = OpenPAConsiglioNotificationTransport::DEFAULT_TRANSPORT;
        $time = time();
        $transport = OpenPAConsiglioNotificationTransport::instance($type);
        $templateName = $transport->notificationTemplateUri($event, $subscribersRuleString);

        $notifiedVersion = $event->attribute(OCEditorialStuffEventType::FIELD_VERSION) - 1;
        $diff = $this->diff($notifiedVersion);
        $oldDate = false;
        
        /** @var eZContentObjectVersion $oldVersion */
        $oldVersion = $this->getObject()->version($notifiedVersion);
        if ($oldVersion instanceof eZContentObjectVersion){
            /** @var eZContentObjectAttribute[] $oldVersionDataMap */
            $oldVersionDataMap = $oldVersion->dataMap();
            /** @var eZDate $data */
            $data = $oldVersionDataMap['data']->content();
            /** @var eZTime $ora */
            $ora = $oldVersionDataMap['orario']->content();
            $oldDate = $this->getDateTimeFromEzContents($data, $ora)->format('U');
        }

        $variables = array(
            'user' => eZUser::fetch($userId),
            'seduta' => $this,
            'diff' => $diff,
            'old_date' => $oldDate
        );

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach ($variables as $name => $value) {
            $tpl->setVariable($name, $value);
        }                                                   
        $content = $tpl->fetch($templateName);
        $subject = $tpl->variable('subject');

        return $transport->addItem(
            array(
                'object_id' => $this->id(),
                'user_id' => $userId,
                'created_time' => time(),
                'type' => $type,
                'subject' => $subject,
                'body' => $content,
                'expected_send_time' => $time
            )
        );
    }


    public function executeAction($actionIdentifier, $actionParameters, eZModule $module = null)
    {
        if ($actionIdentifier == 'ExportVotazioni') {
            $this->exportVotazioni();
            eZExecution::cleanExit();

        } elseif ($actionIdentifier == 'GetConvocazione') {
            $convocazione = ConvocazioneSeduta::get($this->getObject());
            $downloadUrl = 'editorialstuff/download/convocazione_seduta/' . $convocazione->attribute('id') . '?' . http_build_query($actionParameters);
            $module->redirectTo($downloadUrl);

        } elseif ($actionIdentifier == 'GetAttestatoPresenza') {
            $politico = eZContentObject::fetch($actionParameters['presente']);
            if ($politico instanceof eZContentObject) {
                $tpl = eZTemplate::factory();
                $tpl->resetVariables();
                $tpl->setVariable('line_height', '1.3');
                $tpl->setVariable('seduta', $this);
                $tpl->setVariable('politico', $politico);
                /** @var eZContentObjectAttribute[] $politicoDataMap */
                $politicoDataMap = $politico->dataMap();
                if (isset($politicoDataMap['sesso'])) {
                    $tpl->setVariable('sesso', $politicoDataMap['sesso']->toString());
                }
                $competenza = '';
                if ($this->getOrgano()) {
                    $organo = $this->getOrgano()->getObject()->attribute('name');
                    $tpl->setVariable('organo', $organo);
                }
                $tpl->setVariable('organo', $competenza);

                if ($this->dataMap['segretario_verbalizzante']->hasContent()) {
                    $listSegretario = $this->dataMap['segretario_verbalizzante']->content();
                    if (isset( $listSegretario['relation_list'][0]['contentobject_id'] )) {
                        $segretario = eZContentObject::fetch(
                            $listSegretario['relation_list'][0]['contentobject_id']
                        );
                        /** @var eZContentObjectAttribute[] $segretarioDataMap */
                        $segretarioDataMap = $segretario->dataMap();

                        $tpl->setVariable('segretario', trim($segretario->attribute('name')));

                        if (isset($segretarioDataMap['firma']) && $segretarioDataMap['firma']->hasContent()
                            && $segretarioDataMap['firma']->attribute('data_type_string') == 'ezimage'
                        ) {
                            $image = $segretarioDataMap['firma']->content()->attribute('original');
                            $url = $image['url'];
                            eZURI::transformURI($url, false, 'full');
                            $tpl->setVariable('firma', $url);
                        }

                    }
                }

                $helper = new OpenPAConsiglioPresenzaHelper($this, null, $politico->attribute('id'));
                $data = $helper->getData();
                $checkin = $data['checkin'] ? $data['checkin'] : $this->dataOraEffettivaInizio();
                $checkout = $data['checkout'] ? $data['checkout'] : $this->dataOraFine();
                $tpl->setVariable('checkin', $checkin);
                $tpl->setVariable('checkout', $checkout);

                $content = $tpl->fetch('design:pdf/presenza/presenza.tpl');

                /** @var eZContentClass $objectClass */
                $objectClass = $this->getObject()->attribute('content_class');
                $languageCode = eZContentObject::defaultLanguage();
                $fileName = $objectClass->urlAliasName($this->getObject(), false, $languageCode);
                $fileName = eZURLAliasML::convertToAlias($fileName);
                $politicoName = eZURLAliasML::convertToAlias($politico->attribute('name'));
                $fileName .= '-' . $politicoName . '.pdf';

                $parameters = array(
                    'exporter' => 'paradox',
                    'cache' => array(
                        'keys' => array(),
                        'subtree_expiry' => '',
                        'expiry' => -1,
                        'ignore_content_expiry' => false
                    )
                );

                OpenPAConsiglioPdf::create($fileName, $content, $parameters);

                if (eZINI::instance()->variable('DebugSettings', 'DebugOutput') == 'enabled') {
                    echo '<pre>' . htmlentities($content) . '</pre>';
                    eZDisplayDebug();
                }
                eZExecution::cleanExit();

            }

        } elseif ($actionIdentifier == 'SaveVerbale') {
            $this->saveVerbale($actionParameters['Verbale']);
        
        } elseif ($actionIdentifier == 'CreateVerbaleObject') {
            $verbale = $this->createVerbaleObject();
            if ($verbale instanceof Verbale){
                $module->redirectTo($verbale->attribute('editorial_url'));
            }
        }
    }

    protected function exportVotazioni()
    {
        $allData = array();

        $keys = array();
        $keys[] = "Data";
        $keys[] = "Tipo";
        $keys[] = "Testo";
        $keys[] = "Esito";
        $keys[] = "Numero presenti";
        $keys[] = "Numero assenti";
        $keys[] = "Numero votanti";
        $keys[] = "Numero non votanti";
        $keys[] = "Numero favorevoli";
        $keys[] = "Numero contrari";
        $keys[] = "Numero astenuti";
        $keys[] = "Non Votanti";
        $keys[] = "Favorevoli";
        $keys[] = "Contrari";
        $keys[] = "Astenuti";
        $keys[] = "Assenti";

        $allData[] = $keys;

        foreach ($this->votazioni() as $votazione) {
            $state = $votazione->currentState()->attribute('identifier');
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $votazione->getObject()->attribute('data_map');

            /** @var OpenPAConsiglioVotazioneResultHandlerDefault $result */
            $result = $votazione->attribute('result');

            $data = date("Y/m/d H:i", $votazione->getObject()->attribute('published'));
            $esito = 'NON EFFETTUATA';
            $presenti = 0;
            $assenti = 0;
            $votanti = 0;
            $nonVotanti = array();
            $favorevoli = array();
            $contrari = array();
            $astenuti = array();

            if ($state == 'closed') {
                $data = date("Y/m/d H:i", $votazione->getObject()->attribute('modified'));

                if (!$votazione->attribute('is_valid')) {
                    $esito = 'QUORUM NON RAGGIUNTO';
                } elseif ($result->attribute('approvata')) {
                    $esito = 'APPROVATA';
                } elseif (!$result->attribute('approvata')) {
                    $esito = 'RESPINTA';
                }

                $presenti = $result->attribute('presenti_count');
                $assenti = $result->attribute('assenti_count');
                $votanti = $result->attribute('votanti_count');
                $nonVotanti = $result->attribute('non_votanti');
                $favorevoli = $result->attribute('favorevoli');
                $contrari = $result->attribute('contrari');
                $astenuti = $result->attribute('astenuti');
            }


            $row = array(
                "Data" => $data,
                "Tipo" => $dataMap['type']->toString(),
                "Testo" => $dataMap['short_text']->toString(),
                "Esito" => $esito,
                "Numero presenti" => (string)$presenti,
                "Numero assento" => (string)$assenti,
                "Numero votanti" => (string)$votanti,
                "Numero non votanti" => (string)count($nonVotanti),
                "Numero favorevoli" => (string)count($favorevoli),
                "Numero contrari" => (string)count($contrari),
                "Numero astenuti" => (string)count($astenuti),
                "Non Votanti" => '',
                "Favorevoli" => '',
                "Contrari" => '',
                "Astenuti" => '',
                "Assenti" => ''
            );

            foreach ($this->partecipanti() as $partecipante) {
                if ($state == 'closed') {
                    $user = false;
                    /** @var eZUser $votante */

                    foreach ($nonVotanti as $votante) {
                        if ($votante->id() == $partecipante->id()) {
                            $user = 'NON VOTANTE';
                            $row["Non Votanti"] .= $partecipante->getObject()->attribute('name') . ', ';
                        }
                    }
                    if (!$user) {
                        foreach ($favorevoli as $votante) {
                            if ($votante->id() == $partecipante->id()) {
                                $user = 'FAVOREVOLE';
                                $row["Favorevoli"] .= $partecipante->getObject()->attribute('name') . ', ';
                            }
                        }
                    }
                    if (!$user) {
                        foreach ($contrari as $votante) {
                            if ($votante->id() == $partecipante->id()) {
                                $user = 'CONTRARIO';
                                $row["Contrari"] .= $partecipante->getObject()->attribute('name') . ', ';
                            }
                        }
                    }
                    if (!$user) {
                        foreach ($astenuti as $votante) {
                            if ($votante->id() == $partecipante->id()) {
                                $user = 'ASTENUTO';
                                $row["Astenuti"] .= $partecipante->getObject()->attribute('name') . ', ';
                            }
                        }
                    }
                    if (!$user) {
                        $user = 'ASSENTE';
                        $row["Assenti"] .= $partecipante->getObject()->attribute('name') . ', ';
                    }
                } else {
                    $user = '';
                }

                //$row[] = $user;
            }

            $row["Non Votanti"] = trim($row["Non Votanti"]);
            $row["Favorevoli"] = trim($row["Favorevoli"]);
            $row["Contrari"] = trim($row["Contrari"]);
            $row["Astenuti"] = trim($row["Astenuti"]);
            $row["Assenti"] = trim($row["Assenti"]);

            $row["Non Votanti"] = rtrim($row["Non Votanti"], ',');
            $row["Favorevoli"] = rtrim($row["Favorevoli"], ',');
            $row["Contrari"] = rtrim($row["Contrari"], ',');
            $row["Astenuti"] = rtrim($row["Astenuti"], ',');
            $row["Assenti"] = rtrim($row["Assenti"], ',');

            $allData[] = $row;
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator('cal.tn.it')
                    ->setLastModifiedBy('cal.tn.it')
                    ->setTitle('Votazioni')
                    ->setSubject('Votazioni')
                    ->setDescription('Votazioni ' . $this->getObject()->attribute('name'));
        $objPHPExcel->getActiveSheet()->fromArray($allData);

        $filename = 'votazioni ' . $this->getObject()->attribute('name') . '.xls';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        eZExecution::cleanExit();
    }
}
