<?php

namespace Realm\Loader;

use SimpleXMLElement;
use Realm\Model\Value;
use Realm\Model\Concept;
use Realm\Model\Resource;
use Realm\Model\ResourceSection;
use Realm\Model\Project;
use Realm\Model\Property;
use Realm\Model\Codelist;
use Realm\Model\CodelistItem;
use Realm\Model\SectionType;
use Realm\Model\SectionFieldType;
use Realm\Loader\XmlResourceLoader;
use RuntimeException;

class XmlRealmLoader
{
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: " . $filename);
        }
        $basePath = dirname($filename);
        $xml = file_get_contents($filename);
        $root = simplexml_load_string($xml);

        $project = $this->loadProject($root);

        $files = glob($basePath . '/codelists/*.xml');
        foreach ($files as $filename) {
            $xml = file_get_contents($filename);
            $root = simplexml_load_string($xml);
            $codelist = $this->loadCodelist($root, $project);
            $project->addCodelist($codelist);
        }

        $files = glob($basePath . '/concepts/*.xml');
        foreach ($files as $filename) {
            $xml = file_get_contents($filename);
            $root = simplexml_load_string($xml);
            $concept = $this->loadConcept($root, $project);
            $project->addConcept($concept);
        }

        $files = glob($basePath . '/sectionTypes/*.xml');
        foreach ($files as $filename) {
            $xml = file_get_contents($filename);
            $root = simplexml_load_string($xml);
            $sectionType = $this->loadSectionType($root, $project);
            $project->addSectionType($sectionType);
        }
        
        $resourceLoader = new XmlResourceLoader();
        $files = glob($basePath . '/resources/*.xml');
        foreach ($files as $filename) {
            $resource = $resourceLoader->loadFile($filename, $project);
            $project->addResource($resource);
        }


        return $project;
    }
    
    public function loadProject(SimpleXMLElement $root)
    {
        $project = new Project();
        $project->setId((string)$root['id']);
        $this->loadProperties($root, $project);
        return $project;
    }
    
    public function loadCodelist(SimpleXMLElement $root, Project $project)
    {
        $codelist = new Codelist();
        $codelist->setId((string)$root['id']);
        $this->loadProperties($root, $codelist);
        foreach ($root->item as $iNode) {
            $item = new CodelistItem();
            $item->setCode((string)$iNode['code']);
            $item->setCodeSystem((string)$iNode['codeSystem']);
            $item->setDisplayName((string)$iNode['displayName']);
            $item->setLevel((string)$iNode['level']);
            $item->setType((string)$iNode['type']);
            
            $codelist->addItem($item);
        }
        //print_r($sectionType); exit();
        return $codelist;
    }
    
    public function loadConcept(SimpleXMLElement $root, Project $project)
    {
        $concept = new Concept();
        $concept->setId((string)$root['id']);
        $concept->setShortName((string)$root['shortName']);
        $concept->setOid((string)$root['oid']);
        $concept->setType((string)$root['type']);
        $concept->setDataType((string)$root['dataType']);
        $concept->setLengthMax((string)$root['lengthMax']);
        $concept->setLengthMin((string)$root['lengthMin']);
        if (isset($root['codelist'])) {
            $codelistName = (string)$root['codelist'];
            $codelist = $project->getCodelist($codelistName);
            $concept->setCodelist($codelist);
        }
        
        
        $this->loadProperties($root, $concept);
        return $concept;
    }
    
    public function loadSectionType(SimpleXMLElement $root, Project $project)
    {
        $sectionType = new SectionType();
        $sectionType->setId((string)$root['id']);
        $sectionType->setLabel((string)$root['label']);
        $this->loadProperties($root, $sectionType);
        foreach ($root->field as $fNode) {
            $field = new SectionFieldType();
            $concept = $project->getConcept($fNode['concept']);
            $field->setConcept($concept);
            $field->setMin((string)$fNode['min']);
            $field->setMax((string)$fNode['max']);
            
            $sectionType->addField($field);
        }
        //print_r($sectionType); exit();
        return $sectionType;
    }
    
    protected function loadProperties(SimpleXMLElement $root, $obj)
    {
        foreach ($root->property as $pNode) {
            $property = new Property();
            $property->setName((string)$pNode['name']);
            $property->setLanguage((string)$pNode['language']);
            $property->setValue((string)$pNode);
            $obj->addProperty($property);
        }
    }
}
