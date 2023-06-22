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
 * Grading method controller for the Rubric plugin
 *
 * @package    gradingform_rubric_ranges
 * @copyright  2022 Heena Agheda <heenaagheda@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_rubric_ranges;

defined('MOODLE_INTERNAL') || die();

global $CFG, $SITE;

require_once($CFG->libdir.'/pdflib.php');

/**
 * Extending pdf class to customise it.
 */
class printpdf extends \pdf {
    // @codingStandardsIgnoreStart

    /**
     * Define header.
     */
    public function Header() {
        $this->SetY(15);
        $this->SetFont('helvetica', 'I', 8);
        $text = $this->getHeaderData()['title'];
        $this->Cell(0, 15, $text, 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    /**
     * Define footer.
     */
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/'
            . $this->getAliasNbPages(), 0, false, 'C', 0, '' , 0, false, 'T', 'M');
    }

    // @codingStandardsIgnoreEnd
}
