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

        if ( !empty( $this->intervalString ) )
        {
            list( $year, $period ) = explode( '-', $this->intervalString );
            if ( $year >= $this->startYear && $year <= $this->endYear )
            {
                $this->isValid = true;
            }
            switch( $period )
            {
                case 1:
                    $this->startDateTime->setDate( $year, 1, 1 );
                    $this->endDateTime->setDate( $year, 4, 30 );
                    $this->intervalName = "I quadrimestre $year";
                    break;
                case 2:
                    $this->startDateTime->setDate( $year, 5, 1 );
                    $this->endDateTime->setDate( $year, 8, 31 );
                    $this->intervalName = "II quadrimestre $year";
                    break;
                case 3:
                    $this->startDateTime->setDate( $year, 9, 1 );
                    $this->endDateTime->setDate( $year, 12, 31 );
                    $this->intervalName = "III quadrimestre $year";
                    break;
                default:
                    $this->startDateTime->setDate( $year, 1, 1 );
                    $this->endDateTime->setDate( $year, 12, 31 );
                    $this->intervalName = "Anno $year";
            }
        }
    }

    function __toString()
    {
        return $this->intervalName;
    }
}