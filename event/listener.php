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

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor for listener
	*
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\request\request				$request	Request object
	* @param \phpbb\template\template			$template	Template object
	* @param \phpbb\user                		$user		User object
	*
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
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
