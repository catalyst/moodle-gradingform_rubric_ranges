# Ranged Rubrics

<a href="https://github.com/catalyst/moodle-gradingform_rubric_ranges/actions">
<img src="https://github.com/catalyst/moodle-gradingform_rubric_ranges/workflows/ci/badge.svg">
</a>

The ranged rubrics advanced grading method is based on the Moodle core Rubrics grading method but 
adding support for ranged values.

## Branches

| Moodle version   | Branch                                                                                                   |
|------------------|----------------------------------------------------------------------------------------------------------|
| Moodle 4.2+      | [MOODLE_402_STABLE](https://github.com/catalyst/moodle-gradingform_rubric_ranges/tree/MOODLE_402_STABLE) |
| Moodle 3.9 - 4.1 | [MOODLE_39_STABLE](https://github.com/catalyst/moodle-gradingform_rubric_ranges/tree/MOODLE_39_STABLE)   |

# Installation

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/grade/grading/form/rubric_ranges

Afterwards, log in to your Moodle site as an admin and go to _Site administration > Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

# Warm thanks #

Thanks to Monash University (https://www.monash.edu) for funding the development of this plugin.

![Monash University](pix/monash-logo-mono.svg?raw=true)

# License #

2023 Catalyst IT

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
