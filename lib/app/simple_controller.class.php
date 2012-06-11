<?php
namespace app;

class simple_controller extends controller {

    // read filter input and filter session
    // update session filter values and
    // return the current filter values
    protected function _filter ($context) {
        $filter     = $this->_input['filter'];
        $filters    = $this->_session->get($context, 'filter');
        if ($filters === null) {
            $filters = [];
        }
        if ($filter) {
            list($action, $type, $value) = explode(':', $filter);
            switch ($action) {
                case 'add':
                    if (!array_key_exists($type, $filters) ||
                        array_search($value, $filters[$type]) === false) {
                        $filters[$type][] = $value;
                    }
                break;

                case 'del':
                    if ($value == 'all') {
                        if (array_key_exists($type, $filters)) {
                            unset($filters[$type]);
                        }
                    }
                    else {
                        if (array_key_exists($type, $filters)) {
                            $key = array_search($value, $filters[$type]);
                            if ($key !== false &&
                                array_key_exists($key, $filters[$type])) {
                                unset($filters[$type][$key]);
                            }
                            if (count($filters[$type]) == 0) {
                                unset($filters[$type]);
                            }
                        }
                    }
                break;
            }
        }
        $this->_session->set($context, 'filter', $filters);
        return $filters;
    }
}
