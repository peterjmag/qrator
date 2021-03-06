<?php
namespace watoki\qrator;

use watoki\factory\Factory;
use watoki\qrator\representer\generic\GenericActionRepresenter;
use watoki\qrator\representer\generic\GenericEntityRepresenter;
use watoki\qrator\Representer;
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
        $factory->setSingleton(get_class($this), $this);

        $this->register((new GenericActionRepresenter(RootAction::class, $factory))
            ->setHandler(function () {
                return new RootEntity();
            })
            ->setName('Home'));

        $this->register((new GenericEntityRepresenter(RootEntity::class))
            ->setName('Qrator'));

        $this->register((new GenericEntityRepresenter(\DateTime::class))
            ->setStringifier(function (\DateTime $d) {
                return $d->format('Y-m-d H:i:s');
            }));
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
     * @return \watoki\qrator\Representer
     */
    public function register(Representer $representer) {
        $this->representers[$representer->getClass()] = $representer;
        return $representer;
    }

    /**
     * @param string $class Full name of the class
     * @param string $method Name of the method
     * @return MethodActionRepresenter
     */
    public function registerActionMethod($class, $method) {
        return $this->register(new MethodActionRepresenter($class, $method, $this->factory));
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