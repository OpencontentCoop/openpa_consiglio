<?php


class OpenPAConsiglioEditorialStuffPersonaleHandler extends OCEditorialStuffHandler
{
    protected function fetch($limit = 10, $offset = 0, $limitation = null)
    {
        /** @var Organo[] $organi */
        $organi = OCEditorialStuffHandler::instance('organo')->fetchItems(array('limit' => 50, 'offset' => 0));
        $politiciIdList = array();
        foreach ($organi as $organo) {
            $politiciIdList = array_merge(
                $politiciIdList, 
                $organo->stringAttribute('membri',function($string){return explode('-', $string);})
            );
        }
        $politiciIdList = array_unique($politiciIdList);

        $politiciFilters = count($politiciIdList) > 1 ? array('or') : array();
        foreach ($politiciIdList as $id) {
            $politiciFilters[] = 'meta_id_si:' . $id;
        }
        if (empty($this->filters)){
            $this->filters = count($politiciFilters) > 1 ? $politiciFilters : $politiciFilters[0];
            $limit = $limit < count($politiciFilters) ? count($politiciFilters) : $limit;
        }                
        return parent::fetch($limit, $offset, $limitation);
    }
}