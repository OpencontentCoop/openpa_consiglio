<?php


class OpenPAConsiglioDefaultFactory extends OCEditorialStuffPostDefaultFactory
{
    public function __construct( $configuration )
    {
        $this->configuration = $configuration;
        $this->configuration['PersistentVariable'] = array(
            'top_menu' => true,
            'topmenu_template_uri' => 'design:consiglio/page_topmenu.tpl'
        );
    }

    public function instancePost( $data )
    {
        return new OpenPAConsiglioDefaultPost( $data, $this );
    }
}