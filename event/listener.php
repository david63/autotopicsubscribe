<?php
/**
*
* @package Auto Subscribe New Topic Extension
* @copyright (c) 2016 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\autotopicsubscribe\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var driver_interface */
	protected $db;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/**
	* Constructor for listener
	*
	* @param driver_interface	$db
	* @param request			$request	Request object
	* @param template			$template	Template object
	* @param user               $user		User object
	*
	* @access public
	*/
	public function __construct(driver_interface $db, request $request, template $template, user $user)
	{
		$this->db		= $db;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.submit_post_end'				=> 'auto_subscribe',
			'core.ucp_prefs_post_data'			=> 'add_user_prefs',
			'core.ucp_prefs_post_update_data'	=> 'update_user_prefs',
		);
	}

	/**
	* Add the necessay entries to the database
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function auto_subscribe($event)
	{
		if ($this->user->data['user_subscribe_topic'] && $event['data']['topic_first_post_id'] == 0)
		{
			$sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . ' (user_id, topic_id)
				VALUES (' . (int) $event['data']['poster_id'] . ', ' . (int) $event['data']['topic_id'] . ')';
			$this->db->sql_query($sql);
		}
	}

	/**
	* Add the necessay variables
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_user_prefs($event)
	{
		$data = $event['data'];

		$data = array_merge($data, array(
			'user_subscribe_topic' => $this->request->variable('user_subscribe_topic', (!empty($user->data['user_subscribe_topic'])) ? $user->data['user_subscribe_topic'] : 0),
		));

		$event->offsetSet('data', $data);

		$this->template->assign_vars(array(
			'S_SUBSCRIBE_TOPIC'	=> $this->user->data['user_subscribe_topic'],
		));
	}

	/**
	* Update the sql data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function update_user_prefs($event)
	{
		$sql_ary	= $event['sql_ary'];
		$data		= $event['data'];

		$sql_ary = array_merge($sql_ary, array(
			'user_subscribe_topic'	=> $data['user_subscribe_topic'],
		));

		$event->offsetSet('sql_ary', $sql_ary);
	}
}
