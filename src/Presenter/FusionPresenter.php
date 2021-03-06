<?php

namespace Realm\Presenter;

use LinkORB\Presenter\BasePresenter;

class FusionPresenter extends BasePresenter
{
    public function presentValueByConceptId($conceptId)
    {
        $values = [];
        $uniqueValues = [];
        foreach ($this->presenterObject->getSections() as $section) {
            foreach ($section->getValues() as $value) {
                if ($value->getConcept() && ($value->getConcept()->getId() == $conceptId)) {
                    $values[] = $value;
                    $uniqueValues[$value->getValue()] = $value;
                }
            }
        }
        $html = '';
        $multiple = false;
        foreach ($uniqueValues as $value) {
            if ($html != '') {
                $html .= ' / ';
                $multiple = true;
            }
            $html .= $value->getPresenter()->getDisplayValue();
        }
        $visor = '<div class="realm-visor">';

        $visor .= '<div class="realm-debug">';
        $visor .= 'Concept: ' . $conceptId;
        $concept = $this->getConcept($conceptId);
        if ($concept) {
            $visor .= ' (' . $concept->getShortName() . ')';
        }

        $visor .= '<br />';
        foreach ($values as $value) {
            if ($value) {
                $visor .= 'Value: ' . $value->getValue() . '<br />';
                $visor .= 'Display: ' . $value->getPresenter()->getDisplayValue() . '<br />';
            } else {
                $visor .= 'Value: null<br />';
            }
            $visor .= '<hr />';
        }

        $visor .= '</div>';

        $visor .= '<table class="table" style="width: 400px;">';
        $visor .= '<tr>';
        $visor .= '<th colspan="2">Bron</th>';
        //$visor .= '<th>Datum</th>';
        //$visor .= '<th>Waarde</th>';
        $visor .= '</tr>';

        foreach ($values as $value) {
            $visor .= '<tr>';
            $visor .= '<td style="white-space: nowrap;" colspan="2">' . $value->getSection()->getResource()->getSource()->getDisplayName();

            $visor .= '<tr>';
            $visor .= '</tr>';
            $visor .= '<td style="width: 80px;">';
            $visor .= '<img src="' . $value->getSection()->getResource()->getSource()->getLogoUrl() . '" style="margin-right: 3px;"/>';
            $visor .= '<img src="' . $value->getSection()->getResource()->getSource()->getAppLogoUrl() . '" />';
            $visor .= '<br /><small><span class="date" style="white-space: nowrap;">' . $value->getSection()->getPresenter()->getEffectiveAt() . '</span></small><br />';
            $visor .= '</td>';
            $visor .= '<td><b style="border: 1px solid; display: block; padding: 3px; border-radius: 2px; background-color: #ffffff;">' . $value->getPresenter()->getDisplayValue() . '</b>';
            $visor .= '</td>';
            $visor .= '</tr>';
        }
        $visor .= '</table>';
        $visor .= '</div>';
        if ($multiple) {
            $html = '<span class="err">' . $html . '</span>';
        }
        if (!$html) {
            $html = '&#8203;'; // way to enforce minimum 1 character hight spans
        }

        $html = '<span class="realm-value">' . $html . $visor . '</span>';
        return $html;
    }

    protected function getConcept($conceptId)
    {
        // TODO: when no values of this conceptId are in a resource (none of the sections)
        // then this method can't resolve the concept.
        foreach ($this->presenterObject->getSections() as $section) {
            foreach ($section->getValues() as $value) {
                if ($value->getConcept() && ($value->getConcept()->getId() == $conceptId)) {
                    return $value->getConcept();
                }
            }
        }
        return null;
    }

    public function presentConcept($conceptId, $label = '')
    {
        $concept = $this->getConcept($conceptId);
        if ($label == '') {
            $label = $concept->getShortName();
        }
        $html = '';
        $html .= '<dt>' . $label;
        if ($concept) {
            $html .= $concept->getPresenter()->presentTooltip();
        }
        $html .= '</dt>';
        $html .= '<dd>' . $this->presentValueByConceptId($conceptId) . '</dd>';
        return $html;
    }
}
