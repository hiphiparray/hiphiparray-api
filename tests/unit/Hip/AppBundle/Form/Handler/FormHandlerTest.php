<?php

use \Hip\Content\Form\Handler\ContentFormHandler;
use \Hip\Content\Form\Type\ContentType;

/**
 * Class FormHandlerTest
 *
 * "extends \Codeception\TestCase\Test" so we have easy access to the service container
 */
class FormHandlerTest extends \Codeception\TestCase\Test
{
    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $serviceContainer;
    /**
     * @var Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->serviceContainer = $this->getModule('Symfony2')->container;
        $this->formFactory = $this->serviceContainer->get('form.factory');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // tests

    public function testCanGrabFromServiceContainer()
    {
        static::assertInstanceOf(
            'Hip\Content\Form\Handler\ContentFormHandler',
            $this->serviceContainer->get('hip.app_bundle.content_form_handler')
        );
    }


    /**
     * @expectedException TypeError
     * @expectedExceptionMessageRegExp /must be of the type string, object given/
     */
    public function testFormHandlerThrowsWhenGivenInvalidFormType()
    {
        new ContentFormHandler($this->getMockEntityManager(), $this->formFactory, new \stdClass());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessageRegExp /Content, instance of stdClass given/
     */
    public function testProcessFormThrowsWhenGivenInvalidObjectForAGivenFormType()
    {
        $formHandler = new ContentFormHandler($this->getMockEntityManager(), $this->formFactory, ContentType::class);
        $formHandler->processForm(new \stdClass(), [], 'POST');
    }


    /**
     * @expectedException Hip\AppBundle\Exception\InvalidFormException
     */
    public function testProcessFormReturnsWithErrorsWhenFormIsNotValid()
    {
        /**
         * Get Form
         */
        $mockForm = $this->getMockForm();

        /**
         * Create Form
         */
        /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
        $formFactory = $this->getCreatedMockForm($mockForm);

        /**
         * Process Form
         */
        $formHandler = new ContentFormHandler($this->getMockEntityManager(), $formFactory, ContentType::class);
        $formHandler->processForm(new \Hip\AppBundle\Entity\Content(), [], 'POST');
    }

    public function testProcessFormReturnsValidObjectOnSuccess()
    {
        $formHandler = new ContentFormHandler($this->getMockEntityManager(), $this->formFactory, ContentType::class);

        $parameters = ['title' => 'main title', 'body' => 'yada yada yada'];
        static::assertInstanceOf(
            '\Hip\AppBundle\Entity\Content',
            $formHandler->processForm(new \Hip\AppBundle\Entity\Content(), $parameters, 'POST')
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getMockEntityManager()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockForm()
    {
        $mockForm = $this->getMock('Symfony\Component\Form\FormInterface');
        $mockForm
            ->expects(static::once())
            ->method('submit');
        $mockForm
            ->expects(static::once())
            ->method('isValid')
            ->will(static::returnValue(false));
        return $mockForm;
    }

    /**
     * @param $mockForm
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getCreatedMockForm($mockForm)
    {
        $formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $formFactory
            ->expects(static::once())
            ->method('create')
            ->will(static::returnValue($mockForm));
        return $formFactory;
    }
}
