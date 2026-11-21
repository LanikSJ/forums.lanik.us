<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\topicban\notification\type;

/**
 * Notification sent to a user banned from a topic
 */
class topic_ban extends \phpbb\notification\type\base
{
	/** @var array */
	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_TOPICBAN',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	/**
	 * @return string
	 */
	public function get_type()
	{
		return 'stoker.topicban.notification.type.topic_ban';
	}

	/**
	 * @param array $type_data
	 * @return int
	 */
	public static function get_item_id($type_data)
	{
		return (int) $type_data['topic_id'];
	}

	/**
	 * @param array $type_data
	 * @return int
	 */
	public static function get_item_parent_id($type_data)
	{
		return (int) $type_data['forum_id'];
	}

	/**
	 * @return bool
	 */
	public function is_available()
	{
		return true;
	}

	/**
	 * @param array $type_data
	 * @param array $options
	 * @return array
	 */
	public function find_users_for_notification($type_data, $options = [])
	{
		$options = array_merge(['ignore_users' => []], $options);

		return $this->check_user_notification_options([(int) $type_data['user_id']], $options);
	}

	/**
	 * @return array
	 */
	public function users_to_query()
	{
		return [];
	}

	/**
	 * @return string
	 */
	public function get_title()
	{
		$this->language->add_lang('common', 'stoker/topicban');

		$topic_title = censor_text($this->get_data('topic_title'));
		$ban_end = (int) $this->get_data('ban_end');

		if ($ban_end)
		{
			return $this->language->lang('NOTIFICATION_TOPICBAN_UNTIL', $topic_title, $this->user->format_date($ban_end));
		}

		return $this->language->lang('NOTIFICATION_TOPICBAN', $topic_title);
	}

	/**
	 * @return string
	 */
	public function get_reference()
	{
		$reason = (string) $this->get_data('ban_reason');

		return ($reason !== '') ? $this->language->lang('NOTIFICATION_REFERENCE', $reason) : '';
	}

	/**
	 * @return string
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, 't=' . (int) $this->item_id);
	}

	/**
	 * @return bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * @param array $type_data
	 * @param array $pre_create_data
	 * @return void
	 */
	public function create_insert_array($type_data, $pre_create_data = [])
	{
		$this->set_data('topic_title', $type_data['topic_title']);
		$this->set_data('ban_end', (int) $type_data['ban_end']);
		$this->set_data('ban_reason', $type_data['ban_reason']);

		parent::create_insert_array($type_data, $pre_create_data);
	}
}
