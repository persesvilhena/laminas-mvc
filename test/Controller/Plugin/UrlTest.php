<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\Url as UrlPlugin;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\Literal as LiteralRoute;
use Laminas\Mvc\Router\Http\Segment as SegmentRoute;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\Mvc\Router\SimpleRouteStack;
use LaminasTest\Mvc\Controller\TestAsset\SampleController;
use PHPUnit_Framework_TestCase as TestCase;

class UrlTest extends TestCase
{
    public function setUp()
    {
        $router = new SimpleRouteStack;
        $router->addRoute('home', LiteralRoute::factory(array(
            'route'    => '/',
            'defaults' => array(
                'controller' => 'LaminasTest\Mvc\Controller\TestAsset\SampleController',
            ),
        )));
        $this->router = $router;

        $event = new MvcEvent();
        $event->setRouter($router);

        $this->controller = new SampleController();
        $this->controller->setEvent($event);

        $this->plugin = $this->controller->plugin('url');
    }

    public function testPluginCanGenerateUrlWhenProperlyConfigured()
    {
        $url = $this->plugin->fromRoute('home');
        $this->assertEquals('/', $url);
    }

    public function testPluginWithoutControllerRaisesDomainException()
    {
        $plugin = new UrlPlugin();
        $this->setExpectedException('Laminas\Mvc\Exception\DomainException', 'requires a controller');
        $plugin->fromRoute('home');
    }

    public function testPluginWithoutControllerEventRaisesDomainException()
    {
        $controller = new SampleController();
        $plugin     = $controller->plugin('url');
        $this->setExpectedException('Laminas\Mvc\Exception\DomainException', 'event compose a router');
        $plugin->fromRoute('home');
    }

    public function testPluginWithoutRouterInEventRaisesDomainException()
    {
        $controller = new SampleController();
        $event      = new MvcEvent();
        $controller->setEvent($event);
        $plugin = $controller->plugin('url');
        $this->setExpectedException('Laminas\Mvc\Exception\DomainException', 'event compose a router');
        $plugin->fromRoute('home');
    }

    public function testPluginWithoutRouteMatchesInEventRaisesExceptionWhenNoRouteProvided()
    {
        $this->setExpectedException('Laminas\Mvc\Exception\RuntimeException', 'RouteMatch');
        $url = $this->plugin->fromRoute();
    }

    public function testPluginWithRouteMatchesReturningNoMatchedRouteNameRaisesExceptionWhenNoRouteProvided()
    {
        $event = $this->controller->getEvent();
        $event->setRouteMatch(new RouteMatch(array()));
        $this->setExpectedException('Laminas\Mvc\Exception\RuntimeException', 'matched');
        $url = $this->plugin->fromRoute();
    }

    public function testPassingNoArgumentsWithValidRouteMatchGeneratesUrl()
    {
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('home');
        $this->controller->getEvent()->setRouteMatch($routeMatch);
        $url = $this->plugin->fromRoute();
        $this->assertEquals('/', $url);
    }

    public function testCanReuseMatchedParameters()
    {
        $this->router->addRoute('replace', SegmentRoute::factory(array(
            'route'    => '/:controller/:action',
            'defaults' => array(
                'controller' => 'LaminasTest\Mvc\Controller\TestAsset\SampleController',
            ),
        )));
        $routeMatch = new RouteMatch(array(
            'controller' => 'foo',
        ));
        $routeMatch->setMatchedRouteName('replace');
        $this->controller->getEvent()->setRouteMatch($routeMatch);
        $url = $this->plugin->fromRoute('replace', array('action' => 'bar'), array(), true);
        $this->assertEquals('/foo/bar', $url);
    }

    public function testCanPassBooleanValueForThirdArgumentToAllowReusingRouteMatches()
    {
        $this->router->addRoute('replace', SegmentRoute::factory(array(
            'route'    => '/:controller/:action',
            'defaults' => array(
                'controller' => 'LaminasTest\Mvc\Controller\TestAsset\SampleController',
            ),
        )));
        $routeMatch = new RouteMatch(array(
            'controller' => 'foo',
        ));
        $routeMatch->setMatchedRouteName('replace');
        $this->controller->getEvent()->setRouteMatch($routeMatch);
        $url = $this->plugin->fromRoute('replace', array('action' => 'bar'), true);
        $this->assertEquals('/foo/bar', $url);
    }
}
