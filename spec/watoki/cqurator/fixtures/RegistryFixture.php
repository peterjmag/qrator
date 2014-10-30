<?php
namespace spec\watoki\cqurator\fixtures;

use watoki\cqurator\representer\GenericActionRepresenter;
use watoki\cqurator\representer\GenericEntityRepresenter;
use watoki\cqurator\representer\GenericRepresenter;
use watoki\cqurator\RepresenterRegistry;
use watoki\scrut\Fixture;

/**
 * @property ClassFixture class <-
 */
class RegistryFixture extends Fixture {

    /** @var RepresenterRegistry */
    public $registry;

    /** @var GenericRepresenter[] */
    public $representers = array();

    public function setUp() {
        parent::setUp();
        $this->registry = new RepresenterRegistry();
    }

    public function givenIRegisteredAnActionRepresenterFor($class) {
        $this->representers[$class] = new GenericActionRepresenter($this->spec->factory);
        $this->registry->register($class, $this->representers[$class]);
    }

    public function givenIRegisteredAnEntityRepresenterFor($class) {
        $this->representers[$class] = new GenericEntityRepresenter($this->spec->factory);
        $this->registry->register($class, $this->representers[$class]);
    }

    public function givenIAddedTheQuery_ToTheRepresenterOf($query, $class) {
        $this->class->givenTheClass($query);
        $this->representers[$class]->addQuery($query);
    }

    public function givenIAddedTheCommand_ToTheRepresenterOf($command, $class) {
        $this->class->givenTheClass($command);
        $this->representers[$class]->addCommand($command);
    }

    public function givenIHaveTheTheRenderer_For($callable, $class) {
        $this->representers[$class]->setRenderer($callable);
    }

} 