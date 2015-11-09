<?php

class OpenPAConsiglioGettoniInterval
{
    public $start;
    public $isValid = false;
    public $intervalName;
    public $intervalString;
    public $startYear;
    public $endYear;
    public $startDateTime;
    public $endDateTime;
    public $type;
    public $selected;

    public function __construct( $string )
    {
        $this->intervalString = trim( $string );
        /** @var Seduta[] $firstSeduta */
        $firstSeduta = OCEditorialStuffHandler::instance( 'seduta' )->fetchItems(
            array(
                'state' => array( 'closed' ),
                'sort' => array( 'meta_published_dt' => 'asc' ),
                'limit' => 1,
                'offset' => 0
            )
        );

        $now = new DateTime();
        $this->startYear = $now->format( 'Y' );
        $this->endYear = $now->format( 'Y' );
        if ( count( $firstSeduta ) > 0 )
        {
            $this->startYear = $firstSeduta[0]->dataOra( 'Y' );
        }

        $this->startDateTime = new DateTime();
        $this->endDateTime = new DateTime();

        if ( !empty( $this->intervalString ) && strpos( $this->intervalString, '-' ) > 0 )
        {
            list( $year, $period ) = explode( '-', $this->intervalString );
            if ( $year == 'select' )
            {
                $this->type = 'select';
                $selected = explode( ',', $period );
                
                $objects = eZContentObject::fetchIDArray( $selected );
                $this->selected = array();
                $names = array();
                foreach( $objects as $object )
                {
                    $this->selected[] = $object->attribute( 'id' );
                    $names[] = $object->attribute( 'name' );
                }                
                $this->intervalName = implode( ', ', $names );
                
                $this->isValid = true;
            }
            else
            {
                $locale = eZLocale::instance();
                $this->type = 'date';
                if ( $year >= $this->startYear && $year <= $this->endYear )
                {
                    $this->isValid = true;
                }
                switch ( $period )
                {

                    case 'I':
                        $this->startDateTime->setDate( $year, 1, 1 );
                        $this->endDateTime->setDate( $year, 4, 30 );
                        $this->intervalName = "I quadrimestre $year";
                        break;

                    case 'II':
                        $this->startDateTime->setDate( $year, 5, 1 );
                        $this->endDateTime->setDate( $year, 8, 31 );
                        $this->intervalName = "II quadrimestre $year";
                        break;

                    case 'III':
                        $this->startDateTime->setDate( $year, 9, 1 );
                        $this->endDateTime->setDate( $year, 12, 31 );
                        $this->intervalName = "III quadrimestre $year";
                        break;

                    case '0':
                        $this->startDateTime->setDate( $year, 1, 1 );
                        $this->endDateTime->setDate( $year, 12, 31 );
                        $this->intervalName = "Anno $year";
                        break;

                    default:                        
                        $this->startDateTime->setDate( $year, $period, 1 );
                        $this->endDateTime->setDate( $year, $period+1, 1 );
                        $this->intervalName = $locale->longMonthName( $this->startDateTime->format( 'n' ) ) . " $year";
                }
            }
        }
    }

    function fetchFilter()
    {
        if ( $this->type == 'date' )
        {
            $startDate = ezfSolrDocumentFieldBase::preProcessValue(
                $this->startDateTime->getTimestamp(),
                'date'
            );
            $endDate = ezfSolrDocumentFieldBase::preProcessValue(
                $this->endDateTime->getTimestamp(),
                'date'
            );

            return array( 'meta_published_dt:[' . $startDate . ' TO ' . $endDate . ']' );
        }
        elseif( $this->type == 'select' )
        {
            $filters = count( $this->selected ) > 1 ? array( 'or' ) : array();
            foreach( $this->selected as $id )
            {
                $filters[] = 'meta_id_si:' . $id;
            }
            return array( $filters );
        }
        return array( 'meta_id_si:0' );
    }

    function __toString()
    {
        return $this->intervalName;
    }
}