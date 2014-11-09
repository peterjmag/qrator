<?php
namespace watoki\qrator\form\fields;

use watoki\qrator\form\TemplatedField;

class StringField extends TemplatedField {

    protected function getModel() {
        return array_merge(parent::getModel(), [
            'class' => 'form-control'
        ]);
    }


}