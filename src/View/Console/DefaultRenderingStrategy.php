<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\View\Console;

use Laminas\Console\Response as ConsoleResponse;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\View\Model\ConsoleModel as ConsoleViewModel;
use Laminas\View\Model\ModelInterface as ViewModel;
use Laminas\View\View;

/**
 * @category   Laminas
 * @package    Laminas_Mvc
 * @subpackage View
 */
class DefaultRenderingStrategy implements ListenerAggregateInterface
{
    /**
     * @var \Laminas\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'render'), -10000);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Render the view
     *
     * @param  MvcEvent $e
     * @return Response
     */
    public function render(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return $result; // the result is already rendered ...
        }

        // <artial arguments
        $response  = $e->getResponse();

        if (empty($result)) {
            /**
             * There is absolutely no result, so there's nothing to display.
             * We will return an empty response object
             */
            return $response;
        }

        // Collect results from child models
        $responseText = '';
        if ($result->hasChildren()) {
            /* @var $child ViewModel */
            foreach ($result->getChildren() as $child) {
                // Do not use ::getResult() method here as we cannot be sure if children are also console models.
                $responseText .= $child->getVariable(ConsoleViewModel::RESULT);
            }
        }

        // Fetch result from primary model
        $responseText .= $result->getResult();

        // Append console response to response object
        $response->setContent(
            $response->getContent() . $responseText
        );

        // Pass on console-specific options
        if (
            $response  instanceof ConsoleResponse &&
            $result    instanceof ConsoleViewModel
        ) {
            /* @var $response ConsoleResponse */
            /* @var $result ConsoleViewModel */
            $errorLevel = $result->getErrorLevel();
            $response->setErrorLevel($errorLevel);
        }

        return $response;
    }
}
