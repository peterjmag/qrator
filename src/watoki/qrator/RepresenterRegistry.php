<?php
namespace watoki\qrator;

use watoki\qrator\Representer;
use watoki\qrator\representer\generic\GenericActionRepresenter;
use watoki\qrator\representer\generic\GenericEntityRepresenter;
use watoki\factory\Factory;
use watoki\qrator\representer\MethodActionRepresenter;

class RepresenterRegistry {

    /** @var array|Representer[] */
    private $representers = [];

    /** @var Factory */
    private $factory;

    /**
     * @param Factory $factory <-
     */
    public function __construct(Factory $factory) {
        $this->factory = $factory;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function isRegistered($class) {
        return array_key_exists($class, $this->representers);
    }

    /**
     * @param Representer $representer
     */
    public function register(Representer $representer) {
        $this->representers[$representer->getClass()] = $representer;
    }

    /**
     * @param string $handler
     * @param array|string[] $methods
     */
    public function registerMethods($handler, $methods) {
        foreach ($methods as $method) {
            $this->register(new MethodActionRepresenter($handler, $method, $this->factory));
        }
    }

    /**
     * @param string|object|null $class
     * @throws \Exception
     * @return EntityRepresenter
     */
    public function getEntityRepresenter($class) {
        return $this->getRepresenter($class, EntityRepresenter::class, function ($class) {
            return new GenericEntityRepresenter($class);
        });
    }

    /**
     * @param string|object|null $class
     * @throws \Exception
     * @return ActionRepresenter
     */
    public function getActionRepresenter($class) {
        return $this->getRepresenter($class, ActionRepresenter::class, function ($class) {
            return new GenericActionRepresenter($class, $this->factory);
        });
    }

    private function getRepresenter($class, $interface, $defaultGenerator) {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset($this->representers[$class])) {
            $this->representers[$class] = $defaultGenerator($class);
        }

        $representer = $this->representers[$class];
        if (!is_subclass_of($representer, $interface)) {
            throw new \Exception("Class [" . get_class($representer) . "] needs to implement [" . $interface . "].");
        }
        return $representer;
    }
}