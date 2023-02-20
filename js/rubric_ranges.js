M.gradingform_rubric_ranges = { 'name' : null, 'Y' : null};

/**
 * This function is called for each rubric on page.
 */
M.gradingform_rubric_ranges.init = function(Y, options) {
    M.gradingform_rubric_ranges.name = options.name;
    M.gradingform_rubric_ranges.sortlevelsasc = options.sortlevelsasc;
    M.gradingform_rubric_ranges.Y = Y
    Y.on('click', M.gradingform_rubric_ranges.levelclick, '#rubric-'+options.name+' .level', null, Y, options.name);
    // Capture also space and enter keypress.
    Y.on('key', M.gradingform_rubric_ranges.levelclick, '#rubric-' + options.name + ' .level', 'space', Y, options.name);
    Y.on('key', M.gradingform_rubric_ranges.levelclick, '#rubric-' + options.name + ' .level', 'enter', Y, options.name);

    Y.all('#rubric-'+options.name+' .radio').setStyle('display', 'none')
    Y.all('#rubric-'+options.name+' .level').each(function (node) {
      if (node.one('input[type=radio]').get('checked')) {
        node.addClass('checked');
      }
    });

    Y.all('#rubric-'+options.name+' .points').each( function(node) {
        if (node.one('select')) {
            node.one('select').on('change', function (e) {
                var el = e.target
                var gradepoints = parseInt(el.get('value'));
                var maxpoints = M.gradingform_rubric_ranges.getmaxpoints(el);

                if (gradepoints > maxpoints || isNaN(gradepoints)) {
                    el.set('value', '');
                }
                M.gradingform_rubric_ranges.selectrange(e);
            });
        }
    });
};

M.gradingform_rubric_ranges.selectrange = function(e) {
  var el = e.target
  var Y = M.gradingform_rubric_ranges.Y

  var gradepoints = parseInt(el.get('value'));
  if (!isNaN(gradepoints)) {
    var range = [];
    el.get('parentNode').get('previousSibling').all('.scorevalue').each(function (node) {
      range = node.get('innerHTML').split(' to ');
      if (gradepoints >= M.gradingform_rubric_ranges.bottom_range(range) && gradepoints <= M.gradingform_rubric_ranges.top_range(range)) {
        if (!node.ancestor('td .level').hasClass('checked')) {
          node.ancestor('td .level').simulate('click')
        }
      }
    })
  } else {
      el.get('parentNode').get('previousSibling').all('.scorevalue').each(function (node) {
          if (node.ancestor('td .level').hasClass('checked')) {
              node.ancestor('td .level').simulate('click')
          }
      })
  }
}

M.gradingform_rubric_ranges.bottom_range = function(range) {
    // If levels sorted ascending by number of points, then bottom rage is first value (range is 6 to 10 points).
    // Otherwise it's the second value (range is  10 to 6 points).
    if (M.gradingform_rubric_ranges.sortlevelsasc) {
        return range[0];
    } else {
        return range[1]
    }
}

M.gradingform_rubric_ranges.top_range = function(range) {
    // If levels sorted ascending by number of points, then top rage is the second value (range is 6 to 10 points).
    // Otherwise it's the first value (range is 10 to 6 points).
    if (M.gradingform_rubric_ranges.sortlevelsasc) {
        return range[1];
    } else {
        return range[0]
    }
}

M.gradingform_rubric_ranges.getmaxpoints = function(el) {
  // Get max value point.
  var maxpointsparts = el.get("nextSibling").get('innerHTML').split(' ');
  var maxpoints = 0;
  maxpointsparts.forEach(function (item, index) {
    if (!isNaN(item)) {
      maxpoints = parseInt(item);
    }
  });
  return maxpoints;
}

M.gradingform_rubric_ranges.levelclick = function(e) {
    var el = e.target
    while (el && !el.hasClass('level')) el = el.get('parentNode')
    if (!el) return
    e.preventDefault();
    el.siblings().removeClass('checked');

    var gradepoints = '';
    if (el.ancestor('.levels').get("nextSibling").one('select')) {
        gradepoints = el.ancestor('.levels').get("nextSibling").one('select').get('value');
    }

    var needupdategradepoints = 1
    // This means it was simulated click or grade was reset to nothing.
    if (e.clientX == 0 && e.clientY == 0 && gradepoints != '') {
        needupdategradepoints = 0;
    }
    var ranges = el.one('.scorevalue').get('innerHTML').split(' to ');
    // Set aria-checked attribute for siblings to false.
    el.siblings().setAttribute('aria-checked', 'false');
    chb = el.one('input[type=radio]')
    if (!chb.get('checked')) {
        chb.set('checked', true)
        el.addClass('checked')
        // Set aria-checked attribute to true if checked.
        el.setAttribute('aria-checked', 'true');
        // if direct range is selected, grade lower value.
        if (needupdategradepoints) {
            if (el.ancestor('.levels').get("nextSibling").one('select')) {
                el.ancestor('.levels').get("nextSibling").one('select').set('value', M.gradingform_rubric_ranges.bottom_range(ranges))
            }
        }
    } else {
        el.removeClass('checked');
        // Set aria-checked attribute to false if unchecked.
        el.setAttribute('aria-checked', 'false');
        el.get('parentNode').all('input[type=radio]').set('checked', false)
        // if direct range is selected, grade lower value.
        if (needupdategradepoints) {
            if (el.ancestor('.levels').get("nextSibling").one('select')) {
                el.ancestor('.levels').get("nextSibling").one('select').set('value','')
            }
        }
    }
}
