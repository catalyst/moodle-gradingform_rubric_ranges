<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Support for backup API
 *
 * @package    gradingform_rubric_ranges
 * @copyright  2022 Heena Agheda <heenaagheda@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines rubric backup structures
 *
 * @package    gradingform_rubric_ranges
 * @copyright  2022 Heena Agheda <heenaagheda@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_gradingform_rubric_ranges_plugin extends backup_gradingform_plugin {

    /**
     * Declares rubric structures to append to the grading form definition
     */
    protected function define_definition_plugin_structure() {

        // Append data only if the grand-parent element has 'method' set to 'rubric'.
        $plugin = $this->get_plugin_element(null, '../../method', 'rubric_ranges');

        // Create a visible container for our data.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect our visible container to the parent.
        $plugin->add_child($pluginwrapper);

        // Define our elements.

        $criteria = new backup_nested_element('rubric_ranges_criteria');

        $criterion = new backup_nested_element('rubric_ranges_criterion', array('id'), array(
            'sortorder', 'isranged', 'description', 'descriptionformat'));

        $levels = new backup_nested_element('rubric_ranges_levels');

        $level = new backup_nested_element('rubric_ranges_level', array('id'), array(
            'score', 'definition', 'definitionformat'));

        // Build elements hierarchy.
        $pluginwrapper->add_child($criteria);
        $criteria->add_child($criterion);
        $criterion->add_child($levels);
        $levels->add_child($level);

        // Set sources to populate the data.

        $criterion->set_source_table('gradingform_rubric_ranges_c',
                array('definitionid' => backup::VAR_PARENTID));

        $level->set_source_table('gradingform_rubric_ranges_l',
                array('criterionid' => backup::VAR_PARENTID));

        // No need to annotate ids or files yet (one day when criterion definition supports
        // Embedded files, they must be annotated here).

        return $plugin;
    }

    /**
     * Declares rubric structures to append to the grading form instances
     */
    protected function define_instance_plugin_structure() {

        // Append data only if the ancestor 'definition' element has 'method' set to 'rubric'.
        $plugin = $this->get_plugin_element(null, '../../../../method', 'rubric_ranges');

        // Create a visible container for our data.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect our visible container to the parent.
        $plugin->add_child($pluginwrapper);

        // Define our elements.

        $fillings = new backup_nested_element('rubric_ranges_fillings');

        $filling = new backup_nested_element('rubric_ranges_filling', array('id'), array(
            'criterionid', 'levelid', 'grade', 'remark', 'remarkformat'));

        // Build elements hierarchy.

        $pluginwrapper->add_child($fillings);
        $fillings->add_child($filling);

        // Set sources to populate the data.

        // Binding criterionid to ensure it's existence.
        $filling->set_source_sql('SELECT rf.*
                FROM {gradingform_rubric_ranges_f} rf
                JOIN {grading_instances} gi ON gi.id = rf.instanceid
                JOIN {gradingform_rubric_ranges_c} rc ON rc.id = rf.criterionid AND gi.definitionid = rc.definitionid
                WHERE rf.instanceid = :instanceid',
                array('instanceid' => backup::VAR_PARENTID));

        // No need to annotate ids or files yet (one day when remark field supports.
        // Embedded fileds, they must be annotated here).

        return $plugin;
    }
}
