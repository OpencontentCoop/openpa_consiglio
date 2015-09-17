<?php

class ObjectHandlerServiceGestioneSeduteConsiglio extends ObjectHandlerServiceBase implements OCPageDataHandlerInterface
{

    /**
     * Popola l'array $this->data con chiave => valore
     *
     * @return void
     */
    function run()
    {
        $this->fnData['stuff'] = 'getConsiglioPost';
    }

    protected function getConsiglioPost()
    {
        $object = $this->container->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            foreach ( OCEditorialStuffHandler::instances() as $instance )
            {
                if ( $object->attribute( 'class_identifier' ) == $instance->getFactory()->classIdentifier() )
                {
                    try
                    {
                        return $instance->getFactory()->instancePost(
                            array( 'object_id' => $object->attribute( 'id' ) )
                        );
                    }
                    catch ( Exception $e )
                    {
                    }
                }
            }
        }
        return null;
    }

    public function siteTitle()
    {
        $home = OpenPaFunctionCollection::fetchHome();
        if ( $home instanceof eZContentObjectTreeNode )
        {
            return $home->attribute( 'name' );
        }
        return eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
    }

    public function siteUrl()
    {
        $siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $siteUrl );
        if ( count( $parts ) >= 2 )
        {
            array_pop( $parts );
            $siteUrl = implode( '/', $parts );
        }
        return rtrim( $siteUrl, '/' );
    }

    public function assetUrl()
    {
        return $this->siteUrl();
    }

    public function logoPath()
    {
        $logo = (array) OpenPaFunctionCollection::fetchHeaderLogo();
        return $logo['full_path'];
    }

    public function logoTitle()
    {
        return $this->siteTitle();
    }

    public function logoSubtitle()
    {
        return '';
    }

    public function headImages()
    {
        return array();
    }

    public function needLogin()
    {
        return false;
    }

    public function attributeContacts()
    {
        return false;
    }

    public function attributeFooter()
    {
        return false;
    }

    public function textCredits()
    {
        return false;
    }

    public function googleAnalyticsId()
    {
        return false;
    }

    public function cookieLawUrl()
    {
        return false;
    }

    public function menu()
    {
        return array();
    }

    public function userMenu()
    {
        return array();
    }

    public function bannerPath()
    {
        return false;
    }

    public function bannerTitle()
    {
        return false;
    }

    public function bannerSubtitle()
    {
        return false;
    }
}