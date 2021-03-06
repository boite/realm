<?php

namespace Realm\Presenter;

use LinkORB\Presenter\BasePresenter;
use RuntimeException;
use DateTime;

class ValuePresenter extends BasePresenter
{
    public function getLabel()
    {
        if ($this->presenterObject->getLabel()) {
            return $this->presenterObject->getLabel();
        }
        $resource = $this->getResource();
        if (!$resource) {
            throw new RuntimeException('No resource');
        }

        if ($this->presenterObject->getConcept()) {
            $concept = $this->presenterObject->getConcept();
            if ($concept->hasProperty('name', $resource->getLanguage())) {
                $label = $concept->getPropertyValue($resource->getLanguage(), 'name');
            } else {
                $label = $concept->getShortName();
                $label = str_replace('_', ' ', $label);
                $label = ucfirst($label);
            }
            return $label;
        }
        return null;
    }

    public function getLabelWithValue()
    {
        if (!$this->getDisplayValue()) {
            return null;
        }
        return $this->getLabel() . ': ' . $this->getDisplayValue();
    }

    public function getDisplayValue()
    {
        $value = $this->presenterObject->getValue();
        // Prefer explicitly defined displayValue
        if ($this->presenterObject->getDisplayValue()) {
            return $this->presenterObject->getDisplayValue();
        }

        $resource = $this->getResource();
        if (!$resource) {
            throw new RuntimeException('No resource');
        }

        // Logic for transforming value based on concept
        if ($this->presenterObject->getConcept()) {
            $concept = $this->presenterObject->getConcept();
            switch ($concept->getDataType()) {
                case 'date':
                case 'datetime':
                    if (!$value) {
                        return '-';
                    }
                    try {
                        $date = DateTime::createFromFormat('Y-m-d', $value);

                        $value = '??';
                        if ($date) {
                            $value = (string) $date->format('d-m-Y');
                        }
                    } catch (\Exception $e) {
                        $value = '?';
                    }
                    return $value;
                    break;
                case 'boolean':
                    switch ($value) {
                        case 'TRUE':
                            return 'Ja';
                        case 'FALSE':
                            return 'Nee';
                    }
                    return '???';
                    break;
                case 'code':
                    $codelist = $concept->getCodelist();
                    if (!$codelist) {
                        throw new RuntimeException('Type code with undefined codelist');
                    }
                    if (!$codelist->hasItem($value)) {
                        //throw new RuntimeException("No such codelist item: " . $value);
                        return '???';
                    }
                    $item = $codelist->getItem($value);
                    if ($item) {
                        if ($item->hasProperty('name', $resource->getLanguage())) {
                            return $item->getPropertyValue($resource->getLanguage(), 'name');
                        } else {
                            return $item->getDisplayName();
                        }
                    }
                    break;
            }
        }
        // last resort, return raw value
        if ($value === null) {
            return '-';
        }
        return $value;
    }
}
