<?php

/**
 * Smarty Plugin CacheResource APC
 *
 * Implements APC resource for the HTML cache
 *
 * @package Smarty
 * @subpackage Cacher
 * @author Monte Ohrt
 * @author Joe Balancio
 */

/**
 * This class does contain all necessary methods for the HTML cache with APC
 * @todo Add prefix to stored items. Use APCIterator to remove stored items.
 */
class Smarty_CacheResource_Apc
{
    function __construct($smarty)
    {
        $this->smarty = $smarty;
        // test if APC is present
        if(!function_exists('apc_store'))
          throw new Exception('APC Template Caching Error: APC is not installed');
    }

    /**
     * Returns the filepath of the cached template output
     *
     * @param object $_template current template
     * @return string the cache filepath
     */
    public function getCachedFilepath($_template)
    {
        return md5($_template->getTemplateResource().$_template->cache_id.$template->compile_id);
    }

    /**
    * Returns the timpestamp of the cached template output
    *
    * @param object $_template current template
    * @return integer |booelan the template timestamp or false if the file does not exist
    */
    public function getCachedTimestamp($_template)
    {
        $cacheContent = apc_fetch($this->getCachedFilepath($_template));
        return $cacheContent !== false ? $cacheContent[1] : false;
    }

    /**
     * Returns the cached template output
     *
     * @param object $_template current template
     * @return string |booelan the template content or false if the file does not exist
     */
    public function getCachedContents($_template, $no_render = false)
	{
	    if (!$no_render) {
            ob_start();
        }
        $_cache_content = apc_fetch($this->getCachedFilepath($_template));
        $_smarty_tpl = $_template; // used in the cached template code
        eval("?>" . $_cache_content[0]);
        if ($no_render) {
            return null;
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Writes the rendered template output to cache file
     *
     * @param object $_template current template
     * @return boolean status
     */
    public function writeCachedContent($_template, $content)
    {
        return apc_store($this->getCachedFilepath($_template), array($content, time(), $_template->cache_lifetime), $_template->cache_lifetime);
    }

    /**
     * Empty cache folder
     *
     * @param integer $exp_time expiration time
     * @return integer number of cache files deleted
     */
    public function clearAll($exp_time = null)
    {
        return apc_clear_cache('user');
    }

    /**
     * Empty cache for a specific template
     *
     * @param string $resource_name template name
     * @param string $cache_id cache id
     * @param string $compile_id compile id
     * @param integer $exp_time expiration time
     * @return integer number of cache files deleted
     */
    public function clear($resource_name, $cache_id, $compile_id, $exp_time)
    {
        return apc_delete(md5($resource_name.$cache_id.$compile_id));
    }
}