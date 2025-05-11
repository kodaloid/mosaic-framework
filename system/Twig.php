<?php

use \Twig\Extension\AbstractExtension;
use \Twig\TwigFilter;


/**
 * Extend twig adding features for Mosaic.
 */
class MosTwigExtensions extends AbstractExtension {
    public function getFilters() {
        return [
            new TwigFilter('checked', [$this, 'makeChecked']),
            new TwigFilter('selected', [$this, 'makeSelected']),
            new TwigFilter('onBool', [$this, 'makeOnBool'])
        ];
    }

    public function makeChecked(bool $checked) {
        return $checked ? 'checked="checked"' : '';
    }

    public function makeSelected(bool $selected) {
		return $selected ? 'selected="selected"' : '';
    }

    public function makeOnBool(bool $condition, string $class) {
        return $condition ? $class : '';
    }

}