<?php
namespace watoki\cqurator\web;

use watoki\cqurator\ActionDispatcher;
use watoki\cqurator\RepresenterRegistry;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\Responder;
use watoki\deli\Request;
use watoki\factory\Factory;

class QueryResource extends ActionResource {

    const TYPE = 'query';
    const LAST_QUERY_COOKIE = 'lastQuery';

    /** @var \watoki\curir\cookie\CookieStore */
    private $cookies;

    /**
     * @param Factory $factory <-
     * @param ActionDispatcher $dispatcher <-
     * @param RepresenterRegistry $registry <-
     * @param \watoki\curir\cookie\CookieStore $cookies <-
     */
    function __construct(Factory $factory, ActionDispatcher $dispatcher, RepresenterRegistry $registry, CookieStore $cookies) {
        parent::__construct($factory, $registry, $dispatcher);
        $this->cookies = $cookies;
    }

    /**
     * @param Request $request <-
     * @param string $query
     * @return array
     */
    public function doGet(Request $request, $query) {
        $this->cookies->create(new Cookie([
            'action' => $query,
            'arguments' => $request->getArguments()->toArray()
        ]), self::LAST_QUERY_COOKIE);

        $result = $this->doAction($this->dispatcher, $request, $query, self::TYPE);

        if ($result instanceof Responder) {
            return $result;
        }

        return [
            'entity' => $this->assembleResult($result)
        ];
    }

    private function assembleResult($result) {
        if (is_array($result)) {
            return array_map(function ($entity) {
                return $this->assembleEntity($entity);
            }, $result);
        } else if (is_object($result)) {
            return $this->assembleEntity($result);
        } else {
            throw new \InvalidArgumentException("Action had no displayable result: " . var_export($result, true));
        }
    }

    private function assembleEntity($entity) {
        $representer = $this->registry->getRepresenter(get_class($entity));
        return [
            'name' => $representer->toString($entity),
            'properties' => $this->assembleProperties($entity),
            'queries' => $this->assembleQueries($entity),
            'commands' => $this->assembleCommands($entity)
        ];
    }

    private function assembleProperties($object) {
        $properties = [];

        $representer = $this->registry->getRepresenter(get_class($object));
        foreach ($representer->getProperties($object) as $property) {
            if ($property->canGet()) {
                $properties[] = $this->assembleProperty($property->name, $property->get());
            }
        }

        return $properties ? [
            'property' => $properties
        ] : null;
    }

    private function assembleProperty($name, $value) {
        if (is_object($value)) {
            $representer = $this->registry->getRepresenter(get_class($value));
            $value = $representer->render($value);
        }
        return [
            'name' => $name,
            'value' => $value
        ];
    }

    private function assembleQueries($object) {
        $class = get_class($object);
        $queries = $this->registry->getRepresenter($class)->getQueries();
        return $this->assembleActions($queries, $object, self::TYPE);
    }

    private function assembleCommands($object) {
        $class = get_class($object);
        $commands = $this->registry->getRepresenter($class)->getCommands();
        return $this->assembleActions($commands, $object, CommandResource::TYPE);
    }

    private function assembleActions($actions, $object, $type) {
        if (!$actions) {
            return null;
        }

        $representer = $this->registry->getRepresenter(get_class($object));
        $id = $representer->getId($object);

        return [
            'action' => array_map(function ($query) use ($type, $id) {
                return [
                    'name' => $query,
                    'link' => [
                        'href' => "$type?action=$query"
                            . ($type == self::TYPE ? '' : '&do=post')
                            . ($id ? '&id=' . $id : '')
                    ]
                ];
            }, $actions),
        ];
    }
}