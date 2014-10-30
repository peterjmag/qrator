<?php
namespace watoki\cqurator;

use watoki\cqurator\Representer;
use watoki\cqurator\representer\GenericActionRepresenter;
use watoki\cqurator\representer\GenericEntityRepresenter;
use watoki\cqurator\representer\GenericRepresenter;

class RepresenterRegistry {

    /** @var array|Representer[] */
    private $representers = [];

    /**
     * @param string|null $class
     * @param Representer $representer
     */
    public function register($class, Representer $representer) {
        $this->representers[$class] = $representer;
    }

    /**
     * @param string|null $class
     * @throws \Exception
     * @return EntityRepresenter
     */
    public function getEntityRepresenter($class) {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset($this->representers[$class])) {
            $representer = $this->representers[$class];
            if (!($representer instanceof EntityRepresenter)) {
                throw new \Exception("Class [" . get_class($representer) . "] needs to implement [" . EntityRepresenter::class . "].");
            }
            return $representer;
        } else {
            return new GenericEntityRepresenter();
        }
    }

    /**
     * @param string|null $class
     * @throws \Exception
     * @return ActionRepresenter
     */
    public function getActionRepresenter($class) {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset($this->representers[$class])) {
            $representer = $this->representers[$class];
            if (!($representer instanceof ActionRepresenter)) {
                throw new \Exception("Class [" . get_class($representer) . "] needs to implement [" . ActionRepresenter::class . "].");
            }
            return $representer;
        } else {
            return new GenericActionRepresenter();
        }
    }
}