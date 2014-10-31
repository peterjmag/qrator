<?php
namespace spec\watoki\cqurator;

use watoki\collections\Map;
use watoki\cqurator\representer\GenericActionRepresenter;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\cqurator\fixtures\ClassFixture class <-
 */
class DeterminePropertiesOfObjectTest extends Specification {

    function testFindPublicPropertiesAndAccessors() {
        $this->class->givenTheClass_WithTheBody('fields\one\SomeClass', '
            public $public = "one";
            public $publicAndGetter = "two";
            public $publicAndSetter = "three";
            private $private = "four";

            function getPublicAndGetter() { return "five"; }
            function setPublicAndSetter() { }
            function getPrivate() { return "six"; }
            function getGetter() { return "seven"; }
            function setSetter() { }
        ');

        $this->whenIDetermineThePropertiesOf('fields\one\SomeClass');
        $this->thenThereShouldBe_Properties(6);
        $this->then_ShouldBeGettable('public');
        $this->then_ShouldBeSettable('public');
        $this->then_ShouldNotBeSettable('private');
        $this->then_ShouldNotBeGettable('setter');

        $this->whenIDetermineThePropertiesOfAnInstanceOf('fields\one\SomeClass');
        $this->thenThereShouldBe_Properties(6);
        $this->thenTheValueOf_ShouldBe('public', 'one');
        $this->thenTheValueOf_ShouldBe('publicAndGetter', 'two');
        $this->thenTheValueOf_ShouldBe('publicAndSetter', 'three');
        $this->thenTheValueOf_ShouldBe('private', 'six');
        $this->thenTheValueOf_ShouldBe('getter', 'seven');
        $this->then_ShouldNotBeGettable('setter');
    }

    function testFindPropertiesInConstructor() {
        $this->class->givenTheClass_WithTheBody('constructor\ClassWithConstructor', '
            public $three;
            function __construct($one, $two = null, $three = null, $four = null) {}
            function getTwo() {}
        ');

        $this->givenTheActionArgument_Is('one', 'uno');

        $this->whenIDetermineThePropertiesOfAnInstanceOf('constructor\ClassWithConstructor');
        $this->thenThereShouldBe_Properties(4);

        $this->then_ShouldBeSettable('one');
        $this->then_ShouldNotBeGettable('one');

        $this->then_ShouldBeGettable('two');
        $this->then_ShouldBeGettable('three');
        $this->then_ShouldNotBeGettable('four');
    }

    function testRequiredProperties() {
        $this->class->givenTheClass_WithTheBody('required\SomeClass', '
            public $two;
            public $three;
            public $four;
            function __construct($one, $two, $three = null) {}
        ');
        $this->givenTheActionArgument_Is('one', 'uno');
        $this->givenTheActionArgument_Is('two', 'dos');

        $this->whenIDetermineThePropertiesOfAnInstanceOf('required\SomeClass');
        $this->thenThereShouldBe_Properties(4);

        $this->then_ShouldBeRequired('one');
        $this->then_ShouldBeRequired('two');
        $this->then_ShouldBeOptional('three');
        $this->then_ShouldBeOptional('four');

        $this->whenIDetermineThePropertiesOf('required\SomeClass');
        $this->thenThereShouldBe_Properties(4);

        $this->then_ShouldBeRequired('one');
        $this->then_ShouldBeRequired('two');
        $this->then_ShouldBeOptional('three');
        $this->then_ShouldBeOptional('four');
    }

    ##################################################################################################

    private $args = [];

    /** @var \watoki\cqurator\representer\property\ObjectProperty[] */
    private $properties;

    /** @var \watoki\cqurator\ActionRepresenter */
    private $representer;

    protected function setUp() {
        parent::setUp();
        $this->representer = new GenericActionRepresenter($this->factory);
    }

    private function whenIDetermineThePropertiesOfAnInstanceOf($class) {
        $this->properties = $this->representer->getProperties($this->representer->create($class, new Map($this->args)));
        return true;
    }

    private function whenIDetermineThePropertiesOf($class) {
        $this->properties = $this->representer->getProperties($class);
        return true;
    }

    private function thenThereShouldBe_Properties($int) {
        $this->assertCount($int, $this->properties);
    }

    private function thenTheValueOf_ShouldBe($name, $value) {
        $this->assertEquals($value, $this->properties[$name]->get());
    }

    private function then_ShouldNotBeGettable($name) {
        $this->assertFalse($this->properties[$name]->canGet(), "$name should not be gettable");
    }

    private function then_ShouldBeSettable($name) {
        $this->assertTrue($this->properties[$name]->canSet(), "$name should be settable");
    }

    private function then_ShouldNotBeSettable($name) {
        $this->assertFalse($this->properties[$name]->canSet(), "$name should not be settable");
    }

    private function then_ShouldBeGettable($name) {
        $this->assertTrue($this->properties[$name]->canGet(), "$name should be gettable");
    }

    private function givenTheActionArgument_Is($key, $value) {
        $this->args[$key] = $value;
    }

    private function then_ShouldBeRequired($name) {
        $this->assertTrue($this->properties[$name]->isRequired(), "$name should be required");
    }

    private function then_ShouldBeOptional($name) {
        $this->assertFalse($this->properties[$name]->isRequired(), "$name should be optional");
    }

} 