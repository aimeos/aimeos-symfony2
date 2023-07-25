<?php

namespace Aimeos\ShopBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class CatalogControllerTest extends WebTestCase
{
	protected function setUp() : void
	{
		\Aimeos\MShop::cache( false );
		\Aimeos\Controller\Frontend::cache( false );
	}


	public function testCount()
	{
		$client = static::createClient();
		$client->request( 'GET', '/unittest/de/EUR/shop/count?count=tree' );
		$content = $client->getResponse()->getContent();

		$this->assertStringStartsWith( '{"', $content );
	}


	public function testFilterSearch()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$this->assertEquals( 1, $crawler->filter( '.catalog-filter-search' )->count() );

		$form = $crawler->filter( '.catalog-filter-search button' )->form();
		$form['f_search'] = 'Cafe';
		$crawler = $client->submit( $form );

		$this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Cappuccino")' )->count() );
	}


 	public function testFilterTree()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$this->assertEquals( 1, $crawler->filter( '.catalog-filter-tree' )->count() );

		$link = $crawler->filter( '.catalog-filter-tree .coffee a.cat-link' )->link();
		$crawler = $client->click( $link );

		$this->assertEquals( 3, $crawler->filter( '.catalog-stage-breadcrumb li' )->count() );
		$this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->count() );
	}


	public function testFilterAttribute()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$this->assertEquals( 1, $crawler->filter( '.catalog-filter-attribute' )->count() );

		$nodes = $crawler->filter( '.catalog-filter-attribute .attr-size span:contains("XS")' );
		$id = $nodes->closest( '.attr-item' )->attr( 'data-id' );

		$form = $crawler->filter( '.catalog-filter .btn-primary' )->form();
		$values = $form->getPhpValues();
		$values['f_attrid'] = array( $id );
		$crawler = $client->request( $form->getMethod(), $form->getUri(), $values, $form->getPhpFiles() );

		$this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->count() );
		$this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Cappuccino")' )->count() );
	}


	public function testHome()
	{
		$client = static::createClient();
		$client->request( 'GET', '/unittest/de/EUR/' );
		$content = $client->getResponse()->getContent();

		$this->assertStringContainsString( 'aimeos catalog-home', $content );
	}


	public function testStageBreadcrumb()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->link();
		$crawler = $client->click( $link );

		$this->assertEquals( 1, $crawler->filter( '.catalog-stage-breadcrumb li' )->count() );

		$link = $crawler->filter( '.catalog-stage-breadcrumb a' )->link();
		$crawler = $client->click( $link );

		// breadcrumb works differently in current versions
		// $this->assertEquals( 1, $crawler->filter( '.catalog-list' )->count() );
		// $this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->count() );
		// $this->assertEquals( 1, $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Cappuccino")' )->count() );
	}


	public function testStageNavigator()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list .pagination .option-name' )->link();
		$crawler = $client->click( $link );

		$link = $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Cappuccino")' )->link();
		$crawler = $client->click( $link );

		$this->assertEquals( 1, $crawler->filter( '.catalog-detail' )->count() );
		$this->assertEquals( 1, $crawler->filter( '.catalog-detail:contains("Cafe Noire Cappuccino")' )->count() );

		$link = $crawler->filter( '.catalog-detail-navigator a.next' )->link();
		$crawler = $client->click( $link );

		$this->assertEquals( 1, $crawler->filter( '.catalog-detail' )->count() );
		$this->assertEquals( 1, $crawler->filter( '.catalog-detail:contains("Cafe Noire Expresso")' )->count() );

		$link = $crawler->filter( '.catalog-detail-navigator a.prev' )->link();
		$crawler = $client->click( $link );

		$this->assertEquals( 1, $crawler->filter( '.catalog-detail' )->count() );
		$this->assertEquals( 1, $crawler->filter( '.catalog-detail:contains("Cafe Noire Cappuccino")' )->count() );
	}


	public function testListSortationName()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list .pagination .option-name' )->link();
		$crawler = $client->click( $link );

		$products = $crawler->filter( '.catalog-list-items .product' );
		$this->assertEquals( 1, $products->eq( 0 )->filter( 'h2:contains("Cafe Noire Cappuccino")' )->count() );
		$this->assertEquals( 1, $products->eq( 1 )->filter( 'h2:contains("Cafe Noire Expresso")' )->count() );
		$this->assertEquals( 1, $products->eq( 2 )->filter( 'h2:contains("MNOP/16 disc")' )->count() );
		$this->assertEquals( 1, $products->eq( 3 )->filter( 'h2:contains("Unittest: Bundle")' )->count() );

		$link = $crawler->filter( '.catalog-list .pagination .option-name' )->link();
		$crawler = $client->click( $link );

		$products = $crawler->filter( '.catalog-list-items .product' );
		$count = $products->count();

		$this->assertGreaterThan( 3, $count );
		$this->assertEquals( 1, $products->eq( $count - 4 )->filter( 'h2:contains("Unittest: Bundle")' )->count() );
		$this->assertEquals( 1, $products->eq( $count - 3 )->filter( 'h2:contains("MNOP/16 disc")' )->count() );
		$this->assertEquals( 1, $products->eq( $count - 2 )->filter( 'h2:contains("Cafe Noire Expresso")' )->count() );
		$this->assertEquals( 1, $products->eq( $count - 1 )->filter( 'h2:contains("Cafe Noire Cappuccino")' )->count() );
	}


	public function testListSortationPrice()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list .pagination .option-price' )->link();
		$crawler = $client->click( $link );

		$products = $crawler->filter( '.catalog-list-items .product' );
		$count = $products->count();

		$this->assertGreaterThan( 2, $count );
		$this->assertEquals( 1, $products->eq( $count - 2 )->filter( '.value:contains("600,00 €")' )->count() );
		$this->assertEquals( 1, $products->eq( $count - 1 )->filter( '.value:contains("600,00 €")' )->count() );

		$link = $crawler->filter( '.catalog-list .pagination .option-price' )->link();
		$crawler = $client->click( $link );

		$products = $crawler->filter( '.catalog-list-items .product' );
		$this->assertEquals( 1, $products->eq( 0 )->filter( '.value:contains("600,00 €")' )->count() );
		$this->assertEquals( 1, $products->eq( 1 )->filter( '.value:contains("600,00 €")' )->count() );
	}


	public function testDetailPinned()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->link();
		$crawler = $client->click( $link );

		$form = $crawler->filter( '.catalog-detail .actions-pin' )->form();
		$crawler = $client->submit( $form );

		$this->assertEquals( 1, $crawler->filter( '.catalog-session-pinned .pinned-item' )->count() );
	}


	public function testDetailLastSeen()
	{
		$client = static::createClient();
		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$link = $crawler->filter( '.catalog-list-items .product a:contains("Cafe Noire Expresso")' )->link();
		$crawler = $client->click( $link );

		$crawler = $client->request( 'GET', '/unittest/de/EUR/shop/' );

		$this->assertEquals( 1, $crawler->filter( '.catalog-session-seen .seen-item' )->count() );
	}


	public function testSuggest()
	{
		$client = static::createClient();
		$client->request( 'GET', '/unittest/de/EUR/shop/suggest', array( 'f_search' => 'Cafe' ) );
		$content = $client->getResponse()->getContent();

		$this->assertStringStartsWith( '[{', $content );
	}


	public function testStock()
	{
		$client = static::createClient();
		$client->request( 'GET', '/unittest/de/EUR/shop/stock' );
		$content = $client->getResponse()->getContent();

		$this->assertStringContainsString( '.aimeos .product .stock', $content );
		$this->assertStringContainsString( '.aimeos .catalog-detail-basket', $content );
	}
}
