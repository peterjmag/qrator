<?php
namespace spec\watoki\cqurator;

use watoki\cqurator\web\PrepareResource;
use watoki\scrut\Specification;

/**
 * The only reason a form is ever presented is to fill the missing properties of an Action during preparation.
 *
 * @property \spec\watoki\cqurator\fixtures\ClassFixture class <-
 * @property \spec\watoki\cqurator\fixtures\ResourceFixture resource <-
 * @property \spec\watoki\cqurator\fixtures\RegistryFixture registry <-
 */
class ShowPreparationFormTest extends Specification {

    function testAllPropertiesProvided() {
        $this->class->givenTheClass_WithTheBody('PrepareAction', '
            public $one;
            public $two;
        ');
        $this->resource->givenTheRequestArgument_Is('one', 'uno');
        $this->resource->givenTheRequestArgument_Is('two', 'dos');

        $this->whenIPrepare('PrepareAction');
        $this->resource->thenIShouldBeRedirectedTo('?action=PrepareAction&type=query&one=uno&two=dos');
    }

    function testInputForMissingProperties() {
        $this->class->givenTheClass_WithTheBody('PrepareAction', '
            public $one;
            public $two;
        ');
        $this->resource->givenTheRequestArgument_Is('one', 'uno');

        $this->whenIPrepare('PrepareAction');

        $this->thenThereShouldBe_Fields(2);
        $this->thenField_ShouldHaveTheLabel(1, 'One');
        $this->thenField_ShouldBeRenderedAs(1, '<input type="text" name="one" value="uno"/>');
        $this->thenField_ShouldBeRenderedAs(2, '<input type="text" name="two"/>');
    }

    function testSubmitFilledAction() {
        $this->markTestIncomplete();
    }

    function testGetFormDefinitionFromRepresenter() {
        $this->markTestIncomplete();
    }

    ###############################################################################################

    private function whenIPrepare($action) {
        $this->resource->whenIDo_With(function (PrepareResource $resource) use ($action) {
            return $resource->doGet($this->resource->request, $action, 'query');
        }, new PrepareResource($this->factory, $this->registry->registry));
    }

    private function thenThereShouldBe_Fields($int) {
        $this->resource->thenThereShouldBe_Of($int, 'form/field');
    }

    private function thenField_ShouldHaveTheLabel($int, $string) {
        $int--;
        $this->resource->then_ShouldBe("form/field/$int/label", $string);
    }

    private function thenField_ShouldBeRenderedAs($int, $string) {
        $int--;
        $this->resource->then_ShouldBe("form/field/$int/control", $string);
    }

}