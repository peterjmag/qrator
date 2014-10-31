<?php
namespace watoki\cqurator\representer;

use watoki\collections\Map;
use watoki\cqurator\ActionRepresenter;
use watoki\cqurator\form\Field;
use watoki\cqurator\form\fields\StringField;
use watoki\factory\Factory;

class GenericActionRepresenter extends GenericRepresenter implements ActionRepresenter {

    /** @var array|Field[] */
    private $fields = [];

    /** @var Factory */
    private $factory;

    /**
     * @param Factory $factory <-
     */
    public function __construct(Factory $factory) {
        $this->factory = $factory;
    }

    /**
     * @param object|string $action
     * @return array|\watoki\cqurator\form\Field[]
     */
    public function getFields($action) {
        $fields = [];
        foreach ($this->getProperties($action) as $property) {
            if (!$property->canSet() || $property->name() == 'id') {
                continue;
            }

            $field = $this->getField($property->name());
            $fields[] = $field;

            if ($property->canGet()) {
                $field->setValue($property->get());
            }

            if ($property->isRequired()) {
                $field->setRequired(true);
            }
        }
        return $fields;
    }

    /**
     * @param $name
     * @return Field
     */
    public function getField($name) {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        return new StringField($name);
    }

    /**
     * @param string $name
     * @param Field $field
     */
    public function setField($name, Field $field) {
        $this->fields[$name] = $field;
    }

    /**
     * @param string $class
     * @param Map $args
     * @internal param $action
     * @return object
     */
    public function create($class, Map $args) {
        $action = $this->factory->getInstance($class, $args->toArray());

        foreach ($this->getProperties($action) as $property) {
            if ($property->canSet() && $args->has($property->name())) {
                $value = $args->get($property->name());
                $inflated = $this->getField($property->name())->inflate($value);
                $property->set($inflated);
            }
        }

        return $action;
    }

    public function hasMissingProperties($object) {
        foreach ($this->getProperties($object) as $property) {
            if ($property->canGet() && $property->get() === null) {
                return true;
            }
        }
        return false;
    }
}