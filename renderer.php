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
 * Contains renderer used for displaying rubric
 *
 * @package    gradingform_rubric_ranges
 * @copyright  2011 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_rubric_ranges_renderer extends plugin_renderer_base {

    /**
     * This function returns html code for displaying criterion. Depending on $mode it may be the
     * code to edit rubric, to preview the rubric, to evaluate somebody or to review the evaluation.
     *
     * This function may be called from display_rubric() to display the whole rubric, or it can be
     * called by itself to return a template used by JavaScript to add new empty criteria to the
     * rubric being designed.
     * In this case it will use macros like {NAME}, {LEVELS}, {CRITERION-id}, etc.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode.
     * @param array $options display options for this rubric, defaults are:
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param array|null $criterion criterion data
     * @param string $levelsstr evaluated templates for this criterion levels
     * @param array|null $value (only in view mode) teacher's feedback on this criterion
     * @return string
     */
    public function criterion_template($mode, $options, $elementname = '{NAME}',
        $criterion = null, $levelsstr = '{LEVELS}', $value = null) {
        // TODO MDL-31235 description format, remark format.
        if ($criterion === null || !is_array($criterion) || !array_key_exists('id', $criterion)) {
            $criterion = array(
                'id' => '{CRITERION-id}',
                'description' => '{CRITERION-description}',
                'sortorder' => '{CRITERION-sortorder}',
                'isranged' => '{CRITERION-isranged}',
                'class' => '{CRITERION-class}'
            );
        } else {
            foreach (array('sortorder', 'description', 'isranged', 'class') as $key) {
                // Set missing array elements to empty strings to avoid warnings.
                if (!array_key_exists($key, $criterion)) {
                    $criterion[$key] = '';
                }
            }
        }

        $criteriontemplate = html_writer::start_tag('tr', array(
                    'class' => 'criterion'. $criterion['class'],
                    'id' => '{NAME}-criteria-{CRITERION-id}'));
        $controlstemplate = '';
        $rangedchktemplate = '';
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $controlstemplate .= html_writer::start_tag('div', array('class' => 'controls'));
            foreach (array('moveup', 'delete', 'movedown', 'duplicate') as $key) {
                $value = get_string('criterion'.$key, 'gradingform_rubric_ranges');
                $button = html_writer::empty_tag('input', array(
                    'type' => 'submit', 'name' => '{NAME}[criteria][{CRITERION-id}]['.$key.']',
                    'id' => '{NAME}-criteria-{CRITERION-id}-'.$key, 'value' => $value));
                $controlstemplate .= html_writer::tag('div', $button, array('class' => $key));
            }
            $controlstemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                                        'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]',
                                                                        'value' => $criterion['sortorder']));
            $controlstemplate .= html_writer::end_tag('div'); // Controls.

            // Criterion description text area.
            $descriptiontextareaparams = array(
                'name' => '{NAME}[criteria][{CRITERION-id}][description]',
                'id' => '{NAME}-criteria-{CRITERION-id}-description',
                'aria-label' => get_string('criterion', 'gradingform_rubric_ranges', ''),
                'cols' => '10', 'rows' => '5'
            );
            $description = html_writer::tag('textarea', s($criterion['description']), $descriptiontextareaparams);

            $rangedchktemplate = html_writer::start_tag('div', array('class' => 'isranged'));
            $israngedattrs['name'] = '{NAME}[criteria][{CRITERION-id}][isranged]';
            $israngedattrs['id'] = '{NAME}-criteria-{CRITERION-id}-isranged';
            $israngedattrs['type'] = 'checkbox';

            if ($criterion['isranged'] == 1) {
                $israngedattrs['checked'] = 'checked';
                $israngedattrs['value'] = '1';
            }

            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN
                    || $mode == gradingform_rubric_ranges_controller::DISPLAY_PREVIEW) {
                $israngedattrs['disabled'] = 'disabled';
                unset($israngedattrs['name']);
                // Id should be different then the actual input added later.
                $israngedattrs['id'] .= '_disabled';
            }
            $rangedchktemplate .= html_writer::empty_tag('input', $israngedattrs);
            $rangedchktemplate .= html_writer::tag('label',
                get_string('range', 'gradingform_rubric_ranges'),
                array('for' => $israngedattrs['id']));

            $rangedchktemplate .= html_writer::end_tag('div'); // Range checkbox.

        } else {
            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]',
                    'value' => $criterion['sortorder']));
                $criteriontemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][description]',
                    'value' => $criterion['description']));
                $criteriontemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][isranged]',
                    'value' => $criterion['isranged']));
            }
            $description = s($criterion['description']);
        }

        $description .= $controlstemplate;

        $descriptionclass = 'description';
        if (isset($criterion['error_description'])) {
            $descriptionclass .= ' error';
        }

        // Description cell params.
        $descriptiontdparams = array(
            'class' => $descriptionclass,
            'id' => '{NAME}-criteria-{CRITERION-id}-description-cell'
        );
        if ($mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN) {
            // Set description's cell as tab-focusable.
            $descriptiontdparams['tabindex'] = '0';
            // Set label for the criterion cell.
            $descriptiontdparams['aria-label'] = get_string('criterion', 'gradingform_rubric_ranges', s($criterion['description']));
        }

        // Description cell.
        $criteriontemplate .= html_writer::tag('td', $rangedchktemplate.$description, $descriptiontdparams);

        // Levels table.
        $levelsrowparams = array('id' => '{NAME}-criteria-{CRITERION-id}-levels');
        if ($mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $levelsrowparams['role'] = 'radiogroup';
        }
        $levelsrow = html_writer::tag('tr', $levelsstr, $levelsrowparams);

        $levelstableparams = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-table',
            'aria-label' => get_string('levelsgroup', 'gradingform_rubric_ranges')
        );
        $levelsstrtable = html_writer::tag('table', $levelsrow, $levelstableparams);
        $levelsclass = 'levels';
        if (isset($criterion['error_levels'])) {
            $levelsclass .= ' error';
        }

        $criteriontemplate .= html_writer::tag('td', $levelsstrtable, array('class' => $levelsclass));

        $remarktemplate = '';
        $gradetemplate = '';
        $pointstemplate = '';
        $displayremark = ($options['enableremarks']
            && ($mode != gradingform_rubric_ranges_controller::DISPLAY_VIEW || $options['showremarksstudent']));
        if ($displayremark) {
            $currentremark = '';
            if (isset($value['remark'])) {
                $currentremark = $value['remark'];
            }
            $currentgrade = (isset($value['grade']) ? $value['grade'] : 0);
            // Label for criterion remark.
            $remarkinfo = new stdClass();
            $remarkinfo->description = s($criterion['description']);
            $remarkinfo->remark = $currentremark;
            $remarklabeltext = get_string('criterionremark', 'gradingform_rubric_ranges', $remarkinfo);

            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL) {
                if ($criterion['isranged']) {
                    $gradepoints = $value['grade'] ?? '';
                    $gradetemplate = html_writer::select(
                        range(0, $criterion['points']),
                        '{NAME}[criteria][{CRITERION-id}][grade]',
                        $gradepoints,
                        ['' => 'choosedots'],
                        ['id' => '{NAME}-criteria-{CRITERION-id}-grade']
                    );

                }
                // HTML parameters for remarks text area.
                $remarkparams = array(
                    'name' => '{NAME}[criteria][{CRITERION-id}][remark]',
                    'id' => '{NAME}-criteria-{CRITERION-id}-remark',
                    'cols' => '10', 'rows' => '5',
                    'aria-label' => $remarklabeltext
                );
                $input = html_writer::tag('textarea', s($currentremark), $remarkparams);
                $remarktemplate .= html_writer::tag('div', $input, array('class' => 'remark'));
            } else if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN) {
                $gradetemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][grade]',
                    'value' => $currentgrade));
                $remarktemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][remark]',
                    'value' => $currentremark));
            } else if ($mode == gradingform_rubric_ranges_controller::DISPLAY_REVIEW
                || $mode == gradingform_rubric_ranges_controller::DISPLAY_VIEW) {
                if ($criterion['isranged']) {
                    $gradeparams = array(
                        'id' => '{NAME}-criteria-{CRITERION-id}-grade',
                        'class' => 'inline',
                    );
                    $gradetemplate .= html_writer::tag('div',
                        $currentgrade. ' '.get_string('pts', 'gradingform_rubric_ranges'),
                        $gradeparams);
                }
                // HTML parameters for remarks cell.
                $remarkparams = array(
                    'class' => 'remark',
                    'tabindex' => '0',
                    'id' => '{NAME}-criteria-{CRITERION-id}-remark',
                    'aria-label' => $remarklabeltext
                );
                $remarktemplate .= html_writer::tag('div', s($currentremark), $remarkparams);
            }
        }

        $displaygradeinput = !$options['enableremarks'] && $mode != gradingform_rubric_ranges_controller::DISPLAY_VIEW;
        if ($displaygradeinput) {
            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL) {
                if ($criterion['isranged']) {
                    $gradepoints = $value['grade'] ?? '';
                    $gradetemplate = html_writer::select(
                        range(0, $criterion['points']),
                        '{NAME}[criteria][{CRITERION-id}][grade]',
                        $gradepoints,
                        ['' => 'choosedots'],
                        ['id' => '{NAME}-criteria-{CRITERION-id}-grade']
                    );
                }
            } else if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN) {

                $gradetemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][grade]',
                    'value' => $currentgrade));
            }
        }

        $pointstemplate = $gradetemplate;
        $pointstemplate .= html_writer::start_tag('div',
            array('class' => 'inline', 'id' => '{NAME}-criteria-{CRITERION-id}-points'));
        if ($criterion['isranged']) {
            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL) {
                $pointstemplate .= ' / ';
                $pointstemplate .= isset($criterion['points']) ? $criterion['points'] : 0;
                $pointstemplate .= ' '.get_string('pts', 'gradingform_rubric_ranges');
            }
        }
        $pointstemplate .= html_writer::end_tag('div'); // Points.
        $pointstemplate .= $remarktemplate;
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('criterionaddlevel', 'gradingform_rubric_ranges');
            $button = html_writer::empty_tag('input',
                array('type' => 'submit', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][addlevel]',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-addlevel', 'value' => $value, 'class' => 'btn btn-secondary'));
            $pointstemplate .= html_writer::tag('div', $button, array('class' => 'addlevel'));
        }
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $criteriontemplate .= html_writer::tag('td', $pointstemplate, array('class' => 'addlevel'));
        }
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_VIEW
            || $mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL) {
            $criteriontemplate .= html_writer::tag('td', $pointstemplate, array('class' => 'points'));
        }
        $criteriontemplate .= html_writer::end_tag('tr'); // Criterion.
        $criteriontemplate = str_replace('{NAME}', $elementname, $criteriontemplate);
        $criteriontemplate = str_replace('{CRITERION-id}', $criterion['id'], $criteriontemplate);
        return $criteriontemplate;
    }

    /**
     * This function returns html code for displaying one level of one criterion. Depending on $mode
     * it may be the code to edit rubric, to preview the rubric, to evaluate somebody or to review the evaluation.
     *
     * This function may be called from display_rubric() to display the whole rubric, or it can be
     * called by itself to return a template used by JavaScript to add new empty level to the
     * criterion during the design of rubric.
     * In this case it will use macros like {NAME}, {CRITERION-id}, {LEVEL-id}, etc.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode.
     * @param array $options display options for this rubric.
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param string|int $criterionid either id of the nesting criterion or a macro for template
     * @param array|null $level level data,
     * also in view mode it might also have property $level['checked'] whether this level is checked
     * @return string
     */
    public function level_template($mode, $options, $elementname = '{NAME}', $criterionid = '{CRITERION-id}', $level = null) {
        // TODO MDL-31235 definition format.
        if (!isset($level['id'])) {
            $level = array('id' => '{LEVEL-id}',
                'definition' => '{LEVEL-definition}',
                'score' => '{LEVEL-score}',
                'class' => '{LEVEL-class}',
                'checked' => false);
        } else {
            foreach (array('score', 'definition', 'class', 'checked', 'index') as $key) {
                // Set missing array elements to empty strings to avoid warnings.
                if (!array_key_exists($key, $level)) {
                    $level[$key] = '';
                }
            }
        }

        // Get level index.
        $levelindex = isset($level['index']) ? $level['index'] : '{LEVEL-index}';

        // Template for one level within one criterion.
        $tdattributes = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}',
            'class' => 'level' . $level['class']
        );
        if (isset($level['tdwidth'])) {
            $tdattributes['width'] = round($level['tdwidth']).'%';
        }

        $leveltemplate = html_writer::start_tag('div', array('class' => 'level-wrapper'));
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $definitionparams = array(
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]',
                'aria-label' => get_string('leveldefinition', 'gradingform_rubric_ranges', $levelindex),
                'cols' => '10', 'rows' => '4'
            );
            $definition = html_writer::tag('textarea', s($level['definition']), $definitionparams);

            $scoreparams = array(
                'type' => 'text',
                'id' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'aria-label' => get_string('scoreinputforlevel', 'gradingform_rubric_ranges', $levelindex),
                'size' => '3',
                'value' => $level['score']
            );
            $score = html_writer::empty_tag('input', $scoreparams);
        } else {
            if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN) {
                $leveltemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]',
                    'value' => $level['definition']));
                $leveltemplate .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                    'value' => $level['score']));
            }
            $definition = s($level['definition']);
            $score = $level['score'];
        }
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL) {
            $levelradioparams = array(
                'type' => 'radio',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}[criteria][{CRITERION-id}][levelid]',
                'value' => $level['id']
            );
            if ($level['checked']) {
                $levelradioparams['checked'] = 'checked';
            }
            $input = html_writer::empty_tag('input', $levelradioparams);
            $leveltemplate .= html_writer::div($input, 'radio');
        }
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN && $level['checked']) {
            $leveltemplate .= html_writer::empty_tag('input',
                array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][levelid]',
                    'value' => $level['id']
                )
            );
        }
        $score = html_writer::tag('span', $score, array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-score',
            'class' => 'scorevalue'));
        $definitionclass = 'definition';
        if (isset($level['error_definition'])) {
            $definitionclass .= ' error';
        }

        if ($mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN) {

            $tdattributes['tabindex'] = '0';
            $levelinfo = new stdClass();
            $levelinfo->definition = s($level['definition']);
            $levelinfo->score = $level['score'];
            $tdattributes['aria-label'] = get_string('level', 'gradingform_rubric_ranges', $levelinfo);

            if ($mode != gradingform_rubric_ranges_controller::DISPLAY_PREVIEW &&
                $mode != gradingform_rubric_ranges_controller::DISPLAY_PREVIEW_GRADED) {
                // Add role of radio button to level cell if not in edit and preview mode.
                $tdattributes['role'] = 'radio';
                if ($level['checked']) {
                    $tdattributes['aria-checked'] = 'true';
                } else {
                    $tdattributes['aria-checked'] = 'false';
                }
            }
        }

        $leveltemplateparams = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition-container'
        );
        $leveltemplate .= html_writer::div($definition, $definitionclass, $leveltemplateparams);
        $displayscore = true;
        if (!$options['showscoreteacher']
            && in_array($mode, array(
                gradingform_rubric_ranges_controller::DISPLAY_EVAL,
                gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN,
                gradingform_rubric_ranges_controller::DISPLAY_REVIEW,
                gradingform_rubric_ranges_controller::DISPLAY_PRINT))) {
            $displayscore = false;
        }
        if (!$options['showscorestudent']
            && in_array($mode, array(gradingform_rubric_ranges_controller::DISPLAY_VIEW,
                gradingform_rubric_ranges_controller::DISPLAY_PREVIEW_GRADED,
                gradingform_rubric_ranges_controller::DISPLAY_PRINT))) {
            $displayscore = false;
        }

        if ($displayscore) {
            $scoreclass = 'score';
            if (isset($level['error_score'])) {
                $scoreclass .= ' error';
            }
            $leveltemplate .= html_writer::tag('div',
                get_string('scorepostfix', 'gradingform_rubric_ranges', $score),
                array('class' => $scoreclass));
        }
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('leveldelete', 'gradingform_rubric_ranges', $levelindex);
            $buttonparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][delete]',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-delete',
                'value' => $value
            );
            $button = html_writer::empty_tag('input', $buttonparams);
            $leveltemplate .= html_writer::tag('div', $button, array('class' => 'delete'));
        }
        $leveltemplate .= html_writer::end_tag('div'); // Level-wrapper.

        $leveltemplate = html_writer::tag('td', $leveltemplate, $tdattributes); // The .level cell.

        $leveltemplate = str_replace('{NAME}', $elementname, $leveltemplate);
        $leveltemplate = str_replace('{CRITERION-id}', $criterionid, $leveltemplate);
        $leveltemplate = str_replace('{LEVEL-id}', $level['id'], $leveltemplate);
        return $leveltemplate;
    }

    /**
     * This function returns html code for displaying rubric template (content before and after
     * criteria list). Depending on $mode it may be the code to edit rubric, to preview the rubric,
     * to evaluate somebody or to review the evaluation.
     *
     * This function is called from display_rubric() to display the whole rubric.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode.
     * @param array $options display options for this rubric.
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param string $criteriastr evaluated templates for this rubric's criteria
     * @return string
     */
    protected function rubric_ranges_template($mode, $options, $elementname, $criteriastr) {
        $classsuffix = ''; // CSS suffix for class of the main div. Depends on the mode.
        switch ($mode) {
            case gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL:
                $classsuffix = ' editor editable';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN:
                $classsuffix = ' editor frozen';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_PREVIEW:
            case gradingform_rubric_ranges_controller::DISPLAY_PREVIEW_GRADED:
                $classsuffix = ' editor preview';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_EVAL:
                $classsuffix = ' evaluate editable';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN:
                $classsuffix = ' evaluate frozen';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_REVIEW:
                $classsuffix = ' review';
                break;
            case gradingform_rubric_ranges_controller::DISPLAY_VIEW:
                $classsuffix = ' view';
                break;
        }

        $rubrictemplate = html_writer::start_tag('div',
            array('id' => 'rubric-{NAME}',
                'class' => 'clearfix gradingform_rubric_ranges'.$classsuffix));

        // Rubric table.
        $rubrictableparams = array(
            'class' => 'criteria',
            'id' => '{NAME}-criteria',
            'aria-label' => get_string('rubric', 'gradingform_rubric_ranges'));

        $rubrictable = html_writer::tag('table', $criteriastr, $rubrictableparams);
        $rubrictemplate .= $rubrictable;
        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('addcriterion', 'gradingform_rubric_ranges');
            $criteriainputparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][addcriterion]',
                'id' => '{NAME}-criteria-addcriterion',
                'value' => $value
            );
            $input = html_writer::empty_tag('input', $criteriainputparams);
            $rubrictemplate .= html_writer::tag('div', $input, array('class' => 'addcriterion btn btn-secondary'));
        }
        $rubrictemplate .= $this->rubric_edit_options($mode, $options);
        $rubrictemplate .= html_writer::end_tag('div');

        return str_replace('{NAME}', $elementname, $rubrictemplate);
    }

    /**
     * Generates html template to view/edit the rubric options. Expression {NAME} is used in
     * template for the form element name
     *
     * @param int $mode rubric display mode.
     * @param array $options display options for this rubric.
     * @return string
     */
    protected function rubric_edit_options($mode, $options) {

        if ($mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL
                && $mode != gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN
                && $mode != gradingform_rubric_ranges_controller::DISPLAY_PREVIEW) {
            // Options are displayed only for people who can manage.
            return;
        }

        $html = html_writer::start_tag('div', array('class' => 'options'));
        $html .= html_writer::tag('div',
            get_string('rubricoptions', 'gradingform_rubric_ranges'), array('class' => 'optionsheading'));
        $attrs = array('type' => 'hidden', 'name' => '{NAME}[options][optionsset]', 'value' => 1);
        foreach ($options as $option => $value) {
            $html .= html_writer::start_tag('div', array('class' => 'option '.$option));
            $attrs = array('name' => '{NAME}[options]['.$option.']', 'id' => '{NAME}-options-'.$option);
            switch ($option) {
                case 'sortlevelsasc':
                    // Display option as dropdown.
                    $html .= html_writer::label(get_string($option, 'gradingform_rubric_ranges'), $attrs['id'], false);
                    $value = (int)(!!$value); // Make sure $value is either 0 or 1.
                    if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FULL) {
                        $selectoptions = array(0 => get_string($option.'0', 'gradingform_rubric_ranges'),
                        1 => get_string($option.'1', 'gradingform_rubric_ranges'));
                        $valuestr = html_writer::select($selectoptions, $attrs['name'], $value, false, array('id' => $attrs['id']));
                        $html .= html_writer::tag('span', $valuestr, array('class' => 'value'));
                    } else {
                        $html .= html_writer::tag('span', get_string($option.$value, 'gradingform_rubric_ranges'),
                            array('class' => 'value'));
                        if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN) {
                            $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                        }
                    }
                    break;
                default:
                    if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN && $value) {
                        // Id should be different then the actual input added later.
                        $attrs['id'] .= '_hidden';
                        $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                    }
                    // Display option as checkbox.
                    $attrs['type'] = 'checkbox';
                    $attrs['value'] = 1;
                    if ($value) {
                        $attrs['checked'] = 'checked';
                    }
                    if ($mode == gradingform_rubric_ranges_controller::DISPLAY_EDIT_FROZEN
                        || $mode == gradingform_rubric_ranges_controller::DISPLAY_PREVIEW) {
                        $attrs['disabled'] = 'disabled';
                        unset($attrs['name']);
                        // Id should be different then the actual input added later.
                        $attrs['id'] .= '_disabled';
                    }
                    $html .= html_writer::empty_tag('input', $attrs);
                    $html .= html_writer::tag('label', get_string($option, 'gradingform_rubric_ranges'),
                        array('for' => $attrs['id']));
                    break;
            }
            if (get_string_manager()->string_exists($option.'_help', 'gradingform_rubric_ranges')) {
                $html .= $this->help_icon($option, 'gradingform_rubric_ranges');
            }
            $html .= html_writer::end_tag('div'); // Option.
        }
        $html .= html_writer::end_tag('div'); // Options.
        return $html;
    }

    /**
     * Displays ranged core.
     *
     * @param string $mode
     * @param array $levels
     * @param string $sortlevels
     * @param bool $isranged
     *
     * @return mixed
     */
    public function display_range_score($mode, $levels, $sortlevels, $isranged = false) {
        if ($isranged) {
            $rangedisplaymodes = array(
                gradingform_rubric_ranges_controller::DISPLAY_REVIEW,
                gradingform_rubric_ranges_controller::DISPLAY_VIEW,
                gradingform_rubric_ranges_controller::DISPLAY_PREVIEW,
                gradingform_rubric_ranges_controller::DISPLAY_EVAL,
                gradingform_rubric_ranges_controller::DISPLAY_PREVIEW_GRADED,
                gradingform_rubric_ranges_controller::DISPLAY_PRINT,
            );

            if (in_array($mode, $rangedisplaymodes)) {
                $levelsonly = array_values($levels);
                $rangecheck = count($levelsonly) - 1;
                if ($sortlevels) {
                    $rangecheck = 0;
                }

                foreach ($levelsonly as $levelkey => $level) {
                    $a = new stdClass();
                    if ($rangecheck == $levelkey) {
                        if ($sortlevels) {
                            $a->rangestart = '0';
                            $a->rangeend = $level['score'];
                            $levels[$level['id']]['score'] = get_string('levelrange', 'gradingform_rubric_ranges', $a);
                        } else {
                            $a->rangestart = $level['score'];
                            $a->rangeend = '0';
                            $levels[$level['id']]['score'] = get_string('levelrange', 'gradingform_rubric_ranges', $a);
                        }
                    } else {
                        if ($sortlevels) {
                            $previousscore = $levelsonly[$levelkey - 1]['score'] + 1;
                            $a->rangestart = $previousscore;
                            $a->rangeend = $level['score'];
                            $levels[$level['id']]['score'] = get_string('levelrange', 'gradingform_rubric_ranges', $a);
                        } else {
                            $nextscore = $levelsonly[$levelkey + 1]['score'] + 1;
                            $a->rangestart = $level['score'];
                            $a->rangeend = $nextscore;
                            $levels[$level['id']]['score'] = get_string('levelrange', 'gradingform_rubric_ranges', $a);
                        }
                    }
                }
                return $levels;
            }
        }
        return $levels;
    }
    /**
     * This function returns html code for displaying rubric. Depending on $mode it may be the code
     * to edit rubric, to preview the rubric, to evaluate somebody or to review the evaluation.
     *
     * It is very unlikely that this function needs to be overriden by theme. It does not produce
     * any html code, it just prepares data about rubric design and evaluation, adds the CSS
     * class to elements and calls the functions level_template, criterion_template and
     * rubric_template
     *
     * @param array $criteria data about the rubric design
     * @param array $options display options for this rubric, defaults are:
     * @param int $mode rubric display mode.
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param array $values evaluation result
     * @return string
     */
    public function display_rubric($criteria, $options, $mode, $elementname = null, $values = null) {
        $criteriastr = '';
        $cnt = 0;
        foreach ($criteria as $id => $criterion) {
            $criterion['class'] = $this->get_css_class_suffix($cnt++, count($criteria) - 1);
            $criterion['id'] = $id;

            $levelsstr = '';
            $levelcnt = 0;
            if (isset($values['criteria'][$id])) {
                $criterionvalue = $values['criteria'][$id];
            } else {
                $criterionvalue = null;
            }
            $index = 1;

            $criterion['levels'] = $this->display_range_score($mode,
                $criterion['levels'], $options['sortlevelsasc'], $criterion['isranged']);
            foreach ($criterion['levels'] as $levelid => $level) {
                $level['id'] = $levelid;
                $level['class'] = $this->get_css_class_suffix($levelcnt++, count($criterion['levels']) - 1);
                $level['checked'] = (isset($criterionvalue['levelid']) && ((int)$criterionvalue['levelid'] === $levelid));
                if ($level['checked'] && ($mode == gradingform_rubric_ranges_controller::DISPLAY_EVAL_FROZEN
                    || $mode == gradingform_rubric_ranges_controller::DISPLAY_REVIEW
                    || $mode == gradingform_rubric_ranges_controller::DISPLAY_VIEW)) {
                    $level['class'] .= ' checked';
                    // In mode DISPLAY_EVAL the class 'checked' will be added by JS if it is enabled.
                    // If JS is not enabled, the 'checked' class will only confuse.
                }
                if (isset($criterionvalue['savedlevelid']) && ((int)$criterionvalue['savedlevelid'] === $levelid)) {
                    $level['class'] .= ' currentchecked';
                }
                $level['tdwidth'] = 100 / count($criterion['levels']);
                $level['index'] = $index;
                $levelsstr .= $this->level_template($mode, $options, $elementname, $id, $level);
                $index++;
            }

            $criteriastr .= $this->criterion_template($mode, $options, $elementname, $criterion, $levelsstr, $criterionvalue);
        }
        return $this->rubric_ranges_template($mode, $options, $elementname, $criteriastr);
    }

    /**
     * Help function to return CSS class names for element (first/last/even/odd) with leading space
     *
     * @param int $idx index of this element in the row/column
     * @param int $maxidx maximum index of the element in the row/column
     * @return string
     */
    protected function get_css_class_suffix($idx, $maxidx) {
        $class = '';
        if ($idx == 0) {
            $class .= ' first';
        }
        if ($idx == $maxidx) {
            $class .= ' last';
        }
        if ($idx % 2) {
            $class .= ' odd';
        } else {
            $class .= ' even';
        }
        return $class;
    }

    /**
     * Displays for the student the list of instances or default content if no instances found
     *
     * @param array $instances array of objects of type gradingform_rubric_ranges_instance
     * @param string $defaultcontent default string that would be displayed without advanced grading
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @return string
     */
    public function display_instances($instances, $defaultcontent, $cangrade) {
        $return = '';
        if (count($instances)) {
            $return .= html_writer::start_tag('div', array('class' => 'advancedgrade'));
            $idx = 0;
            foreach ($instances as $instance) {
                $return .= $this->display_instance($instance, $idx++, $cangrade);
            }
            $return .= html_writer::end_tag('div');
        }
        return $return. $defaultcontent;
    }

    /**
     * Displays one grading instance
     *
     * @param gradingform_rubric_ranges_instance $instance
     * @param int $idx unique number of instance on page
     * @param bool $cangrade whether current user has capability to grade in this context
     */
    public function display_instance(gradingform_rubric_ranges_instance $instance, $idx, $cangrade) {
        $criteria = $instance->get_controller()->get_definition()->rubric_criteria;
        $options = $instance->get_controller()->get_options();
        $values = $instance->get_rubric_filling();
        if ($cangrade) {
            $mode = gradingform_rubric_ranges_controller::DISPLAY_REVIEW;
            $showdescription = $options['showdescriptionteacher'];
        } else {
            $mode = gradingform_rubric_ranges_controller::DISPLAY_VIEW;
            $showdescription = $options['showdescriptionstudent'];
        }
        $output = '';
        if ($showdescription) {
            $output .= $this->box($instance->get_controller()->get_formatted_description(),
                'gradingform_rubric_ranges-description');
        }
        $output .= $this->display_rubric($criteria, $options, $mode, 'rubric'.$idx, $values);
        return $output;
    }

    /**
     * Displays confirmation that students require re-grading
     *
     * @param string $elementname
     * @param int $changelevel
     * @param string $value
     * @return string
     */
    public function display_regrade_confirmation($elementname, $changelevel, $value) {
        $html = html_writer::start_tag('div', array('class' => 'gradingform_rubric_ranges-regrade', 'role' => 'alert'));
        if ($changelevel <= 2) {
            $html .= html_writer::label(get_string('regrademessage1', 'gradingform_rubric_ranges'),
                'menu' . $elementname . 'regrade');
            $selectoptions = array(
                0 => get_string('regradeoption0', 'gradingform_rubric_ranges'),
                1 => get_string('regradeoption1', 'gradingform_rubric_ranges')
            );
            $html .= html_writer::select($selectoptions, $elementname.'[regrade]', $value, false);
        } else {
            $html .= get_string('regrademessage5', 'gradingform_rubric_ranges');
            $html .= html_writer::empty_tag('input', array('name' => $elementname.'[regrade]', 'value' => 1, 'type' => 'hidden'));
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Generates and returns HTML code to display information box about how rubric score is converted to the grade
     *
     * @param array $scores
     * @return string
     */
    public function display_rubric_mapping_explained($scores) {
        $html = '';
        if (!$scores) {
            return $html;
        }
        if ($scores['minscore'] <> 0) {
            $html .= $this->output->notification(get_string('zerolevelsabsent', 'gradingform_rubric_ranges'), 'error');
        }
        $html .= $this->output->notification(get_string('rubricmappingexplained',
            'gradingform_rubric_ranges', (object)$scores), 'info');
        return $html;
    }
}
