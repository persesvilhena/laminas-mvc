<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\View\Console;

use Laminas\Console\Request as ConsoleRequest;
use Laminas\EventManager\EventManagerInterface as Events;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\MvcEvent;

class InjectNamedConsoleParamsListener implements ListenerAggregateInterface
{
    /**
     * Listeners we've registered
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectNamedParams'), -80);
    }

    /**
     * Detach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function detach(Events $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    /**
     * Inspect the result, and cast it to a ViewModel if a string is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function injectNamedParams(MvcEvent $e)
    {
        if (!$routeMatch = $e->getRouteMatch()) {
            return; // cannot work without route match
        }

        $request = $e->getRequest();
        if (!$request instanceof ConsoleRequest) {
            return; // will not inject non-console requests
        }

        // Inject route match params into request
        $params = array_merge(
            $request->getParams()->toArray(),
            $routeMatch->getParams()
        );
        $request->getParams()->fromArray($params);
    }

}
