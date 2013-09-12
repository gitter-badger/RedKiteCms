<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Core\Deploy;

use RedKiteLabs\RedKiteCmsBundle\Core\Deploy\AlTwigDeployerStage;
use org\bovigo\vfs\vfsStream;

/**
 * AlTwigDeployerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlTwigDeployerStageTest extends AlTwigDeployer
{
    protected function setUp()
    {
        $this->templatesFolder = 'RedKiteCmsStage';
        $this->siteRoutingFile = 'site_routing_stage.yml';
        $this->assetsFolder = 'root\AcmeWebSiteBundle\Resources\public\stage\\';
        
        parent::setUp();
    }
    
    protected function checkSiteMap($seo)
    {
        $sitemapFile = vfsStream::url('root\sitemap.xml');
        $this->assertFileNotExists($sitemapFile);
    }
    
    protected function getDeployer()
    {
        return new AlTwigDeployerStage($this->container);    
    }
    
    protected function initContainer()
    {
        parent::initContainer();
        
        $this->container->expects($this->at(9))
            ->method('getParameter')
            ->with('red_kite_labs_theme_engine.deploy.stage_templates_folder')
            ->will($this->returnValue($this->templatesFolder));
         
        
        $this->container->expects($this->at(18))
            ->method('get')
            ->with('red_kite_cms.block_manager_factory')
            ->will($this->returnValue($this->blockManagerFactory));

        $this->container->expects($this->at(19))
            ->method('getParameter')
            ->with('red_kite_cms.deploy_bundle.views_dir')
            ->will($this->returnValue('Resources/views'));
        
        $this->container->expects($this->at(20))
            ->method('get')
            ->with('red_kite_cms.url_manager_stage')
            ->will($this->returnValue($this->urlManager));
                
        $this->container->expects($this->at(21))
            ->method('get')
            ->with('red_kite_cms.themes_collection_wrapper')
            ->will($this->returnValue($this->themesCollectionWrapper));
        
        $this->containerAtSequenceAfterObjectCreation = 22;
    }
    
    protected function buildExpectedRoutes($seo)
    {
        $routes = array();
        foreach($seo as $seoAttributes) {
            $language = $seoAttributes["language"];
            $page = $seoAttributes["page"];
            $permalink = ( ! $seoAttributes["homepage"]) ? $seoAttributes["permalink"] : "" ;
            $siteRouting = "# Route << %s >> generated for language << %s >> and page << %s >>\n";
            $siteRouting .= "_stage_%s_%s:\n";
            $siteRouting .= "  pattern: /%s\n";
            $siteRouting .= "  defaults: { _controller: AcmeWebSiteBundle:WebSite:stage, _locale: %s, page: %s }\n";

            $routes[] = sprintf($siteRouting, $permalink, $language, $page, $language, str_replace('-', '_', $page), $permalink, $language, $page);
        }
        
        $siteRouting = "# Route <<  >> generated for language << en >> and page << index >>\n";
        $siteRouting .= "stage_home:\n";
        $siteRouting .= "  pattern: /\n";
        $siteRouting .= "  defaults: { _controller: AcmeWebSiteBundle:WebSite:stage, _locale: en, page: index }";
        
        $routes[] = $siteRouting;
        
        return (implode("\n", $routes));
    }
}
