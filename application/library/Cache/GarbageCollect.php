<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Garbage Collection interface for caches that have no GC methods
 * of their own, such as [Cache_File] and [Cache_Sqlite]. Memory based
 * cache systems clean their own caches periodically.
 *
 * @package    Elixir/Cache
 * @category   Base
 * @version    2.0
 * @author     Elixir Team
 * @copyright  (c) 2009-2012 Elixir Team
 * @license    http://Elixirphp.com/license
 * @since      3.0.8
 */
interface Cache_GarbageCollect {
	/**
	 * Garbage collection method that cleans any expired
	 * cache entries from the cache.
	 *
	 * @return void
	 */
	public function garbage_collect();
}
