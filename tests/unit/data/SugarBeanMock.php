<?php

/**
 * Created by PhpStorm.
 * User: gyula
 * Date: 01/08/17
 * Time: 12:39
 */
class SugarBeanMock extends SugarBean
{

    public function __construct()
    {
        global $dictionary;
        static $loaded_definitions = array();
        $this->db = DBManagerFactory::getInstance();
        if (empty($this->module_name)) {
            $this->module_name = $this->module_dir;
        }
        if ((!$this->disable_vardefs && empty($loaded_definitions[$this->object_name])) || !empty($GLOBALS['reload_vardefs'])) {
            VardefManager::loadVardef($this->module_dir, $this->object_name);

            // build $this->column_fields from the field_defs if they exist
            if (!empty($dictionary[$this->object_name]['fields'])) {
                foreach ($dictionary[$this->object_name]['fields'] as $key => $value_array) {
                    $column_fields[] = $key;
                    if (!empty($value_array['required']) && !empty($value_array['name'])) {
                        $this->required_fields[$value_array['name']] = 1;
                    }
                }
                $this->column_fields = $column_fields;
            }

            //setup custom fields
            if (!isset($this->custom_fields) &&
                empty($this->disable_custom_fields)
            ) {
                $this->setupCustomFields($this->module_dir);
            }

            if (isset($GLOBALS['dictionary'][$this->object_name]) && !$this->disable_vardefs) {
                $this->field_name_map = $dictionary[$this->object_name]['fields'];
                $this->field_defs = $dictionary[$this->object_name]['fields'];

                if (!empty($dictionary[$this->object_name]['optimistic_locking'])) {
                    $this->optimistic_lock = true;
                }
            }
            $loaded_definitions[$this->object_name]['column_fields'] =& $this->column_fields;
            $loaded_definitions[$this->object_name]['list_fields'] =& $this->list_fields;
            $loaded_definitions[$this->object_name]['required_fields'] =& $this->required_fields;
            $loaded_definitions[$this->object_name]['field_name_map'] =& $this->field_name_map;
            $loaded_definitions[$this->object_name]['field_defs'] =& $this->field_defs;
        } else {
            $this->column_fields =& $loaded_definitions[$this->object_name]['column_fields'];
            $this->list_fields =& $loaded_definitions[$this->object_name]['list_fields'];
            $this->required_fields =& $loaded_definitions[$this->object_name]['required_fields'];
            $this->field_name_map =& $loaded_definitions[$this->object_name]['field_name_map'];
            $this->field_defs =& $loaded_definitions[$this->object_name]['field_defs'];
            $this->added_custom_field_defs = true;

            if (!isset($this->custom_fields) &&
                empty($this->disable_custom_fields)
            ) {
                $this->setupCustomFields($this->module_dir);
            }
            if (!empty($dictionary[$this->object_name]['optimistic_locking'])) {
                $this->optimistic_lock = true;
            }
        }

        if ($this->bean_implements('ACL') && !empty($GLOBALS['current_user'])) {
            $this->acl_fields = !(isset($dictionary[$this->object_name]['acl_fields']) && $dictionary[$this->object_name]['acl_fields'] === false);
        }
        $this->populateDefaultValues();
    }

    /**
     * @param $value
     * @param bool $time
     * @return string
     */
    public function publicParseDateDefault($value, $time = false) {
        return $this->parseDateDefault($value, $time);
    }

}