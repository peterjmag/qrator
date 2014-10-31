<?php
namespace spec\watoki\cqurator\form;

use watoki\cqurator\form\fields\ArrayField;
use watoki\cqurator\form\fields\StringField;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\cqurator\fixtures\FieldFixture field <-
 */
class ArrayFieldTest extends Specification {

    function testWrapNameOfInnerField() {
        $this->givenAndArrayField_OfStringFields('tests', 'test');

        $this->field->whenIRenderTheField();
        $this->field->thenTheOutputShouldContain('args[tests][]');
    }

    /**
     * @param $outerName
     * @param $innerName
     */
    private function givenAndArrayField_OfStringFields($outerName, $innerName) {
        $this->field->givenTheField(new ArrayField($outerName, new StringField($innerName)));
    }

} 