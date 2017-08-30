<?php

class OpenPAConsiglioPageDataHandler implements OCPageDataHandlerInterface
{
    public function siteTitle()
    {
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
        return 'extension/opencontent/design/ocbootstrap_ftcoop/images/logo.png';
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
