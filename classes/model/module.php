<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Jelly Model Module
 *
 * @package   KW-Modules
 * @author	  Kohana-World Development Team
 * @license	  MIT License
 * @copyright 2011 Kohana-World Development Team
 */
class Model_Module extends Jelly_Model {

	public static function initialize(Jelly_Meta $meta)
	{
		$meta
			->fields(array(
			'id'            => new Jelly_Field_Primary,
			'name'          => new Jelly_Field_String,
			'fullname'      => new Jelly_Field_String(array(
				//'unique'            => TRUE,
				'allow_null'        => FALSE,
			)),
			'fullname_lower' => new Jelly_Field_String(array(
				//'unique'            => TRUE,
				'allow_null'        => FALSE,
			)),
			'description'   => new Jelly_Field_Text,
			'url'           => new Jelly_Field_String(array(
				'allow_null'        => FALSE,
			)),
			'homepage'      => new Jelly_Field_String,
			'created_at'    => new Jelly_Field_Timestamp(array(
				'column'        => 'date_create',
				'pretty_format' => 'j M Y',
			)),
			'has_wiki'      => new Jelly_Field_Boolean,
			'has_issues'    => new Jelly_Field_Boolean,
			'has_downloads' => new Jelly_Field_Boolean,
			'developer'     => new Jelly_Field_BelongsTo(array(
				'allow_null'        => FALSE,
			)),
			'info'          => new Jelly_Field_HasOne(array(
				'foreign'           => 'module_info',
			))
		));
	}

	/**
	 * @param  array $data
	 * @return void
	 */
	public function process_crawler(array $data)
	{
		foreach($data as & $module)
		{

			$this->set($module);
			if (isset($module['id']))
			{
				$this->_original['id'] = $module['id'];
				unset($module['id']);
			}

			try {
				$this->save();
				$this->info->set($module)->set('module', $this)->save();
			}
			catch(Validation_Exception $e)
			{
				// @TODO log errors
				echo $e->getMessage();
				die();
			}

			$this->clear();
		}
	}

	/**
	 * @param  array $names
	 * @return array
	 */
	public function get_available(array $names)
	{
		if (empty($names))
		{
			return array();
		}
		$names = array_map('strtolower', $names);

		$meta = $this->_meta;
		return DB::select('id', 'fullname_lower')
				  ->from($meta->table())
				  ->where('fullname_lower', 'IN', $names)
				  ->execute($meta->db())
				  ->as_array('fullname_lower', 'id');
	}

	/**
	 * @param  $fullname
	 * @return Database_Result|object
	 */
	public function find_by_fullname($fullname)
	{
		return Jelly::query($this)->where('fullname', '=', $fullname)->limit(1)->execute($this->_meta->db());
	}

	/**
	 * @param  int|null $limit
	 * @param  int|null $offset
	 * @return Jelly_Collection|Jelly_Model
	 */
	public function get_modules($limit = NULL, $offset = NULL)
	{
		// @TODO add is_active flag etc
		$result = Jelly::query($this);
		if ($limit OR $offset)
		{
			$result->page((int) $limit, (int) $offset);
		}

		return $result->select();
	}

	/**
	 * @return int
	 */
	public function get_count()
	{
		return Jelly::query($this)->count();
	}

}