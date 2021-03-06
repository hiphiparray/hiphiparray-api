<?php

namespace Hip\AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Hip\AppBundle\Entity\Content;
use Hip\AppBundle\Exception\InvalidFormException;

/**
 * Class PostController
 *
 * By using "implements ClassResourceInterface" we can omit the Class name from the action methods
 * "class PostController extends FOSRestController implements ClassResourceInterface"
 * For example, "getAction" instead of "getPostAction" and "cgetAction" instead of "getPostsAction"
 * see: http://symfony.com/doc/master/bundles/FOSRestBundle/5-automatic-route-generation_single-restful-controller.html#implicit-resource-name-definition
 *
 * Using this controller as the routing.yml resource, will tell Symfony to automatically generate proper REST routes
 * from this controller action names.
 * Notice "type: rest" option (in routing.yml) is required so that the RestBundle can find which routes are supported.
 * see: http://symfony.com/doc/master/bundles/FOSRestBundle/5-automatic-route-generation_single-restful-controller.html#single-restful-controller-routes
 *
 * @package Hip\AppBundle\Controller
 */
class PostController extends FOSRestController
{

    /**
     * Returns content when given a valid id
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retrieves blog post content by id",
     *  output = "Hip\Content\ValueObject\BlogPost",
     *  section="Blog Posts",
     *  statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the requested Content is not found"
     *     }
     * )
     *
     * @View()
     *
     * @param $id
     *
     * @return \Hip\Content\ValueObject\BlogPost
     *
     * @throws NotFoundHttpException
     */
    public function getPostAction($id)
    {
        return $this->get('hip.app_bundle.content_provider')->getPostContent($id);
    }

    /**
     * Returns a collection of Contents filtered by optional criteria
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Returns a collection of Blog Posts",
     *  section="Blog Posts",
     *  requirements={
     *      {"name"="limit", "dataType"="integer", "requirement"="\d+", "description"="the max number of records to return"}
     *  },
     *  parameters={
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="the max number of records to return"},
     *      {"name"="offset", "dataType"="integer", "required"=false, "description"="the record number to start results at"}
     *  }
     * )
     *
     * @View()
     *
     * @QueryParam(name="limit", requirements="\d+", default="10", description="our limit")
     * @QueryParam(name="offset", requirements="\d+", nullable=true, default="0", description="our offset")
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     */
    public function getPostsAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        /**
         * Ensure "fos_rest: param_fetcher_listener: true" is set in the config.xml to allow for paramFetcher
         * see https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/3-listener-support.rst
         */
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        return $this->get('hip.app_bundle.content_provider')->getBlogPostsSummary($limit, $offset);
    }

}
