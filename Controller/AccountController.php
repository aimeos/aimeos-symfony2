<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @copyright Aimeos (aimeos.org), 2014-2016
 * @package symfony
 * @subpackage Controller
 */


namespace Aimeos\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Response;


/**
 * Aimeos controller for account related functionality.
 *
 * @package symfony
 * @subpackage Controller
 */
class AccountController extends AbstractController
{
	/**
	 * Returns the html for the "My account" page.
	 *
	 * @return Response Response object containing the generated output
	 */
	public function indexAction()
	{
		$params = [];
		$shop = $this->container->get( 'shop' );

		foreach( $this->container->getParameter( 'aimeos_shop.page' )['account-index'] as $name )
		{
			$params['aiheader'][$name] = $shop->get( $name )->getHeader();
			$params['aibody'][$name] = $shop->get( $name )->getBody();
		}

		return $this->render( 'AimeosShopBundle:Account:index.html.twig', $params )->setPrivate()->setMaxAge( 300 );
	}


	/**
	 * Returns the html for the "My account" download page.
	 *
	 * @return Response Response object containing the generated output
	 */
	public function downloadAction()
	{
		$response = $this->container->get( 'shop' )->get( 'account/download' )->getView()->response();
		return Response::create( (string) $response->getBody(), $response->getStatusCode(), $response->getHeaders() );
	}


	/**
	 * Returns the output of the account favorite component
	 *
	 * @return Response Response object containing the generated output
	 */
	public function favoriteComponentAction()
	{
		return $this->getOutput( 'account/favorite' );
	}


	/**
	 * Returns the output of the account history component
	 *
	 * @return Response Response object containing the generated output
	 */
	public function historyComponentAction()
	{
		return $this->getOutput( 'account/history' );
	}


	/**
	 * Returns the output of the account profile component
	 *
	 * @return Response Response object containing the generated output
	 */
	public function profileComponentAction()
	{
		return $this->getOutput( 'account/profile' );
	}


	/**
	 * Returns the output of the account watch component
	 *
	 * @return Response Response object containing the generated output
	 */
	public function watchComponentAction()
	{
		return $this->getOutput( 'account/watch' );
	}
}
