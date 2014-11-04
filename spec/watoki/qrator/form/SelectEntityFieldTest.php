<?php
namespace spec\watoki\qrator\form;

use watoki\qrator\form\fields\SelectEntityField;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\qrator\fixtures\FieldFixture field <-
 * @property \spec\watoki\qrator\fixtures\RegistryFixture registry <-
 * @property \spec\watoki\qrator\fixtures\DispatcherFixture dispatcher <-
 * @property \spec\watoki\qrator\fixtures\ClassFixture class <-
 */
class SelectEntityFieldTest extends Specification {

    protected function background() {
        $this->class->givenTheClass('EntityClass');
        $this->class->givenTheClass('ListEntity');
        $this->dispatcher->givenIAddedTheClosure_AsHandlerFor(function () {
            return [];
        }, 'ListEntity');
    }

    function testEmptyEntityList() {
        $this->registry->givenIRegisteredAnEntityRepresenterFor('EntityClass');
        $this->givenASelectEntityField_WithTheListAction('test', 'ListEntity');

        $this->field->whenIRenderTheField();
        $this->field->thenTheOutputShouldBe(
            '<select class="form-control" name="args[test]">
            </select>');
    }

    function testShowEntityOptions() {
        $this->class->givenTheClass_WithTheBody('NamedEntity', '
            function __construct($name) { $this->name = $name; }
            function getId() { return strtolower($this->name); }
        ');
        $this->registry->givenIRegisteredAnEntityRepresenterFor('NamedEntity');
        $this->givenASelectEntityField_WithTheListAction('test', 'ListEntity');


        $this->class->givenTheClass_WithTheBody('EntityHandler', '
            function listEntity() {
                return [
                    new NamedEntity("Bart"),
                    new NamedEntity("Lisa"),
                ];
            }
        ');
        $this->dispatcher->givenIAddedTheClass_AsHandlerFor('EntityHandler', 'ListEntity');

        $this->field->whenIRenderTheField();
        $this->field->thenTheOutputShouldBe(
            '<select class="form-control" name="args[test]">
                <option value="bart">Named Entity [name:Bart|id:bart]</option>
                <option value="lisa">Named Entity [name:Lisa|id:lisa]</option>
            </select>');
    }

    ################################################################################################

    private function givenASelectEntityField_WithTheListAction($name, $action) {
        $this->registry->givenIRegisteredAnActionRepresenterFor($action);
        $this->field->givenTheField(new SelectEntityField($name, new $action, $this->registry->registry));
    }

} 