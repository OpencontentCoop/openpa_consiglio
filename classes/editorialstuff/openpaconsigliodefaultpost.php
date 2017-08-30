<?php

class OpenPAConsiglioDefaultPost extends OCEditorialStuffPostDefault
{
    public function contentAttributes()
    {
        $data = array();
        foreach ($this->dataMap as $identifier => $attribute) {
            $category = $attribute->attribute('contentclass_attribute')->attribute('category');
            if ($category == 'content' || empty( $category )) {
                $data[$identifier] = $attribute;
            }
        }

        return $data;
    }

    public function tabs()
    {
        $currentUser = eZUser::currentUser();
        $templatePath = $this->getFactory()->getTemplateDirectory();
        $tabs = array(
            array(
                'identifier' => 'content',
                'name' => 'Contenuto',
                'template_uri' => "design:{$templatePath}/parts/content.tpl"
            )
        );
        $tabs[] = array(
            'identifier' => 'history',
            'name' => 'Cronologia',
            'template_uri' => "design:{$templatePath}/parts/history.tpl"
        );
        return $tabs;
    }
}
