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
 * gradingform_rubric_ranges plugin upgrade code.
 *
 * @package    gradingform_rubric_ranges
 * @author     Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @copyright  2023 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion
 * @return bool always true
 */
function xmldb_gradingform_rubric_ranges_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023071200) {
        // Rename table for gform_rubric_ranges_criteria.
        $table = new xmldb_table('gform_rubric_ranges_criteria');
        // Conditionally launch rename table for gform_rubric_ranges_criteria.
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'gradingform_rubric_ranges_c');
        }

        // Rename table for gform_rubric_ranges_levels.
        $table = new xmldb_table('gform_rubric_ranges_levels');
        // Conditionally launch rename table for gform_rubric_ranges_levels.
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'gradingform_rubric_ranges_l');
        }

        // Rename table for gform_rubric_ranges_fillings.
        $table = new xmldb_table('gform_rubric_ranges_fillings');
        // Conditionally launch rename table for gform_rubric_ranges_fillings.
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'gradingform_rubric_ranges_f');
        }

        // Coursemigration savepoint reached.
        upgrade_plugin_savepoint(true, 2023071200, 'gradingform', 'rubric_ranges');
    }

    return true;
}
