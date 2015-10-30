<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart
 * @author      Andrew Smith
 * @link        http://www.slimframework.com
 * @copyright   2013 Josh Lockhart
 * @version     0.1.3
 * @package     SlimViews
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Slim\Views;

use Slim\Slim;

class TwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'slim';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('urlFor', array($this, 'urlFor')),
            new \Twig_SimpleFunction('baseUrl', array($this, 'base')),
            new \Twig_SimpleFunction('siteUrl', array($this, 'site')),
            new \Twig_SimpleFunction('currentUrl', array($this, 'currentUrl')),
        );
    }
    public function getFilters(){
        return array(
            new \Twig_SimpleFilter('to_json', array($this, 'toJSON')),
            new \Twig_SimpleFilter('from_json', array($this, 'fromJSON')),
            new \Twig_SimpleFilter('to_attributes', array($this, 'toAttributes', array('pre_escape'=>'html','is_safe' => array('html'))))
            
        );    
    }
    public function toJSON($data=null)
    {
        return json_encode ($data);
    }
    public function toAttributes($data=null)
    {
      $ars=array();
      if(is_array($data)){
      	foreach($data as $a=>$b){
      		$ars[]=$a.'="'. htmlspecialchars ($b) .'"';
      	}
      }
      return implode(' ',$ars);
    }    
    public function fromJSON($data=null)
    {
        return json_decode($data,true);
    }    
    public function urlFor($name, $params = array(), $appName = 'default')
    {
        return Slim::getInstance($appName)->urlFor($name, $params);
    }

    public function site($url='', $action=false, $withUri = true, $appName = 'default')
    {
    	if($url){
			$url='/'. ltrim($url, '/');    	
    	}
    	$base=$this->base('', $action, $withUri, $appName, true);
        return $base . $url;
    }

    public function base($path='',$action=false, $withUri = true, $appName = 'default',$full=false)
    {
    
    	$app=Slim::getInstance($appName);
        $req = $app->request();
        $uri = $req->getUrl();
		$u=$app->config('use_query_string');
        if ($withUri) {
            $uri .= $req->getRootUri();
        }
  
        $bname='/' . basename($app->request->getScriptName());
        $i=strrpos($uri,$bname);
        if($i!==false){
        	if($u){
        		if($full){
        			$uri=substr($uri,0,$i+strlen($bname));
        		}else{
        			$uri=substr($uri,$i+1);
        		}
	        }else{	        	
	        	$uri=substr($uri,0,$i+strlen($bname));	
        	}
        	if(!$action){
    			$uri=dirname($uri);
        	}
        	$uri=trim($uri,'/');
        	if($uri=='.') $uri='';
        }
        if($path){
        	if($u && $action){
        		
        		$path='?r=' . ('/'. trim($path,'/'));
        	}else{
        		$path='/' . trim($path,'/');
        	}
        	if($uri==''){
        	$path=trim($path,'/');
        	}
        }
        return $uri . $path;
    }
	
    public function currentUrl($withQueryString = true, $appName = 'default')
    {
        $app = Slim::getInstance($appName);
        $req = $app->request();
        $uri = $req->getUrl() . $req->getPath();

	
        if ($withQueryString) {
            $env = $app->environment();

            if ($env['QUERY_STRING']) {
                $uri .= '?' . $env['QUERY_STRING'];
            }
        }

        return $uri;
    }
}
